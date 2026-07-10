<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserBranchAccessModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Domain\User\Services\PinFingerprint;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);

    OfficialShiftModel::query()->where('status', 'OPEN')->update([
        'status' => 'CLOSED',
        'closed_at' => now(),
    ]);
});

function cashScopeAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function cashScopeCashierAToken(): string
{
    return nightposLoginPin('1234');
}

function cashScopeCashierBToken(): string
{
    cashScopeEnsureCashierB();

    return nightposLoginPin('4321');
}

function cashScopePinCredentials(string $pin): array
{
    return [
        'pin_hash' => Hash::make($pin),
        'pin_fingerprint' => PinFingerprint::fromPlain($pin, (string) config('app.key')),
    ];
}

function cashScopeEnsureCashierB(): void
{
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()
        ->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()
        ->where('code', 'CENTRO')->value('id');
    $roleId = (int) RoleModel::query()->where('slug', 'cashier')->where('tenant_id', $tenantId)->value('id');

    $user = UserModel::query()->updateOrCreate(
        ['username' => 'cajero.b.demo'],
        array_merge([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'role_id' => $roleId,
            'name' => 'Cajero B Demo',
            'email' => null,
            'password' => null,
            'status' => 'active',
        ], cashScopePinCredentials('4321')),
    );

    StaffProfileModel::query()->updateOrCreate(
        ['user_id' => $user->id],
        [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'staff_role' => 'CASHIER',
            'status' => 'active',
        ],
    );

    UserBranchAccessModel::query()->firstOrCreate([
        'user_id' => $user->id,
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
    ]);
}

function cashScopeSeniorToken(): string
{
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()
        ->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()
        ->where('code', 'CENTRO')->value('id');
    $roleId = (int) RoleModel::query()->where('slug', 'cashier_senior')->where('tenant_id', $tenantId)->value('id');

    $user = UserModel::query()->updateOrCreate(
        ['username' => 'cajera.senior.demo'],
        array_merge([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'role_id' => $roleId,
            'name' => 'Cajera Senior Demo',
            'email' => null,
            'password' => null,
            'status' => 'active',
        ], cashScopePinCredentials('2460')),
    );

    StaffProfileModel::query()->updateOrCreate(
        ['user_id' => $user->id],
        [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'staff_role' => 'CASHIER',
            'status' => 'active',
        ],
    );

    UserBranchAccessModel::query()->firstOrCreate([
        'user_id' => $user->id,
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
    ]);

    return nightposLoginPin('2460');
}

function cashScopeChargeOrder(string $cashierToken): int
{
    $productId = nightposSeedOrderProduct();

    $orderResponse = test()->postJson('/api/v1/orders', [
        'table_label' => 'Venta cash scope',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($cashierToken));
    $orderResponse->assertCreated();
    $orderId = (int) $orderResponse->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 2,
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($cashierToken))->assertOk();
    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => 50]],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    return $orderId;
}

function cashScopeCloseCashSession(string $cashierToken, float $amount = 500): void
{
    test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => $amount,
    ], nightposOperationalHeaders($cashierToken))->assertOk();
}

function cashScopeRefreshCashierAToken(): string
{
    return nightposLoginPin('1234');
}

function cashScopeRefreshCashierBToken(): string
{
    cashScopeEnsureCashierB();

    return nightposLoginPin('4321');
}

it('cashier generates settlements only for her cash session', function () {
    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 100, false);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashier))
        ->json('data.session.id');

    cashScopeChargeOrder($cashier);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated()
        ->assertJsonPath('data.created_items', fn ($v) => $v > 0)
        ->assertJsonPath('data.context.scope', 'my_cash_session');

    $settlements = StaffSettlementModel::query()
        ->where('official_shift_id', OfficialShiftModel::query()->where('status', 'OPEN')->value('id'))
        ->where('cash_session_id', $sessionId)
        ->get();

    expect($settlements->count())->toBeGreaterThan(0)
        ->and($settlements->every(fn ($s) => (int) $s->cash_session_id === $sessionId))->toBeTrue();

    test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk()
        ->assertJsonPath('data.context.scope', 'my_cash_session');
});

