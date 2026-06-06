<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

it('allows cashier to open and close cash session', function () {
    $token = nightposLoginPin('1234');
    nightposEnsureShiftOpen();

    $this->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 500,
        'opening_notes' => 'Apertura turno',
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.session.status', 'OPEN')
        ->assertJsonPath('data.session.opening_amount', '500.00');

    $reasonId = CashMovementReasonModel::query()
        ->where('type', 'INCOME')
        ->where('name', 'Otros')
        ->value('id');

    $this->postJson('/api/v1/cash/movements', [
        'movement_type' => 'INCOME',
        'amount' => 100,
        'cash_movement_reason_id' => $reasonId,
        'notes' => 'Propina',
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.session.income_total', '100.00');

    $this->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.session.status', 'OPEN');

    $this->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 600,
    ], nightposOperationalHeaders($token))
        ->assertOk()
        ->assertJsonPath('data.session.status', 'CLOSED')
        ->assertJsonPath('data.session.expected_amount', '600.00')
        ->assertJsonPath('data.session.difference_amount', '0.00');
});

it('denies opening two sessions for same cashier', function () {
    $token = nightposLoginPin('1234');
    nightposEnsureShiftOpen();

    $this->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 100,
    ], nightposOperationalHeaders($token))->assertCreated();

    $this->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 200,
    ], nightposOperationalHeaders($token))
        ->assertStatus(422)
        ->assertJsonPath('message', 'Ya tiene una sesión de caja abierta en esta sucursal.');
});

it('allows admin to open cash session with pin', function () {
    $token = nightposLoginPin('2468');
    nightposEnsureShiftOpen();

    $this->postJson('/api/v1/cash/session/open', [
        'opening_amount' => 50,
    ], nightposOperationalHeaders($token))
        ->assertCreated()
        ->assertJsonPath('data.session.opening_amount', '50.00');
});
