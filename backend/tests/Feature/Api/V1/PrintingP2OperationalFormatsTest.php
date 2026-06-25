<?php

declare(strict_types=1);

use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function p2AdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function p2RegisterDevice(): void
{
    $name = 'P2 Test Device '.uniqid();

    test()->postJson('/api/v1/print-devices/register', [
        'name' => $name,
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders(p2AdminToken()))->assertCreated();
}

function p2SentOrderWithItem(): array
{
    nightposEnsureShiftOpen();
    p2RegisterDevice();

    $waiterToken = nightposLoginPin('5678');
    $result = nightposCreateOrderWithItem($waiterToken);
    $orderId = $result['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))
        ->assertOk();

    $itemId = (int) \App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel::query()
        ->where('order_id', $orderId)
        ->value('id');

    return compact('orderId', 'itemId', 'waiterToken');
}

function p2PrintJobsForOrder(int $orderId): \Illuminate\Support\Collection
{
    return PrintJobModel::query()
        ->where('source_type', 'order')
        ->where('source_id', $orderId)
        ->orderBy('id')
        ->get();
}

it('creates correction reprint after cancel on SENT_TO_BAR order', function () {
    ['orderId' => $orderId, 'itemId' => $itemId] = p2SentOrderWithItem();
    $cashier = nightposLoginPin('1234');

    expect(p2PrintJobsForOrder($orderId))->toHaveCount(1);

    test()->postJson("/api/v1/orders/{$orderId}/items/{$itemId}/cancel", [
        'reason' => 'Error de producto',
    ], nightposOperationalHeaders($cashier))->assertOk();

    $jobs = p2PrintJobsForOrder($orderId);
    expect($jobs)->toHaveCount(2)
        ->and($jobs->last()->type)->toBe('ORDER_COMMAND')
        ->and($jobs->last()->content_text)->toContain('REIMPRESION');
});

it('does not create correction reprint when correcting OPEN order', function () {
    p2RegisterDevice();
    nightposEnsureShiftOpen();

    $waiter = nightposLoginPin('5678');
    $cashier = nightposLoginPin('1234');
    $productId = corrSeedProductForP2();

    $orderId = (int) test()->postJson('/api/v1/orders', [
        'table_label' => 'Mesa Open P2',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ], nightposOperationalHeaders($waiter))->assertCreated()->json('data.order.id');

    $itemId = (int) test()->postJson("/api/v1/orders/{$orderId}/items", [
        'product_id' => $productId,
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 2,
    ], nightposOperationalHeaders($waiter))->assertCreated()->json('data.order.items.0.id');

    if (! $itemId) {
        $itemId = (int) \App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel::query()
            ->where('order_id', $orderId)
            ->value('id');
    }

    test()->putJson("/api/v1/orders/{$orderId}/items/{$itemId}", [
        'quantity' => 3,
    ], nightposOperationalHeaders($cashier))->assertOk();

    expect(p2PrintJobsForOrder($orderId))->toHaveCount(0);
});

it('correction reprint contains REIMPRESION label', function () {
    ['orderId' => $orderId, 'itemId' => $itemId] = p2SentOrderWithItem();
    $cashier = nightposLoginPin('1234');

    test()->postJson("/api/v1/orders/{$orderId}/items/{$itemId}/cancel", [
        'reason' => 'Correccion test',
    ], nightposOperationalHeaders($cashier))->assertOk();

    $content = (string) p2PrintJobsForOrder($orderId)->last()->content_text;
    expect($content)->toContain('REIMPRESION');
});

it('correction reprint contains correction number', function () {
    ['orderId' => $orderId, 'itemId' => $itemId] = p2SentOrderWithItem();
    $cashier = nightposLoginPin('1234');

    test()->postJson("/api/v1/orders/{$orderId}/items/{$itemId}/cancel", [
        'reason' => 'Primera correccion',
    ], nightposOperationalHeaders($cashier))->assertOk();

    $content = (string) p2PrintJobsForOrder($orderId)->last()->content_text;
    expect($content)->toContain('Correccion #1')
        ->and((int) OrderModel::query()->find($orderId)?->bar_correction_count)->toBe(1);
});

it('precheck content has no fiscal identifiers', function () {
    $builder = app(PrintTicketContentBuilder::class);

    $content = $builder->buildPrecheck([
        'order_number' => 'C-0100',
        'table_label' => 'Mesa 3',
        'opened_at' => '2026-06-21T20:00:00+00:00',
        'total' => '120.00',
        'currency' => 'BOB',
        'items' => [
            ['product_name' => 'Ron', 'quantity' => 2, 'sale_mode' => 'SOLO_CLIENTE', 'item_status' => 'SENT'],
        ],
    ], 'Casa Demo', 'Carlos', 'Salon VIP');

    expect($content)->toContain('PRECUENTA #C-0100')
        ->and($content)->toContain('PENDIENTE DE COBRO')
        ->and($content)->toContain('No tiene validez fiscal')
        ->and($content)->not->toContain('NIT')
        ->and($content)->not->toContain('QR');
});

it('precheck content shows total prominently', function () {
    $builder = app(PrintTicketContentBuilder::class);

    $content = $builder->buildPrecheck([
        'order_number' => 'C-0100',
        'table_label' => 'Mesa 3',
        'opened_at' => '2026-06-21T20:00:00+00:00',
        'total' => '250.00',
        'currency' => 'BOB',
        'items' => [],
    ], 'Casa Demo', null, null);

    expect($content)->toContain('TOTAL')
        ->and($content)->toContain('250.00 BOB');
});

it('sale receipt is created on charge', function () {
    p2RegisterDevice();

    $cashier = nightposLoginPin('1234');
    nightposEnsureShiftOpen();
    test()->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 100,
    ], nightposOperationalHeaders($cashier))->assertCreated();

    $waiterToken = nightposLoginPin('5678');
    $orderId = nightposCreateOrderWithItem($waiterToken)['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))
        ->assertOk();

    $order = test()->getJson("/api/v1/orders/{$orderId}", nightposOperationalHeaders($cashier))
        ->assertOk()
        ->json('data.order');

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [['method' => 'CASH', 'amount' => (float) $order['total']]],
    ], nightposOperationalHeaders($cashier))
        ->assertCreated()
        ->assertJsonPath('data.print_job.type', 'SALE_RECEIPT');
});

