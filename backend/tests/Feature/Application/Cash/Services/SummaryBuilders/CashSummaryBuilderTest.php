<?php

declare(strict_types=1);

use App\Application\Cash\DTOs\CashSummaryDTO;
use App\Domain\Cash\Contracts\SummaryBuilders\CashSummaryBuilder;
use App\Domain\Cash\ValueObjects\CashMovementCategory;
use App\Domain\Cash\ValueObjects\CashMovementFamily;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposCloseOpenOfficialShifts();
});

function cashSummaryOpenSession(float $opening = 100): CashSessionModel
{
    $token = nightposLoginPin('1234');
    nightposOpenCashSession($token, $opening);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data.session.id');

    return CashSessionModel::query()->findOrFail($sessionId);
}

function cashSummaryInsertMovement(
    CashSessionModel $session,
    string $movementType,
    string $movementFamily,
    string $movementCategory,
    float $amount,
    string $paymentMethod,
    ?string $description = null,
): void {
    $userId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');

    CashMovementModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'cash_session_id' => $session->id,
        'movement_type' => $movementType,
        'amount' => number_format($amount, 2, '.', ''),
        'description' => $description,
        'payment_method' => $paymentMethod,
        'movement_family' => $movementFamily,
        'movement_category' => $movementCategory,
        'created_by_user_id' => $userId,
        'created_at' => now(),
    ]);
}

function cashSummaryBuild(CashSessionModel $session): CashSummaryDTO
{
    $builder = app(CashSummaryBuilder::class);

    return $builder->build(new CashSessionId((int) $session->id));
}

it('1) opening cash without movements', function () {
    $session = cashSummaryOpenSession(100);
    $summary = cashSummaryBuild($session);

    expect($summary)
        ->toBeInstanceOf(CashSummaryDTO::class)
        ->and($summary->opening_cash->amount)->toBe('100.00')
        ->and($summary->cash_income_total->amount)->toBe('0.00')
        ->and($summary->cash_expense_total->amount)->toBe('0.00')
        ->and($summary->expected_cash->amount)->toBe('100.00');
});

it('2) CASH sale increases expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::SALE,
        CashMovementCategory::SALE_COLLECTION,
        40,
        'CASH',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_income_sales->amount)->toBe('40.00')
        ->and($summary->expected_cash->amount)->toBe('140.00');
});

it('3) QR sale does not increase expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::SALE,
        CashMovementCategory::SALE_COLLECTION,
        40,
        'QR',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_income_sales->amount)->toBe('0.00')
        ->and($summary->expected_cash->amount)->toBe('100.00');
});

it('4) CARD sale does not increase expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::SALE,
        CashMovementCategory::DIRECT_SALE_COLLECTION,
        40,
        'CARD',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_income_sales->amount)->toBe('0.00')
        ->and($summary->expected_cash->amount)->toBe('100.00');
});

it('5) mixed payment adds only CASH part', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::SALE,
        CashMovementCategory::SALE_COLLECTION,
        30,
        'CASH',
    );
    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::SALE,
        CashMovementCategory::SALE_COLLECTION,
        20,
        'CARD',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_income_sales->amount)->toBe('30.00')
        ->and($summary->expected_cash->amount)->toBe('130.00');
});

it('6) manual CASH income increases expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::MANUAL,
        CashMovementCategory::MANUAL_INCOME,
        20,
        'CASH',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_income_manual->amount)->toBe('20.00')
        ->and($summary->expected_cash->amount)->toBe('120.00');
});

it('7) manual QR income does not increase expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::MANUAL,
        CashMovementCategory::MANUAL_INCOME,
        20,
        'QR',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_income_manual->amount)->toBe('0.00')
        ->and($summary->expected_cash->amount)->toBe('100.00');
});

it('8) GIRL CASH settlement payment decreases expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'EXPENSE',
        CashMovementFamily::SETTLEMENT,
        CashMovementCategory::SETTLEMENT_GIRL_PAYMENT,
        15,
        'CASH',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_expense_settlements->amount)->toBe('15.00')
        ->and($summary->expected_cash->amount)->toBe('85.00');
});

it('9) WAITER CASH settlement payment decreases expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'EXPENSE',
        CashMovementFamily::SETTLEMENT,
        CashMovementCategory::SETTLEMENT_WAITER_PAYMENT,
        10,
        'CASH',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_expense_settlements->amount)->toBe('10.00')
        ->and($summary->expected_cash->amount)->toBe('90.00');
});

it('10) CLEANING CASH settlement payment decreases expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'EXPENSE',
        CashMovementFamily::SETTLEMENT,
        CashMovementCategory::SETTLEMENT_CLEANING_PAYMENT,
        8,
        'CASH',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_expense_settlements->amount)->toBe('8.00')
        ->and($summary->expected_cash->amount)->toBe('92.00');
});

