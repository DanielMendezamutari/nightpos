<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\CreateProductPriceInput;
use App\Application\Product\Support\BranchScopeResolver;
use App\Application\Product\Support\ProductMapper;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Repositories\ProductPriceRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Services\ProductPriceValidator;
use App\Domain\Product\ValueObjects\SaleMode;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class CreateProductPriceUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ProductRepositoryInterface $products,
        private readonly ProductPriceRepositoryInterface $prices,
        private readonly ProductPriceValidator $priceValidator,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof CreateProductPriceInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw ProductDomainException::tenantRequired();
        }

        $product = $this->products->findById($input->productId, $tenant->id);

        if ($product === null) {
            throw new ProductNotFoundException();
        }

        $saleMode = SaleMode::fromString($input->saleMode)->value;
        $branchId = BranchScopeResolver::resolve($input->branchId, $this->branchContext);

        $this->priceValidator->validate(
            saleMode: $saleMode,
            price: $input->price,
            girlAmount: $input->girlAmount,
            houseAmount: $input->houseAmount,
        );

        if ($this->prices->hasActiveSaleMode($tenant->id, $product->id, $branchId, $saleMode)) {
            throw ProductDomainException::duplicateActiveSaleMode();
        }

        $price = $this->prices->create(
            tenantId: $tenant->id,
            branchId: $branchId,
            productId: $product->id,
            saleMode: $saleMode,
            price: $input->price,
            girlAmount: $input->girlAmount,
            houseAmount: $input->houseAmount,
            currency: $input->currency,
            status: $input->status,
            startsAt: $input->startsAt,
            endsAt: $input->endsAt,
        );

        return OperationResult::ok('Precio registrado correctamente.', [
            'price' => ProductMapper::price($price),
        ]);
    }
}
