<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OperationalEventModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

function forceCloseAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function forceCloseCashierToken(): string
{
    return nightposLoginPin('1234');
}

function forceCloseHeaders(?string $token = null): array
{
    return nightposOperationalHeaders($token ?? forceCloseAdminToken());
}

function forceCloseOpenCashierSession(?string $token = null): int
{
    $token ??= forceCloseCashierToken();
    nightposOpenCashSession($token, 150);

    return (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data.session.id');
}

function forceClosePayload(array $overrides = []): array
{
    return array_merge([
        'forced_close_reason' => 'cashier_left',
        'forced_close_notes' => 'La cajera se retiró sin cerrar caja.',
        'declared_closing_amount' => null,
    ], $overrides);
}

it('denies basic cashier from force-close', function () {
    $sessionId = forceCloseOpenCashierSession();

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        forceClosePayload(),
        forceCloseHeaders(forceCloseCashierToken()),
    )->assertForbidden();
});

it('allows admin to force-close open session in their branch', function () {
    $sessionId = forceCloseOpenCashierSession();

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        forceClosePayload(),
        forceCloseHeaders(),
    )
        ->assertOk()
        ->assertJsonPath('data.session.status', 'CLOSED')
        ->assertJsonPath('data.session.is_forced_close', true)
        ->assertJsonPath('data.session.forced_close_reason', 'cashier_left');

    expect(CashSessionModel::query()->find($sessionId)?->status)->toBe('CLOSED');
});

it('denies admin from force-closing session in another branch', function () {
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');

    $otherBranch = BranchModel::query()->create([
        'tenant_id' => $tenantId,
        'name' => 'Sucursal Norte',
        'code' => 'NORTE',
        'status' => 'active',
    ]);

    $shiftId = (int) OfficialShiftModel::query()->where('status', 'OPEN')->value('id');

    $session = CashSessionModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $otherBranch->id,
        'official_shift_id' => $shiftId,
        'opened_by_user_id' => (int) UserModel::query()->where('username', 'cajero.demo')->value('id'),
        'status' => 'OPEN',
        'opening_amount' => 100,
        'opened_at' => now(),
    ]);

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$session->id}/force-close",
        forceClosePayload(),
        forceCloseHeaders(),
    )->assertForbidden();
});

it('requires forced_close_reason', function () {
    $sessionId = forceCloseOpenCashierSession();

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        [
            'forced_close_notes' => 'Notas válidas para el cierre.',
        ],
        forceCloseHeaders(),
    )->assertStatus(422);
});

it('requires forced_close_notes', function () {
    $sessionId = forceCloseOpenCashierSession();

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        [
            'forced_close_reason' => 'operational_error',
        ],
        forceCloseHeaders(),
    )->assertStatus(422);
});

it('stores blockers snapshot on force-close', function () {
    $token = forceCloseCashierToken();
    $sessionId = forceCloseOpenCashierSession($token);

    nightposCreateOrderWithItem($token, ['table_label' => 'Force close blockers']);
    $orderId = (int) OrderModel::query()->where('table_label', 'Force close blockers')->value('id');
    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($token))->assertOk();

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        forceClosePayload(['forced_close_reason' => 'blockers_unresolved']),
        forceCloseHeaders(),
    )->assertOk();

    $session = CashSessionModel::query()->find($sessionId);
    $snapshot = $session?->close_blockers_snapshot;

    expect($snapshot)->toBeArray();
    expect($snapshot['can_close'] ?? null)->toBeFalse();

    $codes = collect($snapshot['blockers'] ?? [])->pluck('code')->all();
    expect($codes)->toContain('active_orders');
});

it('stores financial summary snapshot on force-close', function () {
    $sessionId = forceCloseOpenCashierSession();

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        forceClosePayload(),
        forceCloseHeaders(),
    )->assertOk();

    $session = CashSessionModel::query()->find($sessionId);

    expect($session?->financial_summary_snapshot)->toBeArray();
    expect($session?->financial_summary_snapshot)->toHaveKey('expected_cash');
    expect($session?->expected_amount)->not->toBeNull();
    expect($session?->declared_closing_amount)->toBeNull();
    expect($session?->difference_amount)->toBeNull();
});

