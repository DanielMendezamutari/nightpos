<?php



declare(strict_types=1);



namespace App\Application\User\UseCases;



use App\Application\User\DTOs\CreateUserInput;

use App\Application\User\Support\StaffProfileRules;

use App\Application\User\Support\StaffRoleToRoleResolver;

use App\Application\User\Support\UserAdminMapper;

use App\Domain\User\Repositories\UserRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class CreateUserAdminUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly UserRepositoryInterface $users,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $input instanceof CreateUserInput) {

            return OperationResult::fail('Entrada inválida.');

        }



        $tenant = $this->tenantContext->tenant();



        if ($tenant === null) {

            return OperationResult::fail('Debe indicar la empresa en el contexto.');

        }



        $profile = StaffProfileRules::normalize(
            $input->staffRole,
            $input->waiterCommissionPercent,
            $input->canReceiveGirlCommissions,
            $input->cleaningBaseAmount,
            $input->cleaningRoomAmount,
        );



        $roleId = StaffRoleToRoleResolver::resolveRoleId(

            $tenant->id,

            $input->staffRole,

            $input->roleId,

        );



        $branchIds = $input->accessibleBranchIds;



        if ($input->branchId !== null && ! in_array($input->branchId, $branchIds, true)) {

            $branchIds[] = $input->branchId;

        }



        $this->users->createForTenant(

            tenantId: $tenant->id,

            branchId: $input->branchId,

            roleId: $roleId,

            name: $input->name,

            username: $input->username,

            email: $input->email,

            password: $input->password,

            pinPlain: $input->pin,

            status: $input->status,

            staffRole: $input->staffRole,

            waiterCommissionPercent: $profile['waiter_commission_percent'],

            canReceiveGirlCommissions: $profile['can_receive_girl_commissions'],
            accessibleBranchIds: $branchIds,
            cleaningBaseAmount: $profile['cleaning_base_amount'],
            cleaningRoomAmount: $profile['cleaning_room_amount'],
        );



        $model = UserModel::query()

            ->with(['role', 'staffProfile', 'accessibleBranches', 'branch'])

            ->where('tenant_id', $tenant->id)

            ->where('username', $input->username)

            ->first();



        return OperationResult::ok('Usuario creado correctamente.', [

            'user' => $model ? UserAdminMapper::user($model) : null,

        ]);

    }

}

