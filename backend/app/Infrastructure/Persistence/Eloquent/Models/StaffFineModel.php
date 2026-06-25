<?php



declare(strict_types=1);



namespace App\Infrastructure\Persistence\Eloquent\Models;



use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;



final class StaffFineModel extends Model

{

    protected $table = 'staff_fines';



    protected $fillable = [

        'tenant_id',

        'branch_id',

        'official_shift_id',

        'cash_session_id',

        'staff_user_id',

        'staff_role',

        'amount',

        'reason',

        'notes',

        'status',

        'created_by_user_id',

        'applied_settlement_id',

        'applied_at',

        'applied_by_user_id',

        'cancelled_at',

        'cancelled_by_user_id',

        'cancellation_reason',

    ];



    protected function casts(): array

    {

        return [

            'amount' => 'decimal:2',

            'applied_at' => 'datetime',

            'cancelled_at' => 'datetime',

        ];

    }



    public function staffUser(): BelongsTo

    {

        return $this->belongsTo(UserModel::class, 'staff_user_id');

    }



    public function createdBy(): BelongsTo

    {

        return $this->belongsTo(UserModel::class, 'created_by_user_id');

    }



    public function appliedSettlement(): BelongsTo

    {

        return $this->belongsTo(StaffSettlementModel::class, 'applied_settlement_id');

    }



    public function appliedBy(): BelongsTo

    {

        return $this->belongsTo(UserModel::class, 'applied_by_user_id');

    }

}


