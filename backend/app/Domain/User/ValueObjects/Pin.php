<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use InvalidArgumentException;

final class Pin
{
    private string $value;

    public function __construct(string $value)
    {
        $clean = trim($value);
        if (!preg_match('/^[0-9]{4,6}$/', $clean)) {
            throw new InvalidArgumentException('El PIN debe tener entre 4 y 6 digitos numericos.');
        }
        $this->value = $clean;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
