<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\AuthenticatedUser;
use App\Shared\Contracts\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findById(int $id): ?AuthenticatedUser;

    /**
     * Resolve user by PIN within tenant/branch scope (PIN verified in application layer).
     *
     * @return list<int> Candidate user IDs with pin_hash set
     */
    public function findCandidateIdsByPinScope(?int $tenantId, ?int $branchId, ?string $branchCode): array;

    public function findUserIdByPinFingerprintInScope(
        string $pinFingerprint,
        ?int $tenantId,
        ?int $branchId,
        ?string $branchCode,
    ): ?int;

    public function findByUsername(?int $tenantId, string $username): ?AuthenticatedUser;

    /**
     * Resuelve usuario para login password respetando usuarios plataforma (tenant_id null).
     */
    public function findByUsernameForLogin(?int $tenantId, string $username): ?AuthenticatedUser;

    public function getPinHashById(int $userId): ?string;

    public function isPinFingerprintTaken(string $pinFingerprint, ?int $exceptUserId = null): bool;

    public function getPasswordHashById(int $userId): ?string;

    public function recordLastLogin(int $userId): void;

    /** @return list<AuthenticatedUser> */
    public function listByTenant(int $tenantId): array;

    public function activeGirlExistsByNameInBranch(int $tenantId, int $branchId, string $name): bool;

    public function activeWaiterExistsByNameInBranch(int $tenantId, int $branchId, string $name): bool;

    public function createForTenant(
        int $tenantId,
        ?int $branchId,
        ?int $roleId,
        string $name,
        string $username,
        ?string $email,
        ?string $password,
        ?string $pinPlain,
        string $status,
        ?string $staffRole,
        ?string $waiterCommissionPercent,
        bool $canReceiveGirlCommissions = false,
        array $accessibleBranchIds = [],
        ?string $staffNotes = null,
        ?string $cleaningBaseAmount = null,
        ?string $cleaningRoomAmount = null,
    ): AuthenticatedUser;

    public function findByIdForTenant(int $userId, int $tenantId): ?AuthenticatedUser;

    public function updateForTenant(
        int $userId,
        int $tenantId,
        ?int $branchId,
        ?int $roleId,
        string $name,
        string $username,
        ?string $email,
        string $status,
        ?string $staffRole,
        ?string $waiterCommissionPercent,
        bool $canReceiveGirlCommissions,
        array $accessibleBranchIds,
        ?string $cleaningBaseAmount = null,
        ?string $cleaningRoomAmount = null,
    ): AuthenticatedUser;

    public function resetPinForTenant(int $userId, int $tenantId, string $pinPlain): void;

    public function resetPasswordForTenant(int $userId, int $tenantId, string $passwordPlain): void;

    public function grantBranchAccess(int $userId, int $tenantId, int $branchId): void;

    public function revokeBranchAccess(int $userId, int $tenantId, int $branchId): void;

    /**
     * @param  list<int>  $userIds
     * @return array<int, string> Map user id → display name
     */
    public function findDisplayNamesByIds(array $userIds): array;
}
