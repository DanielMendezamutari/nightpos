<?php

declare(strict_types=1);

namespace App\Domain\Shift\ValueObjects;

final class ShiftType
{
    public const DAY = 'DAY';

    public const NIGHT = 'NIGHT';

    public static function fromString(string $value): self
    {
        $upper = strtoupper(trim($value));

        if (! in_array($upper, [self::DAY, self::NIGHT], true)) {
            throw new \InvalidArgumentException(sprintf('Tipo de turno inválido: %s.', $value));
        }

        return new self($upper);
    }

    private function __construct(public readonly string $value)
    {
    }

    public function isDay(): bool
    {
        return $this->value === self::DAY;
    }

    public function label(): string
    {
        return $this->isDay() ? 'Día' : 'Noche';
    }

    public function defaultName(): string
    {
        return $this->isDay() ? 'Turno Día' : 'Turno Noche';
    }
}
