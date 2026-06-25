<?php

declare(strict_types=1);

use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomServiceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function rspRegisterDevice(): void
{
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    test()->postJson('/api/v1/print-devices/register', [
        'name' => 'Pieza Print '.uniqid(),
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders($token))->assertCreated();
}

function rspCashierToken(): string
{
    return nightposLoginPin('1234');
}

function rspGirlId(): int
{
    return (int) UserModel::query()->where('username', 'chica.centro')->value('id');
}

function rspRoomPayload(array $overrides = []): array
{
    return array_merge([
        'girl_user_id' => rspGirlId(),
        'room_label' => 'Pieza 301',
        'room_number' => '301',
        'total_amount' => 200,
        'girl_percent' => 60,
        'payment_method' => 'CASH',
        'duration_minutes' => 60,
        'started_at' => '2026-06-21 22:00:00',
    ], $overrides);
}

function rspShowPayload(array $overrides = []): array
{
    return array_merge([
        'girl_user_id' => rspGirlId(),
        'show_type' => 'PRIVATE',
        'unit_price' => 150,
        'payment_method' => 'CASH',
    ], $overrides);
}

function rspCreateRoomService(?string $token = null, array $overrides = []): \Illuminate\Testing\TestResponse
{
    $token ??= rspCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 100, false);

    return test()->postJson('/api/v1/room-services', rspRoomPayload($overrides), nightposOperationalHeaders($token));
}

function rspCreateShow(?string $token = null, array $overrides = []): \Illuminate\Testing\TestResponse
{
    $token ??= rspCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 100, false);

    return test()->postJson('/api/v1/shows', rspShowPayload($overrides), nightposOperationalHeaders($token));
}

it('registers room service record on pieza create', function () {
    rspRegisterDevice();

    $response = rspCreateRoomService()->assertCreated();

    expect(RoomServiceModel::query()->whereKey($response->json('data.room_service.id'))->exists())->toBeTrue();
});

it('creates linked order on pieza create', function () {
    rspRegisterDevice();

    $response = rspCreateRoomService()->assertCreated();
    $serviceId = (int) $response->json('data.room_service.id');

    expect($response->json('data.room_service.order_id'))->toBeInt();

    $order = OrderModel::query()->find($response->json('data.room_service.order_id'));
    expect($order)->not->toBeNull()
        ->and($order->source_type)->toBe('ROOM_SERVICE')
        ->and($order->source_id)->toBe($serviceId);
});

it('creates ROOM_SERVICE print job on pieza create', function () {
    rspRegisterDevice();

    $response = rspCreateRoomService()->assertCreated();
    $serviceId = (int) $response->json('data.room_service.id');

    expect($response->json('data.print_job.type'))->toBe('ROOM_SERVICE');

    $job = PrintJobModel::query()
        ->where('source_type', 'room_service')
        ->where('source_id', $serviceId)
        ->first();

    expect($job)->not->toBeNull()
        ->and($job->type)->toBe('ROOM_SERVICE');
});

it('room service ticket contains girl name', function () {
    rspRegisterDevice();
    $response = rspCreateRoomService()->assertCreated();

    $content = (string) PrintJobModel::query()
        ->where('source_type', 'room_service')
        ->where('source_id', $response->json('data.room_service.id'))
        ->value('content_text');

    expect($content)->toContain('Chica')
        ->and($content)->toContain('Chica Centro');
});

it('room service ticket contains room label', function () {
    rspRegisterDevice();
    $response = rspCreateRoomService()->assertCreated();

    $content = (string) PrintJobModel::query()
        ->where('source_type', 'room_service')
        ->where('source_id', $response->json('data.room_service.id'))
        ->value('content_text');

    expect($content)->toContain('PIEZA')
        ->and($content)->toContain('301');
});

