<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

final class ListCompanionsController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);

        // Sync "Personal chica" contacts into companions for POS/piezas foreign key usage.
        if ($siteId) {
            $contactNames = DB::table('site_contacts')
                ->where('site_id', (int) $siteId)
                ->where('contact_type', 'companion')
                ->where('is_active', true)
                ->orderBy('display_name')
                ->pluck('display_name')
                ->filter(static fn ($name) => is_string($name) && trim($name) !== '');

            foreach ($contactNames as $name) {
                $stageName = trim((string) $name);
                $exists = DB::table('companions')
                    ->whereRaw('LOWER(stage_name) = ?', [mb_strtolower($stageName)])
                    ->exists();
                if (! $exists) {
                    DB::table('companions')->insert([
                        'stage_name' => $stageName,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $rows = DB::table('companions')
            ->where('is_active', true)
            ->orderBy('stage_name')
            ->get(['id', 'stage_name'])
            ->map(static fn ($c): array => ['id' => (int) $c->id, 'stage_name' => $c->stage_name])
            ->values()->all();

        return response()->json(['data' => $rows]);
    }
}
