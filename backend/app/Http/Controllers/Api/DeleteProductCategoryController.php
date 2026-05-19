<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class DeleteProductCategoryController extends Controller
{
    public function __invoke(int $categoryId): JsonResponse
    {
        $exists = DB::table('product_categories')->where('id', $categoryId)->exists();
        if (! $exists) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        $inUse = DB::table('products')->where('category_id', $categoryId)->exists();
        if ($inUse) {
            return response()->json([
                'message' => 'No se puede borrar: hay productos usando esta categoría. Cambiálos de categoría primero.',
            ], 422);
        }

        DB::table('product_categories')->where('id', $categoryId)->delete();

        return response()->json(null, 204);
    }
}
