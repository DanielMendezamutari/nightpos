<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductModel extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'description',
        'product_type',
        'unit',
        'track_inventory',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'track_inventory' => 'boolean',
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategoryModel::class, 'category_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPriceModel::class, 'product_id');
    }
}
