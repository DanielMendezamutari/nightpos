<?php

/**
 * Real DB QA — API via JWT from real users (no seeders, no password guessing).
 * Run: php scripts/real_db_qa_internal.php
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

$base = rtrim((string) config('app.url'), '/').'/api/v1';

function tokenFor(int $userId): string
{
    $user = UserModel::query()->findOrFail($userId);

    return JWTAuth::fromUser($user);
}

function headers(string $token, string $branchCode): array
{
    return [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer '.$token,
        'X-Branch-Code' => $branchCode,
    ];
}

function hit(string $method, string $path, string $token, string $branch, array $body = []): array
{
    global $base;
    $url = $base.$path;
    $h = headers($token, $branch);
    $r = $method === 'GET'
        ? Http::withHeaders($h)->get($url, $body)
        : Http::withHeaders($h)->post($url, $body);

    return [
        'status' => $r->status(),
        'message' => $r->json('message') ?? null,
        'data' => $r->json('data') ?? null,
    ];
}

$checks = [];

$userMap = [
    'superadmin' => [1, null],
    'LAURA_C22_owner' => [10, '1'],
    'lizvania_cashier_C22' => [23, '1'],
    'HUGO_waiter_C22' => [18, '1'],
    'garzon_DARDO_R' => [12, '1'],
    'admin_demo_suspended' => [2, 'CENTRO'],
];

foreach ($userMap as $label => [$uid, $branch]) {
    try {
        $token = tokenFor($uid);
        $me = hit('GET', '/auth/me', $token, $branch ?? '1');
        $checks[] = [$label, 'auth/me', $me['status'], $me['message'] ?? 'OK'];
        echo "[{$label}] auth/me → {$me['status']}\n";

        if ($branch && str_contains($label, 'cashier')) {
            $cash = hit('GET', '/cash/session/current', $token, $branch);
            echo "  cash/session/current → {$cash['status']} session=".json_encode($cash['data']['session']['status'] ?? $cash['data']['status'] ?? null)."\n";
            $checks[] = [$label, 'cash/session/current', $cash['status'], null];

            $close = hit('GET', '/cash/session/current/close-check', $token, $branch);
            echo "  cash close-check → {$close['status']}\n";
            $checks[] = [$label, 'cash/close-check', $close['status'], null];
        }

        if ($branch && str_contains($label, 'waiter')) {
            $orders = hit('GET', '/waiter/orders/active', $token, $branch);
            $count = is_array($orders['data']) ? count($orders['data']) : 0;
            echo "  waiter/active → {$orders['status']} count={$count}\n";
            $checks[] = [$label, 'waiter/active', $orders['status'], "count={$count}"];
        }

        if ($branch && (str_contains($label, 'owner') || str_contains($label, 'cashier'))) {
            $sett = hit('GET', '/settlements/current-shift', $token, $branch);
            $count = is_array($sett['data']['settlements'] ?? null) ? count($sett['data']['settlements']) : (is_array($sett['data']) ? count($sett['data']) : 0);
            echo "  settlements/current-shift → {$sett['status']} count={$count}\n";
            $checks[] = [$label, 'settlements/current-shift', $sett['status'], "count={$count}"];
        }

        if ($label === 'lizvania_cashier_C22') {
            $shiftClose = hit('GET', '/shifts/current/close-check', $token, $branch);
            echo "  shift close-check → {$shiftClose['status']} msg=".($shiftClose['message'] ?? '')."\n";
            $checks[] = [$label, 'shift/close-check', $shiftClose['status'], $shiftClose['message'] ?? null];

            $preview = hit('GET', '/settlements/6/pay-preview', $token, $branch);
            echo "  settlement/6 pay-preview → {$preview['status']}\n";
            $checks[] = [$label, 'settlement/6 preview', $preview['status'], null];
        }

        if ($label === 'LAURA_C22_owner') {
            $hist = hit('GET', '/settlements/history?limit=10', $token, $branch);
            echo "  settlements/history → {$hist['status']}\n";
            $checks[] = [$label, 'settlements/history', $hist['status'], null];
        }
    } catch (Throwable $e) {
        echo "[{$label}] ERROR: {$e->getMessage()}\n";
        $checks[] = [$label, 'error', 0, $e->getMessage()];
    }
}

// Orders index for cashier C22 (chargeable filter)
try {
    $token = tokenFor(23);
    $all = hit('GET', '/orders', $token, '1');
    $sent = hit('GET', '/orders?status=SENT_TO_BAR', $token, '1');
    echo "[cajero_C22] orders all → {$all['status']}, SENT_TO_BAR filter → {$sent['status']}\n";
    $checks[] = ['lizvania', 'orders/all', $all['status'], null];
    $checks[] = ['lizvania', 'orders/SENT_TO_BAR', $sent['status'], null];
} catch (Throwable $e) {
    echo 'orders check error: '.$e->getMessage()."\n";
}

file_put_contents(
    __DIR__.'/../storage/app/real_db_qa_api_results.json',
    json_encode($checks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
);

echo "\nWrote storage/app/real_db_qa_api_results.json\n";
