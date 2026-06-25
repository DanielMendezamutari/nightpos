<?php

declare(strict_types=1);

namespace App\Application\Cash\Support;

use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use Carbon\CarbonInterface;

/**
 * Resuelve opened_at/closed_at reales de cash_sessions.
 *
 * MySQL TIMESTAMP puede agregar ON UPDATE a opened_at y sobrescribirlo al cerrar;
 * usamos created_at como respaldo cuando opened_at quedó igual a closed_at.
 */
final class CashSessionTimestampsResolver
{
    public static function openedAtIso(CashSessionModel $model): ?string
    {
        $resolved = self::resolveOpenedAt($model);

        return $resolved?->toIso8601String();
    }

    public static function closedAtIso(CashSessionModel $model): ?string
    {
        return $model->closed_at?->toIso8601String();
    }

    public static function resolveOpenedAt(CashSessionModel $model): ?CarbonInterface
    {
        $openedAt = $model->opened_at;
        $closedAt = $model->closed_at;
        $createdAt = $model->created_at;

        if ($openedAt !== null
            && $closedAt !== null
            && $createdAt !== null
            && $openedAt->equalTo($closedAt)
            && $createdAt->lessThan($closedAt)) {
            return $createdAt;
        }

        return $openedAt;
    }
}
