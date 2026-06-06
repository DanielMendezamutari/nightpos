<?php

declare(strict_types=1);

namespace App\Application\Staff\Support;

use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Str;

final class GirlUsernameGenerator
{
    public static function generate(int $tenantId, string $name): string
    {
        $base = Str::slug(mb_strtolower(trim($name)), '.');
        if ($base === '') {
            $base = 'chica';
        }

        $candidate = $base;
        $suffix = 1;

        while (UserModel::query()
            ->where('tenant_id', $tenantId)
            ->where('username', $candidate)
            ->exists()) {
            $candidate = $base.'.'.$suffix;
            $suffix++;
        }

        return mb_substr($candidate, 0, 100);
    }
}
