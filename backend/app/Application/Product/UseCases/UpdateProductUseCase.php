<?php



declare(strict_types=1);



namespace App\Application\Product\UseCases;



use App\Application\Product\DTOs\UpdateProductInput;

use App\Application\Product\Support\ProductMapper;

use App\Application\Product\Support\ProductSettlementNormalizer;

use App\Domain\Product\Exceptions\ProductDomainException;

use App\Domain\Product\Exceptions\ProductNotFoundException;

use App\Domain\Product\Repositories\ProductCategoryRepositoryInterface;

use App\Domain\Product\Repositories\ProductRepositoryInterface;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class UpdateProductUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly ProductRepositoryInterface $products,

        private readonly ProductCategoryRepositoryInterface $categories,

        private readonly ProductSettlementNormalizer $settlementNormalizer,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $input instanceof UpdateProductInput) {

            return OperationResult::fail('Entrada inválida.');

        }



        $tenant = $this->tenantContext->tenant();



        if ($tenant === null) {

            throw ProductDomainException::tenantRequired();

        }



        $existing = $this->products->findById($input->productId, $tenant->id);



        if ($existing === null) {

            throw new ProductNotFoundException();

        }



        $name = trim($input->name);



        if ($name === '') {

            throw ProductDomainException::emptyName();

        }



        if ($input->categoryId !== null) {

            $category = $this->categories->findById($input->categoryId, $tenant->id);



            if ($category === null) {

                throw ProductDomainException::categoryNotFound();

            }

        }



        $settlement = $this->settlementNormalizer->normalize(

            $input->settlementBehavior,

            $input->braceletUnitsPerLine,

        );



        $product = $this->products->update(

            id: $input->productId,

            tenantId: $tenant->id,

            branchId: $input->branchId,

            categoryId: $input->categoryId,

            name: $name,

            sku: $input->sku,

            barcode: $input->barcode,

            description: $input->description,

            productType: $input->productType,

            unit: $input->unit,

            trackInventory: $input->trackInventory,

            status: $input->status,

            settlementBehavior: $settlement['settlement_behavior'],

            braceletUnitsPerLine: $settlement['bracelet_units_per_line'],

            requiresAllocation: $settlement['requires_allocation'],

            allocationType: $settlement['allocation_type'],

        );



        return OperationResult::ok('Producto actualizado correctamente.', [

            'product' => ProductMapper::product($product),

        ]);

    }

}


