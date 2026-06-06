<?php

declare(strict_types=1);

namespace App\Application\GirlIncome\Services;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class GirlStaffValidator
{
    public function assertGirl(int $tenantId, int $girlUserId): void
    {
        if ($girlUserId <= 0) {
            throw GirlIncomeDomainException::girlRequired();
        }

        $user = UserModel::query()
            ->where('id', $girlUserId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if ($user === null) {
            throw GirlIncomeDomainException::girlNotFound();
        }

        $profile = StaffProfileModel::query()
            ->where('user_id', $girlUserId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if ($profile === null || $profile->staff_role !== 'GIRL') {
            throw GirlIncomeDomainException::invalidGirl();
        }
    }

    public function assertWaiter(int $tenantId, int $waiterUserId): void
    {
        $user = UserModel::query()
            ->where('id', $waiterUserId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if ($user === null) {
            throw GirlIncomeDomainException::invalidWaiter();
        }

        $profile = StaffProfileModel::query()
            ->where('user_id', $waiterUserId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if ($profile === null || $profile->staff_role !== 'WAITER') {
            throw GirlIncomeDomainException::invalidWaiter();
        }
    }

    public function assertWaiterOptional(int $tenantId, ?int $waiterUserId): void
    {
        if ($waiterUserId === null) {
            return;
        }

        $this->assertWaiter($tenantId, $waiterUserId);
    }
}
