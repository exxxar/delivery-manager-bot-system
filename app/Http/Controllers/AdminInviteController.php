<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Facades\UserLog;
use App\Models\AdminInviteToken;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminInviteController extends Controller
{
    /**
     * Получить актуальную ссылку (только для суперадминов)
     */
    public function getCurrent(Request $request)
    {
        $botUser = $request->botUser;

        if ($botUser->role < RoleEnum::SUPERADMIN->value) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $token = AdminInviteToken::getCurrentActive();

        if (!$token) {
            return response()->json([
                'has_link' => false,
                'link' => null,
                'token' => null,
            ]);
        }

        return response()->json([
            'has_link' => true,
            'link' => $token->getRegistrationUrl(),
            'token' => $token->token,
            'created_at' => $token->created_at,
            'expires_at' => $token->expires_at,
        ]);
    }

    /**
     * Сгенерировать новую ссылку (старая будет деактивирована)
     */
    public function generate(Request $request)
    {
        $botUser = $request->botUser;

        if ($botUser->role < RoleEnum::SUPERADMIN->value) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $token = AdminInviteToken::generate($botUser->id, 168); // 7 дней

        UserLog::logSuper(
            "#генерация_ссылки_админа\nСгенерирована новая ссылка приглашения администратора\n" .
            $botUser->getUserTelegramLink()
        );

        return response()->json([
            'message' => 'Новая ссылка сгенерирована. Старая ссылка деактивирована.',
            'has_link' => true,
            'link' => $token->getRegistrationUrl(),
            'token' => $token->token,
            'created_at' => $token->created_at,
            'expires_at' => $token->expires_at,
        ]);
    }

    /**
     * Отозвать (деактивировать) текущую ссылку
     */
    public function revoke(Request $request)
    {
        $botUser = $request->botUser;

        if ($botUser->role < RoleEnum::SUPERADMIN->value) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $token = AdminInviteToken::getCurrentActive();

        if (!$token) {
            return response()->json(['message' => 'Нет активной ссылки для отзыва'], 404);
        }

        $token->update(['is_active' => false]);

        UserLog::logSuper(
            "#отзыв_ссылки_админа\nСсылка приглашения администратора отозвана\n" .
            $botUser->getUserTelegramLink()
        );

        return response()->json([
            'message' => 'Ссылка отозвана',
            'has_link' => false,
        ]);
    }

    /**
     * ПУБЛИЧНЫЙ: Проверить валидность токена (для показа/скрытия формы)
     */
    public function validateToken(string $token)
    {
        $inviteToken = AdminInviteToken::where('token', $token)->first();

        if (!$inviteToken) {
            return response()->json([
                'valid' => false,
                'reason' => 'Ссылка не существует или некорректна',
            ]);
        }

        if (!$inviteToken->is_active) {
            return response()->json([
                'valid' => false,
                'reason' => 'Ссылка была отозвана администратором',
            ]);
        }

        if ($inviteToken->isUsed()) {
            return response()->json([
                'valid' => false,
                'reason' => 'По этой ссылке уже была выполнена регистрация',
            ]);
        }

        if ($inviteToken->isExpired()) {
            return response()->json([
                'valid' => false,
                'reason' => 'Срок действия ссылки истёк',
            ]);
        }

        return response()->json([
            'valid' => true,
            'expires_at' => $inviteToken->expires_at,
        ]);
    }

    /**
     * ПУБЛИЧНЫЙ: Регистрация администратора по токену
     */
    public function register(Request $request, string $token)
    {
        $inviteToken = AdminInviteToken::where('token', $token)->first();

        if (!$inviteToken || !$inviteToken->isValid()) {
            return response()->json([
                'message' => 'Ссылка недействительна. Обратитесь к администратору для получения новой.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:50',
            'region' => 'required|string|max:255',

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed', 

            ],
            'password_confirmation' => 'required|string',
        ], [
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            'password.regex' => 'Пароль должен содержать заглавные, строчные буквы и цифры',
            'password.confirmed' => 'Пароли не совпадают',
            'email.unique' => 'Этот email уже зарегистрирован',
        ]);

        return DB::transaction(function () use ($validated, $inviteToken) {
            // 1. Создаём пользователя с хэшированным паролем
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role' => RoleEnum::AGENT->value,
                'telegram_chat_id' => null,
                'password' => Hash::make($validated['password']),
                'registration_at' => now(),
            ]);

            // 2. Создаём Agent
            $agent = Agent::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'region' => $validated['region'],
                'in_learning' => false,
                'is_test' => false,
            ]);

            // 3. Помечаем токен как использованный
            $inviteToken->markAsUsed($user->id);

            UserLog::logSuper(
                "#регистрация_админа\n" .
                "Зарегистрирован новый администратор:\n" .
                "• <b>Имя:</b> {$validated['name']}\n" .
                "• <b>Email:</b> {$validated['email']}\n" .
                "• <b>Телефон:</b> {$validated['phone']}\n" .
                "• <b>Регион:</b> {$validated['region']}\n" .
                "• <b>User ID:</b> {$user->id}\n" .
                "• <b>Agent ID:</b> {$agent->id}"
            );

            return response()->json([
                'message' => 'Регистрация прошла успешно! Теперь вы можете войти в систему.',
                'user_id' => $user->id,
            ]);
        });
    }
}
