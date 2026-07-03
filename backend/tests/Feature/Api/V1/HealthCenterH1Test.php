<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\DocumentSequenceModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintDeviceModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Shared\Domain\Enums\DocumentSequenceType;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    Cache::flush();
});

function healthSuperToken(): string
{
    return nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
}

function healthOwnerToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function healthDemoTenant(): TenantModel
{
    return TenantModel::query()->where('slug', 'casa-demo')->firstOrFail();
}

function healthDemoBranch(): BranchModel
{
    return BranchModel::query()->where('tenant_id', healthDemoTenant()->id)->firstOrFail();
}

it('returns platform health summary for superadmin', function () {
    $response = test()->getJson('/api/v1/admin/health-center/platform/summary', nightposOperationalHeaders(healthSuperToken(), null))
        ->assertOk()
        ->assertJsonPath('success', true);

    expect($response->json('data.platform.score'))->toBeInt();
    expect($response->json('data.infrastructure.checks'))->toBeArray();
    expect($response->json('data.tenants'))->not->toBeEmpty();
});

it('denies platform health summary without permission', function () {
    test()->getJson('/api/v1/admin/health-center/platform/summary', nightposOperationalHeaders(healthOwnerToken()))
        ->assertForbidden();
});

it('returns tenant health summary for owner', function () {
    $tenant = healthDemoTenant();
    $branch = healthDemoBranch();

    $response = test()->getJson('/api/v1/health-center/summary', nightposOperationalHeaders(healthOwnerToken()))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.tenant.id', $tenant->id)
        ->assertJsonStructure([
            'data' => [
                'tenant' => ['score', 'status_health'],
                'infrastructure' => ['score', 'checks'],
                'branches' => [
                    ['branch_id', 'score', 'status', 'checks', 'alerts'],
                ],
            ],
        ]);

    expect(collect($response->json('data.branches'))->pluck('branch_id'))->toContain($branch->id);
});

it('detects document sequence lag on branch health', function () {
    $branch = healthDemoBranch();
    $shift = OfficialShiftModel::query()->where('status', 'OPEN')->firstOrFail();
    $periodKey = (string) now()->year;

    DocumentSequenceModel::query()->updateOrCreate(
        [
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            'document_type' => DocumentSequenceType::SettlementPayment->value,
            'period_key' => $periodKey,
        ],
        ['last_value' => 1],
    );

    StaffSettlementModel::query()->create([
        'tenant_id' => $branch->tenant_id,
        'branch_id' => $branch->id,
        'official_shift_id' => $shift->id,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'total_amount' => 10,
        'gross_amount' => 10,
        'adjustments_total' => 0,
        'net_amount' => 10,
        'status' => 'PAID',
        'paid_at' => now(),
        'ticket_number' => "{$branch->code}-{$periodKey}-000002",
    ]);

    $response = test()->getJson('/api/v1/health-center/summary', nightposOperationalHeaders(healthOwnerToken()))
        ->assertOk();

    $branchPayload = collect($response->json('data.branches'))
        ->firstWhere('branch_id', $branch->id);

    $codes = collect($branchPayload['checks'])->pluck('code');

    expect($codes)->toContain('DOCSEQ.SETTLEMENT');
    expect(collect($branchPayload['checks'])->firstWhere('code', 'DOCSEQ.SETTLEMENT')['severity'])
        ->toBe('critical');
});

it('detects pending settlements on closed shift', function () {
    $tenant = healthDemoTenant();
    $branch = healthDemoBranch();

    $shift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'name' => 'Turno Test',
        'shift_type' => 'NIGHT',
        'business_date' => now()->toDateString(),
        'starts_at' => now()->subHours(10),
        'ends_at' => now()->addHours(2),
        'status' => 'CLOSED',
        'opened_at' => now()->subHours(10),
        'closed_at' => now()->subHour(),
        'opened_by_user_id' => 1,
        'closed_by_user_id' => 1,
    ]);

    StaffSettlementModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'official_shift_id' => $shift->id,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'total_amount' => 50,
        'gross_amount' => 50,
        'adjustments_total' => 0,
        'net_amount' => 50,
        'status' => 'PENDING',
    ]);

    $response = test()->getJson('/api/v1/health-center/summary', nightposOperationalHeaders(healthOwnerToken()))
        ->assertOk();

    $branchPayload = collect($response->json('data.branches'))
        ->firstWhere('branch_id', $branch->id);

    expect(collect($branchPayload['checks'])->firstWhere('code', 'SETTLE.PENDING_CLOSED_SHIFT')['severity'])
        ->toBe('critical');
});

it('detects stale open cash session', function () {
    $tenant = healthDemoTenant();
    $branch = healthDemoBranch();

    CashSessionModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'status' => 'OPEN',
        'opening_amount' => 100,
        'opened_by_user_id' => 1,
        'opened_at' => Carbon::now()->subHours(20),
    ]);

    $response = test()->getJson('/api/v1/health-center/summary', nightposOperationalHeaders(healthOwnerToken()))
        ->assertOk();

    $branchPayload = collect($response->json('data.branches'))
        ->firstWhere('branch_id', $branch->id);

    expect(collect($branchPayload['checks'])->firstWhere('code', 'CASH.OPEN_TTL')['severity'])
        ->not->toBe('ok');
});

it('detects offline print agent and pending jobs', function () {
    $tenant = healthDemoTenant();
    $branch = healthDemoBranch();

    PrintDeviceModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'name' => 'CAJA TEST',
        'device_key_hash' => bcrypt('test'),
        'device_key_prefix' => 'npd_test_',
        'status' => 'ACTIVE',
        'enabled' => true,
        'last_seen_at' => now()->subHours(2),
    ]);

    $job = PrintJobModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'type' => 'SETTLEMENT_PAYMENT',
        'source_type' => 'staff_settlement',
        'source_id' => 1,
        'status' => 'PENDING',
        'payload' => [],
        'content_text' => 'test ticket',
    ]);
    $job->created_at = now()->subMinutes(30);
    $job->updated_at = now()->subMinutes(30);
    $job->saveQuietly();

    $response = test()->getJson('/api/v1/health-center/summary', nightposOperationalHeaders(healthOwnerToken()))
        ->assertOk();

    $branchPayload = collect($response->json('data.branches'))
        ->firstWhere('branch_id', $branch->id);

    expect(collect($branchPayload['checks'])->firstWhere('code', 'PRINT.AGENT_OFFLINE')['severity'])
        ->toBe('warning');
    expect(collect($branchPayload['checks'])->firstWhere('code', 'PRINT.JOB_PENDING')['severity'])
        ->toBe('warning');
});
