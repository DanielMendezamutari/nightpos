<?php

declare(strict_types=1);

use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\SSE\Repositories\OperationalEventRepositoryInterface;
use App\Domain\SSE\Repositories\SseTokenRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\SseTokenModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    nightposEnsureShiftOpen();
});

// ─── Helpers ─────────────────────────────────────────────────────────────────

function sseAdminToken(): string
{
    return nightposLoginPassword('admin.demo', 'AdminDemo123!');
}

function sseCleaningToken(): string
{
    return nightposLoginPin('3333'); // limpieza.demo PIN
}

function sseHeaders(string $token): array
{
    return nightposOperationalHeaders($token);
}

// ─── Test 1: usuario obtiene token SSE ───────────────────────────────────────

it('authenticated user with branch context can obtain sse token', function () {
    $admin = sseAdminToken();

    $response = test()->postJson('/api/v1/events/token', [], sseHeaders($admin))
        ->assertOk();

    $data = $response->json('data');

    expect($data)->toHaveKey('token');
    expect($data)->toHaveKey('expires_in');
    expect($data['expires_in'])->toBe(60);
    expect(strlen($data['token']))->toBeGreaterThanOrEqual(20);
});

// ─── Test 2: usuario de tenant distinto no recibe eventos del tenant correcto ─

it('user from different tenant cannot read events of another tenant', function () {
    $repo = app(OperationalEventRepositoryInterface::class);

    // Tenant 1, branch 1 emits an event
    $repo->create(1, 1, 'sale.created', ['amount' => 500], null);

    // Query as tenant 2 → should see 0 events
    $events = $repo->findSince(2, 1, null, 0);

    expect($events)->toHaveCount(0);

    // Query as tenant 1 → should see 1 event
    $events = $repo->findSince(1, 1, null, 0);

    expect($events)->toHaveCount(1);
});

// ─── Test 3: stream sin token → 401 ─────────────────────────────────────────

it('stream endpoint without token returns 401', function () {
    test()->getJson('/api/v1/events/stream')->assertUnauthorized();
});

// ─── Test 4: stream con token inválido → 401 ─────────────────────────────────

it('stream endpoint with invalid token returns 401', function () {
    test()->getJson('/api/v1/events/stream?token=invalidtoken123')
        ->assertUnauthorized();
});

// ─── Test 5: token expira – no puede usarse en stream ────────────────────────

it('expired sse token is rejected by stream', function () {
    $admin = sseAdminToken();

    $tokenData = test()->postJson('/api/v1/events/token', [], sseHeaders($admin))
        ->assertOk()
        ->json('data');

    // Manually expire the token
    SseTokenModel::query()
        ->where('token', $tokenData['token'])
        ->update(['expires_at' => Carbon::now()->subSecond()]);

    test()->getJson("/api/v1/events/stream?token={$tokenData['token']}")
        ->assertUnauthorized();
});

// ─── Test 6: stream con token válido → 200 text/event-stream ────────────────

it('stream endpoint with valid token returns 200 with event-stream content type', function () {
    $admin = sseAdminToken();

    $token = test()->postJson('/api/v1/events/token', [], sseHeaders($admin))
        ->assertOk()
        ->json('data.token');

    $response = test()->get("/api/v1/events/stream?token={$token}");

    $response->assertStatus(200);
    expect($response->headers->get('Content-Type'))->toContain('text/event-stream');
});

// ─── Test 7: eventos filtrados por tenant ────────────────────────────────────

it('findSince only returns events for the correct tenant', function () {
    $repo = app(OperationalEventRepositoryInterface::class);

    // Emit for tenant 1 and tenant 2
    $repo->create(1, 1, 'test.event', ['msg' => 'tenant1'], null);
    $repo->create(2, 1, 'test.event', ['msg' => 'tenant2'], null);

    $events = $repo->findSince(1, 1, null, 0);

    expect($events)->toHaveCount(1);
    expect($events[0]['payload']['msg'])->toBe('tenant1');
});

// ─── Test 8: eventos filtrados por sucursal ───────────────────────────────────

