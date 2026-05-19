<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Models\Site;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportBranchProfilePdfController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if ($siteId === null) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si eres super admin, envia el parametro site_id.',
            ], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $site = Site::query()->find($siteId);
        if (! $site) {
            return response()->json(['message' => 'Sucursal no encontrada.'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        $pdf = Pdf::loadView('pdf.branch-profile', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        return $pdf->download('sucursal-'.$site->code.'-datos.pdf');
    }
}
