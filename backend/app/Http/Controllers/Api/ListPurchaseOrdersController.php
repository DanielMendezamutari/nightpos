<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ListPurchaseOrdersController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'Sin sucursal.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rows = DB::table('purchase_orders as po')
            ->leftJoin('site_contacts as sc', 'po.site_contact_id', '=', 'sc.id')
            ->leftJoin('users as uc', 'po.created_by', '=', 'uc.id')
            ->where('po.site_id', $siteId)
            ->orderByDesc('po.purchased_at')
            ->orderByDesc('po.id')
            ->select([
                'po.id',
                'po.site_contact_id',
                'po.document_ref',
                'po.purchased_at',
                'po.notes',
                'po.status',
                'po.cancelled_at',
                'po.created_at',
                'po.document_file_path',
                'po.document_original_name',
                'sc.display_name as supplier_name',
                'uc.name as created_by_name',
            ])
            ->selectRaw(
                '(select count(*) from purchase_order_lines where purchase_order_id = po.id) as line_count'
            )
            ->selectRaw(
                '(select coalesce(sum(quantity * unit_cost), 0) from purchase_order_lines where purchase_order_id = po.id) as total_amount'
            )
            ->get()
            ->map(function ($row): array {
                return [
                    'id' => (int) $row->id,
                    'site_contact_id' => $row->site_contact_id !== null ? (int) $row->site_contact_id : null,
                    'supplier_name' => $row->supplier_name,
                    'document_ref' => $row->document_ref,
                    'purchased_at' => $row->purchased_at,
                    'notes' => $row->notes,
                    'status' => $row->status ?? 'received',
                    'cancelled_at' => $row->cancelled_at,
                    'created_at' => $row->created_at,
                    'created_by_name' => $row->created_by_name,
                    'line_count' => (int) $row->line_count,
                    'total_amount' => (int) $row->total_amount,
                    'has_document' => $row->document_file_path !== null && $row->document_file_path !== '',
                    'document_original_name' => $row->document_original_name,
                ];
            })
            ->all();

        return response()->json(['data' => $rows]);
    }
}
