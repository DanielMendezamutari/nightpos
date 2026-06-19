<?php

declare(strict_types=1);

use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function printAgentHeaders(string $deviceKey): array
{
    return [
        'Authorization' => 'Bearer '.$deviceKey,
        'Accept' => 'application/json',
    ];
}

function registerPrintDevice(string $token, string $name = 'PC Barra'): array
{
    $response = test()->postJson('/api/v1/print-devices/register', [
        'name' => $name,
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders($token));

    $response->assertCreated();

    return [
        'device' => $response->json('data.device'),
        'device_key' => $response->json('data.device_key'),
    ];
}

it('registers a print device and returns device key once', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $result = registerPrintDevice($token);

    expect($result['device_key'])->toStartWith('npd_live_');
    expect($result['device']['device_key_prefix'])->toBe(substr($result['device_key'], 0, 12));
});

it('creates ORDER_COMMAND print job when sending order to bar', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    registerPrintDevice($adminToken);

    $waiterToken = nightposLoginPin('5678');
    $orderId = nightposCreateOrderWithItem($waiterToken)['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))
        ->assertOk();

    test()->getJson('/api/v1/print-jobs', nightposOperationalHeaders($adminToken))
        ->assertOk()
        ->assertJsonPath('data.jobs.0.type', 'ORDER_COMMAND')
        ->assertJsonPath('data.jobs.0.source_id', $orderId)
        ->assertJsonPath('data.jobs.0.status', 'PENDING');
});

it('does not duplicate print job on repeated send idempotency', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    registerPrintDevice($adminToken);

    $waiterToken = nightposLoginPin('5678');
    $orderId = nightposCreateOrderWithItem($waiterToken)['order_id'];

    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))->assertOk();

    $jobs = test()->getJson('/api/v1/print-jobs', nightposOperationalHeaders($adminToken))
        ->json('data.jobs');

    expect($jobs)->toHaveCount(1);
});

it('scopes pending jobs to device branch only', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $centro = registerPrintDevice($adminToken, 'Centro Device');

    $waiterToken = nightposLoginPin('5678');
    $orderId = nightposCreateOrderWithItem($waiterToken)['order_id'];
    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))->assertOk();

    test()->getJson('/api/v1/print-jobs/pending', printAgentHeaders($centro['device_key']))
        ->assertOk()
        ->assertJsonCount(1, 'data.jobs');

    test()->getJson('/api/v1/print-jobs/pending', printAgentHeaders('npd_live_invalid_key_xxxxxxxxxxxx'))
        ->assertUnauthorized();
});

it('claims prints and confirms printed through agent flow', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $device = registerPrintDevice($adminToken);

    $waiterToken = nightposLoginPin('5678');
    $orderId = nightposCreateOrderWithItem($waiterToken)['order_id'];
    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))->assertOk();

    $headers = printAgentHeaders($device['device_key']);
    $jobId = test()->getJson('/api/v1/print-jobs/pending', $headers)
        ->json('data.jobs.0.id');

    test()->postJson("/api/v1/print-jobs/{$jobId}/claim", [], $headers)->assertOk();
    test()->postJson("/api/v1/print-jobs/{$jobId}/printed", [], $headers)->assertOk();

    test()->getJson("/api/v1/orders/{$orderId}/print-status", nightposOperationalHeaders($adminToken))
        ->assertOk()
        ->assertJsonPath('data.print_job.status', 'PRINTED');
});

it('rejects second claim on same job', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    $device = registerPrintDevice($adminToken);

    $waiterToken = nightposLoginPin('5678');
    $orderId = nightposCreateOrderWithItem($waiterToken)['order_id'];
    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))->assertOk();

    $headers = printAgentHeaders($device['device_key']);
    $jobId = test()->getJson('/api/v1/print-jobs/pending', $headers)->json('data.jobs.0.id');

    test()->postJson("/api/v1/print-jobs/{$jobId}/claim", [], $headers)->assertOk();
    test()->postJson("/api/v1/print-jobs/{$jobId}/claim", [], $headers)->assertStatus(409);
});

it('allows manual reprint as new job', function () {
    $adminToken = nightposLoginPassword('admin.demo', 'AdminDemo123!');
    registerPrintDevice($adminToken);

    $waiterToken = nightposLoginPin('5678');
    $orderId = nightposCreateOrderWithItem($waiterToken)['order_id'];
    test()->postJson("/api/v1/orders/{$orderId}/send-to-bar", [], nightposOperationalHeaders($waiterToken))->assertOk();

    test()->postJson("/api/v1/orders/{$orderId}/reprint", [], nightposOperationalHeaders($waiterToken))
        ->assertCreated()
        ->assertJsonPath('data.job.type', 'ORDER_COMMAND');

    $jobs = test()->getJson('/api/v1/print-jobs', nightposOperationalHeaders($adminToken))->json('data.jobs');
    expect($jobs)->toHaveCount(2);
});

it('builds order command ticket content with table and items', function () {
    $builder = app(\App\Application\Printing\Services\PrintTicketContentBuilder::class);

    $content = $builder->buildOrderCommand([
        'order_number' => 'C-0001',
        'table_label' => 'Mesa 5',
        'total' => '50.00',
        'currency' => 'BOB',
        'sent_to_bar_at' => '2026-06-17T20:00:00+00:00',
        'items' => [
            [
                'product_name' => 'Paceña',
                'quantity' => 2,
                'sale_mode' => 'SOLO_CLIENTE',
                'item_status' => 'SENT',
                'line_total' => '50.00',
            ],
        ],
    ], 'Carlos', 'Salon');

    expect($content)->toContain('COMANDA BAR');
    expect($content)->toContain('Mesa 5');
    expect($content)->toContain('Carlos');
    expect($content)->toContain('2x Paceña');
});
