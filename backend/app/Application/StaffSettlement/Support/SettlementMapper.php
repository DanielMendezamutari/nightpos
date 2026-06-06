<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Support;

final class SettlementMapper
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function settlement(array $row): array
    {
        return [
            'id' => $row['id'],
            'tenant_id' => $row['tenant_id'],
            'branch_id' => $row['branch_id'],
            'official_shift_id' => $row['official_shift_id'],
            'cash_session_id' => $row['cash_session_id'],
            'staff_user_id' => $row['staff_user_id'],
            'staff_name' => $row['staff_name'] ?? null,
            'staff_role' => $row['staff_role'],
            'settlement_type' => $row['settlement_type'],
            'total_amount' => $row['total_amount'],
            'status' => $row['status'],
            'paid_by_user_id' => $row['paid_by_user_id'],
            'paid_at' => $row['paid_at'],
            'notes' => $row['notes'],
            'sales_count' => $row['sales_count'] ?? null,
            'commission_percent' => $row['commission_percent'] ?? null,
            'consumption_total' => $row['consumption_total'] ?? null,
            'bracelets_total' => $row['bracelets_total'] ?? null,
            'shift_name' => $row['shift_name'] ?? null,
            'shift_business_date' => $row['shift_business_date'] ?? null,
            'paid_by_name' => $row['paid_by_name'] ?? null,
            'pieces_total' => $row['pieces_total'] ?? null,
            'shows_total' => $row['shows_total'] ?? null,
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function item(array $row): array
    {
        return [
            'id' => $row['id'],
            'staff_settlement_id' => $row['staff_settlement_id'],
            'sale_id' => $row['sale_id'],
            'sale_item_id' => $row['sale_item_id'],
            'order_id' => $row['order_id'],
            'source_id' => $row['source_id'] ?? null,
            'source_type' => $row['source_type'],
            'registered_at' => $row['registered_at'] ?? null,
            'description' => $row['description'],
            'base_amount' => $row['base_amount'],
            'percent' => $row['percent'],
            'amount' => $row['amount'],
            'product_name' => $row['product_name'] ?? null,
            'sale_mode' => $row['sale_mode'] ?? null,
            'sale_number' => $row['sale_number'] ?? null,
            'order_number' => $row['order_number'] ?? null,
            'created_at' => $row['created_at'],
        ];
    }
}
