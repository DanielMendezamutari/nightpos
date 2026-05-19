<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

final class ListSitesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $sites = Site::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active'])
            ->map(fn (Site $s) => [
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'is_active' => $s->is_active,
            ]);

        return response()->json(['data' => $sites]);
    }
}
