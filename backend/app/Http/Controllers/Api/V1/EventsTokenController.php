<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\SSE\UseCases\IssueOperationalEventTokenUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EventsTokenController
{
    public function __construct(
        private readonly IssueOperationalEventTokenUseCase $issueToken
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $result = $this->issueToken->execute();

        return response()->json([
            'data' => $result->data,
        ], $result->success ? 200 : ($result->code ?? 422));
    }
}