it('does not pay settlements on force-close', function () {
    $cashierToken = forceCloseCashierToken();
    $waiterToken = nightposLoginPin('5678');
    $sessionId = forceCloseOpenCashierSession($cashierToken);

    $result = nightposCreateOrderWithItem($waiterToken, ['table_label' => 'Pending settlement force-close']);
    $total = (string) test()->getJson("/api/v1/orders/{$result['order_id']}", nightposOperationalHeaders($waiterToken))
        ->assertOk()
        ->json('data.order.total');

    test()->postJson("/api/v1/orders/{$result['order_id']}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => (float) $total]],
    ], forceCloseHeaders($cashierToken))->assertCreated();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], forceCloseHeaders())
        ->assertCreated();

    $pendingBefore = StaffSettlementModel::query()->where('status', 'PENDING')->count();
    expect($pendingBefore)->toBeGreaterThan(0);

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        forceClosePayload(),
        forceCloseHeaders(),
    )->assertOk();

    expect(StaffSettlementModel::query()->where('status', 'PENDING')->count())->toBe($pendingBefore);
});

it('allows opening a new cash session after force-close', function () {
    $cashierToken = forceCloseCashierToken();
    $sessionId = forceCloseOpenCashierSession($cashierToken);

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        forceClosePayload(),
        forceCloseHeaders(),
    )->assertOk();

    nightposOpenCashSession($cashierToken, 50);

    expect(CashSessionModel::query()->where('status', 'OPEN')->count())->toBe(1);
});

it('returns force-close metadata in admin fiscalization detail', function () {
    $sessionId = forceCloseOpenCashierSession();

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        forceClosePayload(),
        forceCloseHeaders(),
    )->assertOk();

    test()->getJson("/api/v1/admin/cash-sessions/{$sessionId}", forceCloseHeaders())
        ->assertOk()
        ->assertJsonPath('data.session.is_forced_close', true)
        ->assertJsonPath('data.session.forced_close_reason', 'cashier_left')
        ->assertJsonPath('data.session.forced_closed_by.name', 'Admin Demo');
});

it('keeps normal close-check blocking basic cashier', function () {
    $token = forceCloseCashierToken();
    forceCloseOpenCashierSession($token);

    nightposCreateOrderWithItem($token, ['table_label' => 'Bloqueo normal']);

    test()->getJson('/api/v1/cash/session/current/close-check', forceCloseHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.can_close', false);

    test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 150,
    ], forceCloseHeaders($token))->assertStatus(422);
});

it('emits cash.session.closed SSE with forced true', function () {
    $sessionId = forceCloseOpenCashierSession();

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        forceClosePayload(),
        forceCloseHeaders(),
    )->assertOk();

    $event = OperationalEventModel::query()
        ->where('type', 'cash.session.closed')
        ->orderByDesc('id')
        ->first();

    expect($event)->not->toBeNull();
    expect($event->payload['forced'] ?? null)->toBeTrue();
    expect($event->payload['entity']['id'] ?? null)->toBe($sessionId);
});

it('records audit log cash_session.force_closed', function () {
    $sessionId = forceCloseOpenCashierSession();

    test()->postJson(
        "/api/v1/admin/cash-sessions/{$sessionId}/force-close",
        forceClosePayload(),
        forceCloseHeaders(),
    )->assertOk();

    $log = AuditLogModel::query()
        ->where('action', 'cash_session.force_closed')
        ->where('subject_type', 'cash_session')
        ->where('subject_id', $sessionId)
        ->first();

    expect($log)->not->toBeNull();
});

it('returns close-check preview for admin force-close modal', function () {
    $token = forceCloseCashierToken();
    $sessionId = forceCloseOpenCashierSession($token);

    nightposCreateOrderWithItem($token, ['table_label' => 'Preview blockers']);

    test()->getJson("/api/v1/admin/cash-sessions/{$sessionId}/close-check", forceCloseHeaders())
        ->assertOk()
        ->assertJsonPath('data.can_close', false)
        ->assertJsonStructure(['data' => ['blockers', 'financial_preview', 'session']]);
});
