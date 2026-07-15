<?php

declare(strict_types=1);

use App\Application\Cash\DTOs\SalesSummaryDTO;
use App\Domain\Cash\Contracts\SummaryBuilders\SalesSummaryBuilder;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Infrastructure\Persistence\Eloquent\Models\BraceletModel;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\SalePaymentModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposCloseOpenOfficialShifts();
});

function commercialSummaryOpenSession(float $opening = 0): CashSessionModel
{
    $token = nightposLoginPin('1234');
    nightposOpenCashSession($token, $opening);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data.session.id');

    return CashSessionModel::query()->findOrFail($sessionId);
}

function commercialSummaryBuild(CashSessionModel $session): SalesSummaryDTO
{
    return app(SalesSummaryBuilder::class)->build(new CashSessionId((int) $session->id));
}

function commercialSummaryCreateOrderSale(CashSessionModel $session, float $amount, string $paymentMode = 'CASH'): void
{
    $cashierId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    $order = OrderModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'official_shift_id' => $session->official_shift_id,
        'order_number' => 'C-TEST-ORD-'.uniqid(),
        'status' => 'BILLED',
        'table_label' => 'Mesa prueba',
        'waiter_user_id' => $waiterId,
        'opened_by_user_id' => $waiterId,
        'subtotal' => number_format($amount, 2, '.', ''),
        'total' => number_format($amount, 2, '.', ''),
        'currency' => 'BOB',
    ]);

    $sale = SaleModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'official_shift_id' => $session->official_shift_id,
        'cash_session_id' => $session->id,
        'order_id' => $order->id,
        'sale_number' => 'V-TEST-ORD-'.uniqid(),
        'cashier_user_id' => $cashierId,
        'waiter_user_id' => $waiterId,
        'subtotal' => number_format($amount, 2, '.', ''),
        'total' => number_format($amount, 2, '.', ''),
        'currency' => 'BOB',
        'payment_mode' => $paymentMode,
        'status' => 'PAID',
        'paid_at' => now(),
    ]);

    SalePaymentModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'sale_id' => $sale->id,
        'payment_method' => $paymentMode,
        'amount' => number_format($amount, 2, '.', ''),
    ]);
}

function commercialSummaryCreateDirectSale(CashSessionModel $session, float $amount, string $paymentMode = 'QR'): void
{
    $cashierId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');

    $sale = SaleModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'official_shift_id' => $session->official_shift_id,
        'cash_session_id' => $session->id,
        'order_id' => null,
        'sale_number' => 'V-TEST-DIR-'.uniqid(),
        'cashier_user_id' => $cashierId,
        'waiter_user_id' => null,
        'subtotal' => number_format($amount, 2, '.', ''),
        'total' => number_format($amount, 2, '.', ''),
        'currency' => 'BOB',
        'payment_mode' => $paymentMode,
        'status' => 'PAID',
        'paid_at' => now(),
    ]);

    SalePaymentModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'sale_id' => $sale->id,
        'payment_method' => $paymentMode,
        'amount' => number_format($amount, 2, '.', ''),
    ]);
}

function commercialSummaryCreateRoomService(CashSessionModel $session, float $amount, string $paymentMethod = 'CASH'): void
{
    $cashierId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');
    $girlId = (int) StaffProfileModel::query()->where('staff_role', 'GIRL')->orderBy('id')->value('user_id');

    RoomServiceModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'official_shift_id' => $session->official_shift_id,
        'cash_session_id' => $session->id,
        'girl_user_id' => $girlId,
        'room_number' => '101',
        'room_label' => 'Pieza prueba',
        'unit_price' => number_format($amount, 2, '.', ''),
        'total_amount' => number_format($amount, 2, '.', ''),
        'girl_percent' => '50.00',
        'gross_girl_amount' => number_format($amount / 2, 2, '.', ''),
        'girl_amount' => number_format($amount / 2, 2, '.', ''),
        'house_amount' => number_format($amount / 2, 2, '.', ''),
        'cleaning_amount' => '0.00',
        'registered_by_user_id' => $cashierId,
        'registered_at' => now(),
        'started_at' => now(),
        'duration_minutes' => 60,
        'expected_ends_at' => now()->addHour(),
        'status' => 'FINISHED',
        'payment_method' => $paymentMethod,
    ]);
}

function commercialSummaryCreateBracelet(CashSessionModel $session, float $amount, string $paymentMethod = 'CARD'): void
{
    $cashierId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');
    $girlId = (int) StaffProfileModel::query()->where('staff_role', 'GIRL')->orderBy('id')->value('user_id');

    BraceletModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'official_shift_id' => $session->official_shift_id,
        'cash_session_id' => $session->id,
        'girl_user_id' => $girlId,
        'quantity' => 1,
        'unit_price' => number_format($amount, 2, '.', ''),
        'total_amount' => number_format($amount, 2, '.', ''),
        'payment_method' => $paymentMethod,
        'registered_by_user_id' => $cashierId,
        'registered_at' => now(),
    ]);
}

