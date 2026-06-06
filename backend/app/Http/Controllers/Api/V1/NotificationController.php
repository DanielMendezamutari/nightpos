<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Notification\UseCases\GetUnreadNotificationCountUseCase;
use App\Application\Notification\UseCases\ListNotificationsUseCase;
use App\Application\Notification\UseCases\MarkAllNotificationsReadUseCase;
use App\Application\Notification\UseCases\MarkNotificationReadUseCase;
use App\Http\Controllers\Controller;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NotificationController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListNotificationsUseCase $list,
        private readonly GetUnreadNotificationCountUseCase $unreadCount,
        private readonly MarkNotificationReadUseCase $markRead,
        private readonly MarkAllNotificationsReadUseCase $markAllRead,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->presenter->present($this->list->execute((object) [
            'limit' => (int) $request->query('limit', 50),
        ]));
    }

    public function unreadCount(): JsonResponse
    {
        return $this->presenter->present($this->unreadCount->execute());
    }

    public function markRead(int $id): JsonResponse
    {
        return $this->presenter->present($this->markRead->execute((object) ['notificationId' => $id]));
    }

    public function markAllRead(): JsonResponse
    {
        return $this->presenter->present($this->markAllRead->execute());
    }
}
