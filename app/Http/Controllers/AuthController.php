<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Facades\UserLog;
use App\Models\Agent;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                Password::defaults(),
            ],
        ], [
            'current_password.required' => 'Введите текущий пароль',
            'password.required' => 'Введите новый пароль',
            'password.min' => 'Пароль должен содержать минимум 6 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'Необходима авторизация',
            ], 401);
        }

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [
                    'Текущий пароль указан неверно',
                ],
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        UserLog::log(
            'Пользователь изменил пароль',
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Пароль успешно изменён',
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => [
                'required',
                'string',
                'max:30',
                'unique:agents,phone',
                'unique:suppliers,phone',
            ],
            'role' => [
                'required',
                Rule::in([
                    RoleEnum::USER->value,
                    RoleEnum::AGENT->value,
                    RoleEnum::SUPPLIER->value,
                ]),
            ],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'fio_from_telegram' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => (int) $validated['role'],
            ]);

            if ((int) $validated['role'] === RoleEnum::AGENT->value) {
                Agent::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'region' => '',
                ]);
            }

            if ((int) $validated['role'] === RoleEnum::SUPPLIER->value) {
                Supplier::create([
                    'user_id' => $user->id,
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'percent' => 0,
                ]);
            }

            return $user;
        });

        // Сразу авторизуем пользователя
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'message' => 'Регистрация прошла успешно',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        // Ваш дебаг-блок оставлен без изменений, как вы и просили
        if (env("APP_DEBUG")) {
            $user = User::query()->first();
            $user->role = RoleEnum::SUPERADMIN->value;
            $user->base_role = RoleEnum::SUPERADMIN->value;

            Auth::login($user);
            UserLog::log("Вход в систему");
            $request->session()->regenerate();
        } else {
            $request->validate([
                'login' => 'required|string',
                'password' => 'required|string'
            ]);

            // Auth::attempt ПРОВЕРЯЕТ пароль и АВТОМАТИЧЕСКИ авторизует пользователя при успехе.
            // Делать после этого дополнительный запрос к БД и повторный Auth::login() не нужно.
            if (!Auth::attempt([
                'email' => $request->login, // ⚠️ Если в вашей таблице поле называется 'login', замените 'email' на 'login'
                'password' => $request->password
            ])) {
                // Исправлена опечатка и добавлен контекст для отладки
                Log::info('error login', [
                    'attempted_login' => $request->login,
                    'ip' => $request->ip()
                ]);

                return response()->json([
                    'message' => 'Неверный логин или пароль'
                ], 401);
            }

            // Получаем уже авторизованного пользователя (без лишнего запроса к БД)
            $user = Auth::user();

            // Регенерируем сессию для защиты от фиксации сессии
            $request->session()->regenerate();

            // Логируем вход, передавая ID пользователя для надежности
            UserLog::log("Вход в систему", $user->id);
        }

        return response()->json(Auth::user());
    }

    public function me()
    {
        return response()->json(Auth::user());
    }

    public function logout(Request $request)
    {
        UserLog::log("Выход из системы");
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true
        ]);
    }

    public function telegram(Request $request)
    {
        $telegramId = $request->id;

        $user = User::firstOrCreate(
            [
                'telegram_chat_id' => $telegramId
            ],
            [
                'name' => $request->first_name,
                'fio_from_telegram' => $request->username,
                'email' => $telegramId.'@telegram.local',
                'password' => Hash::make(str()->random(32))
            ]
        );

        Auth::login($user);

        $request->session()->regenerate();

        return response()->json($user);
    }
}