it('findSince only returns events for the correct branch', function () {
    $repo = app(OperationalEventRepositoryInterface::class);

    $repo->create(1, 1, 'test.event', ['branch' => 1], null);
    $repo->create(1, 2, 'test.event', ['branch' => 2], null);

    $events = $repo->findSince(1, 1, null, 0);

    expect($events)->toHaveCount(1);
    expect($events[0]['payload']['branch'])->toBe(1);
});

// ─── Test 9: eventos filtrados por rol ───────────────────────────────────────

it('findSince returns broadcast and role-targeted events, not events for other roles', function () {
    $repo = app(OperationalEventRepositoryInterface::class);

    $repo->create(1, 1, 'broadcast.event', ['x' => 1], null);           // visible to all
    $repo->create(1, 1, 'cleaning.event', ['x' => 2], 'cleaning');      // only cleaning
    $repo->create(1, 1, 'cashier.event', ['x' => 3], 'cashier');        // only cashier

    // Cleaning scope should see: broadcast + cleaning (not cashier)
    $cleaningEvents = $repo->findSince(1, 1, 'cleaning', 0);

    expect($cleaningEvents)->toHaveCount(2);
    $types = array_column($cleaningEvents, 'type');
    expect($types)->toContain('broadcast.event');
    expect($types)->toContain('cleaning.event');
    expect($types)->not->toContain('cashier.event');
});

// ─── Test 10: last_event_id devuelve solo eventos nuevos ─────────────────────

it('findSince with last_id returns only newer events', function () {
    $repo = app(OperationalEventRepositoryInterface::class);

    $e1 = $repo->create(1, 1, 'event.one', [], null);
    $e2 = $repo->create(1, 1, 'event.two', [], null);
    $e3 = $repo->create(1, 1, 'event.three', [], null);

    $firstId = (int) $e1['id'];
    $secondId = (int) $e2['id'];

    // Since e1 → should return e2 and e3
    $events = $repo->findSince(1, 1, null, $firstId);

    expect($events)->toHaveCount(2);
    expect($events[0]['id'])->toBe($secondId);

    // Since e2 → should return only e3
    $events2 = $repo->findSince(1, 1, null, $secondId);

    expect($events2)->toHaveCount(1);
    expect($events2[0]['type'])->toBe('event.three');
});

// ─── Test 11: OperationalEventEmitter persiste evento ────────────────────────

it('OperationalEventEmitter creates an event in the repository', function () {
    /** @var OperationalEventEmitter $emitter */
    $emitter = app(OperationalEventEmitter::class);

    $emitter->emit(1, 1, 'room_service.due', ['room_service_id' => 99], 'cleaning');

    $repo = app(OperationalEventRepositoryInterface::class);
    $events = $repo->findSince(1, 1, 'cleaning', 0);

    expect($events)->toHaveCount(1);
    expect($events[0]['type'])->toBe('room_service.due');
    expect($events[0]['payload']['room_service_id'])->toBe(99);
    expect($events[0]['target_role'])->toBe('cleaning');
});

// ─── Test 12: cleaning user gets cleaning scope token ────────────────────────

it('cleaning user receives cleaning role_scope in token', function () {
    $cleaningToken = sseCleaningToken();

    $tokenStr = test()->postJson('/api/v1/events/token', [], sseHeaders($cleaningToken))
        ->assertOk()
        ->json('data.token');

    $ctx = app(SseTokenRepositoryInterface::class)->findValid($tokenStr);

    expect($ctx)->not->toBeNull();
    expect($ctx['role_scope'])->toBe('cleaning');
});

// ─── Test 13: admin user gets null role_scope (broadcast) ────────────────────

it('admin user receives null role_scope (receives all events)', function () {
    $admin = sseAdminToken();

    $tokenStr = test()->postJson('/api/v1/events/token', [], sseHeaders($admin))
        ->assertOk()
        ->json('data.token');

    $ctx = app(SseTokenRepositoryInterface::class)->findValid($tokenStr);

    expect($ctx)->not->toBeNull();
    expect($ctx['role_scope'])->toBeNull();
});
