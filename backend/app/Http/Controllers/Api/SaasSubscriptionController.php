<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SaasSubscriptionController extends Controller
{
    public function quote(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'months_covered' => ['required', 'integer', 'min:1'],
        ]);

        $quote = $this->calculateQuote(
            siteId: (int) $payload['site_id'],
            monthsCovered: (int) $payload['months_covered']
        );

        return response()->json(['data' => $quote]);
    }

    public function index(): JsonResponse
    {
        $dueStatusFilter = request()->query('due_status');

        $rows = DB::table('saas_subscriptions')
            ->join('sites', 'sites.id', '=', 'saas_subscriptions.site_id')
            ->select([
                'sites.id as site_id',
                'sites.code',
                'sites.name',
                'saas_subscriptions.monthly_fee',
                'saas_subscriptions.billing_contact_name',
                'saas_subscriptions.billing_contact_phone',
                'saas_subscriptions.billing_contact_email',
                'saas_subscriptions.status',
                'saas_subscriptions.last_paid_at',
                'saas_subscriptions.next_due_at',
                'saas_subscriptions.suspended_reason',
            ])
            ->orderBy('sites.name')
            ->get()
            ->map(function (object $row): array {
                $dueInDays = $row->next_due_at
                    ? now()->diffInDays(\Illuminate\Support\Carbon::parse($row->next_due_at), false)
                    : null;

                $dueStatus = 'ok';
                if ($row->status === 'suspended' || ($dueInDays !== null && $dueInDays < 0)) {
                    $dueStatus = 'overdue';
                } elseif ($dueInDays !== null && $dueInDays <= 5) {
                    $dueStatus = 'warning';
                }

                return [
                    'site_id' => $row->site_id,
                    'code' => $row->code,
                    'name' => $row->name,
                    'monthly_fee' => $row->monthly_fee,
                    'billing_contact_name' => $row->billing_contact_name,
                    'billing_contact_phone' => $row->billing_contact_phone,
                    'billing_contact_email' => $row->billing_contact_email,
                    'status' => $row->status,
                    'last_paid_at' => $row->last_paid_at,
                    'next_due_at' => $row->next_due_at,
                    'suspended_reason' => $row->suspended_reason,
                    'due_in_days' => $dueInDays,
                    'due_status' => $dueStatus,
                ];
            })
            ->values();

        if (in_array($dueStatusFilter, ['ok', 'warning', 'overdue'], true)) {
            $rows = $rows->filter(fn (array $row): bool => $row['due_status'] === $dueStatusFilter)->values();
        }

        return response()->json(['data' => $rows]);
    }

    public function paymentHistory(int $siteId): JsonResponse
    {
        $rows = DB::table('saas_subscription_payments')
            ->where('site_id', $siteId)
            ->orderByDesc('paid_at')
            ->get();

        return response()->json(['data' => $rows]);
    }

    public function exportPaymentsCsv(int $siteId): Response
    {
        $rows = DB::table('saas_subscription_payments')
            ->where('site_id', $siteId)
            ->orderByDesc('paid_at')
            ->get(['site_id', 'amount', 'months_covered', 'paid_at', 'note']);

        $lines = ['site_id,amount,months_covered,paid_at,note'];
        foreach ($rows as $row) {
            $note = str_replace('"', '""', (string) ($row->note ?? ''));
            $lines[] = implode(',', [
                $row->site_id,
                $row->amount,
                $row->months_covered,
                $row->paid_at,
                "\"{$note}\"",
            ]);
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=saas_payments_site_{$siteId}.csv",
        ]);
    }

    public function registerPayment(int $siteId, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'amount' => ['nullable', 'integer', 'min:1'],
            'months_covered' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $quote = $this->calculateQuote($siteId, (int) $payload['months_covered']);
        $finalAmount = isset($payload['amount']) ? (int) $payload['amount'] : (int) $quote['total_amount'];

        DB::transaction(function () use ($siteId, $payload, $quote, $finalAmount): void {
            DB::table('saas_subscription_payments')->insert([
                'site_id' => $siteId,
                'amount' => $finalAmount,
                'base_amount' => $quote['base_amount'],
                'discount_percent' => $quote['discount_percent'],
                'discount_amount' => $quote['discount_amount'],
                'final_amount' => $finalAmount,
                'months_covered' => $payload['months_covered'],
                'paid_at' => now(),
                'note' => $payload['note'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('saas_subscriptions')
                ->where('site_id', $siteId)
                ->update([
                    'status' => 'active',
                    'suspended_reason' => null,
                    'last_paid_at' => now(),
                    'next_due_at' => now()->addMonths($payload['months_covered']),
                    'updated_at' => now(),
                ]);
        });

        return response()->json([
            'data' => [
                'site_id' => $siteId,
                'amount' => $finalAmount,
                'base_amount' => $quote['base_amount'],
                'discount_percent' => $quote['discount_percent'],
                'discount_amount' => $quote['discount_amount'],
                'status' => 'active',
            ],
        ], 201);
    }

    public function updateStatus(int $siteId, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'status' => ['required', 'in:active,suspended'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::table('saas_subscriptions')
            ->where('site_id', $siteId)
            ->update([
                'status' => $payload['status'],
                'suspended_reason' => $payload['status'] === 'suspended' ? ($payload['reason'] ?? 'Sin motivo') : null,
                'updated_at' => now(),
            ]);

        return response()->json([
            'data' => [
                'site_id' => $siteId,
                'status' => $payload['status'],
            ],
        ]);
    }

    public function updateMonthlyFee(int $siteId, Request $request): JsonResponse
    {
        $payload = $request->validate([
            'monthly_fee' => ['required', 'integer', 'min:1'],
        ]);

        DB::table('saas_subscriptions')
            ->where('site_id', $siteId)
            ->update([
                'monthly_fee' => $payload['monthly_fee'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'data' => [
                'site_id' => $siteId,
                'monthly_fee' => $payload['monthly_fee'],
            ],
        ]);
    }

    private function calculateQuote(int $siteId, int $monthsCovered): array
    {
        $monthlyFee = (int) DB::table('saas_subscriptions')
            ->where('site_id', $siteId)
            ->value('monthly_fee');

        $discountPercent = (int) (DB::table('saas_discount_rules')
            ->where('months_covered', $monthsCovered)
            ->value('discount_percent') ?? 0);

        $baseAmount = $monthlyFee * $monthsCovered;
        $discountAmount = (int) round($baseAmount * ($discountPercent / 100));
        $totalAmount = $baseAmount - $discountAmount;

        return [
            'site_id' => $siteId,
            'months_covered' => $monthsCovered,
            'monthly_fee' => $monthlyFee,
            'base_amount' => $baseAmount,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ];
    }
}
