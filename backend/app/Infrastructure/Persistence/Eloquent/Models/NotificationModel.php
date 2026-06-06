<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationModel extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'user_id',
        'role_target',
        'title',
        'message',
        'type',
        'source_type',
        'source_id',
        'status',
        'priority',
        'channels',
        'sent_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'channels' => 'array',
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class);
    }
}
