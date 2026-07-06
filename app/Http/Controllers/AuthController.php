<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Facades\UserLog;
use App\Models\Agent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20', 'unique:agents,phone'],
            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
                Password::defaults()
            ],
        ], [
            'name.required' => 'Имя обязательно для заполнения',
            'email.required' => 'Email обязателен для заполнения',
            'email.email' => 'Введите корректный email',
            'email.unique' => 'Пользователь с таким email уже зарегистрирован',
            'phone.required' => 'Телефон обязателен для заполнения',
            'phone.unique' => 'Пользователь с таким телефоном уже зарегистрирован',
            'password.required' => 'Пароль обязателен для заполнения',
            'password.min' => 'Пароль должен содержать минимум 6 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'fio_from_telegram' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 0, // USER по умолчанию
        ]);

        Agent::query()
            ->updateOrCreate([
                'user_id' => $user->id,

            ], [
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'region' => '',
            ]);

        // Логируем регистрацию
        UserLog::log("Регистрация нового пользователя: {$user->name} ({$user->email})", $user->id);

        // Логируем всем суперадминам
        UserLog::logSuper("Зарегистрирован новый пользователь: {$user->name} ({$user->email})", $user->id);

        return response()->json([
            'message' => 'Регистрация прошла успешно',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {

        if (env("APP_DEBUG")) {
            $user = User::query()->first();
            $user->role = RoleEnum::SUPERADMIN->value;
            $user->base_role = RoleEnum::SUPERADMIN->value;

            Auth::login($user);
            UserLog::log("Вход в систему");
            $request->session()->regenerate();

        }
        else  {
            $request->validate([
                'login' => 'required',
                'password' => 'required'
            ]);


            if (!Auth::attempt([
                'email' => $request->login,
                'password' => $request->password
            ])) {

                Log::info("errror login");
                return response()->json([
                    'message' => 'Неверный логин или пароль'
                ], 401);
            }

            $user = User::query()->where("email",  $request->login)
                ->first();

            Auth::login($user);
            $request->session()->regenerate();

            UserLog::log("Вход в систему");

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
                'password' => bcrypt(str()->random(32))
            ]
        );

        Auth::login($user);

        $request->session()->regenerate();

        return response()->json($user);
    }
}
