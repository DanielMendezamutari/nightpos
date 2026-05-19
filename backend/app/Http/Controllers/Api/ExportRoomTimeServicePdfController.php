<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportRoomTimeServicePdfController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $serviceId, Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json(['message' => 'No se pudo determinar la sucursal.'], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $service = DB::table('room_time_services as r')
            ->leftJoin('companions', 'companions.id', '=', 'r.companion_id')
            ->leftJoin('users as uw', 'uw.id', '=', 'r.waiter_user_id')
            ->leftJoin('users as uc', 'uc.id', '=', 'r.cashier_user_id')
            ->where('r.id', $serviceId)
            ->where('r.site_id', $siteId)
            ->first([
                'r.id',
                'r.site_id',
                'r.shift_turn_id',
                'r.customer_name',
                'r.room_label',
                'r.rate_per_hour',
                'r.planned_minutes',
                'r.alert_before_minutes',
                'r.grace_minutes',
                'r.started_at',
                'r.closed_at',
                'r.manual_minutes',
                'r.billed_minutes',
                'r.subtotal',
                'r.status',
                'r.notes',
                'companions.stage_name as companion_name',
                'uw.name as waiter_name',
                'uc.name as cashier_name',
            ]);

        if (! $service) {
            return response()->json(['message' => 'Servicio de pieza no encontrado.'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        $extensions = DB::table('room_time_service_extensions as e')
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->where('e.room_time_service_id', $serviceId)
            ->orderBy('e.id')
            ->get([
                'e.added_minutes',
                'e.notes as extension_notes',
                'e.created_at',
                'u.name as user_name',
            ]);

        $payments = DB::table('room_time_service_payments')
            ->where('room_time_service_id', $serviceId)
            ->orderBy('paid_at')
            ->get(['method', 'amount', 'paid_at']);

        $paidTotal = (int) $payments->sum(fn ($p) => (int) $p->amount);

        $site = DB::table('sites')->where('id', $siteId)->first(['code', 'name']);

        $pdf = Pdf::loadView('pdf.room-time-service', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'service' => $service,
            'extensions' => $extensions,
            'payments' => $payments,
            'paidTotal' => $paidTotal,
            'balanceDue' => max(0, (int) $service->subtotal - $paidTotal),
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
            'fmtDt' => static function (?string $iso): string {
                if ($iso === null || $iso === '') {
                    return '—';
                }

                return Carbon::parse($iso)->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i');
            },
        ]);

        return $pdf->download('pieza-servicio-'.$serviceId.'.pdf');
    }
}
