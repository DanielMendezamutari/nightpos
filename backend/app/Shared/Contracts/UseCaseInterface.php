<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

/**
 * Application port: single-purpose use case entry point.
 * Implementations live in App\Application\{Context}\UseCases.
 */
interface UseCaseInterface
{
    /**
     * @param  object|null  $input  Context-specific DTO
     * @return mixed Context-specific output DTO or domain result
     */
    public function execute(?object $input = null): mixed;
}
