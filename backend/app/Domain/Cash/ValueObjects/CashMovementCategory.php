<?php

declare(strict_types=1);

namespace App\Domain\Cash\ValueObjects;

use App\Domain\Cash\Exceptions\CashDomainException;

final readonly class CashMovementCategory
{
    public const SALE_COLLECTION = 'SALE_COLLECTION';

    public const DIRECT_SALE_COLLECTION = 'DIRECT_SALE_COLLECTION';

    public const BRACELET_COLLECTION = 'BRACELET_COLLECTION';

    public const ROOM_SERVICE_COLLECTION = 'ROOM_SERVICE_COLLECTION';

    public const SHOW_COLLECTION = 'SHOW_COLLECTION';

    public const MANUAL_INCOME = 'MANUAL_INCOME';

    public const SETTLEMENT_GIRL_PAYMENT = 'SETTLEMENT_GIRL_PAYMENT';

    public const SETTLEMENT_WAITER_PAYMENT = 'SETTLEMENT_WAITER_PAYMENT';

    public const SETTLEMENT_CLEANING_PAYMENT = 'SETTLEMENT_CLEANING_PAYMENT';

    public const OPERATING_EXPENSE = 'OPERATING_EXPENSE';

    public const PURCHASE = 'PURCHASE';

    public const OTHER_INCOME = 'OTHER_INCOME';

    public const OTHER_EXPENSE = 'OTHER_EXPENSE';

    private function __construct(
        public string $value,
    ) {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (! in_array($normalized, self::values(), true)) {
            throw CashDomainException::invalidMovementType($value);
        }

        return new self($normalized);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [
            self::SALE_COLLECTION,
            self::DIRECT_SALE_COLLECTION,
            self::BRACELET_COLLECTION,
            self::ROOM_SERVICE_COLLECTION,
            self::SHOW_COLLECTION,
            self::MANUAL_INCOME,
            self::SETTLEMENT_GIRL_PAYMENT,
            self::SETTLEMENT_WAITER_PAYMENT,
            self::SETTLEMENT_CLEANING_PAYMENT,
            self::OPERATING_EXPENSE,
            self::PURCHASE,
            self::OTHER_INCOME,
            self::OTHER_EXPENSE,
        ];
    }
}