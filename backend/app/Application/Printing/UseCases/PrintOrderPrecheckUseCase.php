<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Waiter\Services\WaiterOrderAccessPolicy;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Str;

final class PrintOrderPrecheckUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly WaiterOrderAccessPolicy $waiterAccess,
        private readonly CreatePrecheckPrintJobUseCase $createPrecheckJob,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw PrintingDomainException::branchRequired();
        }

        $orderId = (int) ($input->orderId ?? 0);
        $order = $this->orders->findById($orderId, $tenant->id);

        if ($order === null || $order->branchId !== $branch->id) {
            throw new OrderNotFoundException();
        }

        $this->assertCanPrintPrecheck($order);

        if (in_array($order->status, ['BILLED', 'CANCELLED'], true)) {
            return OperationResult::fail('La comanda ya fue cobrada o cancelada.');
        }

        $hasItems = collect($order->items)
            ->contains(fn ($item) => $item->itemStatus !== 'CANCELLED');

        if (! $hasItems) {
            return OperationResult::fail('La comanda no tiene productos para imprimir.');
        }

        $idempotencyKey = 'precheck:'.$orderId.':'.Str::uuid()->toString();

        $job = $this->createPrecheckJob->execute(
            order: $order,
            tenantId: $tenant->id,
            branchId: $branch->id,
            requestedByUserId: $this->staffContext->userId(),
            idempotencyKey: $idempotencyKey,
        );

        if ($job === null) {
            return OperationResult::fail('No hay impresora activa en esta sucursal.');
        }

        return OperationResult::ok('Precuenta enviada a impresora.', [
            'job' => $job,
        ]);
    }

    private function assertCanPrintPrecheck(\App\Domain\Order\Entities\Order $order): void
    {
        if ($this->staffContext->hasPermission('sales.charge')) {
            return;
        }

        $this->waiterAccess->assertCanAccess($order);
    }
}
