<?php

declare(strict_types=1);

namespace App\Domain\Sale\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface SaleItemAllocationRepositoryInterface extends RepositoryInterface
{
    /**
     * @return list<array{
     *   id: int,
     *   sale_item_id: int,
     *   girl_user_id: int,
     *   units: int,
     *   unit_amount_snapshot: string,
     *   total_amount_snapshot: string,
     *   allocation_type: string,
     *   sale_id: int,
     *   order_id: int|null,
     *   product_name_snapshot: string,
     *   sale_number: string,
     *   cash_session_id: int,
     * }>
     */
    public function listUnsettledForShift(int $tenantId, int $branchId, int $officialShiftId): array;

    public function snapshotFromOrderItem(
        int $tenantId,
        int $branchId,
        int $saleItemId,
        int $orderItemId,
    ): void;

    public function existsForSaleItem(int $saleItemId): bool;
}
