<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class UserModel extends Authenticatable implements JWTSubject
{
    use HasUuids;

    protected $table = 'users';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'username',
        'email',
        'password',
        'pin_hash',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'pin_hash',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'tenant_id' => $this->tenant_id,
            'branch_id' => $this->branch_id,
            'role' => $this->role,
            'username' => $this->username,
        ];
    }
}
