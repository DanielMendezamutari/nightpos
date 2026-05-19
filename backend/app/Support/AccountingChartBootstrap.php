<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Crea el plan mínimo por sucursal para registrar ventas POS en el libro diario.
 *
 * @return array{cash: int, revenue: int}
 */
final class AccountingChartBootstrap
{
    public static function ensureDefaultAccountsForSite(int $siteId): array
    {
        $cashId = self::firstOrCreateAccount(
            $siteId,
            '1.1.01',
            'Caja / cobros POS',
            'asset',
        );
        $revenueId = self::firstOrCreateAccount(
            $siteId,
            '4.1.01',
            'Ventas POS',
            'revenue',
        );

        return ['cash' => $cashId, 'revenue' => $revenueId];
    }

    private static function firstOrCreateAccount(int $siteId, string $code, string $name, string $type): int
    {
        $existing = DB::table('accounts')
            ->where('site_id', $siteId)
            ->where('code', $code)
            ->value('id');
        if ($existing !== null) {
            return (int) $existing;
        }

        return (int) DB::table('accounts')->insertGetId([
            'site_id' => $siteId,
            'code' => $code,
            'name' => $name,
            'type' => $type,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
