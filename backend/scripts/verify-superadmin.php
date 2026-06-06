<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Infrastructure\Persistence\Eloquent\Models\UserModel::where('username', 'superadmin')->first();

if ($user === null) {
    echo "MISSING: superadmin user not found. Run: php artisan db:seed\n";
    exit(1);
}

$ok = Illuminate\Support\Facades\Hash::check('SuperAdmin123!', $user->password);

echo "username={$user->username}\n";
echo "tenant_id=".var_export($user->tenant_id, true)."\n";
echo "status={$user->status}\n";
echo "role={$user->role?->slug}\n";
echo "password_hash_check=".($ok ? 'OK' : 'FAIL')."\n";

exit($ok ? 0 : 1);
