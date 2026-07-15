<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
    config()->set('nightpos.printing.max_lines_per_personnel_ticket_80mm', 58);
});

function cppRegisterDevice(): void
{
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!');

    test()->postJson('/api/v1/print-devices/register', [
        'name' => 'CPP Device '.uniqid(),
        'paper_width_mm' => 80,
    ], nightposOperationalHeaders($token))->assertCreated();
}

function cppCashierToken(): string
{
    return nightposLoginPin('1234');
}

function cppRoleIdByStaffRole(string $staffRole): int
{
    $userId = (int) StaffProfileModel::query()->where('staff_role', $staffRole)->value('user_id');

    return (int) UserModel::query()->whereKey($userId)->value('role_id');
}

function cppCreateStaffUser(int $tenantId, int $branchId, int $roleId, string $staffRole, string $nameBase, int $index): int
{
    $username = strtolower($staffRole).'.cppt.'.str_pad((string) $index, 3, '0', STR_PAD_LEFT).'.'.uniqid();

    $user = UserModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'role_id' => $roleId,
        'name' => $nameBase.' '.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
        'username' => $username,
        'email' => $username.'@nightpos.test',
        'password' => bcrypt('secret123'),
        'status' => 'active',
    ]);

    StaffProfileModel::query()->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'user_id' => (int) $user->id,
        'staff_role' => $staffRole,
        'status' => 'active',
        'waiter_commission_percent' => $staffRole === 'WAITER' ? '8.00' : null,
    ]);

    return (int) $user->id;
}

/**
 * @return array{response:\Illuminate\Testing\TestResponse, session_id:int, tenant_id:int, branch_id:int}
 */
function cppCloseSessionWithPersonnel(int $girls, int $waiters = 0, int $cleaning = 0): array
{
    cppRegisterDevice();
    $token = cppCashierToken();

    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 100, false);

    $sessionId = (int) test()->getJson('/api/v1/cash/session/current', nightposOperationalHeaders($token))
        ->assertOk()
        ->json('data.session.id');

    $session = CashSessionModel::query()->findOrFail($sessionId);
    $tenantId = (int) $session->tenant_id;
    $branchId = (int) $session->branch_id;
    $shiftId = $session->official_shift_id !== null ? (int) $session->official_shift_id : null;

    $girlRoleId = cppRoleIdByStaffRole('GIRL');
    $waiterRoleId = cppRoleIdByStaffRole('WAITER');
    $cleaningRoleId = cppRoleIdByStaffRole('CLEANING');

    for ($i = 1; $i <= $girls; $i++) {
        $userId = cppCreateStaffUser($tenantId, $branchId, $girlRoleId, 'GIRL', 'CHICA', $i);

        StaffSettlementModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $shiftId,
            'cash_session_id' => $sessionId,
            'staff_user_id' => $userId,
            'staff_role' => 'GIRL',
            'settlement_type' => 'GIRL',
            'status' => 'PENDING',
            'total_amount' => '80.00',
            'gross_amount' => '80.00',
            'adjustments_total' => '0.00',
            'net_amount' => '80.00',
        ]);
    }

    $cashierUserId = (int) UserModel::query()->where('username', 'cajero.demo')->value('id');

    for ($i = 1; $i <= $waiters; $i++) {
        $userId = cppCreateStaffUser($tenantId, $branchId, $waiterRoleId, 'WAITER', 'GARZON', $i);

        StaffSettlementModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $shiftId,
            'cash_session_id' => $sessionId,
            'staff_user_id' => $userId,
            'staff_role' => 'WAITER',
            'settlement_type' => 'WAITER',
            'status' => 'PENDING',
            'total_amount' => '60.00',
            'gross_amount' => '60.00',
            'adjustments_total' => '0.00',
            'net_amount' => '60.00',
        ]);

        SaleModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $shiftId,
            'cash_session_id' => $sessionId,
            'order_id' => null,
            'sale_number' => 'CPP-W-'.$i.'-'.uniqid(),
            'cashier_user_id' => $cashierUserId,
            'waiter_user_id' => $userId,
            'subtotal' => number_format(1000 + ($i * 70), 2, '.', ''),
            'total' => number_format(1000 + ($i * 70), 2, '.', ''),
            'currency' => 'BOB',
            'payment_mode' => 'CASH',
            'status' => 'PAID',
            'paid_at' => now(),
        ]);
    }

    for ($i = 1; $i <= $cleaning; $i++) {
        $userId = cppCreateStaffUser($tenantId, $branchId, $cleaningRoleId, 'CLEANING', 'LIMPIEZA', $i);

        StaffSettlementModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'official_shift_id' => $shiftId,
            'cash_session_id' => $sessionId,
            'staff_user_id' => $userId,
            'staff_role' => 'CLEANING',
            'settlement_type' => 'CLEANING',
            'status' => 'PENDING',
            'total_amount' => '30.00',
            'gross_amount' => '30.00',
            'adjustments_total' => '0.00',
            'net_amount' => '30.00',
        ]);
    }

    nightposPrepareCashSessionClose($token);

    $response = test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 100,
    ], nightposOperationalHeaders($token))->assertOk();

    return [
        'response' => $response,
        'session_id' => $sessionId,
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
    ];
}

