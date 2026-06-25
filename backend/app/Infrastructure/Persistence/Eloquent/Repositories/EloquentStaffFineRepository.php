<?php



declare(strict_types=1);



namespace App\Infrastructure\Persistence\Eloquent\Repositories;



use App\Domain\StaffSettlement\Exceptions\StaffFineDomainException;

use App\Domain\StaffSettlement\Exceptions\StaffFineNotFoundException;

use App\Domain\StaffSettlement\Repositories\StaffFineRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\StaffFineModel;

use App\Shared\Domain\Enums\StaffFineStatus;



final class EloquentStaffFineRepository implements StaffFineRepositoryInterface

{

    public function list(int $tenantId, int $branchId, array $filters): array

    {

        $query = StaffFineModel::query()

            ->with(['staffUser:id,name', 'createdBy:id,name', 'appliedBy:id,name'])

            ->where('tenant_id', $tenantId)

            ->where('branch_id', $branchId);



        if (! empty($filters['status'])) {

            $query->where('status', strtoupper((string) $filters['status']));

        }



        if (! empty($filters['staff_user_id'])) {

            $query->where('staff_user_id', (int) $filters['staff_user_id']);

        }



        if (! empty($filters['official_shift_id'])) {

            $query->where('official_shift_id', (int) $filters['official_shift_id']);

        }



        if (! empty($filters['cash_session_id'])) {

            $query->where('cash_session_id', (int) $filters['cash_session_id']);

        }



        return $query

            ->orderByDesc('id')

            ->limit((int) ($filters['limit'] ?? 100))

            ->get()

            ->map(fn (StaffFineModel $model) => $this->mapFine($model))

            ->all();

    }



    public function create(array $data): array

    {

        $model = StaffFineModel::query()->create([

            'tenant_id' => $data['tenant_id'],

            'branch_id' => $data['branch_id'],

            'official_shift_id' => $data['official_shift_id'],

            'cash_session_id' => $data['cash_session_id'] ?? null,

            'staff_user_id' => $data['staff_user_id'],

            'staff_role' => $data['staff_role'],

            'amount' => number_format((float) $data['amount'], 2, '.', ''),

            'reason' => $data['reason'],

            'notes' => $data['notes'] ?? null,

            'status' => StaffFineStatus::Pending->value,

            'created_by_user_id' => $data['created_by_user_id'],

        ]);



        return $this->mapFine($model->fresh(['staffUser', 'createdBy']));

    }



    public function findById(int $id, int $tenantId, int $branchId): ?array

    {

        $model = StaffFineModel::query()

            ->with(['staffUser:id,name', 'createdBy:id,name', 'appliedBy:id,name'])

            ->where('id', $id)

            ->where('tenant_id', $tenantId)

            ->where('branch_id', $branchId)

            ->first();



        return $model === null ? null : $this->mapFine($model);

    }



    public function cancel(int $id, int $tenantId, int $branchId, int $cancelledByUserId, string $cancellationReason): array

    {

        $model = StaffFineModel::query()

            ->where('id', $id)

            ->where('tenant_id', $tenantId)

            ->where('branch_id', $branchId)

            ->first();



        if ($model === null) {

            throw new StaffFineNotFoundException();

        }



        if ($model->status === StaffFineStatus::Applied->value) {

            throw StaffFineDomainException::cannotCancelApplied();

        }



        if ($model->status === StaffFineStatus::Cancelled->value) {

            throw StaffFineDomainException::cannotCancelCancelled();

        }



        $model->update([

            'status' => StaffFineStatus::Cancelled->value,

            'cancelled_at' => now(),

            'cancelled_by_user_id' => $cancelledByUserId,

            'cancellation_reason' => $cancellationReason,

        ]);



        return $this->mapFine($model->fresh(['staffUser', 'createdBy']));

    }



    /**

     * @return array<string, mixed>

     */

    private function mapFine(StaffFineModel $model): array

    {

        return [

            'id' => $model->id,

            'tenant_id' => $model->tenant_id,

            'branch_id' => $model->branch_id,

            'official_shift_id' => $model->official_shift_id,

            'cash_session_id' => $model->cash_session_id,

            'staff_user_id' => $model->staff_user_id,

            'staff_name' => $model->staffUser?->name,

            'staff_role' => $model->staff_role,

            'amount' => number_format((float) $model->amount, 2, '.', ''),

            'reason' => $model->reason,

            'notes' => $model->notes,

            'status' => $model->status,

            'created_by_user_id' => $model->created_by_user_id,

            'created_by_name' => $model->createdBy?->name,

            'applied_settlement_id' => $model->applied_settlement_id,

            'applied_at' => $model->applied_at?->format('Y-m-d H:i:s'),

            'applied_by_user_id' => $model->applied_by_user_id,

            'applied_by_name' => $model->appliedBy?->name,

            'cancelled_at' => $model->cancelled_at?->format('Y-m-d H:i:s'),

            'cancelled_by_user_id' => $model->cancelled_by_user_id,

            'cancellation_reason' => $model->cancellation_reason,

            'created_at' => $model->created_at?->format('Y-m-d H:i:s'),

            'updated_at' => $model->updated_at?->format('Y-m-d H:i:s'),

        ];

    }

}


