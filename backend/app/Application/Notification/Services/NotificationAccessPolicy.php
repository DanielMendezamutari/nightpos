<?php

declare(strict_types=1);

namespace App\Application\Notification\Services;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

final class NotificationAccessPolicy
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staff,
    ) {
    }

    public function canViewAll(): bool
    {
        if ($this->staff->isSuperAdmin()) {
            return true;
        }

        return $this->staff->hasPermission('notifications.read');
    }
}
