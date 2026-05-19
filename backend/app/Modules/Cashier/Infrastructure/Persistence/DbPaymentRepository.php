<?php

declare(strict_types=1);

namespace App\Modules\Cashier\Infrastructure\Persistence;

use App\Modules\Cashier\Domain\Ports\PaymentRepository;
use Illuminate\Support\Facades\DB;

final class DbPaymentRepository implements PaymentRepository
{
    public function register(array $payload): int
    {
        return (int) DB::table('payments')->insertGetId([
            ...$payload,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

