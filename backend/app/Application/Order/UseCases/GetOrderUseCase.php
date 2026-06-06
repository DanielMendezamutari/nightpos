<?php

declare(strict_types=1);

namespace App\Application\Order\UseCases;

use App\Application\Order\DTOs\GetOrderInput;
use App\Application\Order\Support\OrderMapper;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetOrderUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly WaiterOrderAccessPolicy $waiterAccess,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof GetOrderInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Contexto de empresa no disponible.');
        }

        $order = $this->orders->findById($input->orderId, $tenant->id);

        if ($order === null) {
            throw new OrderNotFoundException();
        }

        $this->waiterAccess->assertCanAccess($order);

        $data = OrderMapper::order($order);

        if ($order->waiterUserId !== null) {
            $data['waiter_name'] = UserModel::query()->where('id', $order->waiterUserId)->value('name');
        }

        return OperationResult::ok('Comanda obtenida.', [
            'order' => $data,
        ]);
    }
}
