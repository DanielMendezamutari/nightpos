<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Printing\UseCases\GetPrintDeviceMeUseCase;
use App\Application\Printing\UseCases\GetPrintSettingsUseCase;
use App\Application\Printing\UseCases\ListPrintDevicesUseCase;
use App\Application\Printing\UseCases\PrintDeviceHeartbeatUseCase;
use App\Application\Printing\UseCases\RegisterPrintDeviceUseCase;
use App\Application\Printing\UseCases\RotatePrintDeviceKeyUseCase;
use App\Application\Printing\UseCases\UpdatePrintDeviceUseCase;
use App\Application\Printing\UseCases\UpdatePrintSettingsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Printing\PrintDeviceHeartbeatRequest;
use App\Http\Requests\Api\V1\Printing\RegisterPrintDeviceRequest;
use App\Http\Requests\Api\V1\Printing\UpdatePrintDeviceRequest;
use App\Http\Requests\Api\V1\Printing\UpdatePrintSettingsRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class PrintDeviceController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly RegisterPrintDeviceUseCase $registerDevice,
        private readonly ListPrintDevicesUseCase $listDevices,
        private readonly RotatePrintDeviceKeyUseCase $rotateKey,
        private readonly GetPrintDeviceMeUseCase $getMe,
        private readonly PrintDeviceHeartbeatUseCase $heartbeat,
        private readonly GetPrintSettingsUseCase $getSettings,
        private readonly UpdatePrintSettingsUseCase $updateSettings,
        private readonly UpdatePrintDeviceUseCase $updateDevice,
    ) {
    }

    public function register(RegisterPrintDeviceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->registerDevice->execute((object) [
            'name' => $validated['name'],
            'paperWidthMm' => $validated['paper_width_mm'] ?? 80,
            'autoPrintOrder' => $validated['auto_print_order'] ?? true,
        ]), 201);
    }

    public function index(): JsonResponse
    {
        return $this->presenter->present($this->listDevices->execute());
    }

    public function settings(): JsonResponse
    {
        return $this->presenter->present($this->getSettings->execute());
    }

    public function updateSettings(UpdatePrintSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateSettings->execute((object) [
            'autoPrintOrderCommand' => $validated['auto_print_order_command'] ?? null,
        ]));
    }

    public function update(int $id, UpdatePrintDeviceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->updateDevice->execute((object) array_merge(
            ['deviceId' => $id],
            array_filter([
                'name' => $validated['name'] ?? null,
                'enabled' => $validated['enabled'] ?? null,
                'autoPrintOrder' => $validated['auto_print_order'] ?? null,
                'paperWidthMm' => $validated['paper_width_mm'] ?? null,
                'status' => $validated['status'] ?? null,
            ], static fn ($value) => $value !== null),
        )));
    }

    public function rotateKey(int $id): JsonResponse
    {
        return $this->presenter->present($this->rotateKey->execute((object) [
            'deviceId' => $id,
        ]));
    }

    public function me(): JsonResponse
    {
        return $this->presenter->present($this->getMe->execute());
    }

    public function heartbeat(PrintDeviceHeartbeatRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->heartbeat->execute((object) [
            'printerName' => $validated['printer_name'] ?? null,
            'agentVersion' => $validated['agent_version'] ?? null,
            'lastError' => $validated['last_error'] ?? null,
        ]));
    }
}
