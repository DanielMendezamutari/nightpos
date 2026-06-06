<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

final class SettlementAccessPolicy
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staff,
    ) {
    }

    /**
     * null = ver todas las liquidaciones del turno; int = solo el personal indicado.
     */
    public function scopedStaffUserId(): ?int
    {
        if ($this->staff->isSuperAdmin()) {
            return null;
        }

        if ($this->staff->hasPermission('settlements.generate')
            || $this->staff->hasPermission('settlements.pay')
            || $this->staff->hasPermission('settlements.history')) {
            return null;
        }

        return $this->staff->userId();
    }

    /** @deprecated use scopedStaffUserId() */
    public function onlyOwnStaffUserId(): ?int
    {
        return $this->scopedStaffUserId();
    }
}
