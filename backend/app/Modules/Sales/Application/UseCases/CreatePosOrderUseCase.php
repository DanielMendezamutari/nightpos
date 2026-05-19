<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\UseCases;

use App\Modules\Sales\Application\DTO\CreatePosOrderInput;
use App\Modules\Sales\Application\Exceptions\PosFlowException;
use Illuminate\Support\Facades\DB;

final class CreatePosOrderUseCase
{
    /**
     * @return array{id: int, status: string}
     */
    public function execute(CreatePosOrderInput $input): array
    {
        $session = DB::table('customer_sessions')->where('id', $input->customerSessionId)->first();
        if (! $session || $session->status !== 'open') {
            throw new PosFlowException(422, 'La sesion debe estar abierta.');
        }

        if ($input->resolvedSiteId && (int) $session->site_id !== (int) $input->resolvedSiteId) {
            throw new PosFlowException(403, 'La sesion no pertenece a la sucursal activa.');
        }

        $shiftId = (int) DB::table('shift_turns')
            ->where('site_id', (int) $session->site_id)
            ->where('status', 'open')
            ->orderByDesc('id')
            ->value('id');

        if (! $shiftId) {
            throw new PosFlowException(422, 'No hay caja/turno abierto para registrar orden.');
        }

        $id = DB::table('orders')->insertGetId([
            'shift_turn_id' => $shiftId,
            'customer_session_id' => $input->customerSessionId,
            'waiter_user_id' => $input->waiterUserId,
            'status' => 'pending',
            'ordered_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'id' => $id,
            'status' => 'pending',
        ];
    }
}