it('room service ticket contains total amount', function () {
    rspRegisterDevice();
    $response = rspCreateRoomService()->assertCreated();

    $content = (string) PrintJobModel::query()
        ->where('source_type', 'room_service')
        ->where('source_id', $response->json('data.room_service.id'))
        ->value('content_text');

    expect($content)->toContain('200.00');
});

it('room service ticket contains 60 percent girl and 40 percent house split', function () {
    rspRegisterDevice();
    $response = rspCreateRoomService()->assertCreated();

    $content = (string) PrintJobModel::query()
        ->where('source_type', 'room_service')
        ->where('source_id', $response->json('data.room_service.id'))
        ->value('content_text');

    expect($content)->toContain('60.00%')
        ->and($content)->toContain('120.00')
        ->and($content)->toContain('80.00');
});

it('stores cash_session_id on room service', function () {
    rspRegisterDevice();
    $response = rspCreateRoomService()->assertCreated();

    expect($response->json('data.room_service.cash_session_id'))->toBeInt();

    $model = RoomServiceModel::query()->find($response->json('data.room_service.id'));
    expect($model?->cash_session_id)->toBeInt();
});

it('settlement uses 60 percent girl amount for pieza', function () {
    rspRegisterDevice();
    $response = rspCreateRoomService()->assertCreated();
    $serviceId = (int) $response->json('data.room_service.id');
    $token = rspCashierToken();

    test()->postJson("/api/v1/room-services/{$serviceId}/finish", [], nightposOperationalHeaders($token))
        ->assertOk();

    test()->postJson('/api/v1/settlements/generate-current-shift', [], nightposOperationalHeaders(nightposLoginPassword('admin.demo', 'AdminDemo123!')))
        ->assertCreated();

    $item = StaffSettlementItemModel::query()
        ->where('source_type', 'GIRL_ROOM')
        ->where('source_id', $serviceId)
        ->first();

    expect($item)->not->toBeNull()
        ->and((string) $item->amount)->toBe('120.00');
});

it('creates SHOW_TICKET print job on show create', function () {
    rspRegisterDevice();

    $response = rspCreateShow()->assertCreated();
    $showId = (int) $response->json('data.show.id');

    expect($response->json('data.print_job.type'))->toBe('SHOW_TICKET');

    $job = PrintJobModel::query()
        ->where('source_type', 'show')
        ->where('source_id', $showId)
        ->first();

    expect($job)->not->toBeNull()
        ->and($job->type)->toBe('SHOW_TICKET');
});

it('show ticket contains girl and amount', function () {
    rspRegisterDevice();
    $response = rspCreateShow()->assertCreated();

    $content = (string) PrintJobModel::query()
        ->where('source_type', 'show')
        ->where('source_id', $response->json('data.show.id'))
        ->value('content_text');

    expect($content)->toContain('SHOW')
        ->and($content)->toContain('Chica Centro')
        ->and($content)->toContain('150.00');
});

it('registers pieza without failing when no active printer', function () {
    $response = rspCreateRoomService()->assertCreated();

    expect($response->json('data.print_job'))->toBeNull()
        ->and($response->json('data.print_warning'))->toContain('impresora');
});

it('builder unit room service ticket has no fiscal qr', function () {
    $builder = app(PrintTicketContentBuilder::class);

    $content = $builder->buildRoomService([
        'room_label' => 'Pieza VIP',
        'girl_name' => 'Ana',
        'started_at' => '2026-06-21T22:00:00+00:00',
        'duration_minutes' => 60,
        'total_amount' => '200.00',
        'girl_percent' => '60.00',
        'gross_girl_amount' => '120.00',
        'girl_amount' => '120.00',
        'house_amount' => '80.00',
        'cleaning_amount' => '0.00',
        'status' => 'ACTIVE',
        'registered_by_name' => 'Cajera Demo',
    ], 'Centro');

    expect($content)->toContain('PIEZA')
        ->and($content)->not->toContain('NIT')
        ->and($content)->not->toContain('QR');
});
