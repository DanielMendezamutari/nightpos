<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ResolvesBranchSiteId
{
    protected function resolveBranchSiteId(Request $request): ?int
    {
        $user = $request->user();
        if (in_array($user->role, ['admin', 'manager', 'cashier', 'waiter'], true)) {
            if ($user->active_site_id) {
                return (int) $user->active_site_id;
            }

            return $user->site_id ? (int) $user->site_id : null;
        }
        if (in_array($user->role, ['super_admin', 'owner'], true)) {
            $raw = $request->query('site_id');
            if ($raw === null || $raw === '') {
                return null;
            }

            return (int) $raw;
        }

        return null;
    }
}
