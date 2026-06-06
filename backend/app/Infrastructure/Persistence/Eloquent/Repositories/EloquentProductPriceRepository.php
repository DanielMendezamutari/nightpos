<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Product\Entities\ProductPrice;
use App\Domain\Product\Repositories\ProductPriceRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use Illuminate\Support\Carbon;

final class EloquentProductPriceRepository implements ProductPriceRepositoryInterface
{
    public function findActiveForProduct(
        int $tenantId,
        int $productId,
        ?int $branchId,
        string $saleMode,
    ): ?ProductPrice {
        if ($branchId !== null) {
            $branchPrice = $this->findActiveQuery($tenantId, $productId, $branchId, $saleMode)->first();

            if ($branchPrice !== null) {
                return $this->map($branchPrice);
            }
        }

        $tenantWide = $this->findActiveQuery($tenantId, $productId, null, $saleMode)->first();

        return $tenantWide ? $this->map($tenantWide) : null;
    }

    public function hasActiveSaleMode(
        int $tenantId,
        int $productId,
        ?int $branchId,
        string $saleMode,
        ?int $excludePriceId = null,
    ): bool {
        $query = ProductPriceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('sale_mode', $saleMode)
            ->where('status', 'active');

        if ($branchId === null) {
            $query->whereNull('branch_id');
        } else {
            $query->where('branch_id', $branchId);
        }

        if ($excludePriceId !== null) {
            $query->where('id', '!=', $excludePriceId);
        }

        $now = Carbon::now();

        return $query->where(function ($builder) use ($now) {
            $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
        })->where(function ($builder) use ($now) {
            $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
        })->exists();
    }

    public function listForProduct(int $tenantId, int $productId, ?int $branchId): array
    {
        $query = ProductPriceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId);

        if ($branchId !== null) {
            $query->where(function ($builder) use ($branchId) {
                $builder->whereNull('branch_id')
                    ->orWhere('branch_id', $branchId);
            });
        }

        return $query->orderBy('sale_mode')
            ->get()
            ->map(fn (ProductPriceModel $model) => $this->map($model))
            ->all();
    }

    public function create(
        int $tenantId,
        ?int $branchId,
        int $productId,
        string $saleMode,
        string $price,
        ?string $girlAmount,
        ?string $houseAmount,
        string $currency,
        string $status,
        ?string $startsAt,
        ?string $endsAt,
    ): ProductPrice {
        $model = ProductPriceModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'product_id' => $productId,
            'sale_mode' => $saleMode,
            'price' => $price,
            'girl_amount' => $girlAmount,
            'house_amount' => $houseAmount,
            'currency' => $currency,
            'status' => $status,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        return $this->map($model);
    }

    public function deactivateActiveForSaleMode(
        int $tenantId,
        int $productId,
        ?int $branchId,
        string $saleMode,
    ): void {
        $query = ProductPriceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('sale_mode', $saleMode)
            ->where('status', 'active');

        if ($branchId === null) {
            $query->whereNull('branch_id');
        } else {
            $query->where('branch_id', $branchId);
        }

        $now = Carbon::now();

        $query->where(function ($builder) use ($now) {
            $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
        })->where(function ($builder) use ($now) {
            $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
        })->update(['status' => 'inactive']);
    }

    public function listActiveGroupedByProduct(
        int $tenantId,
        array $productIds,
        ?int $branchId,
    ): array {
        if ($productIds === []) {
            return [];
        }

        $now = Carbon::now();
        $query = ProductPriceModel::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('product_id', $productIds)
            ->where('status', 'active')
            ->where(function ($builder) use ($now) {
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($builder) use ($now) {
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });

        if ($branchId !== null) {
            $query->where(function ($builder) use ($branchId) {
                $builder->whereNull('branch_id')
                    ->orWhere('branch_id', $branchId);
            });
        }

        $grouped = [];

        foreach ($query->get() as $model) {
            $productId = (int) $model->product_id;
            $mapped = $this->map($model);

            if (! isset($grouped[$productId])) {
                $grouped[$productId] = [];
            }

            $existing = $grouped[$productId][$model->sale_mode] ?? null;

            if ($existing === null) {
                $grouped[$productId][$model->sale_mode] = $mapped;

                continue;
            }

            if ($branchId !== null && $model->branch_id === $branchId) {
                $grouped[$productId][$model->sale_mode] = $mapped;
            }
        }

        $result = [];

        foreach ($grouped as $productId => $byMode) {
            $result[$productId] = array_values($byMode);
        }

        return $result;
    }

    private function findActiveQuery(int $tenantId, int $productId, ?int $branchId, string $saleMode)
    {
        $now = Carbon::now();

        $query = ProductPriceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('sale_mode', $saleMode)
            ->where('status', 'active');

        if ($branchId === null) {
            $query->whereNull('branch_id');
        } else {
            $query->where('branch_id', $branchId);
        }

        return $query->where(function ($builder) use ($now) {
            $builder->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
        })->where(function ($builder) use ($now) {
            $builder->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
        });
    }

    private function map(ProductPriceModel $model): ProductPrice
    {
        return new ProductPrice(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            branchId: $model->branch_id !== null ? (int) $model->branch_id : null,
            productId: (int) $model->product_id,
            saleMode: $model->sale_mode,
            price: (string) $model->price,
            girlAmount: $model->girl_amount !== null ? (string) $model->girl_amount : null,
            houseAmount: $model->house_amount !== null ? (string) $model->house_amount : null,
            currency: $model->currency,
            status: $model->status,
            startsAt: $model->starts_at?->toIso8601String(),
            endsAt: $model->ends_at?->toIso8601String(),
        );
    }
}