it('cashier does not generate settlements from another cash session', function () {
    nightposEnsureShiftOpen();
    $cashierA = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashierA, 100, false);
    cashScopeChargeOrder($cashierA);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashierA))
        ->assertCreated();

    nightposResetApiAuth();
    $cashierB = cashScopeRefreshCashierBToken();
    nightposOpenCashSession($cashierB, 50, false);

    $generateB = test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashierB))
        ->assertCreated();

    expect($generateB->json('data.created_items'))->toBe(0);

    $sessionBId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashierB))
        ->json('data.session.id');

    expect(StaffSettlementModel::query()->where('cash_session_id', $sessionBId)->count())->toBe(0);
});

it('cashier does not see settlements from another cash session', function () {
    nightposEnsureShiftOpen();
    $cashierA = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashierA, 100, false);
    cashScopeChargeOrder($cashierA);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashierA))
        ->assertCreated();

    nightposResetApiAuth();
    $cashierB = cashScopeRefreshCashierBToken();
    nightposOpenCashSession($cashierB, 50, false);

    $viewB = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashierB))
        ->assertOk();

    expect($viewB->json('data.context.scope'))->toBe('my_cash_session')
        ->and($viewB->json('data.waiters'))->toBeEmpty()
        ->and($viewB->json('data.summary.total_pending'))->toBe('0.00');

    $viewA = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders(cashScopeRefreshCashierAToken()))
        ->assertOk();

    expect($viewA->json('data.waiters'))->not->toBeEmpty();
});

it('cashier cannot pay settlements from another cash session', function () {
    nightposEnsureShiftOpen();
    $cashierA = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashierA, 100, false);
    cashScopeChargeOrder($cashierA);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashierA))
        ->assertCreated();

    $foreignId = (int) StaffSettlementModel::query()->where('status', 'PENDING')->value('id');
    $foreignSessionId = (int) StaffSettlementModel::query()->where('id', $foreignId)->value('cash_session_id');

    nightposResetApiAuth();
    $cashierB = cashScopeRefreshCashierBToken();
    nightposOpenCashSession($cashierB, 50, false);

    $sessionBId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashierB))
        ->json('data.session.id');

    expect($sessionBId)->not->toBe($foreignSessionId);

    test()->postJson("/api/v1/settlements/{$foreignId}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($cashierB))
        ->assertStatus(422)
        ->assertJsonPath('message', 'No puede pagar liquidaciones de otra caja.');
});

it('cashier close-check is not blocked by pending settlements from another cash session', function () {
    nightposEnsureShiftOpen();
    $cashierA = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashierA, 100, false);
    cashScopeChargeOrder($cashierA);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashierA))
        ->assertCreated();

    nightposResetApiAuth();
    $cashierB = cashScopeRefreshCashierBToken();
    nightposOpenCashSession($cashierB, 50, false);

    $closeCheck = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashierB))
        ->assertOk();

    $types = collect($closeCheck->json('data.blockers'))->pluck('type')->all();
    expect($types)->not->toContain('SETTLEMENTS_PENDING_PAYMENT');
});

it('admin sees all shift settlements including null cash_session_id', function () {
    $admin = cashScopeAdminToken();

    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 100, false);
    cashScopeChargeOrder($cashier);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(cashScopeAdminToken()))
        ->assertCreated();

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    $waiterId = nightposDemoWaiterUserId();

    StaffSettlementModel::query()->create([
        'tenant_id' => (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id'),
        'branch_id' => (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id'),
        'official_shift_id' => $shiftId,
        'cash_session_id' => null,
        'staff_user_id' => $waiterId,
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'total_amount' => 99.00,
        'status' => 'PENDING',
    ]);

    $view = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders(cashScopeAdminToken()))
        ->assertOk();

    expect($view->json('data.context.scope'))->toBe('shift')
        ->and(collect($view->json('data.settlements'))->contains(fn ($s) => $s['cash_session_id'] === null))->toBeTrue();
});

it('cashier senior sees full shift scope like admin', function () {
    $senior = cashScopeSeniorToken();

    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 100, false);
    cashScopeChargeOrder($cashier);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated();

    $view = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders(cashScopeSeniorToken()))
        ->assertOk();

    expect($view->json('data.context.scope'))->toBe('shift')
        ->and($view->json('data.waiters'))->not->toBeEmpty();
});

it('null cash_session settlements do not block basic cashier close-check', function () {
    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 100, false);

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');

    StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'cash_session_id' => null,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'total_amount' => 50.00,
        'status' => 'PENDING',
    ]);

    $closeCheck = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk();

    $types = collect($closeCheck->json('data.blockers'))->pluck('type')->all();
    expect($types)->not->toContain('SETTLEMENTS_PENDING_PAYMENT');
});

