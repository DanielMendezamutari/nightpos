<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Eloquent\Models\AuditLogModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementReasonModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashRegisterModel;
use App\Infrastructure\Persistence\Eloquent\Models\PaymentMethodModel;
use App\Infrastructure\Persistence\Eloquent\Models\PermissionModel;
use App\Infrastructure\Persistence\Eloquent\Models\PlanModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\RoleModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Database\Seeders\NightPosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(NightPosSeeder::class);
});

function saasP0SuperToken(): string
{
    return nightposLoginPassword('superadmin', 'SuperAdmin123!', null);
}

/**
 * @return array<string, mixed>
 */
function saasP0PlatformSetupPayload(string $slug, string $username): array
{
    return [
        'tenant' => [
            'name' => 'Wizard '.$slug,
            'slug' => $slug,
            'status' => 'active',
            'plan_name' => 'FREE',
        ],
        'branch' => [
            'name' => 'Sede Wizard',
            'code' => 'WZ01',
            'status' => 'active',
        ],
        'admin' => [
            'name' => 'Admin Wizard',
            'username' => $username,
            'password' => 'Wizard123!',
            'pin' => '1111',
        ],
    ];
}

it('creates superadmin without tenant via artisan command', function () {
    UserModel::query()->where('username', 'superadmin')->delete();

    $this->artisan('nightpos:create-superadmin', [
        '--name' => 'Ops Ribersoft',
        '--username' => 'ops.ribersoft',
        '--password' => 'SecurePass1!',
        '--pin' => '9876',
        '--force-create' => true,
    ])->assertSuccessful();

    $user = UserModel::query()->where('username', 'ops.ribersoft')->first();

    expect($user)->not->toBeNull()
        ->and($user->tenant_id)->toBeNull()
        ->and($user->role?->slug)->toBe('super_admin')
        ->and($user->status)->toBe('active');

    expect(AuditLogModel::query()->where('action', 'SUPERADMIN_CREATED')->exists())->toBeTrue();
});

it('validates minimum password length in create superadmin command', function () {
    UserModel::query()->where('username', 'superadmin')->delete();

    $this->artisan('nightpos:create-superadmin', [
        '--name' => 'Test Admin',
        '--username' => 'short.pass',
        '--password' => 'short1',
        '--pin' => '1234',
        '--force-create' => true,
    ])->assertFailed();
});

it('validates pin length in create superadmin command', function () {
    UserModel::query()->where('username', 'superadmin')->delete();

    $this->artisan('nightpos:create-superadmin', [
        '--name' => 'Test Admin',
        '--username' => 'bad.pin',
        '--password' => 'ValidPass1!',
        '--pin' => '12',
        '--force-create' => true,
    ])->assertFailed();
});

it('provisions tenant_owner with settings.printers permission', function () {
    $this->postJson('/api/v1/admin/platform/setup', saasP0PlatformSetupPayload('p0-printers', 'admin.printers'), [
        'Authorization' => 'Bearer '.saasP0SuperToken(),
    ])->assertCreated();

    $tenant = TenantModel::query()->where('slug', 'p0-printers')->firstOrFail();
    $role = RoleModel::query()->where('tenant_id', $tenant->id)->where('slug', 'tenant_owner')->firstOrFail();

    expect($role->permissions()->where('slug', 'settings.printers')->exists())->toBeTrue();
});

it('provisions tenant_owner with admin.cash_sessions.force_close permission', function () {
    $this->postJson('/api/v1/admin/platform/setup', saasP0PlatformSetupPayload('p0-force-close', 'admin.force'), [
        'Authorization' => 'Bearer '.saasP0SuperToken(),
    ])->assertCreated();

    $tenant = TenantModel::query()->where('slug', 'p0-force-close')->firstOrFail();
    $role = RoleModel::query()->where('tenant_id', $tenant->id)->where('slug', 'tenant_owner')->firstOrFail();

    expect($role->permissions()->where('slug', 'admin.cash_sessions.force_close')->exists())->toBeTrue();
});

it('provisions cashier_senior role via wizard', function () {
    $response = $this->postJson('/api/v1/admin/platform/setup', saasP0PlatformSetupPayload('p0-senior', 'admin.senior'), [
        'Authorization' => 'Bearer '.saasP0SuperToken(),
    ])->assertCreated();

    expect($response->json('data.roles'))->toContain('cashier_senior');

    $tenant = TenantModel::query()->where('slug', 'p0-senior')->firstOrFail();
    $role = RoleModel::query()->where('tenant_id', $tenant->id)->where('slug', 'cashier_senior')->first();

    expect($role)->not->toBeNull()
        ->and($role->permissions()->count())->toBeGreaterThan(0);
});

