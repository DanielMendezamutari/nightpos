<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\Services;



use App\Domain\StaffSettlement\Exceptions\StaffFineDomainException;

use App\Infrastructure\Persistence\Eloquent\Models\StaffFineModel;

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementAdjustmentModel;

use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;

use App\Shared\Domain\Enums\SettlementAdjustmentType;

use App\Shared\Domain\Enums\StaffFineStatus;

use Illuminate\Support\Collection;



final class SettlementFineApplier

{

    public function __construct(
        private readonly SettlementTotalsCalculator $totalsCalculator,
        private readonly \App\Shared\Application\Support\AuditLogRecorder $audit,
    ) {
    }

    /**
     * @return Collection<int, StaffFineModel>
     */
    public function listEligiblePendingFines(StaffSettlementModel $settlement): Collection

    {

        return StaffFineModel::query()

            ->where('tenant_id', $settlement->tenant_id)

            ->where('branch_id', $settlement->branch_id)

            ->where('official_shift_id', $settlement->official_shift_id)

            ->where('staff_user_id', $settlement->staff_user_id)

            ->where('status', StaffFineStatus::Pending->value)

            ->orderBy('id')

            ->get();

    }



    /**

     * @param  array<int, int>  $appliedFineIds

     * @return array{

     *     gross_amount: string,

     *     adjustments: array<int, array<string, mixed>>,

     *     net_amount: string,

     *     available_fines: array<int, array<string, mixed>>

     * }

     */

    public function buildPayPreview(StaffSettlementModel $settlement, array $appliedFineIds): array

    {

        $availableFines = $this->listEligiblePendingFines($settlement);

        $appliedFineIds = array_values(array_unique(array_map('intval', $appliedFineIds)));



        $this->assertAppliedFineIdsSubset($settlement, $availableFines, $appliedFineIds);



        $gross = number_format((float) $settlement->gross_amount, 2, '.', '');

        $adjustments = $this->existingSettlementAdjustments((int) $settlement->id);



        $selectedFines = $availableFines->whereIn('id', $appliedFineIds);



        foreach ($selectedFines as $fine) {

            $adjustments[] = $this->previewFineAdjustment($fine);

        }



        $net = $this->calculateNet((float) $gross, $adjustments);



        return [

            'gross_amount' => $gross,

            'adjustments' => $adjustments,

            'net_amount' => number_format($net, 2, '.', ''),

            'available_fines' => $availableFines

                ->map(fn (StaffFineModel $fine) => $this->mapAvailableFine($fine))

                ->values()

                ->all(),

        ];

    }



    /**

     * @param  array<int, int>  $appliedFineIds

     */

    public function applySelectedFines(StaffSettlementModel $settlement, array $appliedFineIds, int $appliedByUserId): void

    {

        $appliedFineIds = array_values(array_unique(array_map('intval', $appliedFineIds)));



        if ($appliedFineIds === []) {

            return;

        }



        $availableFines = $this->listEligiblePendingFines($settlement);

        $this->assertAppliedFineIdsSubset($settlement, $availableFines, $appliedFineIds);



        foreach ($appliedFineIds as $fineId) {

            $fine = StaffFineModel::query()

                ->where('id', $fineId)

                ->lockForUpdate()

                ->first();



            if ($fine === null || $fine->status !== StaffFineStatus::Pending->value) {

                throw StaffFineDomainException::fineNotPending();

            }



            $this->assertFineMatchesSettlement($settlement, $fine);



            $dedupKey = $this->fineDedupKey($fineId);



            if (StaffSettlementAdjustmentModel::query()->where('dedup_key', $dedupKey)->exists()) {

                throw StaffFineDomainException::fineAlreadyApplied();

            }



            $amount = number_format(-1 * abs((float) $fine->amount), 2, '.', '');



            StaffSettlementAdjustmentModel::query()->create([

                'tenant_id' => $settlement->tenant_id,

                'branch_id' => $settlement->branch_id,

                'staff_settlement_id' => $settlement->id,

                'staff_fine_id' => $fine->id,

                'adjustment_type' => SettlementAdjustmentType::ManualFine->value,

                'amount' => $amount,

                'notes' => $fine->reason,

                'dedup_key' => $dedupKey,

                'created_by_user_id' => $appliedByUserId,

            ]);



            $fine->update([

                'status' => StaffFineStatus::Applied->value,

                'applied_settlement_id' => $settlement->id,

                'applied_at' => now(),

                'applied_by_user_id' => $appliedByUserId,

            ]);

            $this->audit->record(
                'FINE_APPLIED',
                'staff_fine',
                (int) $fine->id,
                [
                    'settlement_id' => $settlement->id,
                    'amount' => number_format((float) $fine->amount, 2, '.', ''),
                ],
            );
        }



        $this->totalsCalculator->recalculate((int) $settlement->id);

    }



