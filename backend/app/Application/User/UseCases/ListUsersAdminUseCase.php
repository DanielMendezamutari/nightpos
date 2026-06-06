<?php



declare(strict_types=1);



namespace App\Application\User\UseCases;



use App\Application\User\Support\UserAdminMapper;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class ListUsersAdminUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        $tenant = $this->tenantContext->tenant();



        if ($tenant === null) {

            return OperationResult::fail('Debe indicar la empresa en el contexto.');

        }



        $models = UserModel::query()

            ->with(['role', 'staffProfile', 'accessibleBranches', 'branch'])

            ->where('tenant_id', $tenant->id)

            ->orderBy('name')

            ->get();



        $data = $models->map(static fn (UserModel $model) => UserAdminMapper::user($model))->all();



        return OperationResult::ok('Listado de usuarios.', ['users' => $data]);

    }

}

