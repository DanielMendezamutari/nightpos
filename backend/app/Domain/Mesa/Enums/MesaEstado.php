<?php

declare(strict_types=1);

namespace App\Domain\Mesa\Enums;

enum MesaEstado: string
{
    case LIBRE = 'LIBRE';
    case OCUPADA = 'OCUPADA';
    case PRECUENTA = 'PRECUENTA';
    case RESERVADA = 'RESERVADA';
    case BLOQUEADA = 'BLOQUEADA';

    public function label(): string
    {
        return match ($this) {
            self::LIBRE => 'Libre',
            self::OCUPADA => 'Ocupada',
            self::PRECUENTA => 'Pre-cuenta Solicitada',
            self::RESERVADA => 'Reservada',
            self::BLOQUEADA => 'Bloqueada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LIBRE => 'success',      // Verde RestoTech
            self::OCUPADA => 'error',       // Rojo RestoTech
            self::PRECUENTA => 'warning',   // Amarillo/Ámbar RestoTech
            self::RESERVADA => 'info',      // Azul RestoTech
            self::BLOQUEADA => 'secondary', // Gris
        };
    }
}