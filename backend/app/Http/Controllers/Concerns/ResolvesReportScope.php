<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

trait ResolvesReportScope
{
    /**
     * Alcance de reportes: un turno cerrado/abierto, o un rango libre de fecha/hora, o sin filtro.
     *
     * @return array{
     *   shift_turn_id: ?int,
     *   range_start: ?Carbon,
     *   range_end: ?Carbon,
     *   filter_label: ?string
     * }
     */
    protected function resolveReportScope(Request $request, ?int $siteId): array
    {
        $shiftIdRaw = $request->query('shift_turn_id');
        if ($shiftIdRaw !== null && $shiftIdRaw !== '') {
            $shiftId = (int) $shiftIdRaw;
            $shift = DB::table('shift_turns')->where('id', $shiftId)->first();
            if (! $shift) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Turno no encontrado.');
            }
            if ($siteId && (int) $shift->site_id !== $siteId) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'El turno no corresponde a la sucursal seleccionada.');
            }

            $opened = Carbon::parse($shift->opened_at);
            $closed = $shift->closed_at ? Carbon::parse($shift->closed_at) : now();
            $period = (string) ($shift->period ?? '');
            $filterLabel = sprintf(
                'Turno #%d %s · %s a %s',
                $shiftId,
                $period !== '' ? '('.$period.')' : '',
                $opened->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                $closed->timezone(config('app.timezone'))->format('d/m/Y H:i'),
            );

            return [
                'shift_turn_id' => $shiftId,
                'range_start' => $opened,
                'range_end' => $closed,
                'filter_label' => $filterLabel,
            ];
        }

        $validated = $request->validate([
            'from' => ['nullable', 'string', 'max:40'],
            'to' => ['nullable', 'string', 'max:40'],
        ]);

        $fromStr = isset($validated['from']) && $validated['from'] !== '' ? $validated['from'] : null;
        $toStr = isset($validated['to']) && $validated['to'] !== '' ? $validated['to'] : null;

        try {
            $rangeStart = $fromStr ? Carbon::parse($fromStr) : null;
            $rangeEnd = $toStr ? Carbon::parse($toStr) : null;
        } catch (\Throwable) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Fecha/hora inválida en desde o hasta.');
        }

        if ($rangeStart && $rangeEnd && $rangeEnd->lt($rangeStart)) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'La fecha/hora hasta debe ser posterior al inicio.');
        }

        $filterLabel = null;
        if ($rangeStart || $rangeEnd) {
            $filterLabel = sprintf(
                'Rango: %s — %s',
                $rangeStart ? $rangeStart->timezone(config('app.timezone'))->format('d/m/Y H:i') : '…',
                $rangeEnd ? $rangeEnd->timezone(config('app.timezone'))->format('d/m/Y H:i') : '…',
            );
        }

        return [
            'shift_turn_id' => null,
            'range_start' => $rangeStart,
            'range_end' => $rangeEnd,
            'filter_label' => $filterLabel,
        ];
    }

    /**
     * Filtro sobre filas que ya tienen join a orders.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyReportScopeToOrders($query, array $scope, string $ordersAlias = 'orders'): void
    {
        if ($scope['shift_turn_id']) {
            $query->where($ordersAlias.'.shift_turn_id', $scope['shift_turn_id']);

            return;
        }
        if ($scope['range_start']) {
            $query->where($ordersAlias.'.ordered_at', '>=', $scope['range_start']);
        }
        if ($scope['range_end']) {
            $query->where($ordersAlias.'.ordered_at', '<=', $scope['range_end']);
        }
    }

    /**
     * Filtro para consultas basadas en payments (ya unidas a orders).
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    protected function applyReportScopeToPayments($query, array $scope, string $paymentsAlias = 'payments', string $ordersAlias = 'orders'): void
    {
        if ($scope['shift_turn_id']) {
            $query->where($ordersAlias.'.shift_turn_id', $scope['shift_turn_id']);

            return;
        }
        if ($scope['range_start']) {
            $query->where($paymentsAlias.'.paid_at', '>=', $scope['range_start']);
        }
        if ($scope['range_end']) {
            $query->where($paymentsAlias.'.paid_at', '<=', $scope['range_end']);
        }
    }
}
