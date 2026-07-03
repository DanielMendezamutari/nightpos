<?php

declare(strict_types=1);

namespace App\Application\Health\Support;

use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;

final class HealthAccessGuard
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function authorizePlatform(): void
    {
        if ($this->staffContext->isSuperAdmin()) {
            return;
        }

        if ($this->staffContext->hasPermission('health.platform.view')
            || $this->staffContext->hasPermission('platform.operations.view')) {
            return;
        }

        throw PermissionDeniedException::forPermission('health.platform.view');
    }

    public function authorizeTenant(): void
    {
        if ($this->staffContext->isSuperAdmin()) {
            return;
        }

        if ($this->staffContext->hasPermission('health.tenant.view')) {
            return;
        }

        throw PermissionDeniedException::forPermission('health.tenant.view');
    }
}
