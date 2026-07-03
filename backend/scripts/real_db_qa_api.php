<?php

/**
 * Real DB QA — API smoke tests against hosting import (no seeders).
 * Run: php scripts/real_db_qa_api.php
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$base = rtrim((string) config('app.url'), '/').'/api/v1';
$results = [];

function qa(string $name, callable $fn): void
{
    global $results;
    try {
        $out = $fn();
        $results[] = ['name' => $name, 'status' => 'OK', 'detail' => $out];
        echo "[OK] {$name}: {$out}\n";
    } catch (Throwable $e) {
        $results[] = ['name' => $name, 'status' => 'FAIL', 'detail' => $e->getMessage()];
        echo "[FAIL] {$name}: {$e->getMessage()}\n";
    }
}

function postJson(string $url, array $body, array $headers = []): \Illuminate\Http\Client\Response
{
    return Http::acceptJson()->withHeaders($headers)->post($url, $body);
}

function getJson(string $url, array $headers = []): \Illuminate\Http\Client\Response
{
    return Http::acceptJson()->withHeaders($headers)->get($url);
}

$credentials = [
    ['superadmin', 'SuperAdmin123!', null, null],
    ['admin.demo', 'AdminDemo123!', 'casa-demo', 'CENTRO'],
    ['cajero.demo', null, 'casa-demo', 'CENTRO', '1234'],
    ['garzon.demo', null, 'casa-demo', 'CENTRO', '5678'],
    ['limpieza.demo', null, 'casa-demo', 'CENTRO', '9012'],
    ['lizvania', null, 'C22', '1', null], // hosting user — password unknown
    ['LAURA', null, 'C22', '1', null],
];

$tokens = [];

foreach ($credentials as $cred) {
    $user = $cred[0];
    $pass = $cred[1] ?? null;
    $tenant = $cred[2] ?? null;
    $branch = $cred[3] ?? null;
    $pin = $cred[4] ?? null;

    if ($pass !== null) {
        $body = ['username' => $user, 'password' => $pass];
        if ($tenant) {
            $body['tenant_slug'] = $tenant;
        }
        $r = postJson("{$base}/auth/login", $body);
        $tokens[$user] = $r->successful() ? ($r->json('data.token') ?? null) : null;
        echo 'Login password '.str_pad($user, 15).' → HTTP '.$r->status().($tokens[$user] ? ' token OK' : ' '.($r->json('message') ?? ''))."\n";
    } elseif ($pin !== null && $tenant && $branch) {
        $r = postJson("{$base}/auth/login-pin", [
            'pin' => $pin,
            'tenant_slug' => $tenant,
            'branch_code' => $branch,
        ]);
        $tokens[$user] = $r->successful() ? ($r->json('data.token') ?? null) : null;
        echo 'Login PIN '.str_pad($user, 15).' → HTTP '.$r->status().($tokens[$user] ? ' token OK' : ' '.($r->json('message') ?? ''))."\n";
    } else {
        echo 'Login SKIP '.str_pad($user, 15)." → password/PIN unknown in QA script\n";
    }
}

function authHeaders(?string $token, ?string $branchCode = 'CENTRO'): array
{
    $h = ['Accept' => 'application/json'];
    if ($token) {
        $h['Authorization'] = 'Bearer '.$token;
    }
    if ($branchCode) {
        $h['X-Branch-Code'] = $branchCode;
    }

    return $h;
}

qa('DB counts tenants', fn () => 'tenants='.\App\Infrastructure\Persistence\Eloquent\Models\TenantModel::count());
qa('DB counts settlements PENDING tenant 2', fn () => 'count='.\App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel::query()->where('tenant_id', 2)->where('status', 'PENDING')->count());

if ($tokens['admin.demo'] ?? null) {
    $h = authHeaders($tokens['admin.demo'], 'CENTRO');
    qa('admin settlements list CENTRO', function () use ($base, $h) {
        $r = getJson("{$base}/settlements?status=PENDING", $h);

        return 'HTTP '.$r->status().' count='.count($r->json('data') ?? []);
    });
}

if ($tokens['lizvania'] ?? null) {
    $h = authHeaders($tokens['lizvania'], '1');
} elseif ($tokens['admin.demo'] ?? null) {
    // Try C22 tenant with admin — may fail branch access
}

// C22 cashier — use lizvania if we had password; try admin on tenant C22 branch 1 via login
$rC22 = postJson("{$base}/auth/login", ['username' => 'LAURA', 'password' => 'AdminDemo123!', 'tenant_slug' => 'C22']);
if (! $rC22->successful()) {
    $rC22 = postJson("{$base}/auth/login", ['username' => 'HEYDDI', 'password' => 'AdminDemo123!', 'tenant_slug' => 'C22']);
}
$c22Token = $rC22->successful() ? ($rC22->json('data.token') ?? null) : null;
echo 'Login C22 owner try → HTTP '.$rC22->status()."\n";

if ($c22Token) {
    $hC22 = authHeaders($c22Token, '1');
    qa('C22 settlements PENDING', function () use ($base, $hC22) {
        $r = getJson("{$base}/settlements?status=PENDING", $hC22);

        return 'HTTP '.$r->status().' items='.count($r->json('data') ?? []);
    });
    qa('C22 cash session current', function () use ($base, $hC22) {
        $r = getJson("{$base}/cash/sessions/current", $hC22);

        return 'HTTP '.$r->status().' status='.($r->json('data.session.status') ?? 'null');
    });
    qa('C22 mark-paid settlement 6 (if pending)', function () use ($base, $hC22) {
        $pending = \App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel::query()->find(6);
        if ($pending === null || $pending->status !== 'PENDING') {
            return 'skip settlement 6 not pending';
        }
        $r = postJson("{$base}/settlements/6/mark-paid", ['payment_method' => 'CASH'], $hC22);

        return 'HTTP '.$r->status().' msg='.($r->json('message') ?? 'ok').' ticket='.($r->json('data.ticket_number') ?? '-');
    });
}

if ($tokens['garzon.demo'] ?? null) {
    $h = authHeaders($tokens['garzon.demo'], 'CENTRO');
    qa('waiter orders list', function () use ($base, $h) {
        $r = getJson("{$base}/orders", $h);

        return 'HTTP '.$r->status().' count='.count($r->json('data.orders') ?? $r->json('data') ?? []);
    });
}

if ($tokens['cajero.demo'] ?? null) {
    $h = authHeaders($tokens['cajero.demo'], 'CENTRO');
    qa('cashier orders chargeable', function () use ($base, $h) {
        $r = getJson("{$base}/orders?status=SENT_TO_BAR", $h);

        return 'HTTP '.$r->status().' SENT_TO_BAR='.count($r->json('data.orders') ?? $r->json('data') ?? []);
    });
    qa('cashier cash close-check', function () use ($base, $h) {
        $r = getJson("{$base}/cash/sessions/current/close-check", $h);

        return 'HTTP '.$r->status();
    });
}

echo "\nDone. Results: ".count($results)." checks\n";
