<?php
require 'C:/xampp/htdocs/nightpos/backend/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/nightpos/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$rows = DB::select("select id, tenant_id, branch_id, staff_user_id, settlement_type, official_shift_id, cash_session_id, gross_amount, adjustments_total, net_amount, total_amount, status, created_at from staff_settlements where settlement_type='GIRL' order by id desc limit 5");
echo json_encode($rows, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), PHP_EOL;
$adj = DB::select("select id, staff_settlement_id, adjustment_type, amount, calculation_base, dedup_key, notes, created_at from staff_settlement_adjustments order by id desc limit 10");
echo "---ADJ---", PHP_EOL;
echo json_encode($adj, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), PHP_EOL;
$profiles = DB::select("select u.id, u.username, u.name, sp.cleaning_base_amount, sp.cleaning_room_amount from users u left join staff_profiles sp on sp.user_id=u.id where sp.staff_role='CLEANING' or sp.cleaning_base_amount is not null or sp.cleaning_room_amount is not null order by u.id limit 10");
echo "---PROFILES---", PHP_EOL;
echo json_encode($profiles, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), PHP_EOL;
