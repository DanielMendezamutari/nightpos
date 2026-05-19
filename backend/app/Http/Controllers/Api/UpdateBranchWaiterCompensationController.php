<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\BranchWaiterAccess;
use App\Support\WaiterCommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class UpdateBranchWaiterCompensationController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $userId, Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if ($siteId === null) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si sos administrador global, enviá ?site_id= en la URL.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! BranchWaiterAccess::waiterBelongsToSite($userId, $siteId)) {
            return response()->json(['message' => 'Mesero no encontrado en esta sucursal.'], Response::HTTP_NOT_FOUND);
        }

        $payload = $request->validate([
            'waiter_compensation_type' => ['required', Rule::in(['per_payment', 'payroll_monthly', 'payroll_weekly'])],
            'waiter_commission_rate_pct' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $target = User::query()->find($userId);
        if (! $target || $target->role !== 'waiter') {
            return response()->json(['message' => 'Usuario inválido.'], Response::HTTP_NOT_FOUND);
        }

        $target->waiter_compensation_type = $payload['waiter_compensation_type'];
        $comp = $payload['waiter_compensation_type'];
        if ($comp === 'per_payment') {
            if (array_key_exists('waiter_commission_rate_pct', $payload)) {
                $raw = $payload['waiter_commission_rate_pct'];
                $target->waiter_commission_rate_pct = ($raw === null || $raw === '')
                    ? null
                    : round((float) $raw, 2);
            }
        } else {
            $target->waiter_commission_rate_pct = null;
        }

        $target->save();

        WaiterCommissionService::recalculateStoredCommissionsForWaiter($target->id);

        return response()->json([
            'data' => [
                'id' => $target->id,
                'name' => $target->name,
                'waiter_compensation_type' => $target->waiter_compensation_type,
                'waiter_commission_rate_pct' => $target->waiter_commission_rate_pct !== null
                    ? round((float) $target->waiter_commission_rate_pct, 2)
                    : null,
            ],
        ]);
    }
}
