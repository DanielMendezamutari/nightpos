<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\Http;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

$userId = (int) ($argv[1] ?? 86);
$branchCode = (string) ($argv[2] ?? '1');
$path = (string) ($argv[3] ?? '/settlements/current-shift');

$user = UserModel::query()->findOrFail($userId);
$token = JWTAuth::fromUser($user);
$base = rtrim((string) config('app.url'), '/').'/api/v1';

$response = Http::withHeaders([
    'Accept' => 'application/json',
    'Authorization' => 'Bearer '.$token,
    'X-Branch-Code' => $branchCode,
])->get($base.$path);

fwrite(STDOUT, json_encode([
    'status' => $response->status(),
    'message' => $response->json('message'),
    'data' => $response->json('data'),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
