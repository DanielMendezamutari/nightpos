<?php

declare(strict_types=1);

namespace App\Application\Notification\Support;

use App\Infrastructure\Persistence\Eloquent\Models\NotificationModel;

final class NotificationMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(NotificationModel $model): array
    {
        return [
            'id' => $model->id,
            'tenant_id' => $model->tenant_id,
            'branch_id' => $model->branch_id,
            'user_id' => $model->user_id,
            'role_target' => $model->role_target,
            'title' => $model->title,
            'message' => $model->message,
            'type' => $model->type,
            'source_type' => $model->source_type,
            'source_id' => $model->source_id,
            'status' => $model->status,
            'priority' => $model->priority,
            'channels' => $model->channels,
            'sent_at' => $model->sent_at?->format('Y-m-d H:i:s'),
            'read_at' => $model->read_at?->format('Y-m-d H:i:s'),
            'created_at' => $model->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
