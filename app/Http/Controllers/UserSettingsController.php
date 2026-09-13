<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserSettingsController extends Controller
{
    /**
     * Получить настройки шрифта текущего пользователя
     */
    public function getFontSettings(Request $request)
    {
        $user = $request->botUser;

        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        return response()->json([
            'settings' => $user->font_settings ?? User::defaultFontSettings(),
        ]);
    }

    /**
     * Обновить настройки шрифта
     */
    public function updateFontSettings(Request $request)
    {
        $user = $request->botUser;

        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $validated = $request->validate([
            'font_family' => 'required|string|in:system,serif,sans,mono',
            'font_size' => 'required|integer|min:12|max:32',
            'line_height' => 'required|numeric|min:1.2|max:2.0',
            'letter_spacing' => 'required|numeric|min:-0.5|max:2',
            'high_contrast' => 'required|boolean',
        ]);

        $user->font_settings = $validated;
        $user->save();

        return response()->json([
            'message' => 'Настройки сохранены',
            'settings' => $user->font_settings,
        ]);
    }

    /**
     * Сбросить настройки к значениям по умолчанию
     */
    public function resetFontSettings(Request $request)
    {
        $user = $request->botUser;

        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        $user->font_settings = User::defaultFontSettings();
        $user->save();

        return response()->json([
            'message' => 'Настройки сброшены',
            'settings' => $user->font_settings,
        ]);
    }
}
