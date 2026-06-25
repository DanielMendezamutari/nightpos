<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\PrintJobModel;
use App\Shared\Domain\Enums\PrintJobStatus;
use Illuminate\Support\Facades\DB;

final class EloquentPrintJobRepository implements PrintJobRepositoryInterface
{
    public function create(array $data): array
    {
        $model = PrintJobModel::query()->create($data);

        return $this->map($model);
    }

    public function findById(int $id, int $tenantId, int $branchId): ?array
    {
        $model = PrintJobModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->first();

        return $model ? $this->map($model) : null;
    }

    public function findByIdempotencyKey(int $tenantId, int $branchId, string $key): ?array
    {
        $model = PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('idempotency_key', $key)
            ->first();

        return $model ? $this->map($model) : null;
    }

    public function listPending(int $tenantId, int $branchId, int $limit = 10): array
    {
        return PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', PrintJobStatus::Pending->value)
            ->orderByDesc('priority')
            ->orderBy('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (PrintJobModel $model) => $this->map($model))
            ->all();
    }

    public function claim(int $jobId, int $tenantId, int $branchId, int $deviceId): bool
    {
        $updated = PrintJobModel::query()
            ->where('id', $jobId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', PrintJobStatus::Pending->value)
            ->update([
                'status' => PrintJobStatus::Claimed->value,
                'device_id' => $deviceId,
                'claimed_at' => now(),
                'attempts' => DB::raw('attempts + 1'),
            ]);

        return $updated === 1;
    }

    public function markPrinted(int $jobId, int $tenantId, int $branchId, int $deviceId): bool
    {
        $updated = PrintJobModel::query()
            ->where('id', $jobId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('device_id', $deviceId)
            ->where('status', PrintJobStatus::Claimed->value)
            ->update([
                'status' => PrintJobStatus::Printed->value,
                'printed_at' => now(),
                'last_error' => null,
            ]);

        return $updated === 1;
    }

    public function markFailed(int $jobId, int $tenantId, int $branchId, int $deviceId, string $error): bool
    {
        $job = PrintJobModel::query()
            ->where('id', $jobId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('device_id', $deviceId)
            ->where('status', PrintJobStatus::Claimed->value)
            ->first();

        if ($job === null) {
            return false;
        }

        $canRetry = ((int) $job->attempts) < ((int) $job->max_attempts);

        $job->status = $canRetry ? PrintJobStatus::Pending->value : PrintJobStatus::Failed->value;
        $job->last_error = $error;
        $job->failed_at = now();

        if ($canRetry) {
            $job->device_id = null;
            $job->claimed_at = null;
        }

        $job->save();

        return true;
    }

    public function listByBranch(int $tenantId, int $branchId, ?string $status, int $limit = 50): array
    {
        $query = PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->orderByDesc('created_at')
            ->limit($limit);

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->get()
            ->map(fn (PrintJobModel $model) => $this->map($model))
            ->all();
    }

    public function findLatestForSource(
        int $tenantId,
        int $branchId,
        string $sourceType,
        int $sourceId,
        string $type,
    ): ?array {
        $model = PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->first();

        return $model ? $this->map($model) : null;
    }

    public function branchQueueSummary(int $tenantId, int $branchId): array
    {
        $base = PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId);

        $lastJob = (clone $base)->orderByDesc('id')->first();

        return [
            'pending_count' => (int) (clone $base)->where('status', PrintJobStatus::Pending->value)->count(),
            'failed_count' => (int) (clone $base)->where('status', PrintJobStatus::Failed->value)->count(),
            'claimed_count' => (int) (clone $base)->where('status', PrintJobStatus::Claimed->value)->count(),
            'last_job' => $lastJob !== null ? $this->map($lastJob) : null,
        ];
    }

    public function deviceJobSummary(int $tenantId, int $branchId, int $deviceId): array
    {
        $lastJob = PrintJobModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('device_id', $deviceId)
            ->orderByDesc('id')
            ->first();

        if ($lastJob === null) {
            return [
                'last_job_id' => null,
                'last_job_status' => null,
                'last_job_at' => null,
            ];
        }

        return [
            'last_job_id' => (int) $lastJob->id,
            'last_job_status' => $lastJob->status,
            'last_job_at' => $lastJob->printed_at?->toIso8601String()
                ?? $lastJob->failed_at?->toIso8601String()
                ?? $lastJob->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function map(PrintJobModel $model): array
    {
        return [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => (int) $model->branch_id,
            'device_id' => $model->device_id !== null ? (int) $model->device_id : null,
            'type' => $model->type,
            'source_type' => $model->source_type,
            'source_id' => (int) $model->source_id,
            'idempotency_key' => $model->idempotency_key,
            'payload' => $model->payload ?? [],
            'content_text' => $model->content_text,
            'status' => $model->status,
            'priority' => (int) $model->priority,
            'attempts' => (int) $model->attempts,
            'max_attempts' => (int) $model->max_attempts,
            'last_error' => $model->last_error,
            'requested_by_user_id' => $model->requested_by_user_id !== null ? (int) $model->requested_by_user_id : null,
            'claimed_at' => $model->claimed_at?->toIso8601String(),
            'printed_at' => $model->printed_at?->toIso8601String(),
            'failed_at' => $model->failed_at?->toIso8601String(),
            'created_at' => $model->created_at?->toIso8601String(),
            'updated_at' => $model->updated_at?->toIso8601String(),
        ];
    }
}
