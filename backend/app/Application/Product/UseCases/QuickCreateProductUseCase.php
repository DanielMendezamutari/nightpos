<?php

declare(strict_types=1);

namespace App\Application\Product\UseCases;

use App\Application\Product\DTOs\QuickCreateProductInput;
use App\Application\Product\Support\BranchScopeResolver;
use App\Application\Product\Support\ProductMapper;
use App\Application\Product\Support\ProductSettlementNormalizer;
use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\Repositories\ProductCategoryRepositoryInterface;
use App\Domain\Product\Repositories\ProductPriceRepositoryInterface;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\Services\ProductPriceValidator;
use App\Domain\Product\ValueObjects\SaleMode;
use App\Domain\Product\ValueObjects\SettlementBehavior;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;
use Illuminate\Support\Facades\DB;

final class QuickCreateProductUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly ProductRepositoryInterface $products,
        private readonly ProductCategoryRepositoryInterface $categories,
        private readonly ProductPriceRepositoryInterface $prices,
        private readonly ProductPriceValidator $priceValidator,
        private readonly ProductSettlementNormalizer $settlementNormalizer,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        if (! $input instanceof QuickCreateProductInput) {
            return OperationResult::fail('Entrada inválida.');
        }

        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            throw ProductDomainException::tenantRequired();
        }

        $name = trim($input->name);

        if ($name === '') {
            throw ProductDomainException::emptyName();
        }

        $category = $this->categories->findById($input->categoryId, $tenant->id);

        if ($category === null) {
            throw ProductDomainException::categoryNotFound();
        }

        $branchId = BranchScopeResolver::resolve(null, $this->branchContext);

        $soloMode = SaleMode::fromString('SOLO_CLIENTE')->value;
        $this->priceValidator->validate(
            saleMode: $soloMode,
            price: $input->soloPrice,
            girlAmount: null,
            houseAmount: null,
        );

        $companionPrice = $input->companionPrice;
        $hasCompanion = $companionPrice !== null && $companionPrice !== '';

        if ($hasCompanion) {
            $companionMode = SaleMode::fromString('CON_ACOMPANANTE')->value;
            $this->priceValidator->validate(
                saleMode: $companionMode,
                price: $companionPrice,
                girlAmount: $input->girlAmount,
                houseAmount: $input->houseAmount,
            );
        }

        $settlement = $this->settlementNormalizer->normalize(
            $input->settlementBehavior ?? SettlementBehavior::GIRL_LINE,
            $input->braceletUnitsPerLine,
        );

        $result = DB::transaction(function () use ($tenant, $branchId, $input, $name, $soloMode, $hasCompanion, $companionPrice, $settlement) {
            $product = $this->products->create(
                tenantId: $tenant->id,
                branchId: null,
                categoryId: $input->categoryId,
                name: $name,
                sku: $input->sku !== null && $input->sku !== '' ? $input->sku : null,
                barcode: null,
                description: null,
                productType: $input->productType,
                unit: $input->unit,
                trackInventory: false,
                status: $input->status,
                settlementBehavior: $settlement['settlement_behavior'],
                braceletUnitsPerLine: $settlement['bracelet_units_per_line'],
                requiresAllocation: $settlement['requires_allocation'],
                allocationType: $settlement['allocation_type'],
            );

            $soloPrice = $this->prices->create(
                tenantId: $tenant->id,
                branchId: $branchId,
                productId: $product->id,
                saleMode: $soloMode,
                price: $input->soloPrice,
                girlAmount: null,
                houseAmount: null,
                currency: 'BOB',
                status: 'active',
                startsAt: null,
                endsAt: null,
            );

            $companionPriceRow = null;

            if ($hasCompanion) {
                $companionMode = SaleMode::fromString('CON_ACOMPANANTE')->value;
                $companionPriceRow = $this->prices->create(
                    tenantId: $tenant->id,
                    branchId: $branchId,
                    productId: $product->id,
                    saleMode: $companionMode,
                    price: $companionPrice,
                    girlAmount: $input->girlAmount,
                    houseAmount: $input->houseAmount,
                    currency: 'BOB',
                    status: 'active',
                    startsAt: null,
                    endsAt: null,
                );
            }

            return [
                'product' => ProductMapper::product($product),
                'prices' => array_values(array_filter([
                    ProductMapper::price($soloPrice),
                    $companionPriceRow ? ProductMapper::price($companionPriceRow) : null,
                ])),
            ];
        });

        return OperationResult::ok('Producto creado con precios.', $result);
    }
}
