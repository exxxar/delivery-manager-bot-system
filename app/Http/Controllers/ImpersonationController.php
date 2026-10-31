<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Переключиться на учётную запись агента.
     */
    public function start(Request $request, int $agentId)
    {
        $actor = Auth::user();

        if (!$actor) {
            return response()->json([
                'message' => 'Необходима авторизация',
            ], 401);
        }

        if ($request->session()->has('impersonator_id')) {
            return response()->json([
                'message' => 'Режим переключения уже активен',
            ], 409);
        }

        // Agent использует SoftDeletes — удалённые агенты не найдутся.
        $agent = Agent::query()->findOrFail($agentId);

        $target = User::query()->find($agent->user_id);

        if (!$target || (int) $target->role !== RoleEnum::AGENT->value) {
            return response()->json([
                'message' => 'Учётная запись агента не найдена',
            ], 404);
        }

        if ((int) $target->id === (int) $actor->id) {
            return response()->json([
                'message' => 'Нельзя переключиться на собственную учётную запись',
            ], 422);
        }

        if ($target->blocked_at) {
            return response()->json([
                'message' => 'Нельзя войти от имени заблокированного пользователя',
            ], 403);
        }

        // Сохраняем исходного администратора в сессии.
        $request->session()->put([
            'impersonator_id' => $actor->id,
            'impersonation_started_at' => now()->toIso8601String(),
        ]);

        Auth::guard('api')->login($target);

        $request->session()->regenerate();

        // Защита от фиксации сессии.
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user' => $target->fresh(),
        ]);
    }

    /**
     * Вернуться к исходному пользователю.
     */
    public function stop(Request $request)
    {
        $actorId = $request->session()->get('impersonator_id');

        if (!$actorId) {
            return response()->json([
                'message' => 'Режим переключения не активен',
            ], 409);
        }

        $actor = User::query()->find($actorId);

        if (!$actor) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'message' => 'Исходная учётная запись недоступна. Войдите заново.',
            ], 401);
        }

        Auth::guard('api')->login($actor);

        $request->session()->regenerate();

        $request->session()->forget([
            'impersonator_id',
            'impersonation_started_at',
        ]);

        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user' => $actor->fresh(),
        ]);
    }

    /**
     * Проверить, активен ли режим переключения.
     */
    public function status(Request $request)
    {
        $actorId = $request->session()->get('impersonator_id');

        if (!$actorId) {
            return response()->json([
                'active' => false,
            ]);
        }

        $actor = User::query()->find($actorId);

        return response()->json([
            'active' => true,
            'started_at' => $request->session()->get(
                'impersonation_started_at'
            ),
            'original_user' => $actor ? [
                'id' => $actor->id,
                'name' => $actor->name,
                'email' => $actor->email,
            ] : null,
        ]);
    }
}
