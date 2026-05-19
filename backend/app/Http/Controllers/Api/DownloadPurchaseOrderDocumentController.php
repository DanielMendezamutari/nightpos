<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DownloadPurchaseOrderDocumentController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $purchaseOrderId, Request $request): StreamedResponse|Response
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'Sin sucursal.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $row = DB::table('purchase_orders')
            ->where('id', $purchaseOrderId)
            ->where('site_id', $siteId)
            ->select(['document_file_path', 'document_original_name'])
            ->first();

        if (! $row || empty($row->document_file_path)) {
            return response()->json(['message' => 'No hay comprobante adjunto para esta compra.'], Response::HTTP_NOT_FOUND);
        }

        $path = (string) $row->document_file_path;
        if (! Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'El archivo ya no está disponible en el servidor.'], Response::HTTP_NOT_FOUND);
        }

        $downloadName = $row->document_original_name ?: basename($path);

        return Storage::disk('public')->download($path, $downloadName);
    }
}