/** @return list<array<string, mixed>> */
function cppPersonnelJobsFromResponse(\Illuminate\Testing\TestResponse $response): array
{
    $jobs = $response->json('data.print_jobs') ?? [];

    return array_values(array_filter($jobs, static fn (array $job): bool => ($job['payload']['ticket_key'] ?? null) === 'personnel'));
}

/** @return \Illuminate\Support\Collection<int, PrintJobModel> */
function cppCashCloseJobs(int $sessionId)
{
    return PrintJobModel::query()
        ->where('source_type', 'cash_session')
        ->where('source_id', $sessionId)
        ->where('type', 'CASH_CLOSE')
        ->orderBy('id')
        ->get();
}

it('req1) key de pagina personnel mide <= 64', function () {
    $result = cppCloseSessionWithPersonnel(100);

    $personnel = cppCashCloseJobs($result['session_id'])
        ->filter(static fn (PrintJobModel $job): bool => ($job->payload['ticket_key'] ?? null) === 'personnel')
        ->values();

    expect($personnel->isNotEmpty())->toBeTrue();
    foreach ($personnel as $job) {
        expect(strlen((string) $job->idempotency_key))->toBeLessThanOrEqual(64);
    }
});

it('req2) key de reimpresion mide <= 64', function () {
    $result = cppCloseSessionWithPersonnel(100);
    $token = cppCashierToken();

    test()->postJson("/api/v1/cash/sessions/{$result['session_id']}/print-close", [
        'ticket' => 'personnel',
        'reprint' => true,
    ], nightposOperationalHeaders($token))->assertOk();

    $reprintJobs = cppCashCloseJobs($result['session_id'])
        ->filter(static fn (PrintJobModel $job): bool => str_contains((string) $job->idempotency_key, ':re:'))
        ->values();

    expect($reprintJobs->isNotEmpty())->toBeTrue();
    foreach ($reprintJobs as $job) {
        expect(strlen((string) $job->idempotency_key))->toBeLessThanOrEqual(64)
            ->and((string) $job->idempotency_key)->toContain(':re:');
    }
});

it('req5) ningun insert falla por longitud', function () {
    $result = cppCloseSessionWithPersonnel(100);

    expect($result['response']->json('data.print_warning'))->toBeNull();
    expect(cppPersonnelJobsFromResponse($result['response']))->toHaveCount(3);
});

it('req7) hash distinto genera key distinta', function () {
    $first = cppCloseSessionWithPersonnel(10);
    $second = cppCloseSessionWithPersonnel(11);

    $firstKey = cppCashCloseJobs($first['session_id'])
        ->first(static fn (PrintJobModel $job): bool => ($job->payload['ticket_key'] ?? null) === 'personnel')
        ?->idempotency_key;

    $secondKey = cppCashCloseJobs($second['session_id'])
        ->first(static fn (PrintJobModel $job): bool => ($job->payload['ticket_key'] ?? null) === 'personnel')
        ?->idempotency_key;

    expect($firstKey)->not->toBeNull();
    expect($secondKey)->not->toBeNull();
    expect($firstKey)->not->toBe($secondKey);
});

it('req8) misma pagina y mismo roster genera misma key', function () {
    $result = cppCloseSessionWithPersonnel(20);
    $token = cppCashierToken();

    $before = cppCashCloseJobs($result['session_id'])
        ->filter(static fn (PrintJobModel $job): bool => ($job->payload['ticket_key'] ?? null) === 'personnel')
        ->pluck('idempotency_key')
        ->values()
        ->all();

    test()->postJson("/api/v1/cash/sessions/{$result['session_id']}/print-close", [
        'ticket' => 'personnel',
        'reprint' => false,
    ], nightposOperationalHeaders($token))->assertOk();

    $after = cppCashCloseJobs($result['session_id'])
        ->filter(static fn (PrintJobModel $job): bool => ($job->payload['ticket_key'] ?? null) === 'personnel')
        ->pluck('idempotency_key')
        ->values()
        ->all();

    expect($after)->toBe($before);
});

