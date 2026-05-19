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

final class ExportPurchaseOrderPdfController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $purchaseOrderId, Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'Sin sucursal.'], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = DB::table('purchase_orders as po')
            ->leftJoin('site_contacts as sc', 'po.site_contact_id', '=', 'sc.id')
            ->leftJoin('users as uc', 'po.created_by', '=', 'uc.id')
            ->leftJoin('users as ucan', 'po.cancelled_by', '=', 'ucan.id')
            ->leftJoin('sites as si', 'po.site_id', '=', 'si.id')
            ->where('po.id', $purchaseOrderId)
            ->where('po.site_id', $siteId)
            ->select([
                'po.id',
                'po.site_id',
                'po.site_contact_id',
                'po.document_ref',
                'po.purchased_at',
                'po.notes',
                'po.status',
                'po.cancelled_at',
                'po.created_at',
                'po.updated_at',
                'sc.display_name as supplier_name',
                'uc.name as created_by_name',
                'ucan.name as cancelled_by_name',
                'si.name as site_name',
                'si.code as site_code',
            ])
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Compra no encontrada.'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        $lines = DB::table('purchase_order_lines as pol')
            ->join('products as p', 'p.id', '=', 'pol.product_id')
            ->where('pol.purchase_order_id', $purchaseOrderId)
            ->orderBy('pol.id')
            ->select([
                'pol.purchase_packaging',
                'pol.quantity',
                'pol.units_per_pack',
                'pol.packs_count',
                'pol.custom_pack_label',
                'pol.unit_cost',
                'pol.cost_per_pack',
                'p.sku',
                'p.name as product_name',
            ])
            ->get();

        $lineRows = [];
        $totalAmount = 0;
        foreach ($lines as $row) {
            $qty = (int) $row->quantity;
            $uc = (int) $row->unit_cost;
            $lineTotal = $qty * $uc;
            $totalAmount += $lineTotal;
            $lineRows[] = [
                'sku' => $row->sku,
                'product_name' => $row->product_name,
                'packaging_label' => $this->packagingLabel(
                    (string) $row->purchase_packaging,
                    $row->custom_pack_label !== null ? (string) $row->custom_pack_label : null,
                ),
                'quantity' => $qty,
                'units_per_pack' => (int) $row->units_per_pack,
                'packs_count' => $row->packs_count !== null ? (int) $row->packs_count : null,
                'unit_cost' => $uc,
                'line_total' => $lineTotal,
            ];
        }

        $purchasedAt = $order->purchased_at !== null
            ? Carbon::parse((string) $order->purchased_at)->timezone(config('app.timezone'))
            : null;

        $pdf = Pdf::loadView('pdf.purchase-order', [
            'appName' => config('app.name', 'NightPOS'),
            'siteName' => $order->site_name,
            'siteCode' => $order->site_code,
            'order' => $order,
            'lines' => $lineRows,
            'totalAmount' => $totalAmount,
            'purchasedAtFormatted' => $purchasedAt?->translatedFormat('d/m/Y H:i'),
            'generatedAtFormatted' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        $filename = 'compra-'.$purchaseOrderId;
        if ($order->document_ref) {
            $filename .= '-'.preg_replace('/[^\p{L}\p{N}_-]+/u', '-', (string) $order->document_ref);
        }
        $filename .= '.pdf';

        return $pdf->download($filename);
    }

    private function packagingLabel(string $packaging, ?string $customLabel): string
    {
        return match ($packaging) {
            'box' => 'Caja',
            'basket' => 'Canastillo',
            'custom' => $customLabel !== null && $customLabel !== '' ? 'Otro: '.$customLabel : 'Otro bulto',
            default => 'Unidad',
        };
    }
}
