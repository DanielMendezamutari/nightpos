<?php

declare(strict_types=1);

namespace App\Application\Settings\UseCases;

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use App\Infrastructure\Persistence\Eloquent\Models\PaymentMethodModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductPriceModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoomTypeCatalogModel;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceAreaModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowTypeModel;
use App\Domain\User\Exceptions\UserDomainException;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class BootstrapBranchOperationalDataUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
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
        $created = [];

        if (ProductCategoryModel::query()->where('tenant_id', $tenantId)->where('branch_id', $branchId)->count() === 0) {
            foreach (['Bebidas', 'Tragos'] as $name) {
                ProductCategoryModel::query()->create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'name' => $name,
                    'type' => 'beverage',
                    'status' => 'active',
                ]);
            }
            $created[] = 'categories';
        }

        $bebidas = ProductCategoryModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('name', 'Bebidas')
            ->value('id');

        if ($bebidas && ProductModel::query()->where('tenant_id', $tenantId)->where('branch_id', $branchId)->count() === 0) {
            $catalog = [
                ['name' => 'Cerveza Paceña', 'solo' => 25, 'companion' => 80, 'girl' => 40, 'house' => 40],
                ['name' => 'Mojito', 'solo' => 35, 'companion' => 120, 'girl' => 60, 'house' => 60],
                ['name' => 'Agua', 'solo' => 10, 'companion' => null, 'girl' => null, 'house' => null],
            ];

            foreach ($catalog as $row) {
                $product = ProductModel::query()->create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'category_id' => $bebidas,
                    'name' => $row['name'],
                    'product_type' => 'beverage',
                    'unit' => 'unit',
                    'status' => 'active',
                ]);

                ProductPriceModel::query()->create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'product_id' => $product->id,
                    'sale_mode' => 'SOLO_CLIENTE',
                    'price' => $row['solo'],
                    'currency' => 'BOB',
                    'status' => 'active',
                ]);

                if ($row['companion'] !== null) {
                    ProductPriceModel::query()->create([
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'product_id' => $product->id,
                        'sale_mode' => 'CON_ACOMPANANTE',
                        'price' => $row['companion'],
                        'girl_amount' => $row['girl'],
                        'house_amount' => $row['house'],
                        'currency' => 'BOB',
                        'status' => 'active',
                    ]);
                }
            }

            $created[] = 'products';
        }

        if (ServiceAreaModel::query()->where('branch_id', $branchId)->count() === 0) {
            foreach ([
                ['code' => 'M01', 'name' => 'Mesa 1', 'area_type' => 'TABLE'],
                ['code' => 'BAR', 'name' => 'Barra', 'area_type' => 'BAR'],
            ] as $area) {
                ServiceAreaModel::query()->create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'code' => $area['code'],
                    'name' => $area['name'],
                    'area_type' => $area['area_type'],
                    'status' => 'active',
                ]);
            }
            $created[] = 'service_areas';
        }

        foreach ([
            ['type' => 'EXPENSE', 'name' => 'Gasto operativo'],
            ['type' => 'INCOME', 'name' => 'Otros ingresos'],
        ] as $reason) {
            CashMovementReasonModel::query()->firstOrCreate(
                ['tenant_id' => $tenantId, 'type' => $reason['type'], 'name' => $reason['name']],
                ['status' => 'active'],
            );
        }

        foreach ([
            ['code' => 'CASH', 'name' => 'Efectivo', 'type' => 'CASH'],
            ['code' => 'QR', 'name' => 'QR', 'type' => 'QR'],
            ['code' => 'CARD', 'name' => 'Tarjeta', 'type' => 'CARD'],
        ] as $pm) {
            PaymentMethodModel::query()->firstOrCreate(
                ['tenant_id' => $tenantId, 'code' => $pm['code']],
                [
                    'name' => $pm['name'],
                    'type' => $pm['type'],
                    'enabled' => true,
                    'requires_reference' => $pm['type'] === 'QR',
                ],
            );
        }

        if (ShowTypeModel::query()->where('tenant_id', $tenantId)->where('branch_id', $branchId)->count() === 0) {
            ShowTypeModel::query()->create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'name' => 'Show estándar',
                'suggested_price' => 200,
                'status' => 'active',
            ]);
            $created[] = 'show_types';
        }

        RoomTypeCatalogModel::query()->firstOrCreate(
            ['tenant_id' => $tenantId, 'code' => 'STANDARD'],
            [
                'name' => 'Estándar',
                'default_duration_minutes' => 60,
                'suggested_price' => 80,
                'status' => 'active',
            ],
        );

        return OperationResult::ok('Datos operativos iniciales aplicados.', [
            'created' => $created,
            'skipped' => $created === [],
        ]);
    }
}
