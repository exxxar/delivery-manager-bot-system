<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Facades\UserLog;
use App\Models\Supplier;
use App\Models\SupplierInviteToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SupplierInviteController extends Controller
{
    /**
     * Получить актуальную ссылку (только для суперадминов)
     */
    public function getCurrent(Request $request)
    {
        $botUser = $request->botUser;

        if (!$botUser || $botUser->role < RoleEnum::SUPERADMIN->value) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $token = SupplierInviteToken::getCurrentActive();

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
     * Сгенерировать новую ссылку
     */
    public function generate(Request $request)
    {
        $botUser = $request->botUser;

        if (!$botUser || $botUser->role < RoleEnum::SUPERADMIN->value) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $token = SupplierInviteToken::generate($botUser->id, 168);

        UserLog::logSuper(
            "#генерация_ссылки_поставщика\nСгенерирована новая ссылка приглашения поставщика\n" .
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
     * Отозвать текущую ссылку
     */
    public function revoke(Request $request)
    {
        $botUser = $request->botUser;

        if (!$botUser || $botUser->role < RoleEnum::SUPERADMIN->value) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $token = SupplierInviteToken::getCurrentActive();

        if (!$token) {
            return response()->json(['message' => 'Нет активной ссылки для отзыва'], 404);
        }

        $token->update(['is_active' => false]);

        UserLog::logSuper(
            "#отзыв_ссылки_поставщика\nСсылка приглашения поставщика отозвана\n" .
            $botUser->getUserTelegramLink()
        );

        return response()->json([
            'message' => 'Ссылка отозвана',
            'has_link' => false,
        ]);
    }

    /**
     * ПУБЛИЧНЫЙ: Проверить валидность токена
     */
    public function validateToken(string $token)
    {
        $inviteToken = SupplierInviteToken::where('token', $token)->first();

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



    public function register(Request $request, string $token)
    {
        $inviteToken = SupplierInviteToken::where('token', $token)->first();

        if (!$inviteToken || !$inviteToken->isValid()) {
            return response()->json([
                'message' => 'Ссылка недействительна. Обратитесь к администратору для получения новой.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50|unique:suppliers,phone',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'birthday' => 'nullable|date',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Введите наименование поставщика',
            'phone.required' => 'Введите телефон',
            'phone.unique' => 'Поставщик с таким телефоном уже зарегистрирован',
            'email.email' => 'Введите корректный email',
            'password.required' => 'Введите пароль',
            'password.min' => 'Пароль должен содержать минимум 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
            'birthday.date' => 'Некорректная дата рождения',
        ]);

        return DB::transaction(function () use ($validated, $inviteToken, $request) {
            // 1. Создаём пользователя
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'],
                'role' => RoleEnum::SUPPLIER->value,
                'telegram_chat_id' => null,
                'password' => Hash::make($validated['password']),
                'registration_at' => now(),
            ]);

            // 2. Создаём поставщика
            $supplier = Supplier::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'description' => $validated['description'] ?? null,
                'birthday' => $validated['birthday'] ?? null,
                'percent' => 8,
            ]);

            // 3. Помечаем токен как использованный
            $inviteToken->markAsUsed($user->id);

            // 🔹 4. АВТОЛОГИН: авторизуем пользователя сразу после регистрации
            Auth::login($user);
            $request->session()->regenerate();

            UserLog::logSuper(
                "#регистрация_поставщика\n" .
                "Зарегистрирован новый поставщик:\n" .
                "• <b>Наименование:</b> {$validated['name']}\n" .
                "• <b>Телефон:</b> {$validated['phone']}\n" .
                "• <b>Email:</b> " . ($validated['email'] ?? '-') . "\n" .
                "• <b>User ID:</b> {$user->id}\n" .
                "• <b>Supplier ID:</b> {$supplier->id}\n" .
                "• <b>Автовход выполнен</b>"
            );

            return response()->json([
                'message' => 'Регистрация прошла успешно! Выполняется вход в систему...',
                'user_id' => $user->id,
                'supplier_id' => $supplier->id,
                'auto_login' => true, // 🔹 флаг для фронта
            ]);
        });
    }
}
