<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\UseCases;



use App\Application\StaffSettlement\Services\SettlementAccessPolicy;

use App\Application\StaffSettlement\Services\SettlementShiftScopeResolver;

use App\Application\StaffSettlement\Support\SettlementOperationalContextBuilder;

use App\Domain\Auth\Exceptions\PermissionDeniedException;

use App\Domain\StaffSettlement\Exceptions\StaffSettlementDomainException;

use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;

use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;

use App\Shared\Application\DTOs\OperationResult;

use App\Shared\Contracts\AuthenticatedStaffContextInterface;

use App\Shared\Contracts\BranchContextInterface;

use App\Shared\Contracts\TenantContextInterface;

use App\Shared\Contracts\UseCaseInterface;

use Illuminate\Support\Facades\Auth;



final class GetCurrentShiftSettlementsUseCase implements UseCaseInterface

{

    public function __construct(

        private readonly TenantContextInterface $tenantContext,

        private readonly BranchContextInterface $branchContext,

        private readonly AuthenticatedStaffContextInterface $staffContext,

        private readonly SettlementShiftScopeResolver $scopeResolver,

        private readonly StaffSettlementRepositoryInterface $settlements,

        private readonly SettlementAccessPolicy $accessPolicy,

        private readonly SettlementOperationalContextBuilder $contextBuilder,

    ) {}



    public function execute(?object $input = null): OperationResult

    {

        if (! $this->staffContext->hasPermission('settlements.access')) {

            throw PermissionDeniedException::forPermission('settlements.access');

        }



        $tenant = $this->tenantContext->tenant();

        $branch = $this->branchContext->branch();

        $userId = $this->staffContext->userId();



        if ($tenant === null || $branch === null) {

            throw StaffSettlementDomainException::shiftRequired();

        }



        $requestedScope = is_object($input) && isset($input->scope) ? (string) $input->scope : null;

        $scopeInfo = $this->scopeResolver->resolve($tenant->id, $branch->id, $userId, $requestedScope);

        $shiftId = $scopeInfo['shift_id'];

        $openShiftId = $this->settlements->resolveOpenShiftId($tenant->id, $branch->id);



        $operational = $this->contextBuilder->build(

            $this->settlements,

            $tenant->id,

            $branch->id,

            $shiftId,

            $scopeInfo['cash_session_id'],

            $userId,

            $scopeInfo['scope'],

            $openShiftId,

            $scopeInfo['cash_session_shift_id'],

            $scopeInfo['shift_rotated'],

            $scopeInfo['empty_overview'],

        );



        if ($shiftId === null || $scopeInfo['empty_overview']) {
            $pendingCount = $operational['settlement_summary']['generated_pending_count'] ?? 0;
            $message = $pendingCount > 0
                ? 'Hay liquidaciones pendientes de pago en su caja.'
                : 'Sin liquidaciones para este turno/caja.';

            return OperationResult::ok($message, array_merge([
                'shift' => $this->resolveShiftMeta($tenant->id, $branch->id, $shiftId ?? $openShiftId),
                'summary' => $this->emptySummary(),
                'waiters' => [],
                'girls' => [],
                'cleaning' => [],
                'settlements' => [],
            ], $operational));
        }



        $overview = $this->settlements->getCurrentShiftOverview(
            $tenant->id,
            $branch->id,
            $shiftId,
            $this->resolveStaffScopeUserId($scopeInfo['scope']),
            $scopeInfo['scope'] === SettlementShiftScopeResolver::SCOPE_MY_CASH_SESSION
                ? $scopeInfo['cash_session_id']
                : null,
        );

        $message = ($operational['settlement_summary']['generated_pending_count'] ?? 0) > 0
            ? 'Liquidaciones del turno actual (hay pagos pendientes).'
            : 'Liquidaciones del turno actual.';

        return OperationResult::ok($message, array_merge($overview, $operational));

    }



    private function resolveStaffScopeUserId(string $scope): ?int

    {

        if ($scope === SettlementShiftScopeResolver::SCOPE_MY_CASH_SESSION) {

            return null;

        }



        $scoped = $this->accessPolicy->scopedStaffUserId();



        if ($scoped !== null) {

            return $scoped;

        }



        if ($this->staffContext->isSuperAdmin()

            || $this->staffContext->hasPermission('settlements.generate')

            || $this->staffContext->hasPermission('settlements.pay')

            || $this->staffContext->hasPermission('settlements.history')) {

            return null;

        }



        $userId = $this->staffContext->userId() ?? Auth::id();



        return $userId !== null ? (int) $userId : null;

    }



    /**

     * @return array<string, mixed>|null

     */

    private function resolveShiftMeta(int $tenantId, int $branchId, ?int $shiftId): ?array

    {

        if ($shiftId === null) {

            return null;

        }



        $shift = OfficialShiftModel::query()

            ->where('id', $shiftId)

            ->where('tenant_id', $tenantId)

            ->where('branch_id', $branchId)

            ->first();



        if ($shift === null) {

            return null;

        }



        return [

            'id' => $shift->id,

            'name' => $shift->name,

            'shift_type' => $shift->shift_type,

            'shift_type_label' => $shift->shift_type === 'DAY' ? 'Día' : 'Noche',

            'business_date' => $shift->business_date?->format('Y-m-d'),

            'status' => $shift->status,

        ];

    }



    /**

     * @return array<string, string>

     */

    private function emptySummary(): array

    {

        return [

            'total_waiters' => '0.00',

            'total_girls' => '0.00',

            'total_cleaning' => '0.00',

            'total_consumption' => '0.00',

            'total_bracelets' => '0.00',

            'total_pieces' => '0.00',

            'total_shows' => '0.00',

            'total_pending' => '0.00',

            'total_paid' => '0.00',

        ];

    }

}


