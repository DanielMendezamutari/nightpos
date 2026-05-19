<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
        'legal_document_type',
        'legal_document_number',
        'legal_name',
        'branch_address',
        'branch_phone',
        'branch_email',
        'economic_activity',
        'authorization_date',
        'authorization_resolution',
        'manager_document_type',
        'manager_document_number',
        'manager_full_name',
        'currency_code',
        'ticket_series_start',
        'boleta_series_start',
        'factura_series_start',
        'logo_path',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'authorization_date' => 'date',
            'ticket_series_start' => 'integer',
            'boleta_series_start' => 'integer',
            'factura_series_start' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'site_id');
    }

    public function workShifts(): HasMany
    {
        return $this->hasMany(SiteWorkShift::class, 'site_id');
    }
}
