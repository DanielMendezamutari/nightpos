<?php

declare(strict_types=1);

use App\Application\Cash\DTOs\MovementSummaryDTO;
use App\Domain\Cash\Contracts\CashMovementRepositoryInterface;
use App\Domain\Cash\Contracts\SummaryBuilders\MovementSummaryBuilder;
use App\Domain\Cash\ValueObjects\CashMovementCategory;
use App\Domain\Cash\ValueObjects\CashMovementFamily;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCashMovementRepository;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposCloseOpenOfficialShifts();
    $this->app->bind(CashMovementRepositoryInterface::class, EloquentCashMovementRepository::class);
});

function movementSummaryOpenSession(float $opening = 100): CashSessionModel
{
    $token = nightposLoginPin('1234');
    nightposOpenCashSession($token, $opening);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data.session.id');

    return CashSessionModel::query()->findOrFail($sessionId);
}

function movementSummaryInsertMovement(
    CashSessionModel $session,
    string $movementType,
    string $movementFamily,
    string $movementCategory,
    float $amount,
    string $paymentMethod,
    ?string $description = null,
    ?\Illuminate\Support\Carbon $createdAt = null,
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
        'created_at' => $createdAt ?? now(),
    ]);
}

function movementSummaryBuild(CashSessionModel $session): MovementSummaryDTO
{
    $builder = app(MovementSummaryBuilder::class);

    return $builder->build(new CashSessionId((int) $session->id));
}

it('1) groups by family', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::SALE, CashMovementCategory::SALE_COLLECTION, 30, 'CASH');
    movementSummaryInsertMovement($session, 'EXPENSE', CashMovementFamily::SETTLEMENT, CashMovementCategory::SETTLEMENT_GIRL_PAYMENT, 10, 'CASH');

    $summary = movementSummaryBuild($session);

    expect($summary->income_by_family[CashMovementFamily::SALE])->toBe('30.00')
        ->and($summary->expense_by_family[CashMovementFamily::SETTLEMENT])->toBe('10.00')
        ->and($summary->total_movements)->toBe(2);
});

it('2) groups by category', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::SALE, CashMovementCategory::DIRECT_SALE_COLLECTION, 11, 'CARD');
    movementSummaryInsertMovement($session, 'EXPENSE', CashMovementFamily::EXPENSE, CashMovementCategory::OTHER_EXPENSE, 7, 'CASH');

    $summary = movementSummaryBuild($session);

    expect($summary->income_by_category[CashMovementCategory::DIRECT_SALE_COLLECTION])->toBe('11.00')
        ->and($summary->expense_by_category[CashMovementCategory::OTHER_EXPENSE])->toBe('7.00');
});

it('3) groups by payment method', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::SALE, CashMovementCategory::SALE_COLLECTION, 12, 'CASH');
    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::MANUAL, CashMovementCategory::MANUAL_INCOME, 8, 'QR');
    movementSummaryInsertMovement($session, 'EXPENSE', CashMovementFamily::EXPENSE, CashMovementCategory::OPERATING_EXPENSE, 5, 'CARD');

    $summary = movementSummaryBuild($session);

    expect($summary->income_by_payment_method['cash'])->toBe('12.00')
        ->and($summary->income_by_payment_method['qr'])->toBe('8.00')
        ->and($summary->expense_by_payment_method['card'])->toBe('5.00');
});

it('4) separates manual income', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::MANUAL, CashMovementCategory::MANUAL_INCOME, 20, 'CASH');

    $summary = movementSummaryBuild($session);

    expect($summary->income_by_family[CashMovementFamily::MANUAL])->toBe('20.00')
        ->and($summary->income_by_category[CashMovementCategory::MANUAL_INCOME])->toBe('20.00');
});

it('5) separates sales', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::SALE, CashMovementCategory::SALE_COLLECTION, 25, 'CASH');
    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::SALE, CashMovementCategory::DIRECT_SALE_COLLECTION, 15, 'CARD');

    $summary = movementSummaryBuild($session);

    expect($summary->income_by_family[CashMovementFamily::SALE])->toBe('40.00')
        ->and($summary->income_by_category[CashMovementCategory::SALE_COLLECTION])->toBe('25.00')
        ->and($summary->income_by_category[CashMovementCategory::DIRECT_SALE_COLLECTION])->toBe('15.00');
});

it('6) separates settlements', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'EXPENSE', CashMovementFamily::SETTLEMENT, CashMovementCategory::SETTLEMENT_GIRL_PAYMENT, 12, 'CASH');
    movementSummaryInsertMovement($session, 'EXPENSE', CashMovementFamily::SETTLEMENT, CashMovementCategory::SETTLEMENT_WAITER_PAYMENT, 6, 'CASH');

    $summary = movementSummaryBuild($session);

    expect($summary->expense_by_family[CashMovementFamily::SETTLEMENT])->toBe('18.00')
        ->and($summary->expense_by_category[CashMovementCategory::SETTLEMENT_GIRL_PAYMENT])->toBe('12.00')
        ->and($summary->expense_by_category[CashMovementCategory::SETTLEMENT_WAITER_PAYMENT])->toBe('6.00');
});

