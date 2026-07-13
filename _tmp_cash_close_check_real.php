<?php
require 'C:/xampp/htdocs/nightpos/backend/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/nightpos/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Application\Cash\Services\CashSessionCloseCheckBuilder;
use Illuminate\Support\Facades\DB;

$tenantId = 2;
$branchId = 2;
$session = DB::selectOne("select id,official_shift_id,status from cash_sessions where tenant_id=? and branch_id=? and status='OPEN' order by id desc limit 1", [$tenantId, $branchId]);
$openShift = DB::selectOne("select id,shift_type,business_date,status from official_shifts where tenant_id=? and branch_id=? and status='OPEN' order by id desc limit 1", [$tenantId, $branchId]);
$salesByShift = DB::select("select official_shift_id,count(*) as sales_count,sum(total) as total_sales from sales where tenant_id=? and branch_id=? and cash_session_id=? group by official_shift_id order by official_shift_id", [$tenantId,$branchId,$session->id]);

$builder = $app->make(CashSessionCloseCheckBuilder::class);
$check = $builder->build($tenantId, $branchId, (int) $session->official_shift_id, (int) $session->id);

echo json_encode([
  'session' => $session,
  'open_shift' => $openShift,
  'sales_by_shift_in_same_session' => $salesByShift,
  'close_check' => $check,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
