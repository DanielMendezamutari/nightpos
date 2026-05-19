<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class CreateCompanionQuickController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'stage_name' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        $stageName = trim($payload['stage_name']);
        $siteId = $this->resolveBranchSiteId($request);

        $existing = DB::table('companions')
            ->whereRaw('LOWER(stage_name) = ?', [mb_strtolower($stageName)])
            ->first(['id', 'stage_name', 'is_active']);

        if ($existing) {
            if (! $existing->is_active) {
                DB::table('companions')->where('id', (int) $existing->id)->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            }
            if ($siteId) {
                self::ensureSiteCompanionContact((int) $siteId, $existing->stage_name);
            }

            return response()->json([
                'data' => [
                    'id' => (int) $existing->id,
                    'stage_name' => $existing->stage_name,
                    'reused' => true,
                ],
            ], Response::HTTP_CREATED);
        }

        $id = DB::table('companions')->insertGetId([
            'stage_name' => $stageName,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($siteId) {
            self::ensureSiteCompanionContact((int) $siteId, $stageName);
        }

        return response()->json([
            'data' => [
                'id' => $id,
                'stage_name' => $stageName,
                'reused' => false,
            ],
        ], Response::HTTP_CREATED);
    }

    private static function ensureSiteCompanionContact(int $siteId, string $displayName): void
    {
        $name = trim($displayName);
        if ($name === '') {
            return;
        }

        $exists = DB::table('site_contacts')
            ->where('site_id', $siteId)
            ->where('contact_type', 'companion')
            ->whereRaw('LOWER(display_name) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('site_contacts')->insert([
            'site_id' => $siteId,
            'contact_type' => 'companion',
            'display_name' => $name,
            'phone' => null,
            'email' => null,
            'document_type' => null,
            'document_number' => null,
            'business_name' => null,
            'service_category' => null,
            'commission_percent' => null,
            'notes' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