it('req9) summary sigue creando job', function () {
    $result = cppCloseSessionWithPersonnel(1);

    $summary = cppCashCloseJobs($result['session_id'])
        ->filter(static fn (PrintJobModel $job): bool => ($job->payload['ticket_key'] ?? null) === 'summary')
        ->values();

    expect($summary)->toHaveCount(1);
});

it('req10) personnel guarda snapshot y content_text', function () {
    $result = cppCloseSessionWithPersonnel(50);

    $personnel = cppCashCloseJobs($result['session_id'])
        ->filter(static fn (PrintJobModel $job): bool => ($job->payload['ticket_key'] ?? null) === 'personnel')
        ->values();

    expect($personnel->isNotEmpty())->toBeTrue();
    foreach ($personnel as $job) {
        expect((string) ($job->payload['personnel_snapshot_id'] ?? ''))->not->toBe('')
            ->and((string) ($job->payload['roster_hash'] ?? ''))->not->toBe('')
            ->and((int) ($job->payload['page_number'] ?? 0))->toBeGreaterThan(0)
            ->and((int) ($job->payload['page_total'] ?? 0))->toBeGreaterThan(0)
            ->and(is_array($job->payload['personnel_page'] ?? null))->toBeTrue()
            ->and((string) $job->content_text)->not->toBe('');
    }
});

it('req13) no aparece warning de snapshot inexistente despues de cierre valido', function () {
    $result = cppCloseSessionWithPersonnel(50);
    $token = cppCashierToken();

    $response = test()->postJson("/api/v1/cash/sessions/{$result['session_id']}/print-close", [
        'ticket' => 'personnel',
        'reprint' => true,
    ], nightposOperationalHeaders($token))->assertOk();

    expect((string) ($response->json('data.print_warning') ?? ''))
        ->not->toContain('No existe snapshot histórico');
});

it('1) 1 chica genera 1 pagina', function () {
    $result = cppCloseSessionWithPersonnel(1);

    expect(cppPersonnelJobsFromResponse($result['response']))->toHaveCount(1);
});

it('2) 20 chicas generan 1 pagina', function () {
    $result = cppCloseSessionWithPersonnel(20);

    expect(cppPersonnelJobsFromResponse($result['response']))->toHaveCount(1);
});

it('3) 50 chicas generan multiples paginas', function () {
    $result = cppCloseSessionWithPersonnel(50);

    expect(count(cppPersonnelJobsFromResponse($result['response'])))->toBeGreaterThan(1);
});

it('4) 100 chicas generan 3 paginas con limite inicial', function () {
    $result = cppCloseSessionWithPersonnel(100);

    expect(cppPersonnelJobsFromResponse($result['response']))->toHaveCount(3);
});

it('5,6,7) todas aparecen una vez, sin omision ni duplicado', function () {
    $result = cppCloseSessionWithPersonnel(100);
    $jobs = cppPersonnelJobsFromResponse($result['response']);

    $names = [];
    foreach ($jobs as $job) {
        foreach (($job['payload']['personnel_page']['entries'] ?? []) as $entry) {
            if (($entry['role'] ?? '') !== 'GIRL') {
                continue;
            }
            $names[] = (string) ($entry['name'] ?? '');
        }
    }

    expect($names)->toHaveCount(100)
        ->and(count(array_unique($names)))->toBe(100);
});

it('8,9,10,11) no parte filas, muestra X/N, subtotal y totales globales en ultima', function () {
    $result = cppCloseSessionWithPersonnel(100);
    $jobs = cppPersonnelJobsFromResponse($result['response']);

    $total = count($jobs);
    foreach ($jobs as $i => $job) {
        $page = $i + 1;
        $content = (string) ($job['content_text'] ?? '');

        expect($content)->toContain("PAGOS PERSONAL {$page}/{$total}")
            ->toContain('SUBTOTAL PAGINA');

        foreach (($job['payload']['personnel_page']['entries'] ?? []) as $entry) {
            $name = strtoupper(substr((string) ($entry['name'] ?? ''), 0, 10));
            expect($content)->toContain($name);
        }
    }

    $last = $jobs[array_key_last($jobs)];
    $lastContent = (string) ($last['content_text'] ?? '');

    expect($lastContent)
        ->toContain('TOTAL CHICAS')
        ->toContain('TOTAL GARZONES')
        ->toContain('TOTAL LIMPIEZA')
        ->toContain('TOTAL PERSONAL');
});

