<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\Services;



use App\Domain\StaffSettlement\Exceptions\StaffFineDomainException;

use App\Infrastructure\Persistence\Eloquent\Models\StaffProfileModel;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;



final class SettlementStaffValidator

{

    /**

     * @return array{staff_role: string}

     */

    public function assertStaffMember(int $tenantId, int $branchId, int $staffUserId, ?string $expectedRole = null): array

    {

        if ($staffUserId <= 0) {

            throw StaffFineDomainException::staffNotFound();

        }



        $user = UserModel::query()

            ->where('id', $staffUserId)

            ->where('tenant_id', $tenantId)

            ->where('status', 'active')

            ->first();



        if ($user === null) {

            throw StaffFineDomainException::staffNotFound();

        }



        $hasBranchAccess = (int) $user->branch_id === $branchId

            || $user->accessibleBranches()->where('branches.id', $branchId)->exists();



        if (! $hasBranchAccess) {

            throw StaffFineDomainException::staffBranchMismatch();

        }



        $profile = StaffProfileModel::query()

            ->where('user_id', $staffUserId)

            ->where('tenant_id', $tenantId)

            ->where('status', 'active')

            ->first();



        if ($profile === null) {

            throw StaffFineDomainException::staffNotFound();

        }



        $staffRole = (string) $profile->staff_role;



        if ($expectedRole !== null && strtoupper($expectedRole) !== $staffRole) {

            throw StaffFineDomainException::invalidStaffRole();

        }



        return ['staff_role' => $staffRole];

    }

}


