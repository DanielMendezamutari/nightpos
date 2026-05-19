<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class UpdateProductCategoryController extends Controller
{
    public function __invoke(int $categoryId, Request $request): JsonResponse
    {
        $exists = DB::table('product_categories')->where('id', $categoryId)->exists();
        if (! $exists) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'product_type' => ['sometimes', 'in:drink,supply'],
        ]);

        if ($payload === []) {
            $row = DB::table('product_categories')->where('id', $categoryId)->first();

            return response()->json([
                'data' => [
                    'id' => (int) $row->id,
                    'slug' => $row->slug,
                    'name' => $row->name,
                    'sort_order' => (int) $row->sort_order,
                    'product_type' => $row->product_type,
                ],
            ]);
        }

        $update = [...$payload, 'updated_at' => now()];
        DB::table('product_categories')
            ->where('id', $categoryId)
            ->update($update);

        if (array_key_exists('product_type', $payload)) {
            DB::table('products')
                ->where('category_id', $categoryId)
                ->update([
                    'product_type' => $payload['product_type'],
                    'updated_at' => now(),
                ]);
        }

        $row = DB::table('product_categories')->where('id', $categoryId)->first();

        return response()->json([
            'data' => [
                'id' => (int) $row->id,
                'slug' => $row->slug,
                'name' => $row->name,
                'sort_order' => (int) $row->sort_order,
                'product_type' => $row->product_type,
            ],
        ]);
    }
}
