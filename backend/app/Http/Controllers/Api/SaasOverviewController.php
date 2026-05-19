<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class SaasOverviewController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $totalBranches = (int) DB::table('sites')->count();
        $activeBranches = (int) DB::table('saas_subscriptions')->where('status', 'active')->count();
        $suspendedBranches = (int) DB::table('saas_subscriptions')->where('status', 'suspended')->count();
        $monthlyRevenue = (int) DB::table('saas_subscription_payments')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        return response()->json([
            'data' => [
                'total_branches' => $totalBranches,
                'active_branches' => $activeBranches,
                'suspended_branches' => $suspendedBranches,
                'monthly_revenue' => $monthlyRevenue,
            ],
        ]);
    }
}
