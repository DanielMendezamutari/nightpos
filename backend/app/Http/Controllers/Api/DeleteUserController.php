<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class DeleteUserController extends Controller
{
    public function __invoke(int $userId, Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        if ((int) $actor->id === $userId) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $target = User::query()->find($userId);
        if (! $target) {
            return response()->json(['message' => 'Usuario no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        if ($actor->role === 'admin') {
            $actorSiteId = (int) ($actor->active_site_id ?: $actor->site_id ?: 0);
            if (! $actorSiteId || (int) $target->site_id !== $actorSiteId) {
                return response()->json(['message' => 'Admin solo puede eliminar usuarios de su sucursal activa.'], Response::HTTP_FORBIDDEN);
            }
            if (! in_array($target->role, ['cashier', 'waiter', 'manager'], true)) {
                return response()->json(['message' => 'Admin no puede eliminar este rol.'], Response::HTTP_FORBIDDEN);
            }
        }

        DB::table('user_site_accesses')->where('user_id', $target->id)->delete();
        $target->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
