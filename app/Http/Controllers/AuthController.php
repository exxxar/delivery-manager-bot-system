<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        if (env("APP_DEBUG")) {
            $user = User::query()->first();
            $user->role = RoleEnum::SUPERADMIN->value;
            $user->base_role = RoleEnum::SUPERADMIN->value;

            Auth::login($user);

            $request->session()->regenerate();

        }
        else  {
            $request->validate([
                'login' => 'required',
                'password' => 'required'
            ]);

            Log::info("auth data".print_r([
                    'email' => $request->login,
                    'password' => $request->password
                ], true);
            if (!Auth::attempt([
                'email' => $request->login,
                'password' => $request->password
            ])) {

                Log::info("errror login");
                return response()->json([
                    'message' => 'Неверный логин или пароль'
                ], 401);
            }
            Log::info("success login");
            $user = User::query()->where("email",  $request->login)
                ->first();

            Log::info("user login".print_r($user->toArray(), true));

            Auth::login($user);
            $request->session()->regenerate();

            Log::info("auth check".print_r(Auth::check() ? 'true':'false', true));
        }


        return response()->json(Auth::user());
    }

    public function me()
    {
        return response()->json(Auth::user());
    }

    public function logout(Request $request)
    {
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
