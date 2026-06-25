<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Order\Services\OrderPresentationService;
use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Order\Entities\Order;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Domain\Settings\Repositories\ServiceAreaRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;

final class CreatePrecheckPrintJobUseCase
{
    public function __construct(
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly OrderPresentationService $presentation,
        private readonly PrintTicketContentBuilder $contentBuilder,
        private readonly UserRepositoryInterface $users,
        private readonly ServiceAreaRepositoryInterface $serviceAreas,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    /**
     * @return array<string, mixed>|null Created job or null if no active print device
     */
    public function execute(
        Order $order,
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        string $idempotencyKey,
    ): ?array {
        if (! $this->devices->hasActiveDevice($tenantId, $branchId)) {
            return null;
        }

        $presented = $this->presentation->presentOrder($order, $tenantId);
        $waiterName = $order->waiterUserId !== null
            ? $this->users->findDisplayNamesByIds([$order->waiterUserId])[$order->waiterUserId] ?? null
            : null;

        $serviceAreaName = null;
        if ($order->serviceAreaId !== null) {
            $area = $this->serviceAreas->findById($order->serviceAreaId, $tenantId, $branchId);
            $serviceAreaName = $area['name'] ?? null;
        }

        $branchName = (string) (BranchModel::query()->where('id', $branchId)->value('name') ?? '');

        $printedAt = now()->toIso8601String();

        $payload = [
            'order' => $presented,
            'waiter_name' => $waiterName,
            'service_area_name' => $serviceAreaName,
            'branch_name' => $branchName !== '' ? $branchName : null,
            'printed_at' => $printedAt,
        ];

        $contentText = $this->contentBuilder->buildPrecheck(
            $presented,
            $branchName !== '' ? $branchName : null,
            $waiterName,
            $serviceAreaName,
            printedAt: $printedAt,
        );

        $job = $this->jobs->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'device_id' => null,
            'type' => PrintJobType::Precheck->value,
            'source_type' => PrintJobSourceType::Order->value,
            'source_id' => $order->id,
            'idempotency_key' => $idempotencyKey,
            'payload' => $payload,
            'content_text' => $contentText,
            'status' => PrintJobStatus::Pending->value,
            'requested_by_user_id' => $requestedByUserId,
        ]);

        $this->eventEmitter->emit(
            $tenantId,
            $branchId,
            'print_job.created',
            [
                'print_job_id' => $job['id'],
                'order_id' => $order->id,
                'type' => PrintJobType::Precheck->value,
                'status' => PrintJobStatus::Pending->value,
            ],
        );

        return $job;
    }
}
