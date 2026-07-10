<?php
require 'C:/xampp/htdocs/nightpos/backend/vendor/autoload.php';
$app = require 'C:/xampp/htdocs/nightpos/backend/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Application\StaffSettlement\Services\SettlementManualCleaningService;
use App\Application\StaffSettlement\Services\SettlementTotalsCalculator;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    $settlement = StaffSettlementModel::query()
        ->where('settlement_type', 'GIRL')
        ->where('status', 'PENDING')
        ->where('tenant_id', 2)
        ->where('branch_id', 2)
        ->orderByDesc('id')
        ->firstOrFail();

    $service = $app->make(SettlementManualCleaningService::class);
    $totals = $app->make(SettlementTotalsCalculator::class);

    $snapshot = static function (StaffSettlementModel $model): array {
        $model->refresh();
        return [
            'id' => (int) $model->id,
            'gross_amount' => (string) $model->gross_amount,
            'adjustments_total' => (string) $model->adjustments_total,
            'net_amount' => (string) $model->net_amount,
            'total_amount' => (string) $model->total_amount,
            'cleaning_rows' => StaffSettlementAdjustmentModel::query()
                ->where('staff_settlement_id', $model->id)
                ->where('adjustment_type', 'CLEANING_DEDUCTION')
                ->get(['amount', 'dedup_key'])
                ->map(fn ($row) => ['amount' => (string) $row->amount, 'dedup_key' => $row->dedup_key])
                ->all(),
        ];
    };

    $before = $snapshot($settlement);
    $service->apply($settlement, 20, 23);
    $afterTwenty = $snapshot($settlement);
    $totals->recalculate((int) $settlement->id);
    $afterTwentyRecalc = $snapshot($settlement);
    $service->apply($settlement, 0, 23);
    $afterZero = $snapshot($settlement);
    $totals->recalculate((int) $settlement->id);
    $afterZeroRecalc = $snapshot($settlement);

    DB::rollBack();

    echo json_encode([
        'before' => $before,
        'after_twenty' => $afterTwenty,
        'after_twenty_recalc' => $afterTwentyRecalc,
        'after_zero' => $afterZero,
        'after_zero_recalc' => $afterZeroRecalc,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), PHP_EOL;
} catch (Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, $e->getMessage().PHP_EOL);
    exit(1);
}
