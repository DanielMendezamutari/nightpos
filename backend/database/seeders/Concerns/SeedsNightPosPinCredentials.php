<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use App\Domain\User\Services\PinFingerprint;
use Illuminate\Support\Facades\Hash;

trait SeedsNightPosPinCredentials
{
    /**
     * @return array{pin_hash: string, pin_fingerprint: string}
     */
    private function pinCredentials(string $pin): array
    {
        return [
            'pin_hash' => Hash::make($pin),
            'pin_fingerprint' => PinFingerprint::fromPlain($pin, (string) config('app.key')),
        ];
    }
}
