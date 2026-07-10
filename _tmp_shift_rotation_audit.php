<?php
require 'C:/xampp/htdocs/nightpos/backend/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/nightpos/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$pdo = Illuminate\Support\Facades\DB::connection()->getPdo();

function section($label) { echo "\n=== $label ===\n"; }
function dumpRows($rows) { foreach ($rows as $row) { echo json_encode($row, JSON_UNESCAPED_UNICODE), PHP_EOL; } }

section('OFFICIAL SHIFTS 13-22');
$rows = $pdo->query("select id,name,shift_type,business_date,status,opened_by_user_id,closed_by_user_id,opened_at,closed_at,starts_at,ends_at,notes from official_shifts where tenant_id=2 and branch_id=2 and id between 13 and 22 order by id")->fetchAll(PDO::FETCH_ASSOC);
dumpRows($rows);

section('CASH SESSION 11');
$rows = $pdo->query("select id,official_shift_id,status,opened_by_user_id,closed_by_user_id,opened_at,closed_at from cash_sessions where tenant_id=2 and branch_id=2 and id=11")->fetchAll(PDO::FETCH_ASSOC);
dumpRows($rows);

section('FIRST SALES ON SHIFT 19');
$rows = $pdo->query("select s.id,s.sale_number,s.official_shift_id,s.cash_session_id,s.waiter_user_id,s.cashier_user_id,s.paid_at,s.created_at,s.total from sales s where s.tenant_id=2 and s.branch_id=2 and s.official_shift_id=19 order by s.id asc limit 10")->fetchAll(PDO::FETCH_ASSOC);
dumpRows($rows);

section('FIRST SETTLEMENTS ON SHIFT 19');
$rows = $pdo->query("select id,official_shift_id,cash_session_id,staff_user_id,staff_role,status,created_at from staff_settlements where tenant_id=2 and branch_id=2 and official_shift_id=19 order by id asc limit 10")->fetchAll(PDO::FETCH_ASSOC);
dumpRows($rows);
