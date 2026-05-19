<?php

declare(strict_types=1);

namespace App\Modules\Cashier\Application\UseCases;

use App\Modules\Cashier\Domain\Ports\PaymentRepository;

final readonly class RegisterPaymentUseCase
{
    public function __construct(private PaymentRepository $paymentRepository)
    {
    }

    public function execute(int $orderId, int $shiftTurnId, string $method, int $amount): int
    {
        return $this->paymentRepository->register([
            'order_id' => $orderId,
            'shift_turn_id' => $shiftTurnId,
            'method' => $method,
            'amount' => $amount,
            'paid_at' => now(),
        ]);
    }
}

