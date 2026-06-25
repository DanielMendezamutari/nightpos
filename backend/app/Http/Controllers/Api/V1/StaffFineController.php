<?php



declare(strict_types=1);



namespace App\Http\Controllers\Api\V1;



use App\Application\StaffSettlement\UseCases\CancelStaffFineUseCase;

use App\Application\StaffSettlement\UseCases\CreateStaffFineUseCase;

use App\Application\StaffSettlement\UseCases\ListStaffFinesUseCase;

use App\Http\Controllers\Controller;

use App\Http\Requests\Api\V1\StaffFine\CancelStaffFineRequest;

use App\Http\Requests\Api\V1\StaffFine\CreateStaffFineRequest;

use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;



final class StaffFineController extends Controller

{

    public function __construct(

        private readonly ApiResponsePresenterInterface $presenter,

        private readonly ListStaffFinesUseCase $listFines,

        private readonly CreateStaffFineUseCase $createFine,

        private readonly CancelStaffFineUseCase $cancelFine,

    ) {

    }



    public function index(Request $request): JsonResponse

    {

        return $this->presenter->present($this->listFines->execute((object) [

            'status' => $request->query('status'),

            'staffUserId' => $request->query('staff_user_id'),

            'officialShiftId' => $request->query('official_shift_id'),

            'cashSessionId' => $request->query('cash_session_id'),

            'limit' => $request->query('limit'),

        ]));

    }



    public function store(CreateStaffFineRequest $request): JsonResponse

    {

        $validated = $request->validated();



        return $this->presenter->present($this->createFine->execute((object) [

            'staffUserId' => (int) $validated['staff_user_id'],

            'staffRole' => $validated['staff_role'] ?? null,

            'amount' => (float) $validated['amount'],

            'reason' => $validated['reason'],

            'notes' => $validated['notes'] ?? null,

        ]), 201);

    }



    public function cancel(int $id, CancelStaffFineRequest $request): JsonResponse

    {

        $validated = $request->validated();



        return $this->presenter->present($this->cancelFine->execute((object) [

            'fineId' => $id,

            'cancellationReason' => $validated['cancellation_reason'],

        ]));

    }

}