it('mark-paid records expense in the paying cashier cash session', function () {
    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 500, false);
    cashScopeChargeOrder($cashier);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashier))
        ->assertCreated();

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashier))
        ->json('data.session.id');

    $settlementId = (int) StaffSettlementModel::query()
        ->where('cash_session_id', $sessionId)
        ->where('status', 'PENDING')
        ->value('id');

    test()->postJson("/api/v1/settlements/{$settlementId}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders($cashier))
        ->assertOk()
        ->assertJsonPath('data.cash_session_id', $sessionId);

    expect(
        \App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel::query()
            ->where('cash_session_id', $sessionId)
            ->where('source_type', 'STAFF_SETTLEMENT')
            ->where('source_id', $settlementId)
            ->exists(),
    )->toBeTrue();
});

it('admin can pay null cash_session settlements', function () {
    $admin = cashScopeAdminToken();

    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 500, false);

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');

    $settlement = StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'cash_session_id' => null,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'total_amount' => 25.00,
        'status' => 'PENDING',
    ]);

    nightposOpenCashSession(cashScopeAdminToken(), 1000, false);

    test()->postJson("/api/v1/settlements/{$settlement->id}/mark-paid", [
        'payment_method' => 'CASH',
    ], nightposOperationalHeaders(cashScopeAdminToken()))
        ->assertOk();
});

it('cashier sees same-session settlements even when they belong to another official shift', function () {
    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 100, false);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashier))
        ->json('data.session.id');
    $sessionShiftId = (int) CashSessionModel::query()->whereKey($sessionId)->value('official_shift_id');

    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');
    $adminId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    $otherShift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Turno alterno scope',
        'shift_type' => 'NIGHT',
        'business_date' => now()->toDateString(),
        'starts_at' => now()->subHours(8),
        'ends_at' => now()->subHours(1),
        'status' => 'CLOSED',
        'opened_by_user_id' => $adminId,
        'opened_at' => now()->subHours(8),
        'closed_at' => now()->subHours(1),
    ]);

    expect((int) $otherShift->id)->not->toBe($sessionShiftId);

    StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => (int) $otherShift->id,
        'cash_session_id' => $sessionId,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'total_amount' => 12.50,
        'status' => 'PENDING',
    ]);

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk();

    expect($response->json('data.context.scope'))->toBe('my_cash_session')
        ->and($response->json('data.context.cash_session_id'))->toBe($sessionId)
        ->and($response->json('data.context.session_official_shift_id'))->toBe($sessionShiftId)
        ->and($response->json('data.context.settlement_official_shift_ids'))->toContain((int) $otherShift->id)
        ->and(collect($response->json('data.waiters'))->pluck('official_shift_id')->all())->toContain((int) $otherShift->id);
});

it('admin current-shift overview remains filtered by official shift', function () {
    $admin = cashScopeAdminToken();
    nightposEnsureShiftOpen();

    $openShiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');
    $adminId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    $otherShift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Turno previo admin scope',
        'shift_type' => 'NIGHT',
        'business_date' => now()->subDay()->toDateString(),
        'starts_at' => now()->subDay()->startOfDay(),
        'ends_at' => now()->subDay()->endOfDay(),
        'status' => 'CLOSED',
        'opened_by_user_id' => $adminId,
        'opened_at' => now()->subDay()->startOfDay(),
        'closed_at' => now()->subDay()->endOfDay(),
    ]);

    StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $openShiftId,
        'cash_session_id' => null,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'total_amount' => 10.00,
        'status' => 'PENDING',
    ]);

    StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => (int) $otherShift->id,
        'cash_session_id' => null,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'total_amount' => 15.00,
        'status' => 'PENDING',
    ]);

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($admin))
        ->assertOk();

    $visibleShiftIds = collect($response->json('data.settlements'))->pluck('official_shift_id')->unique()->all();

    expect($response->json('data.context.scope'))->toBe('shift')
        ->and($visibleShiftIds)->toContain($openShiftId)
        ->and($visibleShiftIds)->not->toContain((int) $otherShift->id);
});

