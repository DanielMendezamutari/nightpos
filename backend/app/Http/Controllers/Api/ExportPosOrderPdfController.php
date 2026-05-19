<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ExportPosOrderPdfController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $orderId, Request $request): SymfonyResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json(['message' => 'No se pudo determinar la sucursal.'], SymfonyResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $order = DB::table('orders')
            ->join('shift_turns', 'shift_turns.id', '=', 'orders.shift_turn_id')
            ->leftJoin('customer_sessions', 'customer_sessions.id', '=', 'orders.customer_session_id')
            ->join('users as uw', 'uw.id', '=', 'orders.waiter_user_id')
            ->where('orders.id', $orderId)
            ->where('shift_turns.site_id', $siteId)
            ->select([
                'orders.id',
                'orders.status',
                'orders.ordered_at',
                'customer_sessions.table_code',
                'customer_sessions.zone_code',
                'uw.name as waiter_name',
                'shift_turns.id as shift_turn_id',
            ])
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Orden no encontrada o no pertenece a esta sucursal.'], SymfonyResponse::HTTP_NOT_FOUND);
        }

        $items = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('order_items.order_id', $orderId)
            ->orderBy('order_items.id')
            ->get([
                'products.sku',
                'products.name as product_name',
                'order_items.consumption_type',
                'order_items.quantity',
                'order_items.unit_price',
                'order_items.subtotal',
            ]);

        $site = DB::table('sites')->where('id', $siteId)->first(['code', 'name']);

        $subtotal = (int) $items->sum(fn ($i) => (int) $i->subtotal);

        $pdf = Pdf::loadView('pdf.pos-order', [
            'appName' => config('app.name', 'NightPOS'),
            'site' => $site,
            'order' => $order,
            'items' => $items,
            'subtotal' => $subtotal,
            'generatedAt' => now()->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i'),
        ]);

        return $pdf->download('orden-pos-'.$orderId.'.pdf');
    }
}
