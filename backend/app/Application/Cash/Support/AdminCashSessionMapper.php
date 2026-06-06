<?php

declare(strict_types=1);

namespace App\Application\Cash\Support;

use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;

final class AdminCashSessionMapper
{
    /**
     * @param  array<string, string|null>  $financials
     * @return array<string, mixed>
     */
    public static function listItem(CashSessionModel $model, array $financials): array
    {
        return [
            'id' => (int) $model->id,
            'tenant' => $model->tenant ? [
                'id' => (int) $model->tenant->id,
                'name' => $model->tenant->name,
                'slug' => $model->tenant->slug,
            ] : null,
            'branch' => $model->branch ? [
                'id' => (int) $model->branch->id,
                'name' => $model->branch->name,
                'code' => $model->branch->code,
            ] : null,
            'cashier' => $model->opener ? [
                'id' => (int) $model->opener->id,
                'name' => $model->opener->name,
                'username' => $model->opener->username,
            ] : null,
            'official_shift' => $model->officialShift ? [
                'id' => (int) $model->officialShift->id,
                'shift_type' => $model->officialShift->shift_type,
                'business_date' => $model->officialShift->business_date,
                'status' => $model->officialShift->status,
            ] : null,
            'status' => $model->status,
            'opening_amount' => (string) $model->opening_amount,
            'expected_cash' => $financials['expected_cash'],
            'counted_cash' => $financials['counted_cash'],
            'cash_difference' => $financials['cash_difference'],
            'total_cash' => $financials['total_cash'],
            'total_qr' => $financials['total_qr'],
            'total_card' => $financials['total_card'],
            'total_sales' => $financials['total_sales'],
            'total_manual_income' => $financials['total_manual_income'],
            'total_manual_expense' => $financials['total_manual_expense'],
            'opened_at' => $model->opened_at?->toIso8601String(),
            'closed_at' => $model->closed_at?->toIso8601String(),
        ];
    }
}
