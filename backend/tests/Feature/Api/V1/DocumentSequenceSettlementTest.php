<?php

declare(strict_types=1);

use App\Application\DocumentSequence\Services\DocumentSequenceService;
use App\Application\StaffSettlement\Services\SettlementTicketNumberGenerator;
use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\DocumentSequenceModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Domain\Enums\DocumentSequenceType;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
    dssRegisterPrintDevice();
});

function dssRegisterPrintDevice(): void
{
    test()->postJson('/api/v1/print-devices/register', [
        'name' => 'DSS Print '.uniqid(),
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders(dssAdminToken()))->assertCreated();
}

function dssGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function dssWaiterId(): int
{
    return (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
}

function dssCashierToken(): string
{
    return nightposLoginPin('1234');
}

function dssWaiterToken(): string
{
    return nightposLoginPin('5678');
}

function dssChargeGirl(float $girlAmount, string $table = 'DSS Girl'): void
{
    $cashier = dssCashierToken();
    $waiter = dssWaiterToken();
    $girlId = dssGirlId();

    nightposOpenCashSession($cashier, 500);

    $productId = nightposSeedOrderProduct([[
        'sale_mode' => 'CON_ACOMPANANTE',
        'price' => $girlAmount * 2,
        'girl_amount' => $girlAmount,
        'house_amount' => $girlAmount,
    ]]);

    $orderId = test()->postJson('/api/v1/orders', [
        'table_label' => $table,
        'waiter_user_id' => dssWaiterId(),
    ], nightposOperationalHeaders($waiter))
        ->assertCreated()
        ->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'girl_user_id' => $girlId,
    ], nightposOperationalHeaders($waiter))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => $girlAmount * 2]],
    ], nightposOperationalHeaders($cashier))->assertCreated();
}

function dssGenerate(): void
{
    nightposResetApiAuth();
    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(dssAdminToken()))
        ->assertCreated();
}

function dssGirlSettlement(): StaffSettlementModel
{
    return StaffSettlementModel::query()
        ->where('staff_user_id', dssGirlId())
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->latest('id')
        ->firstOrFail();
}

function dssMarkPaid(int $settlementId, array $appliedFineIds = [], string $paymentMethod = 'CASH'): array
{
    nightposResetApiAuth();
    nightposOpenCashSession(dssAdminToken(), 500);

    return test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", [
        'payment_method' => $paymentMethod,
        'applied_fine_ids' => $appliedFineIds,
    ], nightposOperationalHeaders(dssAdminToken()))
        ->assertOk()
        ->json('data');
}

function dssAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function dssBranch(): BranchModel
{
    return BranchModel::query()->where('code', 'CENTRO')->firstOrFail();
}

function dssTenantId(): int
{
    return (int) dssBranch()->tenant_id;
}

function dssBranchId(): int
{
    return (int) dssBranch()->id;
}

function dssPayGirlSettlement(): StaffSettlementModel
{
    dssChargeGirl(120, 'DSS Girl '.uniqid());
    dssGenerate();
    $settlement = dssGirlSettlement();
    dssMarkPaid($settlement->id);

    return StaffSettlementModel::query()->findOrFail($settlement->id);
}

it('generates 000001 on first settlement payment', function () {
    dssChargeGirl(100);
    dssGenerate();
    $settlement = dssGirlSettlement();
    dssMarkPaid($settlement->id);

    $fresh = StaffSettlementModel::query()->findOrFail($settlement->id);
    $year = now()->format('Y');

    expect($fresh->ticket_number)->toBe('CENTRO-'.$year.'-000001');
});

it('generates 000002 on second payment same branch', function () {
    dssChargeGirl(100, 'DSS A');
    dssGenerate();
    dssMarkPaid(dssGirlSettlement()->id);

    dssChargeGirl(80, 'DSS B');
    dssGenerate();
    $second = dssGirlSettlement();
    dssMarkPaid($second->id);

    $year = now()->format('Y');
    $fresh = StaffSettlementModel::query()->findOrFail($second->id);

    expect($fresh->ticket_number)->toBe('CENTRO-'.$year.'-000002');
});

