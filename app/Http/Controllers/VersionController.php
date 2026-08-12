<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function check(Request $request)
    {
        return response()->json([
            'version' => env('APP_VERSION', '1.0.0'),
            'force_update' => env('APP_FORCE_UPDATE', false),
            'message' => 'Доступна новая версия приложения. Пожалуйста, обновите для продолжения работы.',
        ]);
    }
}