it('11) operational CASH expense decreases expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'EXPENSE',
        CashMovementFamily::EXPENSE,
        CashMovementCategory::OPERATING_EXPENSE,
        12,
        'CASH',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_expense_operational->amount)->toBe('12.00')
        ->and($summary->expected_cash->amount)->toBe('88.00');
});

it('12) purchase CASH expense decreases expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'EXPENSE',
        CashMovementFamily::EXPENSE,
        CashMovementCategory::PURCHASE,
        11,
        'CASH',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_expense_purchases->amount)->toBe('11.00')
        ->and($summary->expected_cash->amount)->toBe('89.00');
});

it('13) OTHER_EXPENSE CASH decreases expected_cash', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'EXPENSE',
        CashMovementFamily::EXPENSE,
        CashMovementCategory::OTHER_EXPENSE,
        9,
        'CASH',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_expense_other->amount)->toBe('9.00')
        ->and($summary->expected_cash->amount)->toBe('91.00');
});

it('14) counted_cash and difference apply only when closed or declared', function () {
    $session = cashSummaryOpenSession(100);

    $openSummary = cashSummaryBuild($session);

    expect($openSummary->is_closed)->toBeFalse()
        ->and($openSummary->has_declared_count)->toBeFalse()
        ->and($openSummary->counted_cash)->toBeNull()
        ->and($openSummary->cash_difference)->toBeNull();

    $session->status = 'CLOSED';
    $session->declared_closing_amount = '95.00';
    $session->difference_amount = '-5.00';
    $session->closed_at = now();
    $session->save();

    $closedSummary = cashSummaryBuild($session->fresh());

    expect($closedSummary->is_closed)->toBeTrue()
        ->and($closedSummary->has_declared_count)->toBeTrue()
        ->and($closedSummary->counted_cash?->amount)->toBe('95.00')
        ->and($closedSummary->cash_difference?->amount)->toBe('-5.00');
});

it('15) keeps tenant and branch isolated', function () {
    $targetSession = cashSummaryOpenSession(100);

    $tenant = TenantModel::query()->create([
        'name' => 'Tenant Aislado',
        'slug' => 'aislado',
        'status' => 'active',
    ]);

    $branch = BranchModel::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Sucursal Aislada',
        'code' => 'AIS',
        'status' => 'active',
    ]);

    $userId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');
    $shift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'name' => 'Shift Aislado',
        'shift_type' => 'DAY',
        'business_date' => now()->toDateString(),
        'starts_at' => now()->startOfDay(),
        'ends_at' => now()->endOfDay(),
        'status' => 'OPEN',
        'opened_by_user_id' => $userId,
        'opened_at' => now(),
    ]);

    $foreignSession = CashSessionModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'official_shift_id' => $shift->id,
        'opened_by_user_id' => $userId,
        'status' => 'OPEN',
        'opening_amount' => '500.00',
        'opened_at' => now(),
    ]);

    cashSummaryInsertMovement(
        $foreignSession,
        'INCOME',
        CashMovementFamily::SALE,
        CashMovementCategory::SALE_COLLECTION,
        999,
        'CASH',
    );

    $summary = cashSummaryBuild($targetSession);

    expect($summary->opening_cash->amount)->toBe('100.00')
        ->and($summary->cash_income_sales->amount)->toBe('0.00')
        ->and($summary->expected_cash->amount)->toBe('100.00');
});

it('16) does not double count sales', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::SALE,
        CashMovementCategory::SALE_COLLECTION,
        25,
        'CASH',
    );
    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::SALE,
        CashMovementCategory::DIRECT_SALE_COLLECTION,
        25,
        'CASH',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_income_sales->amount)->toBe('50.00')
        ->and($summary->expected_cash->amount)->toBe('150.00');
});

it('17) result does not depend on description', function () {
    $session = cashSummaryOpenSession(100);

    cashSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::MANUAL,
        CashMovementCategory::MANUAL_INCOME,
        20,
        'CASH',
        'Cobro comanda textual',
    );
    cashSummaryInsertMovement(
        $session,
        'EXPENSE',
        CashMovementFamily::EXPENSE,
        CashMovementCategory::OTHER_EXPENSE,
        10,
        'CASH',
        'Pago chicas textual',
    );

    $summary = cashSummaryBuild($session);

    expect($summary->cash_income_manual->amount)->toBe('20.00')
        ->and($summary->cash_expense_other->amount)->toBe('10.00')
        ->and($summary->expected_cash->amount)->toBe('110.00');
});
