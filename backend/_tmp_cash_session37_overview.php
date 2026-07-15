<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenantId = (int) ($argv[1] ?? 2);
$branchId = (int) ($argv[2] ?? 2);
$shiftId = (int) ($argv[3] ?? 147);
$cashSessionId = (int) ($argv[4] ?? 37);

$repo = app(App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface::class);

echo json_encode(
    $repo->getCurrentShiftOverview($tenantId, $branchId, $shiftId, null, $cashSessionId),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
);
