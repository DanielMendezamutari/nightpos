<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

final class UploadPurchaseOrderDocumentController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $purchaseOrderId, Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'Sin sucursal.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = DB::table('purchase_orders')
            ->where('id', $purchaseOrderId)
            ->where('site_id', $siteId)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Compra no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'document' => ['required', 'file', 'max:10240', 'mimes:pdf,jpeg,jpg,png'],
        ]);

        $file = $request->file('document');
        if ($file === null || ! $file->isValid()) {
            return response()->json(['message' => 'Archivo inválido.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $previousPath = $order->document_file_path ?? null;
        if (is_string($previousPath) && $previousPath !== '' && Storage::disk('public')->exists($previousPath)) {
            Storage::disk('public')->delete($previousPath);
        }

        $safeOriginal = $this->sanitizeOriginalFilename((string) $file->getClientOriginalName());
        $storedPath = $file->store("purchase-documents/{$siteId}/{$purchaseOrderId}", 'public');

        DB::table('purchase_orders')->where('id', $purchaseOrderId)->update([
            'document_file_path' => $storedPath,
            'document_original_name' => $safeOriginal !== '' ? $safeOriginal : basename($storedPath),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Comprobante guardado.',
            'data' => [
                'purchase_order_id' => $purchaseOrderId,
                'has_document' => true,
                'document_original_name' => $safeOriginal !== '' ? $safeOriginal : basename($storedPath),
            ],
        ]);
    }

    private function sanitizeOriginalFilename(string $name): string
    {
        $base = basename(str_replace('\\', '/', $name));
        $base = preg_replace('/[^\p{L}\p{N}._ -]/u', '_', $base) ?? '';

        return mb_substr(trim((string) $base), 0, 255);
    }
}
