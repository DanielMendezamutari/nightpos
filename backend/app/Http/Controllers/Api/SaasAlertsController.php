<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class SaasAlertsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $rows = DB::table('saas_subscriptions')
            ->join('sites', 'sites.id', '=', 'saas_subscriptions.site_id')
            ->select([
                'sites.id as site_id',
                'sites.name',
                'saas_subscriptions.status',
                'saas_subscriptions.next_due_at',
            ])
            ->get();

        $critical = [];
        $warning = [];

        foreach ($rows as $row) {
            $dueInDays = $row->next_due_at
                ? now()->diffInDays(Carbon::parse($row->next_due_at), false)
                : null;

            if ($row->status === 'suspended' || ($dueInDays !== null && $dueInDays < 0)) {
                $critical[] = [
                    'site_id' => $row->site_id,
                    'name' => $row->name,
                    'due_in_days' => $dueInDays,
                ];
                continue;
            }

            if ($dueInDays !== null && $dueInDays <= 5) {
                $warning[] = [
                    'site_id' => $row->site_id,
                    'name' => $row->name,
                    'due_in_days' => $dueInDays,
                ];
            }
        }

        return response()->json([
            'data' => [
                'critical_count' => count($critical),
                'warning_count' => count($warning),
                'critical' => $critical,
                'warning' => $warning,
            ],
        ]);
    }
}
