<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;

final class CashMovementTaxonomyBackfillService
{
    public function __construct(
        private readonly CashMovementTaxonomyResolver $resolver,
    ) {
    }

    /**
     * @return array{rows_found: int, rows_updated: int, strategies: array<string, int>}
     */
    public function backfill(?int $tenantId = null, ?int $branchId = null): array
    {
        $query = CashMovementModel::query()
            ->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId))
            ->when($branchId !== null, fn ($q) => $q->where('branch_id', $branchId))
            ->where(function ($q) {
                $q->whereNull('movement_family')
                    ->orWhereNull('movement_category');
            });

        $rowsFound = (clone $query)->count();
        $rowsUpdated = 0;
        $strategies = [];

        $query->with('reason')->orderBy('id')->chunkById(200, function ($rows) use (&$rowsUpdated, &$strategies) {
            foreach ($rows as $movement) {
                $taxonomy = $this->resolver->resolveForExisting($movement);

                $movement->update([
                    'movement_family' => $taxonomy['family'],
                    'movement_category' => $taxonomy['category'],
                ]);

                $rowsUpdated++;
                $strategies[$taxonomy['strategy']] = ($strategies[$taxonomy['strategy']] ?? 0) + 1;
            }
        }, 'id');

        ksort($strategies);

        return [
            'rows_found' => $rowsFound,
            'rows_updated' => $rowsUpdated,
            'strategies' => $strategies,
        ];
    }
}