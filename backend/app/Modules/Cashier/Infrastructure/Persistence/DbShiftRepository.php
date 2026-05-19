<?php

declare(strict_types=1);

namespace App\Modules\Cashier\Infrastructure\Persistence;

use App\Modules\Cashier\Domain\Ports\ShiftRepository;
use Illuminate\Support\Facades\DB;

final class DbShiftRepository implements ShiftRepository
{
    public function open(array $payload): int
    {
        return (int) DB::table('shift_turns')->insertGetId([
            ...$payload,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
