<?php

declare(strict_types=1);

namespace App\Application\Cash\Support;

use App\Application\Cash\DTOs\CashSummaryDTO;
use App\Application\Cash\DTOs\MovementSummaryDTO;
use App\Application\Cash\DTOs\SalesSummaryDTO;
use App\Application\Cash\DTOs\ScopeSummaryDTO;
use App\Application\Cash\DTOs\SettlementSummaryDTO;

final class FinancialSummarySerializer
{
    /**
     * @return array<string, mixed>
     */
    public function salesSummary(SalesSummaryDTO $summary): array
    {
        return [
            'total_sales_amount' => $summary->total_sales_amount->amount,
            'total_sales' => $summary->total_sales_amount->amount,
            'sales_count' => $summary->sales_count,
            'average_ticket' => $summary->average_ticket->amount,
            'cash_total' => $summary->cash_total->amount,
            'qr_total' => $summary->qr_total->amount,
            'card_total' => $summary->card_total->amount,
            'mixed_total' => $summary->mixed_total->amount,
            'sales_cash' => $summary->cash_total->amount,
            'sales_qr' => $summary->qr_total->amount,
            'sales_card' => $summary->card_total->amount,
            'mixed_sales_count' => $summary->mixed_sales_count,
            'by_source' => $summary->by_source,
            'by_method' => $summary->by_method,
            'sales_by_method' => $summary->sales_by_method,
            'products_sold_count' => $summary->products_sold_count,
            'services_sold_count' => $summary->services_sold_count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cashSummary(CashSummaryDTO $summary): array
    {
        return [
            'opening_cash' => $summary->opening_cash->amount,
            'cash_income_sales' => $summary->cash_income_sales->amount,
            'cash_income_sales_normal' => $summary->cash_income_sales_normal->amount,
            'cash_income_sales_room_services' => $summary->cash_income_sales_room_services->amount,
            'cash_income_sales_shows' => $summary->cash_income_sales_shows->amount,
            'cash_income_sales_other' => $summary->cash_income_sales_other->amount,
            'cash_income_manual' => $summary->cash_income_manual->amount,
            'cash_expense_settlements' => $summary->cash_expense_settlements->amount,
            'cash_expense_operational' => $summary->cash_expense_operational->amount,
            'cash_expense_purchases' => $summary->cash_expense_purchases->amount,
            'cash_expense_other' => $summary->cash_expense_other->amount,
            'cash_income_total' => $summary->cash_income_total->amount,
            'cash_expense_total' => $summary->cash_expense_total->amount,
            'expected_cash' => $summary->expected_cash->amount,
            'counted_cash' => $summary->counted_cash?->amount,
            'cash_difference' => $summary->cash_difference?->amount,
            'is_closed' => $summary->is_closed,
            'has_declared_count' => $summary->has_declared_count,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function movementSummary(MovementSummaryDTO $summary): array
    {
        return [
            'total_movements' => $summary->total_movements,
            'total_income' => $summary->total_income->amount,
            'total_expense' => $summary->total_expense->amount,
            'income_by_family' => $summary->income_by_family,
            'expense_by_family' => $summary->expense_by_family,
            'income_by_category' => $summary->income_by_category,
            'expense_by_category' => $summary->expense_by_category,
            'income_by_payment_method' => $summary->income_by_payment_method,
            'expense_by_payment_method' => $summary->expense_by_payment_method,
            'timeline_by_hour' => $summary->timeline_by_hour,
            'largest_income_category' => $summary->largest_income_category,
            'largest_expense_category' => $summary->largest_expense_category,
            'movement_count_by_category' => $summary->movement_count_by_category,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function settlementSummary(SettlementSummaryDTO $summary): array
    {
        return [
            'waiters' => $summary->waiters,
            'girls' => $summary->girls,
            'cleaning' => $summary->cleaning,
            'totals' => $summary->totals,
            'adjustments_breakdown' => $summary->adjustments_breakdown,
            'manual_compensation' => $summary->manual_compensation,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function scopeSummary(ScopeSummaryDTO $summary): array
    {
        return [
            'tenant_id' => $summary->tenant_id,
            'branch_id' => $summary->branch_id,
            'cash_session_id' => $summary->cash_session_id,
            'cash_session_status' => $summary->cash_session_status,
            'cash_session_opened_at' => $summary->cash_session_opened_at,
            'cash_session_closed_at' => $summary->cash_session_closed_at,
            'session_official_shift_id' => $summary->session_official_shift_id,
            'current_official_shift_id' => $summary->current_official_shift_id,
            'official_shift_ids_included' => $summary->official_shift_ids_included,
            'crosses_multiple_shifts' => $summary->crosses_multiple_shifts,
            'open_shift_ids' => $summary->open_shift_ids,
            'has_open_shift_conflict' => $summary->has_open_shift_conflict,
            'open_cash_sessions_on_historical_shifts' => $summary->open_cash_sessions_on_historical_shifts,
            'scope_type' => $summary->scope_type,
            'scope_label' => $summary->scope_label,
            'warnings' => $summary->warnings,
        ];
    }
}
