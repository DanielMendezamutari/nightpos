<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\RoomService\Services\RoomServiceDueNotifier;
use Illuminate\Console\Command;

final class CheckDueRoomServicesCommand extends Command
{
    protected $signature = 'room-services:check-due';

    protected $description = 'Detecta piezas vencidas y crea notificaciones para limpieza';

    public function handle(RoomServiceDueNotifier $notifier): int
    {
        $created = $notifier->processDueRooms();

        $this->info("Notificaciones creadas: {$created}");

        return self::SUCCESS;
    }
}
