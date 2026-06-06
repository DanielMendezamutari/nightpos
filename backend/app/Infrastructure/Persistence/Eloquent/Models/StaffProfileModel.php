<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfileModel extends Model
{
    protected $table = 'staff_profiles';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'user_id',
        'staff_role',
        'waiter_commission_percent',
        'can_receive_girl_commissions',
        'cleaning_base_amount',
        'cleaning_room_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'waiter_commission_percent' => 'decimal:2',
            'can_receive_girl_commissions' => 'boolean',
            'cleaning_base_amount' => 'decimal:2',
            'cleaning_room_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'user_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantModel::class, 'tenant_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(BranchModel::class, 'branch_id');
    }
}
