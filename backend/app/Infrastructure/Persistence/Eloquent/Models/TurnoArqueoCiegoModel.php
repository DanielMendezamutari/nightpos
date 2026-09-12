<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TurnoArqueoCiegoModel extends Model
{
    protected $table = 'turnos_arqueos_ciegos';

    protected $fillable = [
        'turno_id',
        'usuario_id',
        'b200',
        'b100',
        'b50',
        'b20',
        'b10',
        'm5',
        'm2',
        'm1',
        'm050',
        'm020',
        'm010',
        'total_billetes',
        'total_monedas',
        'total_declarado',
        'total_esperado',
        'diferencia',
        'resultado',
        'notas',
    ];

    protected $casts = [
        'total_billetes' => 'decimal:2',
        'total_monedas' => 'decimal:2',
        'total_declarado' => 'decimal:2',
        'total_esperado' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'b200' => 'integer',
        'b100' => 'integer',
        'b50' => 'integer',
        'b20' => 'integer',
        'b10' => 'integer',
        'm5' => 'integer',
        'm2' => 'integer',
        'm1' => 'integer',
        'm050' => 'integer',
        'm020' => 'integer',
        'm010' => 'integer',
    ];

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'usuario_id');
    }
}
