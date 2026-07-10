<?php
require 'C:/xampp/htdocs/nightpos/backend/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/nightpos/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function out($label, $rows){ echo "\n=== $label ===\n"; foreach($rows as $r){ echo json_encode($r, JSON_UNESCAPED_UNICODE), PHP_EOL; }}

$users = DB::select("select u.id,u.name,u.username,u.tenant_id,u.branch_id,r.slug as role_slug from users u left join roles r on r.id=u.role_id where lower(u.name) like '%arace%' or lower(u.name) like '%emerson%' or lower(u.username) like '%arace%' or lower(u.username) like '%emerson%'");
out('USERS', $users);

$araceli = DB::selectOne("select u.id,u.tenant_id,u.branch_id from users u left join roles r on r.id=u.role_id where (lower(u.name) like '%arace%' or lower(u.username) like '%arace%') and r.slug='cashier' order by u.id desc limit 1");
$emerson = DB::selectOne("select u.id,u.tenant_id,u.branch_id from users u left join roles r on r.id=u.role_id where (lower(u.name) like '%emerson%' or lower(u.username) like '%emerson%') and r.slug='waiter' order by u.id desc limit 1");

if ($araceli && $emerson) {
  $t=(int)$araceli->tenant_id; $b=(int)$araceli->branch_id;

  $sales = DB::select("select s.id,s.sale_number,s.order_id,s.official_shift_id,s.cash_session_id,s.waiter_user_id,s.cashier_user_id,s.payment_mode,s.total,s.paid_at,s.created_at from sales s where s.tenant_id=? and s.branch_id=? and (s.waiter_user_id=? or s.cashier_user_id=?) order by s.id desc limit 20", [$t,$b,$emerson->id,$araceli->id]);
  out('RECENT_SALES_EMERSON_OR_ARACELI', $sales);

  $orders = DB::select("select o.id,o.order_number,o.status,o.waiter_user_id,o.official_shift_id,o.created_at from orders o where o.tenant_id=? and o.branch_id=? and o.waiter_user_id=? order by o.id desc limit 20", [$t,$b,$emerson->id]);
  out('RECENT_ORDERS_EMERSON', $orders);

  $cash = DB::select("select id,official_shift_id,status,opened_by_user_id,opened_at,closed_at from cash_sessions where tenant_id=? and branch_id=? order by id desc limit 10", [$t,$b]);
  out('RECENT_CASH_SESSIONS', $cash);

  $sett = DB::select("select id,official_shift_id,cash_session_id,staff_user_id,staff_role,settlement_type,status,gross_amount,adjustments_total,net_amount,total_amount,created_at from staff_settlements where tenant_id=? and branch_id=? and staff_user_id=? order by id desc limit 20", [$t,$b,$emerson->id]);
  out('RECENT_SETTLEMENTS_EMERSON', $sett);

  $pendingByScope = DB::select("select official_shift_id,cash_session_id,count(*) as pending_count from staff_settlements where tenant_id=? and branch_id=? and status='PENDING' group by official_shift_id,cash_session_id order by official_shift_id desc,cash_session_id desc", [$t,$b]);
  out('PENDING_SETTLEMENTS_BY_SHIFT_AND_CASH', $pendingByScope);
}
