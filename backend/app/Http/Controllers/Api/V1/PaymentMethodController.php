<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Settings\UseCases\CreatePaymentMethodUseCase;
use App\Application\Settings\UseCases\ListPaymentMethodsUseCase;
use App\Application\Settings\UseCases\UpdatePaymentMethodUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settings\CreatePaymentMethodRequest;
use App\Http\Requests\Api\V1\Settings\UpdatePaymentMethodRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class PaymentMethodController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListPaymentMethodsUseCase $listMethods,
        private readonly CreatePaymentMethodUseCase $createMethod,
        private readonly UpdatePaymentMethodUseCase $updateMethod,
    ) {
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listMethods->execute());
    }

    public function store(CreatePaymentMethodRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->createMethod->execute((object) [
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'enabled' => $validated['enabled'] ?? true,
            'requiresReference' => $validated['requires_reference'] ?? false,
            'branchScoped' => $validated['branch_scoped'] ?? false,
        ]), 201);
    }

    public function update(int $id, UpdatePaymentMethodRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateMethod->execute((object) [
            'id' => $id,
            'name' => $validated['name'],
            'enabled' => $validated['enabled'],
            'requiresReference' => $validated['requires_reference'] ?? false,
        ]));
    }
}
