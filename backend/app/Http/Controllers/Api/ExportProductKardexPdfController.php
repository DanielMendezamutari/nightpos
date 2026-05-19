<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportProductKardexPdfController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $productId, Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'No hay sucursal configurada.'], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $product = DB::table('products')->where('id', $productId)->first(['id', 'name', 'sku']);
        if (! $product) {
            return response()->json(['message' => 'Producto no encontrado.'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        $movements = DB::table('inventory_movements')
            ->where('product_id', $productId)
            ->where('site_id', $siteId)
            ->orderBy('moved_at')
            ->orderBy('id')
            ->get();

        $running = 0;
        $rows = [];
        foreach ($movements as $m) {
            $quantity = (int) $m->quantity;
            $delta = match ($m->movement_type) {
                'sale_out', 'transfer_out' => -abs($quantity),
                'transfer_in' => abs($quantity),
                default => $quantity,
            };
            $running += $delta;
            $rows[] = [
                'movement_type' => (string) $m->movement_type,
                'quantity' => $quantity,
                'delta' => $delta,
                'running_stock' => $running,
                'unit_cost' => $m->unit_cost !== null ? (int) $m->unit_cost : null,
                'reference_type' => $m->reference_type,
                'reference_id' => $m->reference_id !== null ? (int) $m->reference_id : null,
                'notes' => $m->notes,
                'moved_at' => $m->moved_at,
            ];
        }

        $qty = (int) DB::table('site_product_stocks')
            ->where('site_id', $siteId)
            ->where('product_id', $productId)
            ->value('quantity');

        $site = DB::table('sites')->where('id', $siteId)->first(['code', 'name']);

        $pdf = Pdf::loadView('pdf.product-kardex', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'product' => $product,
            'stockActual' => $qty,
            'rows' => $rows,
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        return $pdf->download('kardex-'.$product->sku.'.pdf');
    }
}
