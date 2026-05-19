<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class CreateProductCategoryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/', Rule::unique('product_categories', 'slug')],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'product_type' => ['required', 'in:drink,supply'],
        ]);

        $slug = $payload['slug'] ?? $this->uniqueSlugFromName($payload['name']);
        $sortOrder = $payload['sort_order'] ?? ((int) DB::table('product_categories')->max('sort_order')) + 10;

        $id = DB::table('product_categories')->insertGetId([
            'slug' => $slug,
            'name' => $payload['name'],
            'sort_order' => $sortOrder,
            'product_type' => $payload['product_type'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $id,
                'slug' => $slug,
                'name' => $payload['name'],
                'sort_order' => $sortOrder,
                'product_type' => $payload['product_type'],
            ],
        ], 201);
    }

    private function uniqueSlugFromName(string $name): string
    {
        $base = Str::slug($name, '_');
        if ($base === '') {
            $base = 'categoria';
        }
        $slug = $base;
        $n = 1;
        while (DB::table('product_categories')->where('slug', $slug)->exists()) {
            $slug = $base.'_'.$n;
            $n++;
        }

        return $slug;
    }
}
