<?php

declare(strict_types=1);

namespace App\Application\Notification\UseCases;

use App\Application\Notification\Services\NotificationAccessPolicy;
use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class GetUnreadNotificationCountUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly NotificationRepositoryInterface $notifications,
        private readonly NotificationAccessPolicy $accessPolicy,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw GirlIncomeDomainException::branchRequired();
        }

        $managerView = $this->accessPolicy->canViewAll();

        return OperationResult::ok('Conteo de notificaciones.', [
            'unread_count' => $this->notifications->countUnreadForScope(
                $tenant->id,
                $branch->id,
                $userId,
                $this->staffContext->staffRole(),
                $managerView,
            ),
        ]);
    }
}
