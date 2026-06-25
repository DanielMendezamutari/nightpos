<?php

declare(strict_types=1);

use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use Carbon\Carbon;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function ccstRegisterDevice(): void
{
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    test()->postJson('/api/v1/print-devices/register', [
        'name' => 'CCST Device '.uniqid(),
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders($token))->assertCreated();
}

it('returns distinct opened_at and closed_at in cash session API', function () {
    ccstRegisterDevice();
    $token = nightposLoginPin('1234');

    Carbon::setTestNow('2026-06-20 21:00:00');
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 300, false);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->json('data.session.id');

    Carbon::setTestNow('2026-06-21 09:00:00');
    nightposPrepareCashSessionClose($token);

    test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 300,
    ], nightposOperationalHeaders($token))->assertOk();

    Carbon::setTestNow();

    $admin = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    $response = test()->getJson("/api/v1/admin/cash-sessions/{$sessionId}", nightposOperationalHeaders($admin))
        ->assertOk();

    $openedAt = $response->json('data.session.opened_at');
    $closedAt = $response->json('data.session.closed_at');

    expect($openedAt)->not->toBeNull()
        ->and($closedAt)->not->toBeNull()
        ->and($openedAt)->not->toBe($closedAt)
        ->and($response->json('data.operational.general.opened_at'))->toBe($openedAt)
        ->and($response->json('data.operational.general.closed_at'))->toBe($closedAt);

    expect(str_contains((string) $openedAt, '21:00') || str_contains((string) $openedAt, 'T21:00'))->toBeTrue();
    expect(str_contains((string) $closedAt, '09:00') || str_contains((string) $closedAt, 'T09:00'))->toBeTrue();
});

it('prints distinct opening and closing times on cash close ticket', function () {
    ccstRegisterDevice();
    $token = nightposLoginPin('1234');

    Carbon::setTestNow('2026-06-20 21:00:00');
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 500, false);
    nightposPrepareCashSessionClose($token);

    Carbon::setTestNow('2026-06-21 09:00:00');

    $response = test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 500,
    ], nightposOperationalHeaders($token))->assertOk();

    Carbon::setTestNow();

    $job = PrintJobModel::query()->find($response->json('data.print_job.id'));
    $content = app(PrintTicketContentBuilder::class)->buildCashClose($job->payload ?? []);

    expect($content)
        ->toContain('Apertura')
        ->toContain('Cierre')
        ->toContain('20/06 21:00')
        ->toContain('21/06 09:00');

    $lines = explode("\n", $content);
    $aperturaLine = collect($lines)->first(fn ($line) => str_contains($line, 'Apertura'));
    $cierreLine = collect($lines)->first(fn ($line) => str_contains($line, 'Cierre') && ! str_contains($line, 'ADMIN'));

    expect($aperturaLine)->not->toBe($cierreLine);
});

it('preserves opened_at after session close in database', function () {
    ccstRegisterDevice();
    $token = nightposLoginPin('1234');

    Carbon::setTestNow('2026-06-20 21:00:00');
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 150, false);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->json('data.session.id');

    $openedBefore = CashSessionModel::query()->find($sessionId)?->opened_at?->format('Y-m-d H:i:s');

    Carbon::setTestNow('2026-06-21 09:00:00');
    nightposPrepareCashSessionClose($token);

    test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 150,
    ], nightposOperationalHeaders($token))->assertOk();

    Carbon::setTestNow();

    $model = CashSessionModel::query()->find($sessionId);

    expect($model?->opened_at?->format('Y-m-d H:i:s'))->toBe($openedBefore)
        ->and($model?->closed_at?->format('Y-m-d H:i:s'))->toBe('2026-06-21 09:00:00')
        ->and($model?->opened_at?->equalTo($model?->closed_at))->toBeFalse();
});
