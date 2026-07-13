<?php

declare(strict_types=1);

use App\Application\Cash\DTOs\SalesSummaryDTO;
use App\Domain\Cash\Contracts\SummaryBuilders\SalesSummaryBuilder;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposCloseOpenOfficialShifts();
});

function salesSummarySeedProduct(array $priceRows, string $name = 'Producto resumen'): int
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('code', 'CENTRO')->value('id');

    $product = ProductModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => null,
        'name' => $name,
        'product_type' => 'food',
        'unit' => 'unit',
        'track_inventory' => false,
        'status' => 'active',
    ]);

    foreach ($priceRows as $row) {
        ProductPriceModel::query()->create(array_merge([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'product_id' => $product->id,
            'currency' => 'BOB',
            'status' => 'active',
        ], $row));
    }

    return (int) $product->id;
}

it('builds sales summary for a cash session using the project operational test pattern', function () {
    $cashierToken = nightposLoginPin('1234');
    nightposOpenCashSession($cashierToken);
    $girlUserId = (int) UserModel::query()->where('username', 'chica.centro')->value('id');

    $productId = salesSummarySeedProduct([
        ['sale_mode' => 'SOLO_CLIENTE', 'price' => 10],
        ['sale_mode' => 'CON_ACOMPANANTE', 'price' => 20, 'girl_amount' => 10, 'house_amount' => 10],
    ]);

    test()->postJson('/api/v1/direct-sales', [
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'SOLO_CLIENTE', 'quantity' => 1, 'girl_user_id' => null],
        ],
        'payments' => [
            ['method' => 'CASH', 'amount' => 10],
        ],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    test()->postJson('/api/v1/direct-sales', [
        'items' => [
            ['product_id' => $productId, 'sale_mode' => 'CON_ACOMPANANTE', 'quantity' => 1, 'girl_user_id' => $girlUserId],
        ],
        'payments' => [
            ['method' => 'QR', 'amount' => 20],
        ],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    $order = nightposCreateOrderWithItem($cashierToken, [
        'table_label' => 'Mesa Resumen',
        'waiter_user_id' => nightposDemoWaiterUserId(),
    ]);
    $orderId = $order['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/charge", [
        'payments' => [
            ['method' => 'CASH', 'amount' => 30],
            ['method' => 'CARD', 'amount' => 20],
        ],
    ], nightposOperationalHeaders($cashierToken))->assertCreated();

    $cashSessionId = (int) SaleModel::query()->latest('id')->value('cash_session_id');

    $builder = app(SalesSummaryBuilder::class);
    $summary = $builder->build(new \App\Domain\Cash\ValueObjects\CashSessionId($cashSessionId));

    expect($summary)
        ->toBeInstanceOf(SalesSummaryDTO::class)
        ->and($summary->total_sales->amount)->toBe('80.00')
        ->and($summary->sales_count)->toBe(3)
        ->and($summary->average_ticket->amount)->toBe('26.66')
        ->and($summary->sales_cash->amount)->toBe('40.00')
        ->and($summary->sales_qr->amount)->toBe('20.00')
        ->and($summary->sales_card->amount)->toBe('20.00')
        ->and($summary->mixed_sales_count)->toBe(1)
        ->and($summary->sales_by_method)->toBe([
            'cash' => '40.00',
            'qr' => '20.00',
            'card' => '20.00',
        ]);
});
