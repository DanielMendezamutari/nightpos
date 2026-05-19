<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\DB;

trait ValidatesWaiterBranchAccess
{
    /**
     * Garzón de la casa (users.site_id) o con fila en user_site_accesses para la sucursal.
     */
    protected function waiterWorksAtBranch(int $waiterUserId, int $siteId): bool
    {
        $row = DB::table('users')
            ->where('id', $waiterUserId)
            ->where('role', 'waiter')
            ->first(['site_id']);

        if (! $row) {
            return false;
        }

        if ((int) $row->site_id === (int) $siteId) {
            return true;
        }

        return DB::table('user_site_accesses')
            ->where('user_id', $waiterUserId)
            ->where('site_id', $siteId)
            ->exists();
    }
}
