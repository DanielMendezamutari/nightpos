<?php



declare(strict_types=1);



namespace App\Http\Controllers\Api\V1\Admin;



use App\Application\User\DTOs\CreateUserInput;

use App\Application\User\DTOs\ResetPasswordInput;

use App\Application\User\DTOs\ResetPinInput;

use App\Application\User\DTOs\UpdateUserInput;

use App\Application\User\DTOs\UserBranchAccessInput;

use App\Application\User\UseCases\CreateUserAdminUseCase;

use App\Application\User\UseCases\GetUserAdminUseCase;

use App\Application\User\UseCases\GrantUserBranchAccessUseCase;

use App\Application\User\UseCases\ListUsersAdminUseCase;

use App\Application\User\UseCases\ResetUserPasswordAdminUseCase;

use App\Application\User\UseCases\ResetUserPinAdminUseCase;

use App\Application\User\UseCases\RevokeUserBranchAccessUseCase;

use App\Application\User\UseCases\UpdateUserAdminUseCase;

use App\Http\Controllers\Controller;

use App\Http\Requests\Api\V1\Admin\CreateUserRequest;

use App\Http\Requests\Api\V1\Admin\ResetPasswordRequest;

use App\Http\Requests\Api\V1\Admin\ResetPinRequest;

use App\Http\Requests\Api\V1\Admin\UpdateUserRequest;

use App\Http\Requests\Api\V1\Admin\UserBranchAccessRequest;

use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;

use Illuminate\Http\JsonResponse;



final class AdminUserController extends Controller

{

    public function __construct(

        private readonly ApiResponsePresenterInterface $presenter,

        private readonly ListUsersAdminUseCase $listUsers,

        private readonly GetUserAdminUseCase $getUser,

        private readonly CreateUserAdminUseCase $createUser,

        private readonly UpdateUserAdminUseCase $updateUser,

        private readonly ResetUserPinAdminUseCase $resetPin,

        private readonly ResetUserPasswordAdminUseCase $resetPassword,

        private readonly GrantUserBranchAccessUseCase $grantBranch,

        private readonly RevokeUserBranchAccessUseCase $revokeBranch,

    ) {

    }



    public function index(): JsonResponse

    {

        return $this->presenter->present($this->listUsers->execute());

    }



    public function show(int $id): JsonResponse

    {

        return $this->presenter->present($this->getUser->execute($id));

    }



    public function store(CreateUserRequest $request): JsonResponse

    {

        $validated = $request->validated();



        $result = $this->createUser->execute(new CreateUserInput(

            name: $validated['name'],

            username: $validated['username'],

            email: $validated['email'] ?? null,

            password: $validated['password'] ?? null,

            pin: $validated['pin'] ?? null,

            branchId: $validated['branch_id'] ?? null,

            roleId: $validated['role_id'] ?? null,

            status: $validated['status'] ?? 'active',

            staffRole: $validated['staff_role'] ?? null,

            waiterCommissionPercent: isset($validated['waiter_commission_percent'])

                ? (string) $validated['waiter_commission_percent']

                : null,

            canReceiveGirlCommissions: $validated['can_receive_girl_commissions'] ?? null,
            cleaningBaseAmount: isset($validated['cleaning_base_amount'])
                ? (string) $validated['cleaning_base_amount']
                : null,
            cleaningRoomAmount: isset($validated['cleaning_room_amount'])
                ? (string) $validated['cleaning_room_amount']
                : null,
            accessibleBranchIds: $validated['accessible_branch_ids'] ?? [],
        ));

        return $this->presenter->present($result, 201);

    }



    public function update(UpdateUserRequest $request, int $id): JsonResponse

    {

        $validated = $request->validated();



        $result = $this->updateUser->execute(new UpdateUserInput(

            userId: $id,

            name: $validated['name'],

            username: $validated['username'],

            email: $validated['email'] ?? null,

            branchId: $validated['branch_id'] ?? null,

            roleId: $validated['role_id'] ?? null,

            status: $validated['status'],

            staffRole: $validated['staff_role'] ?? null,

            waiterCommissionPercent: isset($validated['waiter_commission_percent'])

                ? (string) $validated['waiter_commission_percent']

                : null,

            canReceiveGirlCommissions: $validated['can_receive_girl_commissions'] ?? null,
            cleaningBaseAmount: isset($validated['cleaning_base_amount'])
                ? (string) $validated['cleaning_base_amount']
                : null,
            cleaningRoomAmount: isset($validated['cleaning_room_amount'])
                ? (string) $validated['cleaning_room_amount']
                : null,
            accessibleBranchIds: $validated['accessible_branch_ids'] ?? [],
        ));

        return $this->presenter->present($result);

    }



    public function resetPin(ResetPinRequest $request, int $id): JsonResponse

    {

        $validated = $request->validated();



        $result = $this->resetPin->execute(new ResetPinInput(

            userId: $id,

            pin: $validated['pin'],

        ));



        return $this->presenter->present($result);

    }



    public function resetPassword(ResetPasswordRequest $request, int $id): JsonResponse

    {

        $validated = $request->validated();



        $result = $this->resetPassword->execute(new ResetPasswordInput(

            userId: $id,

            password: $validated['password'],

        ));



        return $this->presenter->present($result);

    }



    public function grantBranch(UserBranchAccessRequest $request, int $id): JsonResponse

    {

        $validated = $request->validated();



        $result = $this->grantBranch->execute(new UserBranchAccessInput(

            userId: $id,

            branchId: (int) $validated['branch_id'],

        ));



        return $this->presenter->present($result);

    }



    public function revokeBranch(int $id, int $branchId): JsonResponse

    {

        $result = $this->revokeBranch->execute(new UserBranchAccessInput(

            userId: $id,

            branchId: $branchId,

        ));



        return $this->presenter->present($result);

    }

}

