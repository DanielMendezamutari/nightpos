<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportSaasSubscriptionPaymentsPdfController extends Controller
{
    public function __invoke(int $siteId): SymfonyResponse
    {
        $site = DB::table('sites')->where('id', $siteId)->first(['id', 'code', 'name']);

        $rows = DB::table('saas_subscription_payments')
            ->where('site_id', $siteId)
            ->orderByDesc('paid_at')
            ->get();

        $pdf = Pdf::loadView('pdf.saas-subscription-payments', [
            'appName' => config('app.name', 'NightPOS'),
            'siteId' => $siteId,
            'site' => $site,
            'rows' => $rows,
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        return $pdf->download('saas-pagos-sucursal-'.$siteId.'.pdf');
    }
}
