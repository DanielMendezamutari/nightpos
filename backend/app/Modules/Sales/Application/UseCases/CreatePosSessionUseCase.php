<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\UseCases;

use App\Modules\Sales\Application\DTO\CreatePosSessionInput;
use App\Modules\Sales\Application\Exceptions\PosFlowException;
use Illuminate\Support\Facades\DB;

final class CreatePosSessionUseCase
{
    /**
     * @return array{id: int, site_id: int, table_code: string|null, zone_code: string|null, customer_name: string|null, status: string}
     */
    public function execute(CreatePosSessionInput $input): array
    {
        $siteId = $input->siteId;
        $payload = [
            'site_table_id' => $input->siteTableId,
            'table_code' => $input->tableCode,
            'zone_code' => $input->zoneCode,
        ];

        $tableQuery = DB::table('site_tables')
            ->leftJoin('site_rooms', 'site_rooms.id', '=', 'site_tables.site_room_id')
            ->where('site_tables.site_id', $siteId);
        if (! empty($payload['site_table_id'])) {
            $tableQuery->where('site_tables.id', (int) $payload['site_table_id']);
        } else {
            $tableQuery->where('site_tables.code', (string) $payload['table_code']);
        }
        $table = $tableQuery->first(['site_tables.id', 'site_tables.code', 'site_tables.is_active', 'site_rooms.code as room_code']);

        if (! $table) {
            if (! empty($payload['table_code'])) {
                $id = DB::table('customer_sessions')->insertGetId([
                    'site_id' => $siteId,
                    'table_code' => (string) $payload['table_code'],
                    'zone_code' => $payload['zone_code'] ?? null,
                    'status' => 'open',
                    'opened_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return [
                    'id' => $id,
                    'site_id' => $siteId,
                    'table_code' => (string) $payload['table_code'],
                    'zone_code' => $payload['zone_code'] ?? null,
                    'customer_name' => $input->customerName,
                    'status' => 'open',
                ];
            }
            throw new PosFlowException(422, 'Mesa no disponible en esta sucursal.');
        }
        if (! $table->is_active) {
            throw new PosFlowException(422, 'Mesa no disponible en esta sucursal.');
        }

        $tableIdForAssignment = ! empty($payload['site_table_id'])
            ? (int) $payload['site_table_id']
            : (int) $table->id;

        $assignment = DB::table('site_table_assignments')
            ->where('site_id', $siteId)
            ->where('site_table_id', $tableIdForAssignment)
            ->where('waiter_user_id', $input->waiterUserId)
            ->exists();
        if (! $assignment) {
            throw new PosFlowException(403, 'La mesa no está asignada a este garzón.');
        }

        $alreadyOpen = DB::table('customer_sessions')
            ->where('site_id', $siteId)
            ->where('status', 'open')
            ->where('table_code', $table->code)
            ->exists();
        if ($alreadyOpen) {
            throw new PosFlowException(422, 'La mesa ya tiene sesión abierta.');
        }

        $id = DB::table('customer_sessions')->insertGetId([
            'site_id' => $siteId,
            'table_code' => $table->code,
            'zone_code' => $table->room_code ?? ($payload['zone_code'] ?? null),
            'status' => 'open',
            'opened_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'id' => $id,
            'site_id' => $siteId,
            'table_code' => $table->code,
            'zone_code' => $table->room_code ?? ($payload['zone_code'] ?? null),
            'customer_name' => $input->customerName,
            'status' => 'open',
        ];
    }
}
