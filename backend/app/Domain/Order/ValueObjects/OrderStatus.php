<?php

declare(strict_types=1);

namespace App\Domain\Order\ValueObjects;

use App\Domain\Order\Exceptions\OrderDomainException;

final readonly class OrderStatus
{
    public const OPEN = 'OPEN';

    public const SENT_TO_BAR = 'SENT_TO_BAR';

    public const IN_PREPARATION = 'IN_PREPARATION';

    public const READY = 'READY';

    public const BILLED = 'BILLED';

    public const CANCELLED = 'CANCELLED';

    private const MODIFIABLE = [self::OPEN, self::SENT_TO_BAR];

    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));
        $allowed = [
            self::OPEN,
            self::SENT_TO_BAR,
            self::IN_PREPARATION,
            self::READY,
            self::BILLED,
            self::CANCELLED,
        ];

        if (! in_array($normalized, $allowed, true)) {
            throw OrderDomainException::invalidStatus($value);
        }

        return new self($normalized);
    }

    public function allowsItemChanges(): bool
    {
        return in_array($this->value, self::MODIFIABLE, true);
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }

    public function canSendToBar(): bool
    {
        return $this->value === self::OPEN;
    }

    public function canCancel(): bool
    {
        return ! in_array($this->value, [self::BILLED, self::CANCELLED], true);
    }
}
