<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Turnos de trabajo por dia de la semana (plantilla semanal).
 *
 * - Varios turnos el mismo dia (ej. 3×8 h o 2×12 h).
 * - Fecha operativa para reportes/caja: la del **inicio** del turno (dia civil del opens_at).
 * - crosses_midnight: el cierre cae en uno o mas dias calendario despues del inicio.
 */
class SiteWorkShift extends Model
{
    protected $table = 'site_work_shifts';

    protected $fillable = [
        'site_id',
        'weekday',
        'slot_index',
        'label',
        'opens_at',
        'closes_at',
        'crosses_midnight',
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
            'slot_index' => 'integer',
            'crosses_midnight' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
