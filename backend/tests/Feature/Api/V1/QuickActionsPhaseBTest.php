<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowTypeModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function phaseBAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function phaseBCashierToken(): string
{
    return nightposLoginPin('1234');
}

function phaseBSuperToken(): string
{
    return nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
}

it('creates tenant branch and admin transactionally via platform setup', function () {
    $response = test()->postJson('/api/v1/admin/platform/setup', [
        'tenant' => [
            'name' => 'Club Nuevo',
            'slug' => 'club-nuevo',
            'status' => 'active',
            'plan_name' => 'standard',
        ],
        'branch' => [
            'name' => 'Sede Central',
            'code' => 'CENTRAL',
            'status' => 'active',
        ],
        'admin' => [
            'name' => 'Admin Club',
            'username' => 'admin.club',
            'password' => 'ClubAdmin123!',
            'pin' => '9999',
        ],
    ], [
        'Authorization' => 'Bearer '.phaseBSuperToken(),
        'Accept' => 'application/json',
    ])->assertCreated();

    expect($response->json('data.tenant.slug'))->toBe('club-nuevo')
        ->and($response->json('data.branch.code'))->toBe('CENTRAL')
        ->and($response->json('data.admin.username'))->toBe('admin.club');

    expect(TenantModel::query()->where('slug', 'club-nuevo')->exists())->toBeTrue();
});

it('rolls back platform setup when tenant slug is invalid duplicate', function () {
    test()->postJson('/api/v1/admin/platform/setup', [
        'tenant' => ['name' => 'Dup', 'slug' => 'casa-demo'],
        'branch' => ['name' => 'B', 'code' => 'X'],
        'admin' => ['name' => 'A', 'username' => 'dup.admin', 'password' => 'secret12'],
    ], [
        'Authorization' => 'Bearer '.phaseBSuperToken(),
        'Accept' => 'application/json',
    ])->assertStatus(422);

    expect(UserModel::query()->where('username', 'dup.admin')->exists())->toBeFalse();
});

it('denies platform setup to tenant admin', function () {
    test()->postJson('/api/v1/admin/platform/setup', [
        'tenant' => ['name' => 'Hack', 'slug' => 'hack-tenant'],
        'branch' => ['name' => 'B', 'code' => 'H'],
        'admin' => ['name' => 'A', 'username' => 'hack.admin', 'password' => 'secret12'],
    ], nightposOperationalHeaders(phaseBAdminToken()))->assertForbidden();
});

it('quick creates waiter with commission', function () {
    $response = test()->postJson('/api/v1/staff/quick-waiters', [
        'name' => 'Garzón Rápido',
        'pin' => '7890',
        'waiter_commission_percent' => 7.5,
    ], nightposOperationalHeaders(phaseBAdminToken()))->assertCreated();

    expect($response->json('data.waiter.staff_role'))->toBe('WAITER')
        ->and($response->json('data.waiter.waiter_commission_percent'))->toBe('7.50');
});

it('denies waiter quick create without permission', function () {
    test()->postJson('/api/v1/staff/quick-waiters', [
        'name' => 'Garzón Hack',
    ], nightposOperationalHeaders(nightposLoginPin('5678')))->assertForbidden();
});

it('creates and lists show types', function () {
    test()->postJson('/api/v1/show-types', [
        'name' => 'Privado VIP',
        'suggested_price' => 350,
        'status' => 'active',
    ], nightposOperationalHeaders(phaseBAdminToken()))->assertCreated();

    $list = test()->getJson('/api/v1/show-types', nightposOperationalHeaders(phaseBCashierToken()))
        ->assertOk();

    expect(collect($list->json('data.show_types'))->pluck('name'))->toContain('Privado VIP');
});

it('registers show using catalog type name', function () {
    ShowTypeModel::query()->create([
        'tenant_id' => 1,
        'branch_id' => 1,
        'name' => 'Escenario',
        'suggested_price' => 200,
        'status' => 'active',
    ]);

    $girlId = UserModel::query()
        ->whereHas('staffProfile', fn ($q) => $q->where('staff_role', 'GIRL'))
        ->value('id');

    nightposOpenCashSession(phaseBAdminToken());

    test()->postJson('/api/v1/shows', [
        'girl_user_id' => $girlId,
        'show_type' => 'Escenario',
        'unit_price' => 200,
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders(phaseBAdminToken()))->assertCreated();
});

it('creates quick product price for CON_ACOMPANANTE', function () {
    $product = ProductModel::query()->create([
        'tenant_id' => 1,
        'branch_id' => null,
        'name' => 'Producto Quick Price',
        'product_type' => 'beverage',
        'unit' => 'unit',
        'status' => 'active',
    ]);

    test()->postJson("/api/v1/products/{$product->id}/quick-prices", [
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => 80,
        'girl_amount' => 40,
        'house_amount' => 40,
    ], nightposOperationalHeaders(phaseBAdminToken()))->assertCreated();

    expect(ProductPriceModel::query()
        ->where('product_id', $product->id)
        ->where('sale_mode', 'CON_ACOMPANANTE')
        ->where('status', 'active')
        ->exists())->toBeTrue();
});

it('returns clear error when product has no price for sale mode', function () {
    $product = ProductModel::query()->create([
        'tenant_id' => 1,
        'branch_id' => null,
        'name' => 'Sin Precio Test',
        'product_type' => 'beverage',
        'unit' => 'unit',
        'status' => 'active',
    ]);

    $order = test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Test',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders(phaseBAdminToken()))->json('data.order');

    $response = test()->postJson("/api/v1/orders/{$order['id']}/items", [
        'product_id' => $product->id,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
    ], nightposOperationalHeaders(phaseBAdminToken()))->assertStatus(422);

    expect($response->json('message'))->toContain('no tiene precio configurado');
});

it('returns pending settlement sources including active room services', function () {
    $girlId = UserModel::query()
        ->whereHas('staffProfile', fn ($q) => $q->where('staff_role', 'GIRL'))
        ->value('id');

    nightposOpenCashSession(phaseBAdminToken());

    test()->postJson('/api/v1/room-services', nightposRoomServicePayload([
        'girl_user_id' => $girlId,
        'room_label' => 'VIP-1',
        'total_amount' => 100,
        'duration_minutes' => 60,
    ]), nightposOperationalHeaders(phaseBAdminToken()))->assertCreated();

    $response = test()->getJson('/api/v1/settlements/current-shift/pending-sources', nightposOperationalHeaders(phaseBCashierToken()))
        ->assertOk();

    expect($response->json('data.active_room_services_count'))->toBeGreaterThan(0);
});
