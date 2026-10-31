<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, int $agentId)
    {
        $agent = Agent::query()->findOrFail($agentId);

        $target = User::query()->find($agent->user_id);

        if (!$target || (int) $target->role !== RoleEnum::AGENT->value) {
            return response()->json([
                'message' => 'Учётная запись агента не найдена',
            ], 404);
        }

        if ($target->blocked_at) {
            return response()->json([
                'message' => 'Нельзя войти от имени заблокированного пользователя',
            ], 403);
        }

        Auth::guard('web')->login($target);

        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'user' => $target,
        ]);
    }
}
