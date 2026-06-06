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

        AuditLogModel::query()->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch?->id,
            'user_id' => $this->staffContext->userId(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => $metadata === [] ? null : $metadata,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
