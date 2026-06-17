<?php

declare(strict_types=1);

namespace App\Application\Sale\UseCases;

use App\Application\Sale\DTOs\GetSaleInput;
use App\Application\Sale\Support\SaleAllocationPresenter;
use App\Application\Sale\Support\SaleMapper;
use App\Domain\Sale\Exceptions\SaleNotFoundException;
use App\Domain\Sale\Repositories\SaleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetSaleUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly SaleRepositoryInterface $sales,
        private readonly SaleAllocationPresenter $saleAllocationPresenter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof GetSaleInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return OperationResult::fail('Contexto de empresa no disponible.');
        }

        $sale = $this->sales->findById($input->saleId, $tenant->id);

        if ($sale === null) {
            throw new SaleNotFoundException();
        }

        $data = $this->saleAllocationPresenter->enrichSale(SaleMapper::sale($sale));
        $data['cashier_name'] = $this->resolveUserName($sale->cashierUserId);
        $data['waiter_name'] = $this->resolveUserName($sale->waiterUserId);

        return OperationResult::ok('Venta obtenida.', [
            'sale' => $data,
        ]);
    }

    private function resolveUserName(?int $userId): ?string
    {
        if ($userId === null) {
            return null;
        }

        return UserModel::query()->where('id', $userId)->value('name');
    }
}
