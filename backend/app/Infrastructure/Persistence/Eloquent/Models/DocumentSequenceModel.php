<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class DocumentSequenceModel extends Model
{
    protected $table = 'document_sequences';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'document_type',
        'period_key',
        'last_value',
    ];

    protected function casts(): array
    {
        return [
            'last_value' => 'integer',
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
}
