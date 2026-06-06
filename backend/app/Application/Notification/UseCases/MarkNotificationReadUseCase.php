<?php

declare(strict_types=1);

namespace App\Application\Notification\UseCases;

use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class MarkNotificationReadUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly NotificationRepositoryInterface $notifications,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $notificationId = $input instanceof \stdClass && isset($input->notificationId)
            ? (int) $input->notificationId
            : 0;

        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw GirlIncomeDomainException::branchRequired();
        }

        if (! $this->notifications->markRead($notificationId, $tenant->id, $branch->id)) {
            throw GirlIncomeDomainException::notFound();
        }

        return OperationResult::ok('Notificación marcada como leída.');
    }
}
