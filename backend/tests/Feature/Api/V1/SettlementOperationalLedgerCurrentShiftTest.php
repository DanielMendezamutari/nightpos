<?php

declare(strict_types=1);

use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemAllocationModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function ledgerContext(): array
{
    $tenantId = (int) TenantModel::query()->where('slug', 'casa-demo')->value('id');
    $branchId = (int) BranchModel::query()->where('tenant_id', $tenantId)->value('id');

    StaffSettlementItemModel::query()->delete();
    StaffSettlementModel::query()->delete();
    SaleItemAllocationModel::query()->delete();
    SaleItemModel::query()->delete();
    SaleModel::query()->delete();
    OrderItemAllocationModel::query()->delete();
    OrderItemModel::query()->delete();
    OrderModel::query()->delete();

    $cashierId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');
    $girlIds = StaffProfileModel::query()->where('staff_role', 'GIRL')->orderBy('id')->limit(2)->pluck('user_id')->all();

    $shift = OfficialShiftModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'name' => 'Turno Ledger',
        'shift_type' => 'DAY',
        'business_date' => '2026-07-15',
        'starts_at' => '2026-07-15 09:00:00',
        'ends_at' => '2026-07-15 21:00:00',
        'status' => 'OPEN',
        'opened_by_user_id' => $cashierId,
        'opened_at' => '2026-07-15 10:00:00',
    ]);

    $session = CashSessionModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'official_shift_id' => (int) $shift->id,
        'opened_by_user_id' => $cashierId,
        'status' => 'OPEN',
        'opening_amount' => '0.00',
        'opened_at' => '2026-07-15 10:07:39',
    ]);

    return [
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'cashier_id' => $cashierId,
        'waiter_id' => $waiterId,
        'girl_a_id' => (int) $girlIds[0],
        'girl_b_id' => (int) $girlIds[1],
        'shift_id' => (int) $shift->id,
        'cash_session_id' => (int) $session->id,
    ];
}

function ledgerCreateOrder247(array $ctx): array
{
    $order = OrderModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_id'],
        'order_number' => 'C-0247-T',
        'status' => 'SENT_TO_BAR',
        'table_label' => 'Mesa Ledger',
        'waiter_user_id' => $ctx['waiter_id'],
        'opened_by_user_id' => $ctx['waiter_id'],
        'subtotal' => '370.00',
        'total' => '370.00',
        'currency' => 'BOB',
        'sent_to_bar_at' => '2026-07-15 10:09:22',
        'created_at' => '2026-07-15 10:08:45',
        'updated_at' => '2026-07-15 10:09:22',
    ]);

    $water = OrderItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'order_id' => (int) $order->id,
        'product_id' => 1,
        'product_name' => 'Agua',
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'unit_price' => '80.00',
        'line_total' => '80.00',
        'girl_amount' => '40.00',
        'house_amount' => '40.00',
        'girl_user_id' => $ctx['girl_a_id'],
        'item_status' => 'SENT',
        'created_at' => '2026-07-15 10:08:57',
        'updated_at' => '2026-07-15 10:08:57',
    ]);

    $combo = OrderItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'order_id' => (int) $order->id,
        'product_id' => 2,
        'product_name' => 'combo 3 corona',
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'unit_price' => '240.00',
        'line_total' => '240.00',
        'girl_amount' => '120.00',
        'house_amount' => '120.00',
        'girl_user_id' => null,
        'item_status' => 'SENT',
        'created_at' => '2026-07-15 10:09:09',
        'updated_at' => '2026-07-15 10:09:09',
    ]);

    OrderItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'order_id' => (int) $order->id,
        'product_id' => 3,
        'product_name' => 'HUARI',
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
        'unit_price' => '50.00',
        'line_total' => '50.00',
        'girl_amount' => null,
        'house_amount' => null,
        'girl_user_id' => null,
        'item_status' => 'SENT',
        'created_at' => '2026-07-15 10:09:20',
        'updated_at' => '2026-07-15 10:09:20',
    ]);

    $allocation = OrderItemAllocationModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'order_item_id' => (int) $combo->id,
        'girl_user_id' => $ctx['girl_b_id'],
        'units' => 3,
        'unit_amount' => '40.00',
        'total_amount' => '120.00',
        'allocation_type' => 'GIRL_BRACELET_UNITS',
        'created_at' => '2026-07-15 10:09:09',
        'updated_at' => '2026-07-15 10:09:09',
    ]);

    return [
        'order_id' => (int) $order->id,
        'water_item_id' => (int) $water->id,
        'combo_item_id' => (int) $combo->id,
        'allocation_id' => (int) $allocation->id,
    ];
}

