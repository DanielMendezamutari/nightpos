<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashRegisterModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\PaymentMethodModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintDeviceModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantOperationChecklistItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function saas15SuperToken(): string
{
    return nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
}

function saas15TenantAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function saas15DemoTenant(): TenantModel
{
    return TenantModel::query()->where('slug', 'casa-demo')->firstOrFail();
}

function saas15DemoBranch(): BranchModel
{
    $tenant = saas15DemoTenant();

    return BranchModel::query()->where('tenant_id', $tenant->id)->firstOrFail();
}

function saas15CreateSale(int $tenantId, int $branchId, ?Carbon $paidAt = null): void
{
    nightposEnsureShiftOpen();
    nightposOpenCashSession(nightposLoginPin('1234'));

    $cashSessionId = (int) CashSessionModel::query()
        ->where('tenant_id', $tenantId)
        ->where('branch_id', $branchId)
        ->where('status', 'OPEN')
        ->value('id');

    $shiftId = (int) OfficialShiftModel::query()
        ->where('tenant_id', $tenantId)
        ->where('branch_id', $branchId)
        ->where('status', 'OPEN')
        ->value('id');

    $cashierId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');

    SaleModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'cash_session_id' => $cashSessionId,
        'cashier_user_id' => $cashierId,
        'sale_number' => 'T-'.uniqid(),
        'subtotal' => 100,
        'total' => 100,
        'currency' => 'BOB',
        'payment_mode' => 'CASH',
        'status' => 'PAID',
        'paid_at' => $paidAt ?? now(),
    ]);
}

function saas15CreatePrintDevice(int $tenantId, int $branchId, ?Carbon $lastSeenAt = null, ?string $agentVersion = null): PrintDeviceModel
{
    return PrintDeviceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Agente Test',
        'device_key_hash' => hash('sha256', 'test-key'),
        'device_key_prefix' => 'test',
        'status' => 'active',
        'enabled' => true,
        'paper_width_mm' => 80,
        'auto_print_order' => true,
        'last_seen_at' => $lastSeenAt,
        'agent_version' => $agentVersion,
    ]);
}

it('dashboard returns aggregate counts', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();
    saas15CreateSale((int) $tenant->id, (int) $branch->id);
    saas15CreatePrintDevice((int) $tenant->id, (int) $branch->id, now());

    $response = test()->getJson('/api/v1/admin/platform/operations/dashboard', [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();

    expect($response->json('data.cards.total_tenants'))->toBeGreaterThan(0)
        ->and($response->json('data.cards.sales_today'))->toBeGreaterThan(0)
        ->and($response->json('data.versions.backend_version'))->not->toBeEmpty();
});

it('tenant with recent sale is online', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();
    saas15CreateSale((int) $tenant->id, (int) $branch->id, now());

    $response = test()->getJson('/api/v1/admin/platform/operations/tenants', [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();

    $item = collect($response->json('data.items'))->firstWhere('tenant_id', $tenant->id);

    expect($item['operational_status'])->toBe('ONLINE');
});

it('tenant without recent activity is warning or offline', function () {
    $tenant = TenantModel::query()->create([
        'name' => 'Inactivo Ops',
        'slug' => 'inactivo-ops',
        'status' => 'active',
        'plan_name' => 'FREE',
    ]);

    BranchModel::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Sede Inactiva',
        'code' => 'INA1',
        'status' => 'active',
    ]);

    PaymentMethodModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => BranchModel::query()->where('tenant_id', $tenant->id)->value('id'),
        'name' => 'Efectivo',
        'code' => 'CASH',
        'type' => 'CASH',
        'enabled' => true,
    ]);

    CashRegisterModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => BranchModel::query()->where('tenant_id', $tenant->id)->value('id'),
        'name' => 'Caja 1',
        'code' => 'C1',
        'status' => 'active',
    ]);

    $response = test()->getJson('/api/v1/admin/platform/operations/tenants', [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();

    $item = collect($response->json('data.items'))->firstWhere('tenant_id', $tenant->id);

    expect($item['operational_status'])->toBeIn(['WARNING', 'OFFLINE', 'CRITICAL']);
});

