<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;

final class CreateRoomServicePrintJobUseCase
{
    public function __construct(
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly RoomServiceRepositoryInterface $roomServices,
        private readonly PrintTicketContentBuilder $contentBuilder,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    /**
     * @return array{job: ?array<string, mixed>, warning: ?string}
     */
    public function execute(
        int $roomServiceId,
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        ?string $idempotencyKey = null,
    ): array {
        if (! $this->devices->hasActiveDevice($tenantId, $branchId)) {
            return [
                'job' => null,
                'warning' => 'Pieza registrada, pero no se pudo imprimir el ticket (sin impresora activa).',
            ];
        }

        $roomService = $this->roomServices->findById($roomServiceId, $tenantId);

        if ($roomService === null || (int) ($roomService['branch_id'] ?? 0) !== $branchId) {
            return [
                'job' => null,
                'warning' => 'Pieza registrada, pero no se pudo imprimir el ticket.',
            ];
        }

        $key = $idempotencyKey ?? "room_service:{$roomServiceId}:v1";

        $existing = $this->jobs->findByIdempotencyKey($tenantId, $branchId, $key);
        if ($existing !== null) {
            return ['job' => $existing, 'warning' => null];
        }

        $branchName = (string) (BranchModel::query()->where('id', $branchId)->value('name') ?? '');
        $printedAt = now()->toIso8601String();

        $payload = [
            'room_service' => $roomService,
            'branch_name' => $branchName !== '' ? $branchName : null,
            'printed_at' => $printedAt,
        ];

        $contentText = $this->contentBuilder->buildRoomService(
            $roomService,
            $branchName !== '' ? $branchName : null,
            printedAt: $printedAt,
        );

        $job = $this->jobs->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'device_id' => null,
            'type' => PrintJobType::RoomService->value,
            'source_type' => PrintJobSourceType::RoomService->value,
            'source_id' => $roomServiceId,
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
                'room_service_id' => $roomServiceId,
                'type' => PrintJobType::RoomService->value,
                'status' => PrintJobStatus::Pending->value,
            ],
        );

        return ['job' => $job, 'warning' => null];
    }
}
