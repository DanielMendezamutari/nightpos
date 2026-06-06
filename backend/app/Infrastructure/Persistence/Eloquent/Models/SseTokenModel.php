<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class SseTokenModel extends Model
{
    protected $table = 'sse_tokens';

    public $timestamps = false;

    protected $fillable = [
        'token',
        'tenant_id',
        'branch_id',
        'user_id',
        'role_scope',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
