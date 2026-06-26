<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class TenantOperationChecklistItemModel extends Model
{
    protected $table = 'tenant_operation_checklist_items';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'item_key',
        'label',
        'completed',
        'completed_at',
        'completed_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'completed_at' => 'datetime',
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

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'completed_by_user_id');
    }
}
