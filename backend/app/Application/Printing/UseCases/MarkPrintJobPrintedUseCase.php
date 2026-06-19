<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Infrastructure\Laravel\Http\Context\RequestPrintDeviceContext;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;
use App\Shared\Domain\Enums\PrintJobStatus;

final class MarkPrintJobPrintedUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly RequestPrintDeviceContext $deviceContext,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $device = $this->deviceContext->device();

        if ($device === null) {
            return OperationResult::fail('Dispositivo no autenticado.');
        }

        $jobId = (int) ($input->jobId ?? 0);
        $job = $this->jobs->findById($jobId, (int) $device['tenant_id'], (int) $device['branch_id']);

        if ($job === null) {
            throw PrintingDomainException::jobNotFound();
        }

        $updated = $this->jobs->markPrinted(
            $jobId,
            (int) $device['tenant_id'],
            (int) $device['branch_id'],
            (int) $device['id'],
        );

        if (! $updated) {
            throw PrintingDomainException::jobNotClaimed();
        }

        $fresh = $this->jobs->findById($jobId, (int) $device['tenant_id'], (int) $device['branch_id']);

        $this->eventEmitter->emit(
            (int) $device['tenant_id'],
            (int) $device['branch_id'],
            'print_job.printed',
            [
                'print_job_id' => $jobId,
                'order_id' => $fresh['source_type'] === 'order' ? $fresh['source_id'] : null,
                'type' => $fresh['type'] ?? null,
                'status' => PrintJobStatus::Printed->value,
            ],
        );

        return OperationResult::ok('Impresión confirmada.', [
            'job' => $fresh,
        ]);
    }
}
