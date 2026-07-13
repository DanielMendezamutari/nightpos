<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

final readonly class SettlementSummaryDTO
{
    public function __construct(
        /**
         * @var array{
         *     pending_count: int,
         *     pending_gross_amount: string,
         *     pending_adjustments_amount: string,
         *     pending_net_amount: string,
         *     paid_count: int,
         *     paid_gross_amount: string,
         *     paid_adjustments_amount: string,
         *     paid_net_amount: string
         * }
         */
        public array $waiters,
        /**
         * @var array{
         *     pending_count: int,
         *     pending_gross_amount: string,
         *     pending_adjustments_amount: string,
         *     pending_net_amount: string,
         *     paid_count: int,
         *     paid_gross_amount: string,
         *     paid_adjustments_amount: string,
         *     paid_net_amount: string
         * }
         */
        public array $girls,
        /**
         * @var array{
         *     pending_count: int,
         *     pending_gross_amount: string,
         *     pending_adjustments_amount: string,
         *     pending_net_amount: string,
         *     paid_count: int,
         *     paid_gross_amount: string,
         *     paid_adjustments_amount: string,
         *     paid_net_amount: string
         * }
         */
        public array $cleaning,
        /**
         * @var array{
         *     pending_total_count: int,
         *     pending_total_gross: string,
         *     pending_total_adjustments: string,
         *     pending_total_net: string,
         *     paid_total_count: int,
         *     paid_total_gross: string,
         *     paid_total_adjustments: string,
         *     paid_total_net: string
         * }
         */
        public array $totals,
        /**
         * @var array{
         *     cleaning_deduction: string,
         *     manual_fines: string,
         *     manual_discounts: string,
         *     other_adjustments: string
         * }
         */
        public array $adjustments_breakdown,
        /**
         * @var array{
         *     waiter_manual_pending_count: int,
         *     waiter_manual_pending_amount: string,
         *     waiter_manual_paid_count: int,
         *     waiter_manual_paid_amount: string
         * }
         */
        public array $manual_compensation,
    ) {
    }
}