it('muestra chicas y garzon como provisionales cuando la comanda sigue sent_to_bar sin sale', function () {
    $ctx = ledgerContext();
    ledgerCreateOrder247($ctx);

    $cashier = nightposLoginPin('1234');

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk();

    $girls = collect($response->json('data.girls'))->keyBy('staff_user_id');
    $waiters = collect($response->json('data.waiters'))->keyBy('staff_user_id');

    expect($response->json('data.summary.total_girls'))->toBe('0.00')
        ->and($response->json('data.summary.total_girls_provisional'))->toBe('160.00')
        ->and($response->json('data.summary.total_waiters_provisional_sales'))->toBe('370.00')
        ->and($girls->get($ctx['girl_a_id'])['total_amount'])->toBe('0.00')
        ->and($girls->get($ctx['girl_a_id'])['provisional_total_amount'])->toBe('40.00')
        ->and($girls->get($ctx['girl_a_id'])['status'])->toBe('PROVISIONAL')
        ->and($girls->get($ctx['girl_b_id'])['total_amount'])->toBe('0.00')
        ->and($girls->get($ctx['girl_b_id'])['provisional_total_amount'])->toBe('120.00')
        ->and($waiters->get($ctx['waiter_id'])['sales_total_amount'])->toBe('0.00')
        ->and($waiters->get($ctx['waiter_id'])['provisional_sales_total_amount'])->toBe('370.00')
        ->and($waiters->get($ctx['waiter_id'])['status'])->toBe('PROVISIONAL');
});

it('al cobrar la comanda el provisional pasa a confirmado para chicas y garzon', function () {
    $ctx = ledgerContext();
    $order = ledgerCreateOrder247($ctx);

    $sale = SaleModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'official_shift_id' => $ctx['shift_id'],
        'cash_session_id' => $ctx['cash_session_id'],
        'order_id' => $order['order_id'],
        'sale_number' => 'V-LEDGER-1',
        'cashier_user_id' => $ctx['cashier_id'],
        'waiter_user_id' => $ctx['waiter_id'],
        'subtotal' => '370.00',
        'total' => '370.00',
        'currency' => 'BOB',
        'payment_mode' => 'CASH',
        'status' => 'PAID',
        'paid_at' => '2026-07-15 10:12:00',
        'created_at' => '2026-07-15 10:12:00',
        'updated_at' => '2026-07-15 10:12:00',
    ]);

    $waterSaleItem = SaleItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'sale_id' => (int) $sale->id,
        'order_item_id' => $order['water_item_id'],
        'product_id' => 1,
        'product_name_snapshot' => 'Agua',
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'unit_price_snapshot' => '80.00',
        'line_total' => '80.00',
        'girl_user_id' => $ctx['girl_a_id'],
        'girl_amount_snapshot' => '40.00',
        'house_amount_snapshot' => '40.00',
        'waiter_commission_percent_snapshot' => '0.00',
        'waiter_commission_amount_snapshot' => '0.00',
    ]);

    $comboSaleItem = SaleItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'sale_id' => (int) $sale->id,
        'order_item_id' => $order['combo_item_id'],
        'product_id' => 2,
        'product_name_snapshot' => 'combo 3 corona',
        'sale_mode' => 'CON_ACOMPANANTE',
        'quantity' => 1,
        'unit_price_snapshot' => '240.00',
        'line_total' => '240.00',
        'girl_user_id' => null,
        'girl_amount_snapshot' => '120.00',
        'house_amount_snapshot' => '120.00',
        'waiter_commission_percent_snapshot' => '0.00',
        'waiter_commission_amount_snapshot' => '0.00',
    ]);

    SaleItemModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'sale_id' => (int) $sale->id,
        'order_item_id' => null,
        'product_id' => 3,
        'product_name_snapshot' => 'HUARI',
        'sale_mode' => 'SOLO_CLIENTE',
        'quantity' => 1,
        'unit_price_snapshot' => '50.00',
        'line_total' => '50.00',
        'girl_user_id' => null,
        'girl_amount_snapshot' => null,
        'house_amount_snapshot' => null,
        'waiter_commission_percent_snapshot' => '0.00',
        'waiter_commission_amount_snapshot' => '0.00',
    ]);

    SaleItemAllocationModel::query()->create([
        'tenant_id' => $ctx['tenant_id'],
        'branch_id' => $ctx['branch_id'],
        'sale_item_id' => (int) $comboSaleItem->id,
        'girl_user_id' => $ctx['girl_b_id'],
        'units' => 3,
        'unit_amount_snapshot' => '40.00',
        'total_amount_snapshot' => '120.00',
        'source_order_item_allocation_id' => $order['allocation_id'],
        'allocation_type' => 'GIRL_BRACELET_UNITS',
    ]);

    app(StaffSettlementRepositoryInterface::class)->syncFromSale($ctx['tenant_id'], $ctx['branch_id'], (int) $sale->id);

    $cashier = nightposLoginPin('1234');

    $response = test()->getJson('/api/v1/settlements/current-shift', nightposOperationalHeaders($cashier))
        ->assertOk();

    $girls = collect($response->json('data.girls'))->keyBy('staff_user_id');
    $waiters = collect($response->json('data.waiters'))->keyBy('staff_user_id');

    expect($response->json('data.summary.total_girls'))->toBe('160.00')
        ->and($response->json('data.summary.total_girls_provisional'))->toBe('0.00')
        ->and($response->json('data.summary.total_waiters_provisional_sales'))->toBe('0.00')
        ->and($girls->get($ctx['girl_a_id'])['total_amount'])->toBe('40.00')
        ->and($girls->get($ctx['girl_a_id'])['provisional_total_amount'])->toBe('0.00')
        ->and($girls->get($ctx['girl_b_id'])['total_amount'])->toBe('120.00')
        ->and($girls->get($ctx['girl_b_id'])['provisional_total_amount'])->toBe('0.00')
        ->and($waiters->get($ctx['waiter_id'])['sales_total_amount'])->toBe('370.00')
        ->and($waiters->get($ctx['waiter_id'])['provisional_sales_total_amount'])->toBe('0.00');
});
