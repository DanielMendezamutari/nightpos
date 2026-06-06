<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

/**
 * Huella determinística del PIN para unicidad en BD (no almacena el PIN en claro).
 */
final class PinFingerprint
{
    public static function fromPlain(string $pin, string $appKey): string
    {
        return hash_hmac('sha256', $pin, $appKey);
    }
}
