<?php



declare(strict_types=1);



namespace App\Infrastructure\Persistence\Eloquent\Repositories;



use App\Domain\Product\Entities\Product;

use App\Domain\Product\Exceptions\ProductNotFoundException;

use App\Domain\Product\Repositories\ProductRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;



final class EloquentProductRepository implements ProductRepositoryInterface

{

    public function findById(int $id, int $tenantId): ?Product

    {

        $model = ProductModel::query()

            ->where('id', $id)

            ->where('tenant_id', $tenantId)

            ->first();



        return $model ? $this->map($model) : null;

    }



    public function listForTenant(

        int $tenantId,

        ?int $branchId,

        bool $activeOnly,

    ): array {

        $query = ProductModel::query()->where('tenant_id', $tenantId);



        if ($activeOnly) {

            $query->where('status', 'active');

        }



        if ($branchId !== null) {

            $query->where(function ($builder) use ($branchId) {

                $builder->whereNull('branch_id')

                    ->orWhere('branch_id', $branchId);

            });

        }



        return $query->orderBy('name')

            ->get()

            ->map(fn (ProductModel $model) => $this->map($model))

            ->all();

    }



    public function create(

        int $tenantId,

        ?int $branchId,

        ?int $categoryId,

        string $name,

        ?string $sku,

        ?string $barcode,

        ?string $description,

        string $productType,

        string $unit,

        bool $trackInventory,

        string $status,

        string $settlementBehavior = 'GIRL_LINE',

        int $braceletUnitsPerLine = 1,

        bool $requiresAllocation = false,

        ?string $allocationType = null,

    ): Product {

        $model = ProductModel::query()->create([

            'tenant_id' => $tenantId,

            'branch_id' => $branchId,

            'category_id' => $categoryId,

            'name' => $name,

            'sku' => $sku,

            'barcode' => $barcode,

            'description' => $description,

            'product_type' => $productType,

            'unit' => $unit,

            'track_inventory' => $trackInventory,

            'status' => $status,

            'settlement_behavior' => $settlementBehavior,

            'bracelet_units_per_line' => $braceletUnitsPerLine,

            'requires_allocation' => $requiresAllocation,

            'allocation_type' => $allocationType,

        ]);



        return $this->map($model);

    }



    public function update(

        int $id,

        int $tenantId,

        ?int $branchId,

        ?int $categoryId,

        string $name,

        ?string $sku,

        ?string $barcode,

        ?string $description,

        string $productType,

        string $unit,

        bool $trackInventory,

        string $status,

        string $settlementBehavior = 'GIRL_LINE',

        int $braceletUnitsPerLine = 1,

        bool $requiresAllocation = false,

        ?string $allocationType = null,

    ): Product {

        $model = ProductModel::query()

            ->where('id', $id)

            ->where('tenant_id', $tenantId)

            ->first();



        if ($model === null) {

            throw new ProductNotFoundException();

        }



        $model->update([

            'branch_id' => $branchId,

            'category_id' => $categoryId,

            'name' => $name,

            'sku' => $sku,

            'barcode' => $barcode,

            'description' => $description,

            'product_type' => $productType,

            'unit' => $unit,

            'track_inventory' => $trackInventory,

            'status' => $status,

            'settlement_behavior' => $settlementBehavior,

            'bracelet_units_per_line' => $braceletUnitsPerLine,

            'requires_allocation' => $requiresAllocation,

            'allocation_type' => $allocationType,

        ]);



        return $this->map($model->fresh());

    }



    private function map(ProductModel $model): Product

    {

        return new Product(

            id: (int) $model->id,

            tenantId: (int) $model->tenant_id,

            branchId: $model->branch_id !== null ? (int) $model->branch_id : null,

            categoryId: $model->category_id !== null ? (int) $model->category_id : null,

            name: $model->name,

            sku: $model->sku,

            barcode: $model->barcode,

            description: $model->description,

            productType: $model->product_type,

            unit: $model->unit,

            trackInventory: (bool) $model->track_inventory,

            status: $model->status,

            settlementBehavior: $model->settlement_behavior ?? 'GIRL_LINE',

            braceletUnitsPerLine: (int) ($model->bracelet_units_per_line ?? 1),

            requiresAllocation: (bool) ($model->requires_allocation ?? false),

            allocationType: $model->allocation_type,

        );

    }

}


