<?php
require 'C:/xampp/htdocs/nightpos/backend/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/nightpos/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$pdo = Illuminate\Support\Facades\DB::connection()->getPdo();

function dumpRows($label, $rows) {
    echo $label, PHP_EOL;
    foreach ($rows as $row) {
        echo json_encode($row, JSON_UNESCAPED_UNICODE), PHP_EOL;
    }
}

$rows1 = $pdo->query("select id,name,shift_type,business_date,status,starts_at,ends_at,notes from official_shifts where tenant_id=2 and branch_id=2 order by id desc limit 8")->fetchAll(PDO::FETCH_ASSOC);
dumpRows('SHIFTS', $rows1);

$rows2 = $pdo->query("select s.id,s.sale_number,s.official_shift_id,s.cash_session_id,s.waiter_user_id,s.paid_at,s.total from sales s where s.tenant_id=2 and s.branch_id=2 and s.waiter_user_id=18 order by s.id desc limit 20")->fetchAll(PDO::FETCH_ASSOC);
dumpRows('HUGO SALES', $rows2);

$rows3 = $pdo->query("select si.id as sale_item_id,s.id as sale_id,s.sale_number,s.official_shift_id,s.cash_session_id,s.paid_at,si.product_name_snapshot,si.line_total from sale_items si join sales s on s.id=si.sale_id where s.tenant_id=2 and s.branch_id=2 and s.waiter_user_id=18 order by s.id desc, si.id desc limit 30")->fetchAll(PDO::FETCH_ASSOC);
dumpRows('HUGO ITEM SHIFTS', $rows3);
