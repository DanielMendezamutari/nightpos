<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Support\ProductStockAggregator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class CreatePurchaseOrderController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $this->mergeMultipartJsonLines($request);

        $siteId = $this->resolveBranchSiteId($request)
            ?? (int) DB::table('sites')->orderBy('id')->value('id');
        if (! $siteId) {
            return response()->json(['message' => 'No hay sucursal para registrar la compra.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $request->validate([
            'site_contact_id' => ['nullable', 'integer', 'exists:site_contacts,id'],
            'document_ref' => ['nullable', 'string', 'max:64'],
            'purchased_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:400'],
            'document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpeg,jpg,png'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lines.*.purchase_packaging' => ['nullable', Rule::in(['unit', 'box', 'basket', 'custom'])],
            'lines.*.pack_quantity' => ['nullable', 'integer', 'min:1'],
            'lines.*.quantity' => ['nullable', 'integer', 'min:1'],
            'lines.*.units_per_pack' => ['nullable', 'integer', 'min:1'],
            'lines.*.cost_per_pack' => ['nullable', 'integer', 'min:0'],
            'lines.*.unit_cost' => ['nullable', 'integer', 'min:0'],
            'lines.*.custom_pack_label' => ['nullable', 'string', 'max:48'],
        ]);

        if (! empty($payload['site_contact_id'])) {
            $contactSite = (int) DB::table('site_contacts')->where('id', $payload['site_contact_id'])->value('site_id');
            if ($contactSite !== $siteId) {
                return response()->json(['message' => 'El proveedor no pertenece a esta sucursal.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $purchasedAt = isset($payload['purchased_at']) ? $payload['purchased_at'] : now();

        $resolvedLines = [];
        foreach ($payload['lines'] as $idx => $line) {
            $resolved = $this->resolvePurchaseLine($line, $idx);
            if ($resolved instanceof JsonResponse) {
                return $resolved;
            }
            $resolvedLines[] = $resolved;
        }

        $hasTrackedLine = false;
        foreach ($resolvedLines as $resolved) {
            if ((bool) DB::table('products')->where('id', $resolved['product_id'])->value('track_stock')) {
                $hasTrackedLine = true;
                break;
            }
        }
        if (! $hasTrackedLine) {
            return response()->json([
                'message' => 'Al menos un producto debe tener control de stock para registrar la compra.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $purchaseOrderId = DB::transaction(function () use ($request, $resolvedLines, $siteId, $purchasedAt, $payload): int {
            $orderId = DB::table('purchase_orders')->insertGetId([
                'site_id' => $siteId,
                'created_by' => $request->user()?->id,
                'site_contact_id' => $payload['site_contact_id'] ?? null,
                'document_ref' => $payload['document_ref'] ?? null,
                'purchased_at' => $purchasedAt,
                'notes' => $payload['notes'] ?? null,
                'status' => 'received',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $uploaded = $request->file('document');
            if ($uploaded !== null && $uploaded->isValid()) {
                $safeOriginal = $this->sanitizeOriginalFilename((string) $uploaded->getClientOriginalName());
                $storedPath = $uploaded->store("purchase-documents/{$siteId}/{$orderId}", 'public');
                DB::table('purchase_orders')->where('id', $orderId)->update([
                    'document_file_path' => $storedPath,
                    'document_original_name' => $safeOriginal !== '' ? $safeOriginal : basename($storedPath),
                    'updated_at' => now(),
                ]);
            }

            foreach ($resolvedLines as $resolved) {
                $productId = $resolved['product_id'];
                $qty = $resolved['base_quantity'];
                $unitCost = $resolved['unit_cost'];

                $trackStock = (bool) DB::table('products')->where('id', $productId)->value('track_stock');
                if (! $trackStock) {
                    continue;
                }

                $lineId = DB::table('purchase_order_lines')->insertGetId([
                    'purchase_order_id' => $orderId,
                    'product_id' => $productId,
                    'purchase_packaging' => $resolved['purchase_packaging'],
                    'quantity' => $qty,
                    'units_per_pack' => $resolved['units_per_pack'],
                    'packs_count' => $resolved['packs_count'],
                    'custom_pack_label' => $resolved['custom_pack_label'],
                    'unit_cost' => $unitCost,
                    'cost_per_pack' => $resolved['cost_per_pack'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $updated = DB::table('site_product_stocks')
                    ->where('site_id', $siteId)
                    ->where('product_id', $productId)
                    ->increment('quantity', $qty);

                if (! $updated) {
                    DB::table('site_product_stocks')->insert([
                        'site_id' => $siteId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('products')->where('id', $productId)->update([
                    'purchase_price' => $unitCost,
                    'updated_at' => now(),
                ]);

                DB::table('inventory_movements')->insert([
                    'product_id' => $productId,
                    'site_id' => $siteId,
                    'movement_type' => 'transfer_in',
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'reference_type' => 'purchase_order_line',
                    'reference_id' => $lineId,
                    'notes' => 'Compra registrada',
                    'moved_at' => $purchasedAt,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                ProductStockAggregator::syncBaseStock($productId);
            }

            return $orderId;
        });

        return response()->json([
            'data' => [
                'purchase_order_id' => $purchaseOrderId,
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * @param  array<string, mixed>  $line
     * @return array<string, mixed>|JsonResponse
     */
    private function resolvePurchaseLine(array $line, int $idx): array|JsonResponse
    {
        $productId = (int) $line['product_id'];
        $packaging = $line['purchase_packaging'] ?? 'unit';
        if (! in_array($packaging, ['unit', 'box', 'basket', 'custom'], true)) {
            $packaging = 'unit';
        }

        $packQty = $line['pack_quantity'] ?? $line['quantity'] ?? null;
        if ($packQty === null || (int) $packQty < 1) {
            return response()->json([
                'message' => 'Línea '.($idx + 1).': indicá cuántas unidades o bultos compraste.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $packsCount = (int) $packQty;

        $costPerPack = $line['cost_per_pack'] ?? $line['unit_cost'] ?? null;
        if ($costPerPack === null || (int) $costPerPack < 0) {
            return response()->json([
                'message' => 'Línea '.($idx + 1).': indicá el costo por unidad o por bulto.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $costPerPack = (int) $costPerPack;

        $customLabel = isset($line['custom_pack_label']) ? trim((string) $line['custom_pack_label']) : null;
        if ($customLabel === '') {
            $customLabel = null;
        }

        if ($packaging === 'custom' && $customLabel === null) {
            return response()->json([
                'message' => 'Línea '.($idx + 1).': para “Otro bulto” escribí cómo se llama (ej. palet, bolsa).',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($packaging === 'unit') {
            $unitsPerPack = 1;
        } else {
            $unitsPerPack = isset($line['units_per_pack']) ? (int) $line['units_per_pack'] : 0;
            if ($unitsPerPack < 1) {
                $column = $packaging === 'box' ? 'purchase_units_per_box' : ($packaging === 'basket' ? 'purchase_units_per_basket' : null);
                if ($column !== null) {
                    $fromProduct = (int) DB::table('products')->where('id', $productId)->value($column);
                    if ($fromProduct >= 1) {
                        $unitsPerPack = $fromProduct;
                    }
                }
            }
            if ($unitsPerPack < 1) {
                return response()->json([
                    'message' => 'Línea '.($idx + 1).': indicá cuántas unidades de stock entran en cada bulto, o configurá el producto en Mantenimiento.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $baseQuantity = $packsCount * $unitsPerPack;
        $unitCost = (int) max(0, (int) round((float) $costPerPack / $unitsPerPack));

        return [
            'product_id' => $productId,
            'purchase_packaging' => $packaging,
            'packs_count' => $packsCount,
            'units_per_pack' => $unitsPerPack,
            'custom_pack_label' => $customLabel,
            'cost_per_pack' => $costPerPack,
            'base_quantity' => $baseQuantity,
            'unit_cost' => $unitCost,
        ];
    }

    private function mergeMultipartJsonLines(Request $request): void
    {
        $raw = $request->input('lines');
        if (! is_string($raw)) {
            return;
        }
        $decoded = json_decode($raw, true);
        $request->merge([
            'lines' => is_array($decoded) ? $decoded : [],
        ]);
    }

    private function sanitizeOriginalFilename(string $name): string
    {
        $base = basename(str_replace('\\', '/', $name));
        $base = preg_replace('/[^\p{L}\p{N}._ -]/u', '_', $base) ?? '';

        return mb_substr(trim((string) $base), 0, 255);
    }
}
