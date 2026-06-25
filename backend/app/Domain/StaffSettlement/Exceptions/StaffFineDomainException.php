<?php



declare(strict_types=1);



namespace App\Domain\StaffSettlement\Exceptions;



use App\Shared\Domain\Exceptions\DomainException;



final class StaffFineDomainException extends DomainException

{

    public static function shiftRequired(): self

    {

        return new self('Debe haber un turno oficial abierto para registrar multas.');

    }



    public static function invalidAmount(): self

    {

        return new self('El monto de la multa debe ser mayor a cero.');

    }



    public static function reasonRequired(): self

    {

        return new self('Debe indicar el motivo de la multa.');

    }



    public static function staffNotFound(): self

    {

        return new self('Personal no encontrado o inactivo.');

    }



    public static function staffBranchMismatch(): self

    {

        return new self('El personal no pertenece a esta sucursal.');

    }



    public static function invalidStaffRole(): self

    {

        return new self('El rol indicado no coincide con el perfil del personal.');

    }



    public static function cashSessionRequired(): self

    {

        return new self('Debe abrir caja para registrar multas en su sesión.');

    }



    public static function cannotCancelApplied(): self

    {

        return new self('No se puede cancelar una multa ya aplicada.');

    }



    public static function cannotCancelCancelled(): self

    {

        return new self('La multa ya está cancelada.');

    }



    public static function cancellationReasonRequired(): self

    {

        return new self('Debe indicar el motivo de cancelación.');

    }



    public static function fineNotPending(): self

    {

        return new self('La multa no está pendiente de aplicación.');

    }



    public static function fineStaffMismatch(): self

    {

        return new self('La multa no corresponde a la persona de esta liquidación.');

    }



    public static function fineShiftMismatch(): self

    {

        return new self('La multa no corresponde al turno de esta liquidación.');

    }



    public static function fineBranchMismatch(): self

    {

        return new self('La multa no pertenece a esta sucursal.');

    }



    public static function fineAlreadyApplied(): self

    {

        return new self('La multa ya fue aplicada.');

    }



    public static function invalidAppliedFineIds(): self

    {

        return new self('Una o más multas seleccionadas no son válidas para esta liquidación.');

    }

}


