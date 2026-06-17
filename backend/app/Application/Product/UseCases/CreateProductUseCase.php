<?php



declare(strict_types=1);



namespace App\Application\Product\UseCases;



use App\Application\Product\DTOs\CreateProductInput;

use App\Application\Product\Support\BranchScopeResolver;

use App\Application\Product\Support\ProductMapper;

use App\Application\Product\Support\ProductSettlementNormalizer;

use App\Domain\Product\Exceptions\ProductDomainException;

use App\Domain\Product\Repositories\ProductCategoryRepositoryInterface;

use App\Domain\Product\Repositories\ProductRepositoryInterface;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;



final class CreateProductUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly ProductRepositoryInterface $products,

        private readonly ProductCategoryRepositoryInterface $categories,

        private readonly ProductSettlementNormalizer $settlementNormalizer,

    ) {

    }



    public function execute(?object $input = null): OperationResult

    {

        if (! $input instanceof CreateProductInput) {

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



        $branchId = BranchScopeResolver::resolve($input->branchId, $this->branchContext);



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



        $product = $this->products->create(

            tenantId: $tenant->id,

            branchId: $branchId,

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



        return OperationResult::ok('Producto creado correctamente.', [

            'product' => ProductMapper::product($product),

        ]);

    }

}