it('7) separates purchases', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'EXPENSE', CashMovementFamily::EXPENSE, CashMovementCategory::PURCHASE, 14, 'CASH');

    $summary = movementSummaryBuild($session);

    expect($summary->expense_by_category[CashMovementCategory::PURCHASE])->toBe('14.00')
        ->and($summary->movement_count_by_category[CashMovementCategory::PURCHASE])->toBe(1);
});

it('8) separates operating expenses', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'EXPENSE', CashMovementFamily::EXPENSE, CashMovementCategory::OPERATING_EXPENSE, 9, 'CASH');

    $summary = movementSummaryBuild($session);

    expect($summary->expense_by_category[CashMovementCategory::OPERATING_EXPENSE])->toBe('9.00');
});

it('9) separates other expenses', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'EXPENSE', CashMovementFamily::EXPENSE, CashMovementCategory::OTHER_EXPENSE, 4, 'CASH');

    $summary = movementSummaryBuild($session);

    expect($summary->expense_by_category[CashMovementCategory::OTHER_EXPENSE])->toBe('4.00');
});

it('10) separates other incomes', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::MANUAL, CashMovementCategory::OTHER_INCOME, 7, 'QR');

    $summary = movementSummaryBuild($session);

    expect($summary->income_by_category[CashMovementCategory::OTHER_INCOME])->toBe('7.00');
});

it('11) keeps ADJUSTMENT prepared when no records exist', function () {
    $session = movementSummaryOpenSession(100);

    $summary = movementSummaryBuild($session);

    expect($summary->income_by_family[CashMovementFamily::ADJUSTMENT])->toBe('0.00')
        ->and($summary->expense_by_family[CashMovementFamily::ADJUSTMENT])->toBe('0.00');
});

it('12) keeps tenant isolated', function () {
    $targetSession = movementSummaryOpenSession(100);

    $tenant = TenantModel::query()->create([
        'name' => 'Tenant Aislado Mov',
        'slug' => 'aislado-mov',
        'status' => 'active',
    ]);

    $branch = BranchModel::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Sucursal Aislada Mov',
        'code' => 'AMV',
        'status' => 'active',
    ]);

    $userId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');
    $shift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenant->id,
        'branch_id' => $branch->id,
        'name' => 'Shift Aislado Mov',
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

    movementSummaryInsertMovement($foreignSession, 'INCOME', CashMovementFamily::SALE, CashMovementCategory::SALE_COLLECTION, 999, 'CASH');

    $summary = movementSummaryBuild($targetSession);

    expect($summary->total_movements)->toBe(0)
        ->and($summary->total_income->amount)->toBe('0.00');
});

it('13) keeps branch isolated', function () {
    $targetSession = movementSummaryOpenSession(100);
    $tenantId = (int) $targetSession->tenant_id;

    $branch = BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Sucursal Aislada Branch',
        'code' => 'BRA',
        'status' => 'active',
    ]);

    $userId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');
    $shift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branch->id,
        'name' => 'Shift Branch Mov',
        'shift_type' => 'DAY',
        'business_date' => now()->toDateString(),
        'starts_at' => now()->startOfDay(),
        'ends_at' => now()->endOfDay(),
        'status' => 'OPEN',
        'opened_by_user_id' => $userId,
        'opened_at' => now(),
    ]);

    $foreignSession = CashSessionModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branch->id,
        'official_shift_id' => $shift->id,
        'opened_by_user_id' => $userId,
        'status' => 'OPEN',
        'opening_amount' => '300.00',
        'opened_at' => now(),
    ]);

    movementSummaryInsertMovement($foreignSession, 'EXPENSE', CashMovementFamily::EXPENSE, CashMovementCategory::OPERATING_EXPENSE, 77, 'CASH');

    $summary = movementSummaryBuild($targetSession);

    expect($summary->total_movements)->toBe(0)
        ->and($summary->total_expense->amount)->toBe('0.00');
});

it('14) does not depend on description', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement(
        $session,
        'INCOME',
        CashMovementFamily::MANUAL,
        CashMovementCategory::MANUAL_INCOME,
        20,
        'CASH',
        'Cobro comanda textual',
    );
    movementSummaryInsertMovement(
        $session,
        'EXPENSE',
        CashMovementFamily::EXPENSE,
        CashMovementCategory::OTHER_EXPENSE,
        10,
        'CASH',
        'Pago chicas textual',
    );

    $summary = movementSummaryBuild($session);

    expect($summary->income_by_category[CashMovementCategory::MANUAL_INCOME])->toBe('20.00')
        ->and($summary->expense_by_category[CashMovementCategory::OTHER_EXPENSE])->toBe('10.00')
        ->and($summary->total_movements)->toBe(2);
});

it('15) does not duplicate counting', function () {
    $session = movementSummaryOpenSession(100);

    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::SALE, CashMovementCategory::SALE_COLLECTION, 25, 'CASH');
    movementSummaryInsertMovement($session, 'INCOME', CashMovementFamily::SALE, CashMovementCategory::SALE_COLLECTION, 15, 'CARD');

    $summary = movementSummaryBuild($session);

    expect($summary->total_movements)->toBe(2)
        ->and($summary->total_income->amount)->toBe('40.00')
        ->and($summary->movement_count_by_category[CashMovementCategory::SALE_COLLECTION])->toBe(2);
});
