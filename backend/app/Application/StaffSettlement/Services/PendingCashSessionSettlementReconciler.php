<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use Illuminate\Support\Facades\DB;

final class PendingCashSessionSettlementReconciler
{
    public function __construct(
        private readonly SettlementTotalsCalculator $totalsCalculator,
    ) {
    }

    /**
     * @return array{groups:int,merged_headers:int,moved_items:int,moved_adjustments:int,skipped_duplicate_items:int}
     */
    public function reconcile(
        ?int $tenantId = null,
        ?int $branchId = null,
        ?int $cashSessionId = null,
        bool $dryRun = true,
        ?int $actorUserId = null,
    ): array {
        $groups = StaffSettlementModel::query()
            ->selectRaw('tenant_id, branch_id, cash_session_id, staff_user_id, settlement_type, COUNT(*) as total_rows, COUNT(DISTINCT official_shift_id) as distinct_shifts')
            ->where('status', 'PENDING')
            ->whereNotNull('cash_session_id')
            ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($branchId !== null, fn ($q) => $q->where('branch_id', $branchId))
            ->when($cashSessionId !== null, fn ($q) => $q->where('cash_session_id', $cashSessionId))
            ->groupBy('tenant_id', 'branch_id', 'cash_session_id', 'staff_user_id', 'settlement_type')
            ->havingRaw('COUNT(*) > 1')
            ->havingRaw('COUNT(DISTINCT official_shift_id) > 1')
            ->get();

        $mergedHeaders = 0;
        $movedItems = 0;
        $movedAdjustments = 0;
        $skippedDuplicateItems = 0;

        foreach ($groups as $group) {
            $work = function () use (&$mergedHeaders, &$movedItems, &$movedAdjustments, &$skippedDuplicateItems, $group, $dryRun, $actorUserId): void {
                $settlements = StaffSettlementModel::query()
                    ->where('tenant_id', (int) $group->tenant_id)
                    ->where('branch_id', (int) $group->branch_id)
                    ->where('cash_session_id', (int) $group->cash_session_id)
                    ->where('staff_user_id', (int) $group->staff_user_id)
                    ->where('settlement_type', (string) $group->settlement_type)
                    ->where('status', 'PENDING')
                    ->orderBy('id')
                    ->get();

                if ($settlements->count() < 2) {
                    return;
                }

                $canonical = $settlements->first();
                $redundants = $settlements->slice(1);

                foreach ($redundants as $redundant) {
                    $items = StaffSettlementItemModel::query()
                        ->where('staff_settlement_id', (int) $redundant->id)
                        ->orderBy('id')
                        ->get();

                    foreach ($items as $item) {
                        $existsInCanonical = StaffSettlementItemModel::query()
                            ->where('staff_settlement_id', (int) $canonical->id)
                            ->where('source_type', (string) $item->source_type)
                            ->where(function ($q) use ($item): void {
                                if ($item->source_id !== null) {
                                    $q->where('source_id', (int) $item->source_id);
                                } elseif ($item->sale_item_id !== null) {
                                    $q->where('sale_item_id', (int) $item->sale_item_id);
                                } else {
                                    $q->whereRaw('1 = 0');
                                }
                            })
                            ->exists();

                        if ($existsInCanonical) {
                            $skippedDuplicateItems++;

                            if (! $dryRun) {
                                StaffSettlementItemModel::query()
                                    ->where('id', (int) $item->id)
                                    ->delete();
                            }

                            continue;
                        }

                        $movedItems++;

                        if (! $dryRun) {
                            StaffSettlementItemModel::query()
                                ->where('id', (int) $item->id)
                                ->update([
                                    'staff_settlement_id' => (int) $canonical->id,
                                ]);
                        }
                    }

                    $adjustmentsCount = (int) StaffSettlementAdjustmentModel::query()
                        ->where('staff_settlement_id', (int) $redundant->id)
                        ->count();

                    $movedAdjustments += $adjustmentsCount;

                    if (! $dryRun && $adjustmentsCount > 0) {
                        StaffSettlementAdjustmentModel::query()
                            ->where('staff_settlement_id', (int) $redundant->id)
                            ->update([
                                'staff_settlement_id' => (int) $canonical->id,
                            ]);
                    }

                    $mergedHeaders++;

                    if (! $dryRun) {
                        $mergeNote = trim(((string) $redundant->notes).' | MERGED_INTO:'.(int) $canonical->id);

                        StaffSettlementModel::query()
                            ->where('id', (int) $redundant->id)
                            ->update([
                                'status' => 'CANCELLED',
                                'notes' => $mergeNote,
                            ]);
                    }

                    if (! $dryRun) {
                        AuditLogModel::query()->create([
                            'tenant_id' => (int) $group->tenant_id,
                            'branch_id' => (int) $group->branch_id,
                            'user_id' => $actorUserId,
                            'action' => 'settlement.pending_merged_cash_session',
                            'subject_type' => 'staff_settlement',
                            'subject_id' => (int) $redundant->id,
                            'metadata' => [
                                'canonical_settlement_id' => (int) $canonical->id,
                                'redundant_settlement_id' => (int) $redundant->id,
                                'cash_session_id' => (int) $group->cash_session_id,
                                'staff_user_id' => (int) $group->staff_user_id,
                                'settlement_type' => (string) $group->settlement_type,
                                'dry_run' => false,
                            ],
                            'ip_address' => null,
                            'created_at' => now(),
                        ]);
                    }
                }

                if (! $dryRun) {
                    $this->totalsCalculator->recalculate((int) $canonical->id);
                }
            };

            if ($dryRun) {
                $work();
            } else {
                DB::transaction($work);
            }
        }

        return [
            'groups' => $groups->count(),
            'merged_headers' => $mergedHeaders,
            'moved_items' => $movedItems,
            'moved_adjustments' => $movedAdjustments,
            'skipped_duplicate_items' => $skippedDuplicateItems,
        ];
    }
}
