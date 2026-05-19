<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait ValidatesTransferSiteAccess
{
    protected function userCanAccessSite(Request $request, int $siteId): bool
    {
        $user = $request->user();
        if (in_array($user->role, ['owner', 'super_admin'], true)) {
            return DB::table('sites')->where('id', $siteId)->exists();
        }

        $hasAccess = DB::table('user_site_accesses')
            ->where('user_id', $user->id)
            ->where('site_id', $siteId)
            ->exists();

        if ($hasAccess) {
            return true;
        }

        return $user->site_id !== null && (int) $user->site_id === $siteId;
    }

    protected function userOperatesFromResolvedBranch(Request $request, int $fromSiteId): bool
    {
        $user = $request->user();
        if (in_array($user->role, ['owner', 'super_admin'], true)) {
            return true;
        }

        $active = $user->active_site_id ?? $user->site_id;

        return $active !== null && (int) $active === $fromSiteId;
    }

}
