<?php

declare(strict_types=1);

namespace App\Application\Room\Services;

use App\Domain\Room\Enums\RoomType;
use App\Domain\Room\Exceptions\RoomDomainException;
use App\Domain\Settings\Repositories\RoomTypeCatalogRepositoryInterface;

final class RoomTypeResolver
{
    public function __construct(
        private readonly RoomTypeCatalogRepositoryInterface $roomTypes,
    ) {
    }

    public function resolve(int $tenantId, ?int $roomTypeId, ?string $roomType): string
    {
        if ($roomTypeId !== null && $roomTypeId > 0) {
            $catalog = $this->roomTypes->findById($roomTypeId, $tenantId);

            if ($catalog === null || $catalog['status'] !== 'active') {
                throw RoomDomainException::invalidType();
            }

            return strtoupper($catalog['code']);
        }

        $type = strtoupper(trim((string) $roomType));

        if ($type === '') {
            throw RoomDomainException::invalidType();
        }

        if (in_array($type, RoomType::values(), true)) {
            return $type;
        }

        $catalog = $this->roomTypes->findByCode($tenantId, $type);

        if ($catalog !== null) {
            return strtoupper($catalog['code']);
        }

        throw RoomDomainException::invalidType();
    }
}
