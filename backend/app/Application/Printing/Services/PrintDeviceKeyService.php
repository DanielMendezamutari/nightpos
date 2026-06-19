<?php

declare(strict_types=1);

namespace App\Application\Printing\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class PrintDeviceKeyService
{
    private const PREFIX = 'npd_live_';

    /**
     * @return array{plaintext: string, prefix: string, hash: string}
     */
    public function generate(): array
    {
        $secret = Str::lower(Str::random(32));
        $plaintext = self::PREFIX.$secret;
        $prefix = substr($plaintext, 0, 12);

        return [
            'plaintext' => $plaintext,
            'prefix' => $prefix,
            'hash' => Hash::make($plaintext),
        ];
    }

    public function verify(string $plaintext, string $hash): bool
    {
        return Hash::check($plaintext, $hash);
    }
}
