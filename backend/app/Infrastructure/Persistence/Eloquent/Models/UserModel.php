<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class UserModel extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'role_id',
        'name',
        'username',
        'email',
        'password',
        'pin_hash',
        'pin_fingerprint',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'pin_hash',
        'pin_fingerprint',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'tenant_id' => $this->tenant_id,
            'branch_id' => $this->branch_id,
            'username' => $this->username,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(RoleModel::class, 'role_id');
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfileModel::class, 'user_id');
    }

    public function branchAccess(): HasMany
    {
        return $this->hasMany(UserBranchAccessModel::class, 'user_id');
    }

    public function accessibleBranches(): BelongsToMany
    {
        return $this->belongsToMany(BranchModel::class, 'user_branch_access', 'user_id', 'branch_id')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuperAdmin(): bool
    {
        return $this->tenant_id === null;
    }
}
