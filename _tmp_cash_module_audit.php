<?php
require 'C:/xampp/htdocs/nightpos/backend/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/nightpos/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Application\Cash\Services\CashSessionFinancialSummaryBuilder;
use Illuminate\Support\Facades\DB;

function section(string $label): void { echo "\n=== {$label} ===\n"; }
function dumpRows($rows): void { foreach ($rows as $row) { echo json_encode($row, JSON_UNESCAPED_UNICODE), PHP_EOL; } }

$tenantId = 2;
$branchId = 2;

section('TABLE_COUNTS');
$tables = ['cash_sessions','cash_movements','cash_movement_reasons','sales','sale_payments','staff_settlements','staff_settlement_adjustments','orders','room_services','official_shifts'];
foreach ($tables as $table) {
    $count = DB::table($table)->count();
    echo json_encode(['table' => $table, 'count' => $count], JSON_UNESCAPED_UNICODE), PHP_EOL;
}

section('CASH_SESSION_COLUMNS');
$sessionCols = DB::select("select column_name,data_type,is_nullable,column_default from information_schema.columns where table_schema=database() and table_name='cash_sessions' order by ordinal_position");
dumpRows($sessionCols);

section('CASH_MOVEMENT_COLUMNS');
$movementCols = DB::select("select column_name,data_type,is_nullable,column_default from information_schema.columns where table_schema=database() and table_name='cash_movements' order by ordinal_position");
dumpRows($movementCols);

section('LATEST_CASH_SESSIONS');
$sessions = DB::select(
    "select id,tenant_id,branch_id,official_shift_id,opened_by_user_id,closed_by_user_id,status,opening_amount,expected_amount,declared_closing_amount,difference_amount,opening_notes,closing_notes,is_forced_close,forced_close_reason,opened_at,closed_at from cash_sessions where tenant_id=? and branch_id=? order by id desc limit 8",
    [$tenantId, $branchId]
);
dumpRows($sessions);

section('CASH_MOVEMENT_REASONS');
$reasons = DB::select("select id,name,type,status from cash_movement_reasons where tenant_id=? order by id", [$tenantId]);
dumpRows($reasons);

section('MOVEMENTS_BY_SESSION_TYPE_METHOD');
$movementsAgg = DB::select(
    "select cash_session_id,movement_type,payment_method,count(*) as rows_count,sum(amount) as total from cash_movements where tenant_id=? and branch_id=? group by cash_session_id,movement_type,payment_method order by cash_session_id desc,movement_type,payment_method",
    [$tenantId, $branchId]
);
dumpRows($movementsAgg);

section('LATEST_MOVEMENTS_DETAIL');
$movements = DB::select(
    "select id,cash_session_id,movement_type,amount,payment_method,description,notes,source_type,source_id,created_by_user_id,created_at from cash_movements where tenant_id=? and branch_id=? order by id desc limit 25",
    [$tenantId, $branchId]
);
dumpRows($movements);

section('SALES_BY_SESSION_AND_METHOD');
$salesAgg = DB::select(
    "select cash_session_id,payment_mode,count(*) as sales_count,sum(total) as total_sales from sales where tenant_id=? and branch_id=? group by cash_session_id,payment_mode order by cash_session_id desc,payment_mode",
    [$tenantId, $branchId]
);
dumpRows($salesAgg);

section('SALE_PAYMENTS_BY_SESSION_METHOD');
$salePayAgg = DB::select(
    "select s.cash_session_id,sp.payment_method,count(*) as rows_count,sum(sp.amount) as total from sale_payments sp join sales s on s.id=sp.sale_id where s.tenant_id=? and s.branch_id=? group by s.cash_session_id,sp.payment_method order by s.cash_session_id desc,sp.payment_method",
    [$tenantId, $branchId]
);
dumpRows($salePayAgg);

section('SETTLEMENTS_BY_SESSION_STATUS_TYPE');
$settlementsAgg = DB::select(
    "select cash_session_id,status,settlement_type,count(*) as rows_count,sum(total_amount) as total_amount,sum(net_amount) as net_amount from staff_settlements where tenant_id=? and branch_id=? group by cash_session_id,status,settlement_type order by cash_session_id desc,status,settlement_type",
    [$tenantId, $branchId]
);
dumpRows($settlementsAgg);

section('LATEST_SETTLEMENTS_DETAIL');
$settlements = DB::select(
    "select id,cash_session_id,official_shift_id,staff_user_id,staff_role,settlement_type,status,gross_amount,adjustments_total,net_amount,total_amount,paid_at,paid_by_user_id,created_at from staff_settlements where tenant_id=? and branch_id=? order by id desc limit 25",
    [$tenantId, $branchId]
);
dumpRows($settlements);

section('ADJUSTMENTS_BY_SESSION_TYPE');
$adjustments = DB::select(
    "select ss.cash_session_id,ssa.adjustment_type,count(*) as rows_count,sum(ssa.amount) as total from staff_settlement_adjustments ssa join staff_settlements ss on ss.id=ssa.staff_settlement_id where ss.tenant_id=? and ss.branch_id=? group by ss.cash_session_id,ssa.adjustment_type order by ss.cash_session_id desc,ssa.adjustment_type",
    [$tenantId, $branchId]
);
dumpRows($adjustments);

section('CURRENT_OPEN_SESSION_SUMMARY');
$current = DB::selectOne("select id,opening_amount,expected_amount,declared_closing_amount,difference_amount,status from cash_sessions where tenant_id=? and branch_id=? and status='OPEN' order by id desc limit 1", [$tenantId, $branchId]);
if ($current) {
    /** @var CashSessionFinancialSummaryBuilder $builder */
    $builder = $app->make(CashSessionFinancialSummaryBuilder::class);
    $summary = $builder->build(
        sessionId: (int) $current->id,
        openingAmount: (string) $current->opening_amount,
        storedExpectedAmount: $current->expected_amount !== null ? (string) $current->expected_amount : null,
        declaredClosingAmount: $current->declared_closing_amount !== null ? (string) $current->declared_closing_amount : null,
        differenceAmount: $current->difference_amount !== null ? (string) $current->difference_amount : null,
        status: (string) $current->status,
    );
    echo json_encode(['session_id' => (int) $current->id, 'summary' => $summary], JSON_UNESCAPED_UNICODE), PHP_EOL;
}

section('LATEST_CLOSED_NOTES');
$closedNotes = DB::select("select id,declared_closing_amount,expected_amount,difference_amount,closing_notes,closed_at from cash_sessions where tenant_id=? and branch_id=? and status='CLOSED' order by id desc limit 5", [$tenantId, $branchId]);
dumpRows($closedNotes);
