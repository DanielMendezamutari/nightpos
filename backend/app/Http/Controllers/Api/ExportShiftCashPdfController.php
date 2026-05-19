<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\EnsuresShiftBelongsToResolvedSite;
use App\Http\Controllers\Controller;
use App\Support\ShiftCashierReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportShiftCashPdfController extends Controller
{
    use EnsuresShiftBelongsToResolvedSite;

    public function __invoke(int $shiftTurnId, Request $request): SymfonyResponse
    {
        if ($response = $this->ensureShiftBelongsToResolvedSite($request, $shiftTurnId)) {
            return $response;
        }

        try {
            $report = ShiftCashierReport::build($shiftTurnId);
        } catch (\InvalidArgumentException) {
            return response()->json(['message' => 'Turno no encontrado.'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        $meta = DB::table('shift_turns as st')
            ->join('sites as s', 's.id', '=', 'st.site_id')
            ->join('users as uc', 'uc.id', '=', 'st.cashier_user_id')
            ->where('st.id', $shiftTurnId)
            ->first([
                's.code as site_code',
                's.name as site_name',
                'uc.name as cashier_name',
                'st.period',
                'st.opened_at',
                'st.closed_at',
                'st.status',
                'st.closing_cash',
            ]);

        $movements = DB::table('cash_drawer_movements as m')
            ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
            ->where('m.shift_turn_id', $shiftTurnId)
            ->orderByDesc('m.id')
            ->get([
                'm.direction',
                'm.amount',
                'm.notes',
                'm.created_at',
                'u.name as user_name',
            ]);

        $pdf = Pdf::loadView('pdf.shift-cash', [
            'appName' => config('app.name', 'NightPOS'),
            'shiftTurnId' => $shiftTurnId,
            'meta' => $meta,
            'report' => $report,
            'movements' => $movements,
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        return $pdf->download('turno-caja-'.$shiftTurnId.'.pdf');
    }
}
