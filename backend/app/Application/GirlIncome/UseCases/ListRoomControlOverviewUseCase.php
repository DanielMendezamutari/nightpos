<?php



declare(strict_types=1);



namespace App\Application\GirlIncome\UseCases;



use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;

use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;

use App\Domain\Room\Repositories\RoomRepositoryInterface;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class ListRoomControlOverviewUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly RoomServiceRepositoryInterface $roomServices,

        private readonly RoomRepositoryInterface $rooms,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();



        if ($tenant === null || $branch === null) {

            throw GirlIncomeDomainException::branchRequired();

        }



        $active = $this->roomServices->listActive($tenant->id, $branch->id);

        $due = $this->roomServices->listDue($tenant->id, $branch->id);

        $cleaning = $this->rooms->listCleaningOverview($tenant->id, $branch->id);

        $finishedToday = $this->roomServices->listFinishedToday($tenant->id, $branch->id);



        return OperationResult::ok('Control de piezas.', [

            'active' => $active,

            'due' => $due,

            'cleaning' => $cleaning,

            'cleaning_count' => count($cleaning),

            'finished_today' => $finishedToday,

            'due_count' => count($due),

        ]);

    }

}

