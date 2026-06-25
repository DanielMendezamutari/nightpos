<?php

declare(strict_types=1);

namespace App\Domain\Printing\Repositories;

use App\Shared\Contracts\RepositoryInterface;

interface PrintJobRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(array $data): array;

    public function findById(int $id, int $tenantId, int $branchId): ?array;

    public function findByIdempotencyKey(int $tenantId, int $branchId, string $key): ?array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listPending(int $tenantId, int $branchId, int $limit = 10): array;

    public function claim(int $jobId, int $tenantId, int $branchId, int $deviceId): bool;

    public function markPrinted(int $jobId, int $tenantId, int $branchId, int $deviceId): bool;

    public function markFailed(int $jobId, int $tenantId, int $branchId, int $deviceId, string $error): bool;

    /**
     * @return list<array<string, mixed>>
     */
    public function listByBranch(int $tenantId, int $branchId, ?string $status, int $limit = 50): array;

    public function findLatestForSource(
        int $tenantId,
        int $branchId,
        string $sourceType,
        int $sourceId,
        string $type,
    ): ?array;

    /**
     * @return array{pending_count: int, failed_count: int, claimed_count: int, last_job: array<string, mixed>|null}
     */
    public function branchQueueSummary(int $tenantId, int $branchId): array;

    /**
     * @return array{last_job_id: int|null, last_job_status: string|null, last_job_at: string|null}
     */
    public function deviceJobSummary(int $tenantId, int $branchId, int $deviceId): array;
}
