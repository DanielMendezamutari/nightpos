<?php

declare(strict_types=1);

namespace App\Domain\StaffSettlement\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class StaffSettlementDomainException extends DomainException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 422,
    ) {
        parent::__construct($message);
    }

    public static function shiftRequired(): self
    {
        return new self('Debe haber un turno oficial para liquidar.');
    }

    public static function notFound(): self
    {
        return new self('Liquidación no encontrada.');
    }

    public static function alreadyPaid(): self
    {
        return new self('La liquidación ya está pagada.');
    }

    public static function cannotPayCancelled(): self
    {
        return new self('No se puede pagar una liquidación cancelada.');
    }

    public static function accessDenied(): self
    {
        return new self('No tiene permiso para ver esta liquidación.');
    }

    public static function cashRequiredForPayment(): self
    {
        return new self('Debe abrir caja para pagar esta liquidación.');
    }

    public static function cashRequiredForGeneration(): self
    {
        return new self('Debe abrir caja para generar liquidaciones de su sesión.');
    }

    public static function cannotPayOtherCashSession(): self
    {
        return new self('No puede pagar liquidaciones de otra caja.');
    }

    public static function expenseReasonRequired(): self
    {
        return new self('No hay motivo de egreso configurado para registrar el pago en caja.');
    }

    public static function paymentMethodRequired(): self
    {
        return new self('Debe indicar el método de pago para registrar la liquidación.');
    }

    public static function cannotModifyPaidSettlement(): self
    {
        return new self('No se puede modificar una liquidación pagada.');
    }

    public static function manualDiscountReasonRequired(): self
    {
        return new self('Debe indicar el motivo del descuento manual.');
    }

    public static function manualDiscountExceedsAvailable(): self
    {
        return new self('El descuento supera el saldo disponible.');
    }

    public static function invalidDiscountMode(): self
    {
        return new self('El tipo de descuento debe ser PERCENT o AMOUNT.');
    }

    public static function invalidDiscountValue(): self
    {
        return new self('El valor del descuento debe ser mayor a cero.');
    }

    public static function manualDiscountNotFound(): self
    {
        return new self('No hay descuento manual para cancelar.');
    }

    public static function settlementNotPaid(): self
    {
        return new self('La liquidación debe estar pagada para imprimir el comprobante.');
    }

    public static function ticketNumberConflict(): self
    {
        return new self('No se pudo asignar el número de comprobante. Intente nuevamente.', 409);
    }
}
