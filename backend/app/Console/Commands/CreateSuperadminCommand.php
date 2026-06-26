<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\User\Services\PinFingerprint;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Shared\Application\Support\AuditLogRecorder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

final class CreateSuperadminCommand extends Command
{
    protected $signature = 'nightpos:create-superadmin
        {--name= : Nombre completo}
        {--username= : Usuario o email}
        {--password= : Contraseña (mínimo 8 caracteres)}
        {--pin= : PIN numérico de 4 a 6 dígitos}
        {--force-create : Crear otro superadmin aunque ya exista uno}';

    protected $description = 'Crea un usuario superadmin de plataforma (tenant_id null) sin depender de seeders';

    public function handle(AuditLogRecorder $audit): int
    {
        if (PermissionModel::query()->count() === 0) {
            $this->error('El catálogo de permisos está vacío. Ejecute migraciones antes de crear el superadmin.');

            return self::FAILURE;
        }

        $existingCount = UserModel::query()
            ->whereHas('role', fn ($query) => $query->where('slug', 'super_admin'))
            ->count();

        if ($existingCount > 0 && ! $this->option('force-create')) {
            if (! $this->confirm('Ya existe al menos un superadmin. ¿Desea crear otro?', false)) {
                $this->info('Operación cancelada. Los superadmins existentes no fueron modificados.');

                return self::SUCCESS;
            }
        }

        $name = $this->option('name') ?: $this->ask('Nombre completo');
        $username = strtolower(trim((string) ($this->option('username') ?: $this->ask('Usuario / email'))));
        $password = $this->option('password') ?: $this->secret('Contraseña (mínimo 8 caracteres)');
        $passwordConfirmation = $this->option('password') ? $password : $this->secret('Confirmar contraseña');
        $pin = $this->option('pin') ?: $this->secret('PIN (4 a 6 dígitos)');
        $pinConfirmation = $this->option('pin') ? $pin : $this->secret('Confirmar PIN');

        $validator = Validator::make([
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
            'pin' => $pin,
            'pin_confirmation' => $pinConfirmation,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'pin' => ['required', 'regex:/^\d{4,6}$/', 'confirmed'],
        ], [
            'pin.regex' => 'El PIN debe tener entre 4 y 6 dígitos numéricos.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $role = RoleModel::query()->firstOrCreate(
            ['tenant_id' => null, 'slug' => 'super_admin'],
            ['name' => 'Super Admin SaaS'],
        );

        $role->permissions()->sync(PermissionModel::query()->pluck('id')->all());

        $pinFingerprint = PinFingerprint::fromPlain($pin, (string) config('app.key'));

        if (UserModel::query()->where('pin_fingerprint', $pinFingerprint)->exists()) {
            $this->error('El PIN ya está asignado a otro usuario.');

            return self::FAILURE;
        }

        $user = UserModel::query()->create([
            'tenant_id' => null,
            'branch_id' => null,
            'role_id' => $role->id,
            'name' => trim((string) $name),
            'username' => $username,
            'email' => str_contains($username, '@') ? $username : null,
            'password' => $password,
            'pin_hash' => Hash::make($pin),
            'pin_fingerprint' => $pinFingerprint,
            'status' => 'active',
        ]);

        $audit->recordPlatform(
            userId: (int) $user->id,
            action: 'SUPERADMIN_CREATED',
            subjectType: 'user',
            subjectId: (int) $user->id,
            metadata: [
                'username' => $user->username,
                'created_by' => 'cli',
            ],
        );

        $this->info("Superadmin creado: {$user->username} (id {$user->id})");

        return self::SUCCESS;
    }
}
