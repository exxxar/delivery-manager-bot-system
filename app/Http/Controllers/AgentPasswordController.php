<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgentPasswordController extends Controller
{
    /**
     * Устанавливает агенту временный пароль 12345678.
     * Доступен только администраторам и супер-администраторам (role >= 3).
     */
    public function reset(Request $request, int $agentId)
    {
        $actor = $request->user();

        if (!$actor) {
            return response()->json(['message' => 'Необходима авторизация'], 401);
        }

        if ((int) $actor->role < 3) {
            return response()->json(['message' => 'Недостаточно прав для сброса пароля'], 403);
        }

        $agent = Agent::query()->findOrFail($agentId);

        if (!$agent->user_id) {
            return response()->json([
                'message' => 'У этой записи администратора не привязан пользовательский аккаунт',
            ], 422);
        }

        $user = User::query()->findOrFail($agent->user_id);
        $user->password = Hash::make('12345678');
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Пароль администратора успешно сброшен.',
        ]);
    }
}
