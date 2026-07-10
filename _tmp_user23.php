<?php
require 'C:/xampp/htdocs/nightpos/backend/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/nightpos/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
$row = $pdo->query("select id,name,username,role_id,tenant_id,branch_id from users where id=23")->fetch(PDO::FETCH_ASSOC);
echo json_encode($row, JSON_UNESCAPED_UNICODE), PHP_EOL;
