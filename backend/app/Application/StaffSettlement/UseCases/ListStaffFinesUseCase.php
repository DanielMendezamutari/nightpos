<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\UseCases;



use App\Application\StaffSettlement\Support\StaffFineMapper;

use App\Domain\StaffSettlement\Repositories\StaffFineRepositoryInterface;

use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class ListStaffFinesUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly StaffFineRepositoryInterface $fines,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();



        if ($tenant === null || $branch === null) {

            return OperationResult::fail('Contexto de sucursal requerido.');

        }



        $rows = $this->fines->list($tenant->id, $branch->id, [

            'status' => $input->status ?? null,

            'staff_user_id' => isset($input->staffUserId) ? (int) $input->staffUserId : null,

            'official_shift_id' => isset($input->officialShiftId) ? (int) $input->officialShiftId : null,

            'cash_session_id' => isset($input->cashSessionId) ? (int) $input->cashSessionId : null,

            'limit' => isset($input->limit) ? (int) $input->limit : 100,

        ]);



        return OperationResult::ok('Multas listadas.', [

            'fines' => array_map(static fn (array $row) => StaffFineMapper::fine($row), $rows),

        ]);

    }

}


