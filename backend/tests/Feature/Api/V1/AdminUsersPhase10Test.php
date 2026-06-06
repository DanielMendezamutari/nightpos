<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function adminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

it('allows admin to create waiter with 5 percent commission', function () {
    $token = adminToken();

    $response = $this->postJson('/api/v1/admin/users', [
        'name' => 'Garzón 5',
        'username' => 'garzon.5',
        'pin' => '7001',
        'staff_role' => 'WAITER',
        'waiter_commission_percent' => 5,
        'branch_id' => BranchModel::query()->where('code', 'CENTRO')->value('id'),
        'accessible_branch_ids' => [BranchModel::query()->where('code', 'CENTRO')->value('id')],
    ], nightposOperationalHeaders($token));

    $response->assertCreated()
        ->assertJsonPath('data.user.staff_role', 'WAITER')
        ->assertJsonPath('data.user.waiter_commission_percent', '5.00');
});

it('allows admin to create waiter with 6 percent commission', function () {
    $token = adminToken();

    $this->postJson('/api/v1/admin/users', [
        'name' => 'Garzón 6',
        'username' => 'garzon.6',
        'pin' => '7002',
        'staff_role' => 'WAITER',
        'waiter_commission_percent' => 6,
        'branch_id' => BranchModel::query()->where('code', 'CENTRO')->value('id'),
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.user.waiter_commission_percent', '6.00');
});

it('allows admin to create girl with girl commissions flag', function () {
    $token = adminToken();

    $this->postJson('/api/v1/admin/users', [
        'name' => 'Chica Demo',
        'username' => 'chica.demo',
        'pin' => '7003',
        'staff_role' => 'GIRL',
        'can_receive_girl_commissions' => true,
        'branch_id' => BranchModel::query()->where('code', 'CENTRO')->value('id'),
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.user.staff_role', 'GIRL')
        ->assertJsonPath('data.user.can_receive_girl_commissions', true);
});

it('denies cashier from creating users', function () {
    $token = nightposLoginPin('1234');

    $this->postJson('/api/v1/admin/users', [
        'name' => 'Hack',
        'username' => 'hack.user',
        'pin' => '9999',
        'staff_role' => 'CASHIER',
    ], nightposOperationalHeaders($token))
        ->assertForbidden();
});

it('denies login for inactive user', function () {
    $user = UserModel::query()->where('username', 'garzon.demo')->first();
    $user->update(['status' => 'inactive']);

    $this->postJson('/api/v1/auth/login-pin', [
        'pin' => '5678',
        'tenant_slug' => 'casa-demo',
        'branch_code' => 'CENTRO',
    ])->assertUnauthorized();
});

it('denies admin from viewing user of another tenant', function () {
    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra',
        'slug' => 'otra-empresa',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $otherUser = UserModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'branch_id' => null,
        'role_id' => null,
        'name' => 'Externo',
        'username' => 'externo.user',
        'status' => 'active',
    ]);

    $token = adminToken();

    $this->getJson('/api/v1/admin/users/'.$otherUser->id, nightposOperationalHeaders($token))
        ->assertNotFound();
});

it('stores pin hashed not plain', function () {
    $token = adminToken();

    $this->postJson('/api/v1/admin/users', [
        'name' => 'PIN Test',
        'username' => 'pin.test',
        'pin' => '4321',
        'staff_role' => 'CASHIER',
        'branch_id' => BranchModel::query()->where('code', 'CENTRO')->value('id'),
    ], nightposOperationalHeaders($token))->assertCreated();

    $model = UserModel::query()->where('username', 'pin.test')->first();

    expect($model->pin_hash)->not->toBe('4321')
        ->and(Hash::check('4321', (string) $model->pin_hash))->toBeTrue();
});

it('allows user access to multiple branches', function () {
    $token = adminToken();
    $tenantId = TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $branch2 = BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Norte',
        'code' => 'NORTE',
        'status' => 'active',
    ]);

    $create = $this->postJson('/api/v1/admin/users', [
        'name' => 'Multi Sucursal',
        'username' => 'multi.branch',
        'pin' => '4322',
        'staff_role' => 'CASHIER',
        'branch_id' => BranchModel::query()->where('code', 'CENTRO')->value('id'),
        'accessible_branch_ids' => [
            BranchModel::query()->where('code', 'CENTRO')->value('id'),
            $branch2->id,
        ],
    ], nightposOperationalHeaders($token));

    $create->assertCreated();

    $userId = (int) $create->json('data.user.id');

    expect($create->json('data.user.accessible_branch_ids'))->toHaveCount(2);

    $this->postJson("/api/v1/admin/users/{$userId}/branches", [
        'branch_id' => $branch2->id,
    ], nightposOperationalHeaders($token))->assertOk();

    $this->deleteJson(
        "/api/v1/admin/users/{$userId}/branches/".$branch2->id,
        [],
        nightposOperationalHeaders($token),
    )->assertOk();
});