it('agent with recent last_seen is online in print agents list', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();
    $device = saas15CreatePrintDevice((int) $tenant->id, (int) $branch->id, now());

    $response = test()->getJson('/api/v1/admin/platform/operations/print-agents', [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();

    $item = collect($response->json('data.items'))->firstWhere('id', $device->id);

    expect($item['online'])->toBeTrue();
});

it('agent with old last_seen is offline in print agents list', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();
    $device = saas15CreatePrintDevice((int) $tenant->id, (int) $branch->id, now()->subMinutes(10));

    $response = test()->getJson('/api/v1/admin/platform/operations/print-agents', [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();

    $item = collect($response->json('data.items'))->firstWhere('id', $device->id);

    expect($item['online'])->toBeFalse();
});

it('long open cash session generates issue', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();
    $userId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    CashSessionModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'opened_by_user_id' => $userId,
        'status' => 'OPEN',
        'opening_amount' => 100,
        'opened_at' => now()->subHours(20),
    ]);

    saas15CreateSale((int) $tenant->id, (int) $branch->id, now());

    $response = test()->getJson("/api/v1/admin/platform/operations/tenants/{$tenant->id}", [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();

    $types = collect($response->json('data.issues'))->pluck('type');

    expect($types->contains('CASH_SESSION_TOO_LONG'))->toBeTrue();
});

it('open shift too long generates issue', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();
    $userId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    OfficialShiftModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'name' => 'Turno Test',
        'shift_type' => 'NIGHT',
        'business_date' => now()->toDateString(),
        'starts_at' => now()->subHours(20),
        'ends_at' => now()->addHours(4),
        'status' => 'OPEN',
        'opened_by_user_id' => $userId,
        'opened_at' => now()->subHours(20),
    ]);

    saas15CreateSale((int) $tenant->id, (int) $branch->id, now());

    $response = test()->getJson("/api/v1/admin/platform/operations/tenants/{$tenant->id}", [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();

    $types = collect($response->json('data.issues'))->pluck('type');

    expect($types->contains('SHIFT_NOT_CLOSED'))->toBeTrue();
});

it('failed print job generates issue', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();

    PrintJobModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'type' => 'TEST',
        'source_type' => 'manual',
        'source_id' => 1,
        'idempotency_key' => 'fail-'.uniqid(),
        'payload' => [],
        'content_text' => 'test',
        'status' => 'FAILED',
        'priority' => 1,
        'attempts' => 1,
        'max_attempts' => 3,
    ]);

    saas15CreateSale((int) $tenant->id, (int) $branch->id, now());

    $response = test()->getJson("/api/v1/admin/platform/operations/tenants/{$tenant->id}", [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();

    $types = collect($response->json('data.issues'))->pluck('type');

    expect($types->contains('PRINT_JOB_FAILED'))->toBeTrue();
});

it('incomplete checklist affects health score', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();
    saas15CreateSale((int) $tenant->id, (int) $branch->id, now());

    $withChecklist = test()->getJson("/api/v1/admin/platform/operations/tenants/{$tenant->id}", [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk()->json('data.summary.health_score');

    TenantOperationChecklistItemModel::query()->where('tenant_id', $tenant->id)->delete();

    $withoutChecklist = test()->getJson("/api/v1/admin/platform/operations/tenants/{$tenant->id}", [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk()->json('data.summary.health_score');

    expect($withoutChecklist)->toBeLessThanOrEqual($withChecklist);
});

it('technical profile rejects password fields', function () {
    $tenant = saas15DemoTenant();

    test()->putJson("/api/v1/admin/platform/operations/tenants/{$tenant->id}/technical-profile", [
        'remote_support_id' => '123456789',
        'password' => 'secret',
    ], [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertStatus(422);
});

it('tenant detail returns branches agents and issues', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();
    saas15CreatePrintDevice((int) $tenant->id, (int) $branch->id, now());
    saas15CreateSale((int) $tenant->id, (int) $branch->id, now());

    $response = test()->getJson("/api/v1/admin/platform/operations/tenants/{$tenant->id}", [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();

    expect($response->json('data.summary.tenant_id'))->toBe($tenant->id)
        ->and($response->json('data.branches'))->not->toBeEmpty()
        ->and($response->json('data.print_agents'))->not->toBeEmpty()
        ->and($response->json('data.installation_checklist'))->not->toBeEmpty();
});

it('superadmin can access platform operations endpoints', function () {
    test()->getJson('/api/v1/admin/platform/operations/dashboard', [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk();
});

it('tenant user cannot access platform operations endpoints', function () {
    test()->getJson('/api/v1/admin/platform/operations/dashboard', [
        'Authorization' => 'Bearer '.saas15TenantAdminToken(),
    ])->assertForbidden();
});

it('legacy agent without version does not break dashboard', function () {
    $tenant = saas15DemoTenant();
    $branch = saas15DemoBranch();
    saas15CreatePrintDevice((int) $tenant->id, (int) $branch->id, now(), null);

    test()->getJson('/api/v1/admin/platform/operations/dashboard', [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk()
        ->assertJsonPath('success', true);
});

it('can patch checklist item', function () {
    $tenant = saas15DemoTenant();

    test()->patchJson("/api/v1/admin/platform/operations/tenants/{$tenant->id}/checklist/domain", [
        'completed' => true,
        'notes' => 'Configurado',
    ], [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk()
        ->assertJsonPath('data.item.completed', true);
});

it('can save technical profile without passwords', function () {
    $tenant = saas15DemoTenant();

    test()->putJson("/api/v1/admin/platform/operations/tenants/{$tenant->id}/technical-profile", [
        'remote_support_tool' => 'AnyDesk',
        'remote_support_id' => '123456789',
        'primary_pc_name' => 'PC-Caja',
    ], [
        'Authorization' => 'Bearer '.saas15SuperToken(),
    ])->assertOk()
        ->assertJsonPath('data.profile.remote_support_id', '123456789');
});
