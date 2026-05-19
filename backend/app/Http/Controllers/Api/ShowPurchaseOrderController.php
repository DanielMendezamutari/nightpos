<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ShowPurchaseOrderController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $purchaseOrderId, Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'Sin sucursal.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = DB::table('purchase_orders as po')
            ->leftJoin('site_contacts as sc', 'po.site_contact_id', '=', 'sc.id')
            ->leftJoin('users as uc', 'po.created_by', '=', 'uc.id')
            ->leftJoin('users as ucan', 'po.cancelled_by', '=', 'ucan.id')
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
                'po.document_file_path',
                'po.document_original_name',
                'sc.display_name as supplier_name',
                'uc.name as created_by_name',
                'ucan.name as cancelled_by_name',
            ])
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Compra no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $lines = DB::table('purchase_order_lines as pol')
            ->join('products as p', 'p.id', '=', 'pol.product_id')
            ->where('pol.purchase_order_id', $purchaseOrderId)
            ->orderBy('pol.id')
            ->select([
                'pol.id',
                'pol.product_id',
                'p.sku',
                'p.name as product_name',
                'pol.purchase_packaging',
                'pol.quantity',
                'pol.units_per_pack',
                'pol.packs_count',
                'pol.custom_pack_label',
                'pol.unit_cost',
                'pol.cost_per_pack',
            ])
            ->get()
            ->map(function ($row): array {
                $qty = (int) $row->quantity;
                $uc = (int) $row->unit_cost;

                return [
                    'id' => (int) $row->id,
                    'product_id' => (int) $row->product_id,
                    'sku' => $row->sku,
                    'product_name' => $row->product_name,
                    'purchase_packaging' => $row->purchase_packaging,
                    'quantity' => $qty,
                    'units_per_pack' => (int) $row->units_per_pack,
                    'packs_count' => $row->packs_count !== null ? (int) $row->packs_count : null,
                    'custom_pack_label' => $row->custom_pack_label,
                    'unit_cost' => $uc,
                    'cost_per_pack' => $row->cost_per_pack !== null ? (int) $row->cost_per_pack : null,
                    'line_total' => $qty * $uc,
                ];
            })
            ->all();

        $totalAmount = array_sum(array_column($lines, 'line_total'));

        return response()->json([
            'data' => [
                'order' => [
                    'id' => (int) $order->id,
                    'site_id' => (int) $order->site_id,
                    'site_contact_id' => $order->site_contact_id !== null ? (int) $order->site_contact_id : null,
                    'supplier_name' => $order->supplier_name,
                    'document_ref' => $order->document_ref,
                    'purchased_at' => $order->purchased_at,
                    'notes' => $order->notes,
                    'status' => $order->status ?? 'received',
                    'cancelled_at' => $order->cancelled_at,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'created_by_name' => $order->created_by_name,
                    'cancelled_by_name' => $order->cancelled_by_name,
                    'total_amount' => $totalAmount,
                    'line_count' => count($lines),
                    'has_document' => $order->document_file_path !== null && $order->document_file_path !== '',
                    'document_original_name' => $order->document_original_name,
                ],
                'lines' => $lines,
            ],
        ]);
    }
}