it('sale receipt shows payment method', function () {
    $builder = app(PrintTicketContentBuilder::class);

    $content = $builder->buildSaleReceipt(
        [
            'sale_number' => 'V-001',
            'total' => '80.00',
            'currency' => 'BOB',
            'payment_mode' => 'QR',
            'paid_at' => '2026-06-21T21:00:00+00:00',
            'payments' => [['payment_method' => 'QR', 'amount' => '80.00']],
        ],
        ['order_number' => 'C-0100', 'table_label' => 'Mesa 5'],
        'Cajera Demo',
        null,
        null,
        'Centro',
    );

    expect($content)->toContain('PAGADO')
        ->and($content)->toContain('QR')
        ->and($content)->toContain('PAGO #C-0100');
});

it('sale receipt shows mixed payment breakdown', function () {
    $builder = app(PrintTicketContentBuilder::class);

    $content = $builder->buildSaleReceipt(
        [
            'sale_number' => 'V-002',
            'total' => '100.00',
            'currency' => 'BOB',
            'payment_mode' => 'MIXED',
            'paid_at' => '2026-06-21T21:00:00+00:00',
            'payments' => [
                ['payment_method' => 'CASH', 'amount' => '40.00'],
                ['payment_method' => 'QR', 'amount' => '60.00'],
            ],
        ],
        ['order_number' => 'C-0101', 'table_label' => 'Mesa 6'],
        'Cajera Demo',
        null,
        null,
        'Centro',
    );

    expect($content)->toContain('MIXTO')
        ->and($content)->toContain('EFECTIVO')
        ->and($content)->toContain('40.00')
        ->and($content)->toContain('60.00');
});

it('order command has no total or unit prices', function () {
    $builder = app(PrintTicketContentBuilder::class);

    $content = $builder->buildOrderCommand([
        'order_number' => 'C-0001',
        'table_label' => 'Mesa 5',
        'total' => '50.00',
        'currency' => 'BOB',
        'opened_at' => '2026-06-21T20:00:00+00:00',
        'sent_to_bar_at' => '2026-06-21T20:05:00+00:00',
        'items' => [
            [
                'product_name' => 'Paceña',
                'quantity' => 2,
                'unit_price' => '25.00',
                'line_total' => '50.00',
                'sale_mode' => 'SOLO_CLIENTE',
                'item_status' => 'SENT',
            ],
        ],
    ], 'Carlos', 'Salon');

    expect($content)->toContain('COMANDA #C-0001')
        ->and($content)->toContain('EN BARRA')
        ->and($content)->not->toContain('TOTAL')
        ->and($content)->not->toContain('25.00')
        ->and($content)->not->toContain('50.00');
});

it('cashier can print precheck on chargeable order', function () {
    p2RegisterDevice();
    ['orderId' => $orderId] = p2SentOrderWithItem();
    $cashier = nightposLoginPin('1234');

    test()->postJson("/api/v1/orders/{$orderId}/precheck/print", [], nightposOperationalHeaders($cashier))
        ->assertCreated()
        ->assertJsonPath('data.job.type', 'PRECHECK');
});

function corrSeedProductForP2(): int
{
    $tenantId = (int) \App\Infrastructure\Persistence\Eloquent\Models\TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = \App\Infrastructure\Persistence\Eloquent\Models\ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => null,
        'name' => 'P2 Product',
        'product_type' => 'beverage',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'active',
    ]);

    \App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'product_id' => $product->id,
        'sale_mode' => 'SOLO_CLIENTE',
        'price' => 30,
        'currency' => 'BOB',
        'status' => 'active',
    ]);

    return (int) $product->id;
}
