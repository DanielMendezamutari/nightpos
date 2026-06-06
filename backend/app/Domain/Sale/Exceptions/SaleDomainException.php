<?php

declare(strict_types=1);

namespace App\Domain\Sale\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class SaleDomainException extends DomainException
{
    public static function cashSessionRequired(): self
    {
        return new self('Debe tener una caja abierta para cobrar.');
    }

    public static function orderNotChargeable(): self
    {
        return new self('La comanda no puede cobrarse en su estado actual.');
    }

    public static function orderEmpty(): self
    {
        return new self('La comanda no tiene productos para cobrar.');
    }

    public static function orderAlreadyBilled(): self
    {
        return new self('La comanda ya fue cobrada.');
    }

    public static function paymentMismatch(): self
    {
        return new self('La suma de pagos no coincide con el total de la comanda.');
    }

    public static function invalidPaymentMethod(string $method): self
    {
        return new self(sprintf('Método de pago inválido: %s.', $method));
    }

    public static function girlRequiredOnItems(): self
    {
        return new self('Hay ítems CON_ACOMPANANTE sin chica asignada.');
    }

    public static function invalidPaymentAmount(): self
    {
        return new self('Cada pago debe tener un monto mayor a cero.');
    }

    public static function directSaleItemsRequired(): self
    {
        return new self('La venta directa debe tener al menos un ítem.');
    }

    public static function directSaleGirlRequired(): self
    {
        return new self('Hay ítems CON_ACOMPANANTE sin chica asignada.');
    }
}
