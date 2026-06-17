<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function nightposLoginPin(string $pin = '1234', ?string $tenantSlug = 'casa-demo', ?string $branchCode = 'CENTRO'): string
{
    auth('api')->forgetUser();

    $payload = ['pin' => $pin];

    if ($tenantSlug !== null) {
        $payload['tenant_slug'] = $tenantSlug;
    }

    if ($branchCode !== null) {
        $payload['branch_code'] = $branchCode;
    }

    $response = test()->postJson('/api/v1/auth/login-pin', $payload);
    $response->assertOk();

    nightposResetApiAuth();

    return (string) $response->json('data.token');
}

function nightposLoginPassword(
    string $username,
    string $password,
    ?string $tenantSlug = 'casa-demo',
): string {
    auth('api')->forgetUser();

    $payload = [
        'username' => $username,
        'password' => $password,
    ];

    if ($tenantSlug !== null) {
        $payload['tenant_slug'] = $tenantSlug;
    }

    $response = test()->postJson('/api/v1/auth/login-password', $payload);
    $response->assertOk();

    nightposResetApiAuth();

    return (string) $response->json('data.token');
}

/**
 * @return array<string, string>
 */
function nightposCloseOpenOfficialShifts(): void
{
    \App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel::query()
        ->where('status', 'OPEN')
        ->update([
            'status' => 'CLOSED',
            'closed_at' => now(),
        ]);
}

function nightposOpenShift(
    string $token,
    string $shiftType = 'DAY',
    ?string $businessDate = null,
): void {
    $date = $businessDate ?? date('Y-m-d');

    test()->postJson('/api/v1/shifts/open', [
        'shift_type' => $shiftType,
        'business_date' => $date,
    ], nightposOperationalHeaders($token))->assertCreated();
}

function nightposEnsureShiftOpen(?string $branchCode = 'CENTRO'): void
{
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    nightposResetApiAuth();

    $current = test()->getJson('/api/v1/shifts/current', nightposOperationalHeaders($token, $branchCode));

    if ($current->json('data.shift') === null) {
        nightposResetApiAuth();
        nightposOpenShift($token);
    }

    nightposResetApiAuth();
}

function nightposDemoWaiterUserId(): int
{
    return (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'garzon.demo')
        ->value('id');
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function nightposRoomServicePayload(array $overrides = []): array
{
    $total = (float) ($overrides['total_amount'] ?? $overrides['unit_price'] ?? 100);
    $girlPercent = (float) ($overrides['girl_percent']
        ?? config('nightpos.room_service.default_girl_percent', 50));

    $girlId = $overrides['girl_user_id'] ?? (int) \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
        ->where('username', 'chica.centro')
        ->value('id');

    return array_merge([
        'girl_user_id' => $girlId,
        'room_label' => 'Pieza Test',
        'room_number' => 'T1',
        'total_amount' => $total,
        'girl_percent' => $girlPercent,
        'payment_method' => 'CASH',
        'duration_minutes' => 60,
    ], $overrides);
}

function nightposResetApiAuth(): void
{
    auth('api')->forgetUser();
    \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::unsetToken();
    test()->flushHeaders();
}

function nightposOpenCashSession(string $token, float $openingAmount = 100): void
{
    nightposEnsureShiftOpen();
    nightposResetApiAuth();

    $response = test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => $openingAmount,
    ], nightposOperationalHeaders($token));

    if ($response->status() === 201) {
        return;
    }

    $response
        ->assertStatus(422)
        ->assertJsonPath('message', 'Ya tiene una sesión de caja abierta en esta sucursal.');
}

function nightposOperationalHeaders(string $token, ?string $branchCode = 'CENTRO'): array
{
    nightposResetApiAuth();

    $headers = [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ];

    if ($branchCode !== null) {
        $headers['X-Branch-Code'] = $branchCode;
    }

    return $headers;
}

function nightposSeedOrderProduct(array $extraPrices = []): int
{
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()
        ->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()
        ->where('code', 'CENTRO')->value('id');

    $product = \App\Infrastructure\Persistence\Eloquent\Models\ProductModel::query()->create([
        'tenant_id'       => $tenantId,
        'branch_id'       => null,
        'name'            => 'Cerveza Comanda',
        'product_type'    => 'beverage',
        'unit'            => 'unit',
        'track_inventory' => false,
        'status'          => 'active',
    ]);

    \App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel::query()->create([
        'tenant_id'  => $tenantId,
        'branch_id'  => $branchId,
        'product_id' => $product->id,
        'sale_mode'  => 'SOLO_CLIENTE',
        'price'      => 25,
        'currency'   => 'BOB',
        'status'     => 'active',
    ]);

    foreach ($extraPrices as $priceRow) {
        \App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel::query()->create(array_merge([
            'tenant_id'  => $tenantId,
            'branch_id'  => $branchId,
            'product_id' => $product->id,
            'currency'   => 'BOB',
            'status'     => 'active',
        ], $priceRow));
    }

    return (int) $product->id;
}

/**
 * @param  array<string, mixed>  $orderPayload
 * @param  array<string, mixed>  $itemPayload
 * @return array{order_id: int, product_id: int}
 */
function nightposCreateOrderWithItem(string $token, array $orderPayload = [], array $itemPayload = []): array
{
    nightposEnsureShiftOpen();

    $productId = nightposSeedOrderProduct();

    $orderResponse = test()->postJson('/api/v1/orders', array_merge([
        'table_label'    => 'Mesa 5',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], $orderPayload), nightposOperationalHeaders($token));

    $orderResponse->assertCreated();
    $orderId = (int) $orderResponse->json('data.order.id');

    test()->postJson("/api/v1/orders/{$orderId}/items", array_merge([
        'product_id' => $productId,
        'sale_mode'  => 'SOLO_CLIENTE',
        'quantity'   => 2,
    ], $itemPayload), nightposOperationalHeaders($token))
        ->assertCreated();

    return ['order_id' => $orderId, 'product_id' => $productId];
}

/**
 * Prepara cierre de caja: genera/paga liquidaciones y cancela comandas abiertas del turno.
 */
function nightposPrepareCashSessionClose(string $cashierToken, ?string $adminToken = null): void
{
    $adminToken ??= nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $shiftId = (int) \App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel::query()
        ->where('status', 'OPEN')
        ->value('id');

    if ($shiftId === 0) {
        return;
    }

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders($adminToken))
        ->assertCreated();

    $pendingIds = \App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel::query()
        ->where('official_shift_id', $shiftId)
        ->where('status', 'PENDING')
        ->pluck('id');

    foreach ($pendingIds as $id) {
        test()->postJson("/api/v1/settlements/{$id}/mark-paid", [
            'payment_method' => 'CASH',
        ], nightposOperationalHeaders($cashierToken))
            ->assertOk();
    }

    \App\Infrastructure\Persistence\Eloquent\Models\OrderModel::query()
        ->where('official_shift_id', $shiftId)
        ->whereIn('status', ['OPEN', 'SENT_TO_BAR'])
        ->update(['status' => 'CANCELLED', 'cancelled_at' => now()]);
}
