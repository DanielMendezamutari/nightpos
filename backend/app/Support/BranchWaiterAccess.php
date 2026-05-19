<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;

final class BranchWaiterAccess
{
    public static function waiterBelongsToSite(int $userId, int $siteId): bool
    {
        $u = DB::table('users')->where('id', $userId)->where('role', 'waiter')->first(['id', 'site_id']);
        if (! $u) {
            return false;
        }
        if ((int) $u->site_id === $siteId) {
            return true;
        }

        return DB::table('user_site_accesses')
            ->where('user_id', $userId)
            ->where('site_id', $siteId)
            ->exists();
    }
}
