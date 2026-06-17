<?php

declare(strict_types=1);

namespace App\Domain\Product\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class ProductDomainException extends DomainException
{
    public static function emptyName(): self
    {
        return new self('El producto debe tener un nombre.');
    }

    public static function notFound(): self
    {
        return new self('Producto no encontrado.');
    }

    public static function categoryNotFound(): self
    {
        return new self('Categoría no encontrada.');
    }

    public static function negativePrice(): self
    {
        return new self('El precio no puede ser negativo.');
    }

    public static function invalidSaleMode(string $value): self
    {
        return new self(sprintf('Modalidad de venta inválida: %s.', $value));
    }

    public static function duplicateActiveSaleMode(): self
    {
        return new self('Ya existe un precio activo para esta modalidad en el mismo ámbito.');
    }

    public static function splitMustEqualPrice(): self
    {
        return new self('girl_amount + house_amount debe ser igual al precio.');
    }

    public static function splitOnlyForConAcompanante(): self
    {
        return new self('girl_amount y house_amount solo aplican a CON_ACOMPANANTE.');
    }

    public static function tenantRequired(): self
    {
        return new self('El producto debe pertenecer a una empresa.');
    }

    public static function emptyCategoryName(): self
    {
        return new self('La categoría debe tener un nombre.');
    }

    public static function inactiveProduct(): self
    {
        return new self('El producto no está activo.');
    }

    public static function priceNotFoundForMode(string $mode): self
    {
        $label = $mode === 'CON_ACOMPANANTE' ? 'CON_ACOMPANANTE' : 'SOLO_CLIENTE';

        return new self(sprintf(
            'Este producto no tiene precio configurado para la modalidad %s.',
            $label,
        ));
    }

    public static function invalidSettlementBehavior(string $value): self
    {
        return new self(sprintf('Comportamiento de liquidación inválido: %s.', $value));
    }

    public static function invalidAllocationType(string $value): self
    {
        return new self(sprintf('Tipo de asignación inválido: %s.', $value));
    }

    public static function invalidBraceletUnits(): self
    {
        return new self('Las manillas por combo deben ser al menos 1.');
    }

    public static function directSaleAllocationNotSupported(): self
    {
        return new self('Este combo debe venderse por comanda para asignar manillas.');
    }
}
