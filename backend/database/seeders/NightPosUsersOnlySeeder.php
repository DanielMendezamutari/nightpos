<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsNightPosFoundation;
use Illuminate\Database\Seeder;

/**
 * Seed limpio: solo tenant, sucursal, roles, permisos, usuarios y mínimos de caja.
 *
 * Uso:
 *   php artisan migrate:fresh
 *   php artisan db:seed --class=NightPosUsersOnlySeeder
 */
final class NightPosUsersOnlySeeder extends Seeder
{
    use SeedsNightPosFoundation;

    public function run(): void
    {
        $this->seedNightPosFoundation();
    }
}
