<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PaymentRegistered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $paymentId,
        public readonly int $siteId,
        public readonly int $amount,
        public readonly string $method,
    ) {
    }
}
