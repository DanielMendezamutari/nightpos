<?php

declare(strict_types=1);

namespace App\Application\RoomService\Services;

use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\GirlIncome\Repositories\RoomServiceRepositoryInterface;
use App\Domain\Notification\Repositories\NotificationRepositoryInterface;
use App\Infrastructure\Notification\Channels\DatabaseNotificationChannel;
use App\Infrastructure\Notification\Channels\WhatsAppNotificationChannel;
use Illuminate\Support\Facades\Log;

final class RoomServiceDueNotifier
{
    private const ROLE_TARGETS = ['CLEANING', 'CASHIER'];

    public function __construct(
        private readonly RoomServiceRepositoryInterface $roomServices,
        private readonly NotificationRepositoryInterface $notifications,
        private readonly DatabaseNotificationChannel $databaseChannel,
        private readonly WhatsAppNotificationChannel $whatsAppChannel,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    public function processDueRooms(): int
    {
        $created = 0;

        foreach ($this->roomServices->findDueUnalerted() as $room) {
            $roomId = (int) $room['id'];
            $type = 'ROOM_SERVICE_DUE';

            $this->roomServices->markDue($roomId);

            $label = $room['room_label'] ?? $room['room_number'] ?? 'pieza';

            foreach (self::ROLE_TARGETS as $roleTarget) {
                if ($this->notifications->existsForSourceRole($type, 'ROOM_SERVICE', $roomId, $roleTarget)) {
                    continue;
                }

                $payload = [
                    'tenant_id' => $room['tenant_id'],
                    'branch_id' => $room['branch_id'],
                    'user_id' => null,
                    'role_target' => $roleTarget,
                    'title' => 'Pieza cumplida',
                    'message' => "La pieza {$label} ya cumplió su tiempo.",
                    'type' => $type,
                    'source_type' => 'ROOM_SERVICE',
                    'source_id' => $roomId,
                    'priority' => 'HIGH',
                    'channels' => ['database'],
                ];

                $this->databaseChannel->send($payload);

                if (config('nightpos.notifications.whatsapp_enabled', false)) {
                    $this->whatsAppChannel->send($payload);
                }

                $created++;

                Log::info('Room service due notification created.', [
                    'room_service_id' => $roomId,
                    'role_target' => $roleTarget,
                ]);
            }

            $this->eventEmitter->emit(
                (int) $room['tenant_id'],
                (int) $room['branch_id'],
                'room_service.due',
                [
                    'entity'  => ['type' => 'room_service', 'id' => $roomId],
                    'summary' => "Pieza cumplida: {$label}",
                    'refresh' => ['room_services'],
                ]
            );

            $this->roomServices->markAlertSent($roomId);
        }

        return $created;
    }
}
