<?php

declare(strict_types=1);

namespace App\Domain\Order\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class OrderDomainException extends DomainException
{
    public static function notFound(): self
    {
        return new self('Comanda no encontrada.');
    }

    public static function invalidStatus(string $value): self
    {
        return new self(sprintf('Estado de comanda inválido: %s.', $value));
    }

    public static function notModifiable(): self
    {
        return new self('La comanda no permite modificaciones en su estado actual.');
    }

    public static function girlRequiredForSaleMode(): self
    {
        return new self('CON_ACOMPANANTE requiere asignar una chica antes de continuar.');
    }

    public static function branchRequired(): self
    {
        return new self('Debe indicar la sucursal en el contexto.');
    }

    public static function invalidQuantity(): self
    {
        return new self('La cantidad debe ser mayor a cero.');
    }

    public static function cannotSendToBar(): self
    {
        return new self('La comanda no puede enviarse a barra en su estado actual.');
    }

    public static function waiterRequired(): self
    {
        return new self('Debe seleccionar un garzón para abrir la comanda.');
    }

    public static function invalidTableLabel(): self
    {
        return new self('Indique mesa, ambiente catalogado o etiqueta de servicio.');
    }

    public static function itemNotFound(): self
    {
        return new self('Ítem de comanda no encontrado.');
    }

    public static function itemNotRemovable(): self
    {
        return new self('Este ítem no puede eliminarse en su estado actual.');
    }

    public static function cancelReasonRequired(): self
    {
        return new self('Debe indicar el motivo para cancelar la línea.');
    }

    public static function itemAlreadyCancelled(): self
    {
        return new self('La línea ya está cancelada.');
    }

    public static function onlyGirlChangeAllowed(): self
    {
        return new self('En este estado solo puede cambiar la chica asignada.');
    }

    public static function changeReasonRequired(): self
    {
        return new self('Debe indicar el motivo para cambiar esta línea enviada a barra.');
    }
}
