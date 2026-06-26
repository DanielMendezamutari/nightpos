<?php

declare(strict_types=1);

namespace App\Shared\Application\Support;

use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;

final class AuditLogRecorder
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
    ) {
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $metadata = [],
    ): void {
        $tenant = $this->tenantContext->tenant();

        if ($tenant === null) {
            return;
        }

        $branch = $this->branchContext->branch();

        $this->write(
            tenantId: $tenant->id,
            branchId: $branch?->id,
            userId: $this->staffContext->userId(),
            action: $action,
            subjectType: $subjectType,
            subjectId: $subjectId,
            metadata: $metadata,
        );
    }

    /**
     * Eventos de plataforma (superadmin, provisioning CLI) sin tenant activo.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function recordPlatform(
        int $userId,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $metadata = [],
    ): void {
        $this->write(
            tenantId: null,
            branchId: null,
            userId: $userId,
            action: $action,
            subjectType: $subjectType,
            subjectId: $subjectId,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function recordForUser(
        ?int $tenantId,
        ?int $branchId,
        int $userId,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $metadata = [],
    ): void {
        $this->write(
            tenantId: $tenantId,
            branchId: $branchId,
            userId: $userId,
            action: $action,
            subjectType: $subjectType,
            subjectId: $subjectId,
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function write(
        ?int $tenantId,
        ?int $branchId,
        ?int $userId,
        string $action,
        ?string $subjectType,
        ?int $subjectId,
        array $metadata,
    ): void {
        AuditLogModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'user_id' => $userId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => $metadata === [] ? null : $metadata,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
