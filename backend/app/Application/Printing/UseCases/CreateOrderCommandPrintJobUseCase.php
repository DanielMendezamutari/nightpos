<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Order\Services\OrderPresentationService;
use App\Application\Printing\Services\BranchPrintSettingsReader;
use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Order\Entities\Order;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Domain\Settings\Repositories\ServiceAreaRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;

final class CreateOrderCommandPrintJobUseCase
{
    public function __construct(
        private readonly BranchPrintSettingsReader $branchSettings,
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
     * @return array<string, mixed>|null Created job or null if skipped
     */
    public function execute(
        Order $order,
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        ?string $idempotencyKey = null,
        bool $force = false,
        bool $isCorrectionReprint = false,
        ?int $correctionNumber = null,
    ): ?array {
        if (! $force && ! $this->branchSettings->isAutoPrintOrderCommandEnabled($branchId)) {
            return null;
        }

        if (! $this->devices->hasActiveDevice($tenantId, $branchId)) {
            return null;
        }

        $key = $idempotencyKey ?? "order_command:{$order->id}:v1";

        $existing = $this->jobs->findByIdempotencyKey($tenantId, $branchId, $key);
        if ($existing !== null) {
            return $existing;
        }

        $printedAt = now()->toIso8601String();
        $presented = $this->presentation->presentOrder($order, $tenantId);
        $waiterName = $order->waiterUserId !== null
            ? $this->users->findDisplayNamesByIds([$order->waiterUserId])[$order->waiterUserId] ?? null
            : null;

        $serviceAreaName = null;
        if ($order->serviceAreaId !== null) {
            $area = $this->serviceAreas->findById($order->serviceAreaId, $tenantId, $branchId);
            $serviceAreaName = $area['name'] ?? null;
        }

        $payload = [
            'order' => $presented,
            'waiter_name' => $waiterName,
            'service_area_name' => $serviceAreaName,
            'is_reprint' => $isCorrectionReprint,
            'correction_number' => $correctionNumber,
            'printed_at' => $printedAt,
        ];

        $contentText = $this->contentBuilder->buildOrderCommand(
            $presented,
            $waiterName,
            $serviceAreaName,
            isReprint: $isCorrectionReprint,
            correctionNumber: $correctionNumber,
            printedAt: $printedAt,
        );

        $job = $this->jobs->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'device_id' => null,
            'type' => PrintJobType::OrderCommand->value,
            'source_type' => PrintJobSourceType::Order->value,
            'source_id' => $order->id,
            'idempotency_key' => $key,
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
                'type' => PrintJobType::OrderCommand->value,
                'status' => PrintJobStatus::Pending->value,
                'is_reprint' => $isCorrectionReprint,
                'correction_number' => $correctionNumber,
            ],
        );

        return $job;
    }
}
