<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\DB;

trait BuildsStaffAccessibleSiteIds
{
    /**
     * Sucursales donde el personal puede operar: accesos explícitos, casa (users.site_id),
     * y para garzones cualquier sede con mesa asignada (evita 0 mesas por datos desalineados).
     *
     * @return list<int>
     */
    protected function staffAccessibleSiteIds(User $user): array
    {
        if (in_array($user->role, ['owner', 'super_admin'], true)) {
            return [];
        }

        $ids = DB::table('user_site_accesses')
            ->where('user_id', $user->id)
            ->pluck('site_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if ($user->site_id) {
            $home = (int) $user->site_id;
            if (! in_array($home, $ids, true)) {
                $ids[] = $home;
            }
        }

        if ($user->role === 'waiter') {
            $fromTables = DB::table('site_table_assignments')
                ->join('site_tables', 'site_tables.id', '=', 'site_table_assignments.site_table_id')
                ->where('site_table_assignments.waiter_user_id', (int) $user->id)
                ->distinct()
                ->pluck('site_tables.site_id')
                ->map(static fn ($id) => (int) $id)
                ->all();
            $ids = array_merge($ids, $fromTables);
        }

        if ($ids === []) {
            return [];
        }

        $ids = array_values(array_unique($ids));
        sort($ids);

        return $ids;
    }
}
