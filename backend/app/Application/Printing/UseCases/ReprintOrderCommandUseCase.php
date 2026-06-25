<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use App\Shared\Domain\Enums\PrintJobType;
use Illuminate\Support\Str;

final class ReprintOrderCommandUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly OrderRepositoryInterface $orders,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly CreateOrderCommandPrintJobUseCase $createOrderCommandJob,
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

        $idempotencyKey = 'order_command:'.$orderId.':reprint:'.Str::uuid()->toString();

        $job = $this->createOrderCommandJob->execute(
            order: $order,
            tenantId: $tenant->id,
            branchId: $branch->id,
            requestedByUserId: $this->staffContext->userId(),
            idempotencyKey: $idempotencyKey,
            force: true,
            isCorrectionReprint: true,
        );

        if ($job === null) {
            return OperationResult::fail('No se pudo crear el trabajo de reimpresión. Verifique auto impresión y dispositivo activo.');
        }

        return OperationResult::ok('Reimpresión encolada.', [
            'job' => $job,
        ]);
    }
}
