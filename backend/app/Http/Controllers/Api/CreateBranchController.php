<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CreateBranchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:sites,code'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'monthly_fee' => ['required', 'integer', 'min:1'],
            'billing_contact_name' => ['required', 'string', 'max:255'],
            'billing_contact_phone' => ['nullable', 'string', 'max:50'],
            'billing_contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $id = DB::transaction(function () use ($payload): int {
            $siteId = DB::table('sites')->insertGetId([
                'code' => $payload['code'],
                'name' => $payload['name'],
                'is_active' => $payload['is_active'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('saas_subscriptions')->insert([
                'site_id' => $siteId,
                'monthly_fee' => $payload['monthly_fee'],
                'billing_contact_name' => $payload['billing_contact_name'],
                'billing_contact_phone' => $payload['billing_contact_phone'] ?? null,
                'billing_contact_email' => $payload['billing_contact_email'] ?? null,
                'status' => 'active',
                'suspended_reason' => null,
                'last_paid_at' => null,
                'next_due_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $siteId;
        });

        return response()->json([
            'data' => [
                'id' => $id,
                'code' => $payload['code'],
                'name' => $payload['name'],
                'is_active' => $payload['is_active'] ?? true,
                'monthly_fee' => $payload['monthly_fee'],
                'billing_contact_name' => $payload['billing_contact_name'],
            ],
        ], 201);
    }
}