it('1) suma comandas, venta directa, pieza y manilla en la venta total', function () {
    $session = commercialSummaryOpenSession();

    commercialSummaryCreateOrderSale($session, 100, 'CASH');
    commercialSummaryCreateDirectSale($session, 50, 'QR');
    commercialSummaryCreateRoomService($session, 80, 'CASH');
    commercialSummaryCreateBracelet($session, 20, 'CARD');

    $summary = commercialSummaryBuild($session);

    expect($summary->total_sales_amount->amount)->toBe('250.00')
        ->and($summary->sales_count)->toBe(4)
        ->and($summary->cash_total->amount)->toBe('180.00')
        ->and($summary->qr_total->amount)->toBe('50.00')
        ->and($summary->card_total->amount)->toBe('20.00')
        ->and($summary->mixed_total->amount)->toBe('0.00')
        ->and($summary->by_source['order_sales'])->toBe('100.00')
        ->and($summary->by_source['direct_sales'])->toBe('50.00')
        ->and($summary->by_source['room_services'])->toBe('80.00')
        ->and($summary->by_source['bracelets'])->toBe('20.00')
        ->and($summary->by_source['other_sales'])->toBe('0.00');
});

it('2) mixed sale entra una sola vez en el total y no duplica por metodo', function () {
    $session = commercialSummaryOpenSession();

    commercialSummaryCreateOrderSale($session, 120, 'MIXED');

    $summary = commercialSummaryBuild($session);

    expect($summary->total_sales_amount->amount)->toBe('120.00')
        ->and($summary->sales_count)->toBe(1)
        ->and($summary->mixed_total->amount)->toBe('120.00')
        ->and($summary->cash_total->amount)->toBe('0.00')
        ->and($summary->qr_total->amount)->toBe('0.00')
        ->and($summary->card_total->amount)->toBe('0.00')
        ->and(array_sum(array_map('floatval', $summary->by_method)))->toBe(120.0);
});

it('3) una comanda no cobrada no entra en venta total', function () {
    $session = commercialSummaryOpenSession();
    $cashierId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');
    $waiterId = (int) UserModel::query()->where('username', 'garzon.demo')->value('id');

    OrderModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'official_shift_id' => $session->official_shift_id,
        'order_number' => 'C-OPEN-'.uniqid(),
        'status' => 'SENT_TO_BAR',
        'table_label' => 'Mesa abierta',
        'waiter_user_id' => $waiterId,
        'opened_by_user_id' => $cashierId,
        'subtotal' => '90.00',
        'total' => '90.00',
        'currency' => 'BOB',
    ]);

    $summary = commercialSummaryBuild($session);

    expect($summary->total_sales_amount->amount)->toBe('0.00')
        ->and($summary->sales_count)->toBe(0);
});

it('4) una venta cancelada no entra en la venta total', function () {
    $session = commercialSummaryOpenSession();

    SaleModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'official_shift_id' => $session->official_shift_id,
        'cash_session_id' => $session->id,
        'order_id' => null,
        'sale_number' => 'V-CANCEL-'.uniqid(),
        'cashier_user_id' => (int) UserModel::query()->where('username', 'cajero.demo')->value('id'),
        'waiter_user_id' => null,
        'subtotal' => '70.00',
        'total' => '70.00',
        'currency' => 'BOB',
        'payment_mode' => 'CASH',
        'status' => 'CANCELLED',
        'paid_at' => now(),
    ]);

    $summary = commercialSummaryBuild($session);

    expect($summary->total_sales_amount->amount)->toBe('0.00')
        ->and($summary->sales_count)->toBe(0);
});

it('5) la suma por origen coincide con la venta total', function () {
    $session = commercialSummaryOpenSession();

    commercialSummaryCreateOrderSale($session, 100, 'CASH');
    commercialSummaryCreateDirectSale($session, 50, 'QR');
    commercialSummaryCreateRoomService($session, 80, 'CASH');
    commercialSummaryCreateBracelet($session, 20, 'CARD');

    $summary = commercialSummaryBuild($session);

    $bySourceTotal = array_sum(array_map('floatval', $summary->by_source));

    expect($bySourceTotal)->toBe(250.0)
        ->and((float) $summary->total_sales_amount->amount)->toBe(250.0);
});

it('6) la suma por metodo coincide con la venta total', function () {
    $session = commercialSummaryOpenSession();

    commercialSummaryCreateOrderSale($session, 100, 'CASH');
    commercialSummaryCreateDirectSale($session, 50, 'QR');
    commercialSummaryCreateRoomService($session, 80, 'CASH');
    commercialSummaryCreateBracelet($session, 20, 'CARD');

    $summary = commercialSummaryBuild($session);

    $byMethodTotal = array_sum(array_map('floatval', $summary->by_method));

    expect($byMethodTotal)->toBe(250.0)
        ->and((float) $summary->total_sales_amount->amount)->toBe(250.0);
});

it('7) un show no entra en la venta total', function () {
    $session = commercialSummaryOpenSession();
    $cashierId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');
    $girlId = (int) StaffProfileModel::query()->where('staff_role', 'GIRL')->orderBy('id')->value('user_id');

    ShowModel::query()->create([
        'tenant_id' => $session->tenant_id,
        'branch_id' => $session->branch_id,
        'official_shift_id' => $session->official_shift_id,
        'cash_session_id' => $session->id,
        'girl_user_id' => $girlId,
        'show_type' => 'SHOW',
        'unit_price' => '150.00',
        'total_amount' => '150.00',
        'payment_method' => 'CASH',
        'registered_by_user_id' => $cashierId,
        'registered_at' => now(),
    ]);

    $summary = commercialSummaryBuild($session);

    expect($summary->total_sales_amount->amount)->toBe('0.00')
        ->and($summary->cash_total->amount)->toBe('0.00');
});