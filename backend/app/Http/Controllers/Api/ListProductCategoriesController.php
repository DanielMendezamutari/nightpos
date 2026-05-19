<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class ListProductCategoriesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $rows = DB::table('product_categories')
            ->select(['id', 'slug', 'name', 'sort_order', 'product_type'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $rows,
        ]);
    }
}
