<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Infrastructure\Laravel\Http\Context\RequestPrintDeviceContext;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class ClaimPrintJobUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly RequestPrintDeviceContext $deviceContext,
        private readonly PrintJobRepositoryInterface $jobs,
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

        $claimed = $this->jobs->claim(
            $jobId,
            (int) $device['tenant_id'],
            (int) $device['branch_id'],
            (int) $device['id'],
        );

        if (! $claimed) {
            throw PrintingDomainException::jobAlreadyClaimed();
        }

        $updated = $this->jobs->findById($jobId, (int) $device['tenant_id'], (int) $device['branch_id']);

        return OperationResult::ok('Trabajo reclamado.', [
            'job' => $updated,
        ]);
    }
}
