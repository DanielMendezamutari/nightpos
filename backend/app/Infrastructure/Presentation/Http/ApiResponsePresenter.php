<?php

declare(strict_types=1);

namespace App\Infrastructure\Presentation\Http;

use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use App\Shared\Application\DTOs\OperationResult;
use Illuminate\Http\JsonResponse;

final class ApiResponsePresenter implements ApiResponsePresenterInterface
{
    public function present(OperationResult $result, int $successStatus = 200): JsonResponse
    {
        $status = $result->success ? $successStatus : 422;

        if (! $result->success && $result->errors === [] && str_contains(strtolower($result->message), 'credenciales')) {
            $status = 401;
        }

        return response()->json([
            'success' => $result->success,
            'message' => $result->message,
            'data' => $result->data ?? (object) [],
            'errors' => $result->errors !== [] ? $result->errors : (object) [],
        ], $status);
    }
}
