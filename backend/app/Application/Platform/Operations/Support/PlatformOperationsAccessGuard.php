<?php

declare(strict_types=1);

namespace App\Application\Platform\Operations\Support;

use App\Domain\Auth\Exceptions\PermissionDeniedException;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;

final class PlatformOperationsAccessGuard
{
    public function __construct(
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    public function authorize(): void
    {
        if ($this->staffContext->isSuperAdmin()) {
            return;
        }

        if (! $this->staffContext->hasPermission('platform.operations.view')) {
            throw PermissionDeniedException::forPermission('platform.operations.view');
        }
    }
}
