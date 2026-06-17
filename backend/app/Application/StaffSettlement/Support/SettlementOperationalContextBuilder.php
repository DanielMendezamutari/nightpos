<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\Support;



use App\Application\StaffSettlement\Services\SettlementShiftScopeResolver;

use App\Domain\StaffSettlement\Repositories\StaffSettlementRepositoryInterface;



final class SettlementOperationalContextBuilder

{

    /**

     * @return array{

     *     context: array{

     *         tenant_id: int,

     *         branch_id: int,

     *         current_shift_id: ?int,

     *         open_shift_id: ?int,

     *         cash_session_id: ?int,

     *         cash_session_official_shift_id: ?int,

     *         cashier_user_id: ?int,

     *         scope: ?string,

     *         shift_rotated: bool,

     *         empty_overview: bool

     *     },

     *     sources_summary: array{

     *         sales: int,

     *         bracelets: int,

     *         rooms: int,

     *         shows: int,

     *         cleaning_tasks: int

     *     }

     * }

     */

    public function build(

        StaffSettlementRepositoryInterface $settlements,

        int $tenantId,

        int $branchId,

        ?int $shiftId,

        ?int $cashSessionId = null,

        ?int $cashierUserId = null,

        ?string $scope = null,

        ?int $openShiftId = null,

        ?int $cashSessionShiftId = null,

        bool $shiftRotated = false,

        bool $emptyOverview = false,

    ): array {

        $openShiftId ??= $settlements->resolveOpenShiftId($tenantId, $branchId);



        $sourcesCashSessionId = $scope === SettlementShiftScopeResolver::SCOPE_MY_CASH_SESSION
            ? $cashSessionId
            : null;

        $settlementSummary = $shiftId !== null && ! $emptyOverview
            ? $settlements->settlementScopeSummary($tenantId, $branchId, $shiftId, $sourcesCashSessionId)
            : ($shiftId !== null
                ? $settlements->settlementScopeSummary($tenantId, $branchId, $shiftId, $sourcesCashSessionId)
                : [
                    'generated_pending_count' => 0,
                    'generated_pending_amount' => '0.00',
                    'unsettled_sources_count' => 0,
                    'already_generated_count' => 0,
                    'already_generated_pending_count' => 0,
                ]);

        return [
            'context' => [
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'current_shift_id' => $shiftId,
                'open_shift_id' => $openShiftId,
                'resolved_settlement_shift_id' => $shiftId,
                'cash_session_id' => $cashSessionId,
                'cash_session_official_shift_id' => $cashSessionShiftId,
                'cashier_user_id' => $cashierUserId,
                'scope' => $scope,
                'shift_rotated' => $shiftRotated,
                'empty_overview' => $emptyOverview,
            ],
            'sources_summary' => $shiftId !== null && ! $emptyOverview
                ? $settlements->countShiftSources($tenantId, $branchId, $shiftId, $sourcesCashSessionId)
                : [
                    'sales' => 0,
                    'bracelets' => 0,
                    'rooms' => 0,
                    'shows' => 0,
                    'cleaning_tasks' => 0,
                ],
            'settlement_summary' => $settlementSummary,
        ];

    }

}