it('cashier still does not see settlements from another cash session', function () {
    nightposEnsureShiftOpen();
    $cashierA = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashierA, 100, false);
    cashScopeChargeOrder($cashierA);

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($cashierA))
        ->assertCreated();

    nightposResetApiAuth();
    $cashierB = cashScopeRefreshCashierBToken();
    nightposOpenCashSession($cashierB, 50, false);

    $viewB = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashierB))
        ->assertOk();

    expect($viewB->json('data.context.scope'))->toBe('my_cash_session')
        ->and($viewB->json('data.waiters'))->toBeEmpty()
        ->and($viewB->json('data.girls'))->toBeEmpty();
});

it('girls settlements remain visible and stable in my cash-session scope', function () {
    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 100, false);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashier))
        ->json('data.session.id');
    $shiftId = (int) CashSessionModel::query()->whereKey($sessionId)->value('official_shift_id');
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');
    $girlId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');

    StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'cash_session_id' => $sessionId,
        'staff_user_id' => $girlId,
        'staff_role' => 'GIRL',
        'settlement_type' => 'GIRL',
        'total_amount' => 30.00,
        'status' => 'PENDING',
    ]);

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk();

    expect($response->json('data.girls'))->not->toBeEmpty();
});

it('waiters with zero commission still appear for cashier current-shift list', function () {
    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 100, false);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashier))
        ->json('data.session.id');
    $shiftId = (int) CashSessionModel::query()->whereKey($sessionId)->value('official_shift_id');
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');

    StaffProfileModel::query()
        ->where('user_id', nightposDemoWaiterUserId())
        ->update(['waiter_commission_percent' => 0]);

    StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => $shiftId,
        'cash_session_id' => $sessionId,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'compensation_mode' => 'MANUAL',
        'compensation_source' => 'REQUIRES_MANUAL_INPUT',
        'total_amount' => 0.00,
        'status' => 'PENDING',
    ]);

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk();

    expect($response->json('data.waiters'))->not->toBeEmpty()
        ->and($response->json('data.waiters.0.compensation_mode'))->toBe('MANUAL');
});

it('cash close-check detects pending settlements by cash session', function () {
    nightposEnsureShiftOpen();
    $cashier = cashScopeRefreshCashierAToken();
    nightposOpenCashSession($cashier, 100, false);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($cashier))
        ->json('data.session.id');
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');
    $adminId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    $otherShift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Turno alterno cash close-check',
        'shift_type' => 'NIGHT',
        'business_date' => now()->toDateString(),
        'starts_at' => now()->subHours(6),
        'ends_at' => now()->subHour(),
        'status' => 'CLOSED',
        'opened_by_user_id' => $adminId,
        'opened_at' => now()->subHours(6),
        'closed_at' => now()->subHour(),
    ]);

    StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => (int) $otherShift->id,
        'cash_session_id' => $sessionId,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'total_amount' => 20.00,
        'status' => 'PENDING',
    ]);

    $check = test()->getJson('/api/v1/cash/session/current/close-check', nightposOperationalHeaders($cashier))
        ->assertOk();

    expect($check->json('data.can_close'))->toBeFalse()
        ->and(collect($check->json('data.blockers'))->pluck('code')->all())->toContain('settlements_pending_payment');
});

it('shift close-check keeps settlement validation by official shift', function () {
    $admin = cashScopeAdminToken();
    nightposEnsureShiftOpen();

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');
    StaffSettlementModel::query()->where('official_shift_id', $shiftId)->update(['status' => 'PAID']);

    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');
    $adminId = (int) UserModel::query()->where('username', 'admin.demo')->value('id');

    $otherShift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Turno externo shift close-check',
        'shift_type' => 'NIGHT',
        'business_date' => now()->subDay()->toDateString(),
        'starts_at' => now()->subDay()->startOfDay(),
        'ends_at' => now()->subDay()->endOfDay(),
        'status' => 'CLOSED',
        'opened_by_user_id' => $adminId,
        'opened_at' => now()->subDay()->startOfDay(),
        'closed_at' => now()->subDay()->endOfDay(),
    ]);

    StaffSettlementModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => (int) $otherShift->id,
        'cash_session_id' => null,
        'staff_user_id' => nightposDemoWaiterUserId(),
        'staff_role' => 'WAITER',
        'settlement_type' => 'WAITER',
        'total_amount' => 40.00,
        'status' => 'PENDING',
    ]);

    $check = test()->getJson('/api/v1/shifts/current/close-check', nightposOperationalHeaders($admin))
        ->assertOk();

    expect((int) $check->json('data.summary.pending_settlements'))->toBe(0);
});