it('12,13) garzones muestran venta/pago y limpieza aparece', function () {
    $result = cppCloseSessionWithPersonnel(3, 2, 2);
    $jobs = cppPersonnelJobsFromResponse($result['response']);
    $content = implode("\n", array_map(static fn (array $job): string => (string) ($job['content_text'] ?? ''), $jobs));

    expect($content)
        ->toContain('GARZONES')
        ->toContain('VENTA')
        ->toContain('PAGO')
        ->toContain('LIMPIEZA');
});

it('14) jobs se crean en orden de pagina', function () {
    $result = cppCloseSessionWithPersonnel(100);

    $jobs = PrintJobModel::query()
        ->where('source_type', 'cash_session')
        ->where('source_id', $result['session_id'])
        ->where('type', 'CASH_CLOSE')
        ->where('payload->ticket_key', 'personnel')
        ->orderBy('created_at')
        ->get();

    $pages = $jobs->map(static fn (PrintJobModel $job): int => (int) (($job->payload['page_number'] ?? 0)))->all();
    $sorted = $pages;
    sort($sorted);

    expect($pages)->toBe($sorted);
});

it('15) idempotencia evita duplicados en ticket personal no-reprint', function () {
    $result = cppCloseSessionWithPersonnel(50);
    $token = cppCashierToken();

    $before = PrintJobModel::query()
        ->where('source_type', 'cash_session')
        ->where('source_id', $result['session_id'])
        ->where('type', 'CASH_CLOSE')
        ->where('payload->ticket_key', 'personnel')
        ->count();

    test()->postJson("/api/v1/cash/sessions/{$result['session_id']}/print-close", [
        'ticket' => 'personnel',
        'reprint' => false,
    ], nightposOperationalHeaders($token))->assertOk();

    $after = PrintJobModel::query()
        ->where('source_type', 'cash_session')
        ->where('source_id', $result['session_id'])
        ->where('type', 'CASH_CLOSE')
        ->where('payload->ticket_key', 'personnel')
        ->count();

    expect($after)->toBe($before);
});

it('16) reimpresion conserva orden', function () {
    $result = cppCloseSessionWithPersonnel(100);
    $token = cppCashierToken();

    $response = test()->postJson("/api/v1/cash/sessions/{$result['session_id']}/print-close", [
        'ticket' => 'personnel',
        'reprint' => true,
    ], nightposOperationalHeaders($token))->assertOk();

    $jobs = array_values(array_filter($response->json('data.print_jobs') ?? [], static fn (array $job): bool => ($job['payload']['ticket_key'] ?? null) === 'personnel'));
    $pages = array_map(static fn (array $job): int => (int) ($job['payload']['page_number'] ?? 0), $jobs);

    expect($pages)->toBe([1, 2, 3]);
});

it('17) reimpresion de una sola pagina funciona', function () {
    $result = cppCloseSessionWithPersonnel(100);
    $token = cppCashierToken();

    $response = test()->postJson("/api/v1/cash/sessions/{$result['session_id']}/print-close", [
        'ticket' => 'personnel',
        'reprint' => true,
        'page' => 2,
    ], nightposOperationalHeaders($token))->assertOk();

    $jobs = array_values(array_filter($response->json('data.print_jobs') ?? [], static fn (array $job): bool => ($job['payload']['ticket_key'] ?? null) === 'personnel'));

    expect($jobs)->toHaveCount(1)
        ->and((int) ($jobs[0]['payload']['page_number'] ?? 0))->toBe(2);
});

it('18,19) fallo de impresion no revierte cierre y no bloquea respuesta', function () {
    $token = cppCashierToken();
    nightposEnsureShiftOpen();
    nightposOpenCashSession($token, 100, false);
    nightposPrepareCashSessionClose($token);

    $response = test()->postJson('/api/v1/cash/session/close', [
        'declared_closing_amount' => 100,
    ], nightposOperationalHeaders($token))->assertOk();

    expect($response->json('data.session.status'))->toBe('CLOSED')
        ->and($response->json('data.print_warning'))->toContain('no se pudo imprimir');
});

it('20) orden de cola pendiente se mantiene por priority desc y created_at asc (sin cambios en agente)', function () {
    $result = cppCloseSessionWithPersonnel(100);

    $jobs = PrintJobModel::query()
        ->where('source_type', 'cash_session')
        ->where('source_id', $result['session_id'])
        ->where('type', 'CASH_CLOSE')
        ->where('payload->ticket_key', 'personnel')
        ->orderByDesc('priority')
        ->orderBy('created_at')
        ->get();

    $pages = $jobs->map(static fn (PrintJobModel $job): int => (int) ($job->payload['page_number'] ?? 0))->all();

    expect($pages)->toBe([1, 2, 3]);
});
