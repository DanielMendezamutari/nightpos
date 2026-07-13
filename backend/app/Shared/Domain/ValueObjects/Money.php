<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObjects;

use App\Shared\Domain\ValueObject;
use InvalidArgumentException;

final readonly class Money extends ValueObject
{
    public function __construct(
        public string $amount,
        public string $currency = 'BOB',
    ) {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Money amount must be numeric.');
        }

        if ($currency === '') {
            throw new InvalidArgumentException('Money currency cannot be empty.');
        }
    }

    public function add(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot add money with different currencies.');
        }

        $result = bcadd($this->amount, $other->amount, 2);

        return new self($result, $this->currency);
    }

    public function subtract(self $other): self
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException('Cannot subtract money with different currencies.');
        }

        $result = bcsub($this->amount, $other->amount, 2);

        return new self($result, $this->currency);
    }

    public function divide(int|string $divisor): self
    {
        if ((string) $divisor === '0') {
            throw new InvalidArgumentException('Cannot divide money by zero.');
        }

        $result = bcdiv($this->amount, (string) $divisor, 2);

        return new self($result, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }
}
