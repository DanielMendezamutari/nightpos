<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Http\Contracts;

use App\Shared\Application\DTOs\OperationResult;

/**
 * Serializes application results to JSON API format.
 * Controllers delegate here — no business logic.
 */
interface ApiResponsePresenterInterface
{
    public function present(OperationResult $result, int $successStatus = 200): mixed;
}
