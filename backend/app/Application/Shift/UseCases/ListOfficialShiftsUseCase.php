<?php

declare(strict_types=1);

namespace App\Application\Shift\UseCases;

use App\Application\Shift\Support\ShiftMapper;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ShiftClosureModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListOfficialShiftsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly OfficialShiftRepositoryInterface $shifts,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            return OperationResult::fail('Contexto operativo incompleto.');
        }

        $items = $this->shifts->listForBranch($tenant->id, $branch->id);

        $closures = ShiftClosureModel::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('official_shift_id', array_map(static fn ($s) => $s->id, $items))
            ->get()
            ->keyBy('official_shift_id');

        $data = array_map(function ($shift) use ($closures) {
            $row = ShiftMapper::shiftListItem($shift);
            $closure = $closures->get($shift->id);
            if ($closure !== null) {
                $row['total_sales'] = (string) $closure->total_sales;
                $row['cash_difference'] = (string) $closure->cash_difference;
            }

            return $row;
        }, $items);

        return OperationResult::ok('Historial de turnos.', ['shifts' => $data]);
    }
}
