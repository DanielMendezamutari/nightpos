<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Audit\UseCases\ListAuditLogsUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class AuditLogController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListAuditLogsUseCase $listLogs,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listLogs->execute());
    }
}
