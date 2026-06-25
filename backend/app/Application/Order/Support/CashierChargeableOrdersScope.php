<?php

declare(strict_types=1);

namespace App\Application\Order\Support;

use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * Fuente única de verdad para comandas pendientes de cobro de cajera.
 * Debe alinearse con GET /orders?scope=cashier_chargeable&cashier_scope=1
 * y con el close-check de caja.
 */
final class CashierChargeableOrdersScope
{
    /** @var list<string> */
    public const STATUSES = OrderListScopeResolver::CASHIER_CHARGEABLE;

    public function __construct(
        private readonly OfficialShiftRepositoryInterface $shifts,
    ) {}

    /**
     * Misma resolución de turno que ListOrdersUseCase con cashier_scope=1.
     */
    public function countForCashierScope(int $tenantId, int $branchId): int
    {
        $shiftId = $this->shifts->findOpenForBranch($tenantId, $branchId)?->id;

        if ($shiftId === null) {
            return 0;
        }

        return $this->count($tenantId, $branchId, $shiftId);
    }

    /**
     * @return Builder<OrderModel>
     */
    public function query(int $tenantId, int $branchId, int $officialShiftId): Builder
    {
        return OrderModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('official_shift_id', $officialShiftId)
            ->whereIn('status', self::STATUSES);
    }

    public function count(int $tenantId, int $branchId, int $officialShiftId): int
    {
        return $this->query($tenantId, $branchId, $officialShiftId)->count();
    }
}
