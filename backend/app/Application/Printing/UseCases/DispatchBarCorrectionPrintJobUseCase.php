<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Order\ValueObjects\OrderStatus;

final class DispatchBarCorrectionPrintJobUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly CreateOrderCommandPrintJobUseCase $createOrderCommandJob,
    ) {
    }

    /**
     * Encola REIMPRESIÓN de comanda barra tras corrección en producción.
     *
     * @return array<string, mixed>|null
     */
    public function execute(Order $order, int $tenantId, int $branchId, ?int $requestedByUserId): ?array
    {
        if ($order->status !== OrderStatus::SENT_TO_BAR) {
            return null;
        }

        $correctionNumber = $this->orders->incrementBarCorrectionCount($order->id, $tenantId);

        $fresh = $this->orders->findById($order->id, $tenantId) ?? $order;

        return $this->createOrderCommandJob->execute(
            order: $fresh,
            tenantId: $tenantId,
            branchId: $branchId,
            requestedByUserId: $requestedByUserId,
            idempotencyKey: "order_command:{$order->id}:correction:{$correctionNumber}",
            force: false,
            isCorrectionReprint: true,
            correctionNumber: $correctionNumber,
        );
    }
}
