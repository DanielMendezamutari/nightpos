<?php

declare(strict_types=1);

namespace App\Domain\Order\Repositories;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Shared\Contracts\RepositoryInterface;

interface OrderRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id, int $tenantId): ?Order;

    /**
     * @param  list<string>|null  $statuses
     * @return list<Order>
     */
    public function listForBranch(
        int $tenantId,
        int $branchId,
        ?string $status = null,
        ?int $officialShiftId = null,
        ?array $statuses = null,
        ?int $cashSessionId = null,
        ?int $cashierUserId = null,
        bool $includeItems = false,
    ): array;

    /**
     * @param  list<string>|null  $statuses
     * @return list<Order>
     */
    public function listForWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        ?string $status = null,
        ?array $statuses = null,
        ?int $officialShiftId = null,
    ): array;

    /**
     * @param  list<string>|null  $statuses
     */
    public function countForWaiter(
        int $tenantId,
        int $branchId,
        int $waiterUserId,
        ?string $status = null,
        ?array $statuses = null,
        ?int $officialShiftId = null,
    ): int;

    public function create(
        int $tenantId,
        int $branchId,
        int $officialShiftId,
        string $orderNumber,
        ?string $tableLabel,
        ?int $serviceAreaId,
        ?int $serviceTableId,
        ?int $waiterUserId,
        int $openedByUserId,
        ?string $notes,
        ?string $sourceType = null,
        ?int $sourceId = null,
    ): Order;

    public function findActiveByServiceTable(
        int $tenantId,
        int $branchId,
        int $serviceTableId,
        ?int $officialShiftId = null,
    ): ?Order;

    public function addItem(
        int $tenantId,
        int $branchId,
        int $orderId,
        int $productId,
        string $productName,
        string $saleMode,
        int $quantity,
        string $unitPrice,
        string $lineTotal,
        ?string $girlAmount,
        ?string $houseAmount,
        ?int $girlUserId,
        ?string $notes,
    ): OrderItem;

    public function recalculateTotals(int $orderId): Order;

    public function updateStatus(int $orderId, int $tenantId, string $status, ?int $changedByUserId): Order;

    public function markItemsSentToBar(int $orderId): void;

    public function updateItemGirlUserId(int $tenantId, int $orderId, int $itemId, int $girlUserId): void;

    public function updateItem(
        int $tenantId,
        int $orderId,
        int $itemId,
        int $productId,
        string $productName,
        string $saleMode,
        int $quantity,
        string $unitPrice,
        string $lineTotal,
        ?string $girlAmount,
        ?string $houseAmount,
        ?int $girlUserId,
    ): void;

    public function removeItem(int $tenantId, int $orderId, int $itemId): void;

    public function cancelItem(
        int $tenantId,
        int $orderId,
        int $itemId,
        string $reason,
        int $cancelledByUserId,
    ): void;

    public function updateHeader(
        int $tenantId,
        int $orderId,
        string $tableLabel,
        ?int $serviceAreaId,
        ?string $notes,
    ): Order;

    public function nextOrderNumber(int $branchId): string;

    public function incrementBarCorrectionCount(int $orderId, int $tenantId): int;
}