it('allows same ticket string in different tenants with branch code 1', function () {
    $year = now()->format('Y');
    $branch = dssBranch();
    $branch->update(['code' => '1']);

    $generator = app(SettlementTicketNumberGenerator::class);
    $firstTicket = $generator->next((int) $branch->tenant_id, (int) $branch->id);

    expect($firstTicket)->toBe('1-'.$year.'-000001');

    $otherTenant = TenantModel::query()->create([
        'name' => 'Otra DSS',
        'slug' => 'otra-dss',
        'status' => 'active',
        'plan_name' => 'basic',
        'subscription_starts_at' => now()->subMonth(),
        'subscription_ends_at' => now()->addYear(),
    ]);

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $otherTenant->id,
        'name' => 'Sucursal 1',
        'code' => '1',
        'status' => 'active',
    ]);

    $otherTicket = $generator->next($otherTenant->id, $otherBranch->id);

    expect($otherTicket)->toBe('1-'.$year.'-000001')
        ->and($otherTicket)->toBe($firstTicket);
});

it('reserves unique sequence values under rapid sequential calls', function () {
    $service = app(DocumentSequenceService::class);
    $year = (string) now()->format('Y');
    $values = [];

    DB::transaction(function () use ($service, $year, &$values): void {
        for ($i = 0; $i < 5; $i++) {
            $values[] = $service->reserveNext(
                dssTenantId(),
                dssBranchId(),
                DocumentSequenceType::SettlementPayment,
                $year,
            );
        }
    });

    expect($values)->toBe([1, 2, 3, 4, 5]);
});

it('stores correct last_value in document_sequences after payments', function () {
    dssPayGirlSettlement();

    $year = (string) now()->format('Y');
    $row = DocumentSequenceModel::query()
        ->where('tenant_id', dssTenantId())
        ->where('branch_id', dssBranchId())
        ->where('document_type', DocumentSequenceType::SettlementPayment->value)
        ->where('period_key', $year)
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->last_value)->toBe(1);
});

it('does not change ticket when settlement already paid', function () {
    dssChargeGirl(100);
    dssGenerate();
    $settlement = dssGirlSettlement();
    dssMarkPaid($settlement->id);

    $ticket = StaffSettlementModel::query()->findOrFail($settlement->id)->ticket_number;

    nightposResetApiAuth();
    nightposOpenCashSession(dssAdminToken(), 500);

    test()->postJson("/api/v1/settlements/{$settlement->id}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders(dssAdminToken()))
        ->assertStatus(422);

    expect(StaffSettlementModel::query()->findOrFail($settlement->id)->ticket_number)->toBe($ticket);
});

it('starts new year sequence at 000001 without using prior year counter', function () {
    $service = app(DocumentSequenceService::class);
    $branch = dssBranch();

    DocumentSequenceModel::query()->create([
        'tenant_id' => $branch->tenant_id,
        'branch_id' => $branch->id,
        'document_type' => DocumentSequenceType::SettlementPayment->value,
        'period_key' => '2025',
        'last_value' => 99,
    ]);

    $next2026 = $service->reserveNext(
        (int) $branch->tenant_id,
        (int) $branch->id,
        DocumentSequenceType::SettlementPayment,
        '2026',
    );

    expect($next2026)->toBe(1);

    $generator = app(SettlementTicketNumberGenerator::class);

    \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::create(2026, 6, 15, 12, 0, 0));

    $formatted = $generator->next((int) $branch->tenant_id, (int) $branch->id);

    \Illuminate\Support\Carbon::setTestNow();

    expect($formatted)->toBe('CENTRO-2026-000002');
});

