<?php



declare(strict_types=1);



namespace App\Application\StaffSettlement\Support;



final class StaffFineMapper

{

    /**

     * @return array<string, mixed>

     */

    public static function fine(array $row): array

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

            'amount' => $row['amount'],

            'reason' => $row['reason'],

            'notes' => $row['notes'],

            'status' => $row['status'],

            'created_by_user_id' => $row['created_by_user_id'],

            'created_by_name' => $row['created_by_name'] ?? null,

            'applied_settlement_id' => $row['applied_settlement_id'],

            'applied_at' => $row['applied_at'],

            'applied_by_user_id' => $row['applied_by_user_id'],

            'applied_by_name' => $row['applied_by_name'] ?? null,

            'cancelled_at' => $row['cancelled_at'],

            'cancelled_by_user_id' => $row['cancelled_by_user_id'],

            'cancellation_reason' => $row['cancellation_reason'],

            'created_at' => $row['created_at'],

            'updated_at' => $row['updated_at'],

        ];

    }

}


