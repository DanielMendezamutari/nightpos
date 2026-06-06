<?php

declare(strict_types=1);

namespace App\Application\Order\Support;

/**
 * Scopes operativos para GET /orders.
 *
 * IN_PREPARATION y READY se mantienen en pending_charge/cashier_chargeable
 * para futura integración de barra; hoy casi no se usan en runtime.
 */
final class OrderListScopeResolver
{
    public const OPERATIONAL_ACTIVE = ['OPEN', 'SENT_TO_BAR'];

    public const PENDING_CHARGE = ['SENT_TO_BAR', 'IN_PREPARATION', 'READY'];

    /** Solo barra/cocina futura — KPI garzón sin duplicar SENT_TO_BAR. */
    public const PENDING_CHARGE_BAR_ONLY = ['IN_PREPARATION', 'READY'];

    public const CASHIER_CHARGEABLE = ['OPEN', 'SENT_TO_BAR', 'IN_PREPARATION', 'READY'];

    public const BILLED_RECENT_LIMIT = 50;

    /**
     * @return array{status: ?string, statuses: ?list<string>, limit: ?int}
     */
    public function resolve(?string $scope): array
    {
        if ($scope === null || $scope === '') {
            return ['status' => null, 'statuses' => null, 'limit' => null];
        }

        return match ($scope) {
            'operational_active' => ['status' => null, 'statuses' => self::OPERATIONAL_ACTIVE, 'limit' => null],
            'open' => ['status' => 'OPEN', 'statuses' => null, 'limit' => null],
            'sent_to_bar' => ['status' => 'SENT_TO_BAR', 'statuses' => null, 'limit' => null],
            'pending_charge' => ['status' => null, 'statuses' => self::PENDING_CHARGE, 'limit' => null],
            'billed_recent' => ['status' => 'BILLED', 'statuses' => null, 'limit' => self::BILLED_RECENT_LIMIT],
            'cancelled' => ['status' => 'CANCELLED', 'statuses' => null, 'limit' => null],
            'cashier_chargeable' => ['status' => null, 'statuses' => self::CASHIER_CHARGEABLE, 'limit' => null],
            default => ['status' => null, 'statuses' => null, 'limit' => null],
        };
    }
}
