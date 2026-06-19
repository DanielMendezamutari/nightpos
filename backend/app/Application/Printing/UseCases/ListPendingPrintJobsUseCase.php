<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Infrastructure\Laravel\Http\Context\RequestPrintDeviceContext;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\UseCaseInterface;

final class ListPendingPrintJobsUseCase implements UseCaseInterface
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

        $limit = max(1, min(20, (int) ($input->limit ?? 10)));

        $jobs = $this->jobs->listPending(
            (int) $device['tenant_id'],
            (int) $device['branch_id'],
            $limit,
        );

        return OperationResult::ok('Trabajos pendientes.', [
            'jobs' => array_map(static fn (array $job) => [
                'id' => $job['id'],
                'type' => $job['type'],
                'source_type' => $job['source_type'],
                'source_id' => $job['source_id'],
                'content_text' => $job['content_text'],
                'attempts' => $job['attempts'],
                'created_at' => $job['created_at'],
            ], $jobs),
        ]);
    }
}
