<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Salon\Entities\Salon;
use App\Domain\Salon\Repositories\SalonRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\SalonModel;

class EloquentSalonRepository implements SalonRepositoryInterface
{
    public function getSalonesByBranch(string $tenantId, string $branchId): array
    {
        $salones = SalonModel::where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('activo', true)
            ->withCount([
                'mesas',
                'mesas as mesas_ocupadas_count' => function ($query) {
                    $query->whereHas('visitaActiva');
                },
            ])
            ->orderBy('orden')
            ->get();

        return $salones->map(fn (SalonModel $model) => $this->toDomain($model))->all();
    }

    public function findById(int $id): ?Salon
    {
        $model = SalonModel::withCount([
            'mesas',
            'mesas as mesas_ocupadas_count' => function ($query) {
                $query->whereHas('visitaActiva');
            },
        ])->find($id);

        return $model ? $this->toDomain($model) : null;
    }

    private function toDomain(SalonModel $model): Salon
    {
        return new Salon(
            id: $model->id,
            tenantId: (string) $model->tenant_id,
            branchId: (string) $model->branch_id,
            codigo: $model->codigo,
            nombre: $model->nombre,
            impresoraCuenta: $model->impresora_cuenta,
            impresoraFactura: $model->impresora_factura,
            orden: $model->orden,
            activo: (bool) $model->activo,
            totalMesas: (int) ($model->mesas_count ?? 0),
            mesasOcupadas: (int) ($model->mesas_ocupadas_count ?? 0),
        );
    }
}