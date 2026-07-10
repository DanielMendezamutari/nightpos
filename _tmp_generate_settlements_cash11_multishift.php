<?php
require 'C:/xampp/htdocs/nightpos/backend/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/nightpos/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;
use Illuminate\Support\Facades\DB;

$tenantId = 2;
$branchId = 2;
$cashSessionId = 11;

/** @var StaffSettlementRepositoryInterface $repo */
$repo = $app->make(StaffSettlementRepositoryInterface::class);

$shiftIds = $repo->resolveCashSessionShiftIdsWithActivity($tenantId, $branchId, $cashSessionId);

echo "shift_ids_for_cash_session_11=", json_encode($shiftIds), PHP_EOL;

$totalCreated = 0;
$totalTouched = 0;
$results = [];

foreach ($shiftIds as $shiftId) {
    $partial = $repo->generateForShift($tenantId, $branchId, (int) $shiftId, $cashSessionId);
    $results[] = $partial;
    $totalCreated += (int) ($partial['created_items'] ?? 0);
    $totalTouched += (int) ($partial['settlements_touched'] ?? 0);
}

echo "generated_results=", json_encode($results, JSON_UNESCAPED_UNICODE), PHP_EOL;
echo "totals=", json_encode(['created_items' => $totalCreated, 'settlements_touched' => $totalTouched], JSON_UNESCAPED_UNICODE), PHP_EOL;

$emersonRows = DB::select(
    "select id,official_shift_id,cash_session_id,staff_user_id,staff_role,settlement_type,status,total_amount,created_at
     from staff_settlements
     where tenant_id=? and branch_id=? and staff_user_id=39
     order by id desc limit 10",
    [$tenantId, $branchId]
);

echo "emerson_settlements=", json_encode($emersonRows, JSON_UNESCAPED_UNICODE), PHP_EOL;