    public function fineDedupKey(int $fineId): string

    {

        return sprintf('fine:%d', $fineId);

    }



    /**

     * @param  Collection<int, StaffFineModel>  $availableFines

     * @param  array<int, int>  $appliedFineIds

     */

    private function assertAppliedFineIdsSubset(

        StaffSettlementModel $settlement,

        Collection $availableFines,

        array $appliedFineIds,

    ): void {

        if ($appliedFineIds === []) {

            return;

        }



        $eligibleIds = $availableFines->pluck('id')->map(fn ($id) => (int) $id)->all();



        foreach ($appliedFineIds as $fineId) {

            if (! in_array($fineId, $eligibleIds, true)) {

                throw StaffFineDomainException::invalidAppliedFineIds();

            }

        }

    }



    private function assertFineMatchesSettlement(StaffSettlementModel $settlement, StaffFineModel $fine): void

    {

        if ((int) $fine->branch_id !== (int) $settlement->branch_id) {

            throw StaffFineDomainException::fineBranchMismatch();

        }



        if ((int) $fine->staff_user_id !== (int) $settlement->staff_user_id) {

            throw StaffFineDomainException::fineStaffMismatch();

        }



        if ((int) $fine->official_shift_id !== (int) $settlement->official_shift_id) {

            throw StaffFineDomainException::fineShiftMismatch();

        }

    }



    /**

     * @return array<int, array<string, mixed>>

     */

    private function existingSettlementAdjustments(int $settlementId): array

    {

        return StaffSettlementAdjustmentModel::query()

            ->where('staff_settlement_id', $settlementId)

            ->orderBy('id')

            ->get()

            ->map(function (StaffSettlementAdjustmentModel $row) {

                $item = [

                    'type' => $row->adjustment_type,

                    'amount' => number_format((float) $row->amount, 2, '.', ''),

                ];



                if ($row->adjustment_type === SettlementAdjustmentType::ManualFine->value && $row->staff_fine_id !== null) {

                    $item['fine_id'] = (int) $row->staff_fine_id;

                    $item['reason'] = $row->notes;

                }



                return $item;

            })

            ->all();

    }



    /**

     * @return array<string, mixed>

     */

    private function previewFineAdjustment(StaffFineModel $fine): array

    {

        return [

            'type' => SettlementAdjustmentType::ManualFine->value,

            'amount' => number_format(-1 * abs((float) $fine->amount), 2, '.', ''),

            'fine_id' => (int) $fine->id,

            'reason' => $fine->reason,

        ];

    }



    /**

     * @return array<string, mixed>

     */

    private function mapAvailableFine(StaffFineModel $fine): array

    {

        return [

            'id' => (int) $fine->id,

            'staff_user_id' => (int) $fine->staff_user_id,

            'staff_role' => $fine->staff_role,

            'amount' => number_format((float) $fine->amount, 2, '.', ''),

            'reason' => $fine->reason,

            'notes' => $fine->notes,

            'status' => $fine->status,

            'cash_session_id' => $fine->cash_session_id !== null ? (int) $fine->cash_session_id : null,

        ];

    }



    /**

     * @param  array<int, array<string, mixed>>  $adjustments

     */

    private function calculateNet(float $gross, array $adjustments): float

    {

        $total = $gross;



        foreach ($adjustments as $adjustment) {

            $total += (float) $adjustment['amount'];

        }



        return $total;

    }

}


