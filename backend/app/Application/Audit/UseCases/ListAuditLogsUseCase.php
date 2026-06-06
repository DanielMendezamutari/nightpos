<?php

declare(strict_types=1);

namespace App\Application\Audit\UseCases;

use App\Domain\User\Exceptions\UserDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class ListAuditLogsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw UserDomainException::branchNotInTenant();
        }

        $logs = AuditLogModel::query()
            ->with('user:id,name,username')
            ->where('tenant_id', $tenant->id)
            ->where(function ($query) use ($branch) {
                $query->where('branch_id', $branch->id)
                    ->orWhereNull('branch_id');
            })
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(static fn (AuditLogModel $row) => [
                'id' => (int) $row->id,
                'action' => $row->action,
                'subject_type' => $row->subject_type,
                'subject_id' => $row->subject_id,
                'metadata' => $row->metadata,
                'ip_address' => $row->ip_address,
                'created_at' => $row->created_at?->toIso8601String(),
                'user' => $row->user ? [
                    'id' => (int) $row->user->id,
                    'name' => $row->user->name,
                    'username' => $row->user->username,
                ] : null,
            ])
            ->all();

        return OperationResult::ok('Bitácora de auditoría.', ['logs' => $logs]);
    }
}
