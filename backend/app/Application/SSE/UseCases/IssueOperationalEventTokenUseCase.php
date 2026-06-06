<?php

declare(strict_types=1);

namespace App\Application\SSE\UseCases;

use App\Domain\SSE\Repositories\SseTokenRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

/**
 * Issues a short-lived SSE token so the frontend can open EventSource
 * without exposing the JWT in the query string.
 *
 * Token TTL: 60 seconds (enough to establish the connection).
 */
final class IssueOperationalEventTokenUseCase implements UseCaseInterface
{
    private const TOKEN_TTL = 60;

    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly SseTokenRepositoryInterface $sseTokens
    ) {}

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            return OperationResult::error('Contexto operacional requerido.', 422);
        }

        $roleScope = $this->resolveRoleScope($userId);

        $token = $this->sseTokens->create(
            $tenant->id,
            $branch->id,
            $userId,
            $roleScope,
            self::TOKEN_TTL
        );

        return OperationResult::ok('Token SSE generado.', [
            'token'      => $token,
            'expires_in' => self::TOKEN_TTL,
        ]);
    }

    /**
     * Maps the user's primary operational role to a role_scope string
     * used for SSE event filtering.
     *
     * - tenant_owner / superadmin → null (receives all events)
     * - cashier  → 'cashier'
     * - waiter   → 'waiter'
     * - girl     → 'girl'
     * - cleaning → 'cleaning'
     *
     * Each user has exactly one role (role_id → roles table with a slug).
     */
    private function resolveRoleScope(int $userId): ?string
    {
        $roleSlug = UserModel::query()
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->where('users.id', $userId)
            ->value('roles.slug');

        $operationalRoles = ['cleaning', 'girl', 'waiter', 'cashier'];

        return in_array($roleSlug, $operationalRoles, true) ? $roleSlug : null;
    }
}
