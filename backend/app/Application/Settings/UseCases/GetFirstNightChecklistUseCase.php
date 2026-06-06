<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Domain\Settings\Repositories\CashMovementReasonRepositoryInterface;
use App\Domain\Settings\Repositories\PaymentMethodRepositoryInterface;
use App\Domain\ShowType\Repositories\ShowTypeRepositoryInterface;
use App\Domain\User\Exceptions\UserDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserBranchAccessModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetFirstNightChecklistUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly PaymentMethodRepositoryInterface $paymentMethods,
        private readonly CashMovementReasonRepositoryInterface $cashReasons,
        private readonly ShowTypeRepositoryInterface $showTypes,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $tenantId = $tenant->id;
        $branchId = $branch->id;

        $activeProducts = ProductModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->count();

        $activePrices = ProductPriceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'active')
            ->count();

        $availableRooms = RoomModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'AVAILABLE')
            ->count();

        $branchUserIds = UserBranchAccessModel::query()
            ->where('branch_id', $branchId)
            ->pluck('user_id');

        $waiters = $this->countStaffByRole($branchUserIds, 'WAITER');
        $girls = $this->countStaffByRole($branchUserIds, 'GIRL');
        $cashiers = UserModel::query()
            ->whereIn('id', $branchUserIds)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['cashier', 'cashier_senior', 'tenant_owner']))
            ->count();

        $paymentOk = $this->paymentMethods->hasEnabledCash($tenantId, $branchId)
            && count($this->paymentMethods->listForBranch($tenantId, $branchId, true)) > 0;

        $incomeReasons = count(array_filter(
            $this->cashReasons->listForBranch($tenantId, $branchId, 'INCOME', true),
        ));
        $expenseReasons = count(array_filter(
            $this->cashReasons->listForBranch($tenantId, $branchId, 'EXPENSE', true),
        ));
        $cashReasonsOk = $incomeReasons > 0 && $expenseReasons > 0;

        $showTypesOk = count($this->showTypes->listForBranch($tenantId, $branchId)) > 0;

        $items = [
            $this->item('active_products', 'Productos activos', $activeProducts > 0, 'nightpos-products'),
            $this->item('product_prices', 'Precios configurados', $activePrices > 0, 'nightpos-catalog-prices'),
            $this->item('available_rooms', 'Habitaciones disponibles', $availableRooms > 0, 'nightpos-rooms-list'),
            $this->item('waiters', 'Garzones operativos', $waiters > 0, 'nightpos-staff-waiters'),
            $this->item('girls', 'Chicas operativas', $girls > 0, 'nightpos-staff-girls'),
            $this->item('cashiers', 'Cajeras / caja', $cashiers > 0, 'nightpos-staff-cashiers'),
            $this->item('payment_methods', 'Métodos de pago', $paymentOk, 'nightpos-settings-payments'),
            $this->item('cash_reasons', 'Motivos de caja', $cashReasonsOk, 'nightpos-settings-cash-reasons'),
            $this->item('show_types', 'Tipos de show', $showTypesOk, 'nightpos-services-shows-create'),
        ];

        $complete = array_reduce($items, fn (bool $ok, array $row) => $ok && $row['complete'], true);

        return OperationResult::ok('Checklist primera noche.', [
            'checklist' => [
                'complete' => $complete,
                'items' => $items,
            ],
        ]);
    }

    /**
     * @param \Illuminate\Support\Collection<int, int> $userIds
     */
    private function countStaffByRole($userIds, string $staffRole): int
    {
        return StaffProfileModel::query()
            ->whereIn('user_id', $userIds)
            ->where('staff_role', $staffRole)
            ->where('status', 'active')
            ->count();
    }

    /**
     * @return array<string, mixed>
     */
    private function item(string $key, string $label, bool $complete, string $routeName): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'complete' => $complete,
            'status' => $complete ? 'complete' : 'incomplete',
            'configure_route' => $routeName,
        ];
    }
}