it('syncs sequence from existing paid tickets', function () {
    $branch = dssBranch();
    $shift = OfficialShiftModel::query()->where('status', 'OPEN')->firstOrFail();

    StaffSettlementModel::query()->create([
        'tenant_id' => $branch->tenant_id,
        'branch_id' => $branch->id,
        'official_shift_id' => $shift->id,
        'staff_user_id' => dssGirlId(),
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'total_amount' => 50,
        'gross_amount' => 50,
        'adjustments_total' => 0,
        'net_amount' => 50,
        'status' => 'PAID',
        'paid_at' => now(),
        'ticket_number' => 'CENTRO-2026-000007',
    ]);

    DocumentSequenceModel::query()
        ->where('document_type', DocumentSequenceType::SettlementPayment->value)
        ->delete();

    app(DocumentSequenceService::class)->syncSettlementPaymentSequencesFromExistingTickets();

    expect(app(DocumentSequenceService::class)->currentValue(
        (int) $branch->tenant_id,
        (int) $branch->id,
        DocumentSequenceType::SettlementPayment,
        '2026',
    ))->toBe(7);

    $next = app(SettlementTicketNumberGenerator::class)->next((int) $branch->tenant_id, (int) $branch->id);

    expect($next)->toBe('CENTRO-2026-000008');
});

it('does not block payment when same ticket exists in another branch', function () {
    $branch = dssBranch();
    $shift = OfficialShiftModel::query()->where('status', 'OPEN')->firstOrFail();

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $branch->tenant_id,
        'name' => 'Norte',
        'code' => 'NORTE',
        'status' => 'active',
    ]);

    StaffSettlementModel::query()->create([
        'tenant_id' => $branch->tenant_id,
        'branch_id' => $otherBranch->id,
        'official_shift_id' => $shift->id,
        'staff_user_id' => dssGirlId(),
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'total_amount' => 40,
        'gross_amount' => 40,
        'adjustments_total' => 0,
        'net_amount' => 40,
        'status' => 'PAID',
        'paid_at' => now(),
        'ticket_number' => 'CENTRO-2026-000001',
    ]);

    dssChargeGirl(90);
    dssGenerate();
    dssMarkPaid(dssGirlSettlement()->id);

    expect(StaffSettlementModel::query()
        ->where('branch_id', $branch->id)
        ->whereNotNull('ticket_number')
        ->value('ticket_number'))->toBe('CENTRO-'.now()->format('Y').'-000001');
});

it('returns controlled 409 instead of raw 500 on duplicate ticket_number', function () {
    $branch = dssBranch();
    $shift = OfficialShiftModel::query()->where('status', 'OPEN')->firstOrFail();
    $year = now()->format('Y');

    StaffSettlementModel::query()->create([
        'tenant_id' => $branch->tenant_id,
        'branch_id' => $branch->id,
        'official_shift_id' => $shift->id,
        'staff_user_id' => dssGirlId(),
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'total_amount' => 30,
        'gross_amount' => 30,
        'adjustments_total' => 0,
        'net_amount' => 30,
        'status' => 'PAID',
        'paid_at' => now(),
        'ticket_number' => 'CENTRO-'.$year.'-000099',
    ]);

    DocumentSequenceModel::query()->updateOrCreate(
        [
            'tenant_id' => $branch->tenant_id,
            'branch_id' => $branch->id,
            'document_type' => DocumentSequenceType::SettlementPayment->value,
            'period_key' => $year,
        ],
        [
            'last_value' => 98,
        ],
    );

    dssChargeGirl(70);
    dssGenerate();
    $pending = dssGirlSettlement();

    nightposResetApiAuth();
    nightposOpenCashSession(dssAdminToken(), 500);

    test()->postJson("/api/v1/settlements/{$pending->id}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders(dssAdminToken()))
        ->assertStatus(409)
        ->assertJsonPath('message', StaffSettlementDomainException::ticketNumberConflict()->getMessage());
});