it('runs operational bootstrap when provisioning tenant', function () {
    $this->postJson('/api/v1/admin/platform/setup', saasP0PlatformSetupPayload('p0-bootstrap', 'admin.boot'), [
        'Authorization' => 'Bearer '.saasP0SuperToken(),
    ])->assertCreated();

    $tenant = TenantModel::query()->where('slug', 'p0-bootstrap')->firstOrFail();

    expect(PaymentMethodModel::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(CashMovementReasonModel::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(ProductCategoryModel::query()->where('tenant_id', $tenant->id)->count())->toBeGreaterThan(0)
        ->and(CashRegisterModel::query()->where('tenant_id', $tenant->id)->count())->toBe(1);
});

it('returns settings.printers in auth me for new tenant admin', function () {
    $this->postJson('/api/v1/admin/platform/setup', saasP0PlatformSetupPayload('p0-me-perms', 'admin.me'), [
        'Authorization' => 'Bearer '.saasP0SuperToken(),
    ])->assertCreated();

    $token = nightposLoginPassword('admin.me', 'Wizard123!', 'p0-me-perms');

    $response = $this->getJson('/api/v1/auth/me', [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertOk();

    expect($response->json('data.user.permissions'))->toContain('settings.printers')
        ->and($response->json('data.user.permissions'))->toContain('admin.cash_sessions.force_close');
});

it('changes password via PATCH auth me password', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!', 'casa-demo');

    $this->patchJson('/api/v1/auth/me/password', [
        'current_password' => 'AdminDemo123!',
        'new_password' => 'NewSecure1!',
        'new_password_confirmation' => 'NewSecure1!',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertOk();

    $user = UserModel::query()->where('username', 'admin.demo')->firstOrFail();
    expect(Hash::check('NewSecure1!', $user->password))->toBeTrue();
});

it('changes pin via PATCH auth me pin', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!', 'casa-demo');

    $this->patchJson('/api/v1/auth/me/pin', [
        'current_password' => 'AdminDemo123!',
        'new_pin' => '5555',
        'new_pin_confirmation' => '5555',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertOk();

    $pinToken = nightposLoginPin('5555', 'casa-demo', 'CENTRO');
    expect($pinToken)->not->toBeEmpty();
});

it('requires current password to change password', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!', 'casa-demo');

    $this->patchJson('/api/v1/auth/me/password', [
        'current_password' => 'WrongPass1!',
        'new_password' => 'AnotherPass1!',
        'new_password_confirmation' => 'AnotherPass1!',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertUnauthorized();
});

it('requires current password to change pin', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!', 'casa-demo');

    $this->patchJson('/api/v1/auth/me/pin', [
        'current_password' => 'WrongPass1!',
        'new_pin' => '6666',
        'new_pin_confirmation' => '6666',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertUnauthorized();
});

it('records audit log when password changes', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!', 'casa-demo');
    $user = UserModel::query()->where('username', 'admin.demo')->firstOrFail();

    AuditLogModel::query()->where('action', 'USER_PASSWORD_CHANGED')->delete();

    $this->patchJson('/api/v1/auth/me/password', [
        'current_password' => 'AdminDemo123!',
        'new_password' => 'AuditPass1!',
        'new_password_confirmation' => 'AuditPass1!',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertOk();

    expect(AuditLogModel::query()
        ->where('action', 'USER_PASSWORD_CHANGED')
        ->where('user_id', $user->id)
        ->exists())->toBeTrue();
});

it('records audit log when pin changes', function () {
    $token = nightposLoginPassword('admin.demo', 'AdminDemo123!', 'casa-demo');
    $user = UserModel::query()->where('username', 'admin.demo')->firstOrFail();

    AuditLogModel::query()->where('action', 'USER_PIN_CHANGED')->delete();

    $this->patchJson('/api/v1/auth/me/pin', [
        'current_password' => 'AdminDemo123!',
        'new_pin' => '7777',
        'new_pin_confirmation' => '7777',
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ])->assertOk();

    expect(AuditLogModel::query()
        ->where('action', 'USER_PIN_CHANGED')
        ->where('user_id', $user->id)
        ->exists())->toBeTrue();
});
