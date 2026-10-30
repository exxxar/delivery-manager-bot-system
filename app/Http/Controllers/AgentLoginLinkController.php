<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\AgentLoginLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AgentLoginLinkController extends Controller
{
    /**
     * Создать одноразовую ссылку для входа в аккаунт, связанный с агентом.
     * Вызывать из защищённого API под auth:sanctum.
     */
    public function store(Request $request, int $agentId): JsonResponse
    {
        $actor = $request->user();

        // Подстройте порог под вашу систему ролей.
        abort_unless($actor && (int) $actor->role >= 3, 403, 'Недостаточно прав.');

        $agent = Agent::query()->findOrFail($agentId);
        $targetUser = $agent->user_id
            ? \App\Models\User::query()->findOrFail($agent->user_id)
            : null;

        abort_unless($targetUser, 422, 'У агента не привязан пользовательский аккаунт.');
       /* abort_if((int) $targetUser->id === (int) $actor->id, 422, 'Нельзя создавать ссылку для собственного аккаунта.');*/

        // Отзываем ранее созданные неиспользованные ссылки для этого пользователя.
        AgentLoginLink::query()
            ->where('user_id', $targetUser->id)
            ->whereNull('used_at')
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        $plainToken = Str::random(80);

        AgentLoginLink::query()->create([
            'user_id' => $targetUser->id,
            'created_by' => $actor->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes(15),
        ]);

        $url = route('agent.magic-login.show', ['token' => $plainToken]);

        return response()->json([
            'message' => 'Одноразовая ссылка создана.',
            'link' => $url,
            'expires_in' => 900,
        ]);
    }

    /**
     * Отозвать все неиспользованные ссылки для указанного агента.
     */
    public function destroy(Request $request, int $agentId): JsonResponse
    {
        $actor = $request->user();
        abort_unless($actor && (int) $actor->role >= 3, 403, 'Недостаточно прав.');

        $agent = Agent::query()->findOrFail($agentId);
        abort_unless($agent->user_id, 422, 'У агента не привязан пользовательский аккаунт.');

        AgentLoginLink::query()
            ->where('user_id', $agent->user_id)
            ->whereNull('used_at')
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return response()->json(['message' => 'Активные ссылки отозваны.']);
    }

    /**
     * GET только показывает подтверждение. Не потребляем токен на GET,
     * поскольку почтовые/мессенджерные сканеры могут автоматически открывать URL.
     */
    public function show(string $token): View
    {
        $link = AgentLoginLink::query()
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        return view('auth.magic-login', [
            'token' => $token,
            'valid' => (bool) $link,
            'userName' => $link?->user?->name,
        ]);
    }

    /**
     * POST атомарно потребляет токен и создаёт обычную web-сессию Laravel.
     */
    public function consume(Request $request, string $token): RedirectResponse
    {
        $link = DB::transaction(function () use ($token) {
            $record = AgentLoginLink::query()
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->first();

            if (
                !$record ||
                $record->used_at ||
                $record->revoked_at ||
                $record->expires_at->isPast()
            ) {
                return null;
            }

            $record->forceFill(['used_at' => now()])->save();

            return $record->load('user');
        });

        if (!$link || !$link->user) {
            return redirect(config('app.frontend_url', '/login') . '?magic_login=invalid');
        }

        Auth::guard('web')->login($link->user);
        $request->session()->regenerate();

        return redirect(config('app.frontend_url', '/') . '?magic_login=success');
    }
}
