<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteWorkShift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Plantilla semanal de **turnos de trabajo** por sucursal (varios turnos por dia).
 *
 * Cubre boliches con 3×8 h, 2×12 h, turno noche que cierra al dia siguiente y turnos de 24 h (21:00→21:00).
 * Para reportes y caja: la **fecha operativa** del turno es la del **inicio** (dia ISO del opens_at).
 */
final class BranchOperatingHoursController extends Controller
{
    use ResolvesBranchSiteId;

    private const MAX_SHIFTS_PER_DAY = 12;

    public function show(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if ($siteId === null) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si eres super admin, envia el parametro site_id.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! Site::query()->whereKey($siteId)->exists()) {
            return response()->json(['message' => 'Sucursal no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => [
                'site_id' => $siteId,
                'reporting_rule' => 'La fecha operativa de cada turno es la del dia en que inicia (opens_at), aunque el cierre sea al dia siguiente.',
                'weekdays' => $this->buildWeekdaysPayload($siteId),
            ],
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if ($siteId === null) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si eres super admin, envia el parametro site_id.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! Site::query()->whereKey($siteId)->exists()) {
            return response()->json(['message' => 'Sucursal no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'weekdays' => ['required', 'array', 'size:7'],
            'weekdays.*.weekday' => ['required', 'integer', 'between:1,7'],
            'weekdays.*.shifts' => ['present', 'array', 'max:'.self::MAX_SHIFTS_PER_DAY],
            'weekdays.*.shifts.*.label' => ['nullable', 'string', 'max:80'],
            /** HH:MM o HH:MM:SS; orden evita que 16:00 se lea como 1 (2[0-3]|1\d|0?\d) */
            'weekdays.*.shifts.*.opens_at' => ['required', 'regex:#^(2[0-3]|1\d|0?\d):[0-5]\d(:[0-5]\d)?$#'],
            'weekdays.*.shifts.*.closes_at' => ['required', 'regex:#^(2[0-3]|1\d|0?\d):[0-5]\d(:[0-5]\d)?$#'],
            'weekdays.*.shifts.*.crosses_midnight' => ['required', 'boolean'],
        ]);

        foreach ($validated['weekdays'] as &$day) {
            foreach ($day['shifts'] as &$shift) {
                $shift['opens_at'] = $this->normalizeToHhMm($shift['opens_at']);
                $shift['closes_at'] = $this->normalizeToHhMm($shift['closes_at']);
            }
        }
        unset($day, $shift);

        $weekdays = collect($validated['weekdays'])->pluck('weekday')->sort()->values()->all();
        if ($weekdays !== range(1, 7)) {
            return response()->json([
                'message' => 'Debes enviar exactamente los dias 1 al 7 (Lunes a Domingo), sin repetir.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        foreach ($validated['weekdays'] as $day) {
            foreach ($day['shifts'] as $shift) {
                $err = $this->validateShiftWindow($shift['opens_at'], $shift['closes_at'], $shift['crosses_midnight']);
                if ($err !== null) {
                    return response()->json(['message' => $err], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        }

        DB::transaction(function () use ($siteId, $validated): void {
            SiteWorkShift::query()->where('site_id', $siteId)->delete();
            foreach ($validated['weekdays'] as $day) {
                $slot = 1;
                foreach ($day['shifts'] as $shift) {
                    SiteWorkShift::query()->create([
                        'site_id' => $siteId,
                        'weekday' => $day['weekday'],
                        'slot_index' => $slot,
                        'label' => $shift['label'] ?? null,
                        'opens_at' => $shift['opens_at'],
                        'closes_at' => $shift['closes_at'],
                        'crosses_midnight' => $shift['crosses_midnight'],
                    ]);
                    $slot++;
                }
            }
        });

        return $this->show($request);
    }

    private function normalizeToHhMm(string $value): string
    {
        $value = trim($value);
        if (preg_match('/^(2[0-3]|1\d|0?\d):([0-5]\d)(?::([0-5]\d))?$/', $value, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return $value;
    }

    private function validateShiftWindow(string $opensAt, string $closesAt, bool $crossesMidnight): ?string
    {
        if (! $crossesMidnight && $closesAt <= $opensAt) {
            return 'Sin "cruce al dia siguiente", la hora de fin debe ser mayor que la de inicio (turno dentro del mismo dia calendario).';
        }

        return null;
    }

    /**
     * @return list<array{weekday: int, weekday_label: string, shifts: list<array{slot_index: int, label: ?string, opens_at: string, closes_at: string, crosses_midnight: bool}>}>
     */
    private function buildWeekdaysPayload(int $siteId): array
    {
        $labels = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        $grouped = SiteWorkShift::query()
            ->where('site_id', $siteId)
            ->orderBy('weekday')
            ->orderBy('slot_index')
            ->get()
            ->groupBy('weekday');

        $out = [];
        for ($w = 1; $w <= 7; $w++) {
            $shifts = [];
            foreach ($grouped->get($w, collect()) as $row) {
                $shifts[] = [
                    'slot_index' => $row->slot_index,
                    'label' => $row->label,
                    'opens_at' => $row->opens_at,
                    'closes_at' => $row->closes_at,
                    'crosses_midnight' => $row->crosses_midnight,
                ];
            }
            $out[] = [
                'weekday' => $w,
                'weekday_label' => $labels[$w],
                'shifts' => $shifts,
            ];
        }

        return $out;
    }
}
