<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Facades\BotManager;
use App\Facades\BotMethods;
use App\Facades\StartCodesService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Telegram\Bot\FileUpload\InputFile;

class TelegramController extends Controller
{
    public function getSelf(Request $request)
    {


        if (env("APP_DEBUG")) {
            $user = User::query()->first();
            $user->role = RoleEnum::SUPERADMIN->value;
            $user->base_role = RoleEnum::SUPERADMIN->value;

            Auth::guard('web')->login($user);

            $request->session()->regenerate();
        } else {
            $user = User::query()
                ->find($request->botUser->id);
            $user->base_role = $user->role;
        }


        return response()->json($user);
    }

    public function registerWebhooks(Request $request)
    {
        return response()->json(BotManager::bot()->setWebhook());
    }

    public function handler(Request $request)
    {
        BotManager::bot()->handler();

        return response()->json([
            "message" => "Ok"
        ]);
    }

    public function uploadAnyKindOfMedia(...$data)
    {
        $caption = $data[2] ?? null;
        $doc = $data[3] ?? null;
        $type = $data[4] ?? "document";

        $botUser = BotManager::bot()->currentBotUser();

        if (!$botUser->is_admin && !$botUser->is_manager) {
            BotManager::bot()
                ->sendMessage(
                    $botUser->telegram_chat_id,
                    "Данная опция доступна только персоналу бота!");
            return;
        }

        $docToSend = $doc->file_id ?? null;


        BotManager::bot()
            ->sendMessage(
                $botUser->telegram_chat_id,
                "Медиа файл загружен!");

    }

    public function getMyId(...$data)
    {
        $message = "Ваш чат id: <pre><code>" . ($data[0]->chat->id ?? 'не указан') . "</code></pre>\nИдентификатор топика: " . ($data[0]->message_thread_id ?? 'Не указан');

        BotManager::bot()
            ->reply($message);
    }

    public function generateAuthLinks(...$data)
    {

        $botUser = BotManager::bot()
            ->currentBotUser();

        if ($botUser->role != RoleEnum::SUPERADMIN->value) {
            BotManager::bot()
                ->reply("Данный метод для вас недоступен!");
            return;
        }


        $rolesTitles = ["Пользователь", "Администратор", "Поставщик", "Старший администратор", "Суперадмин"];

        $transformedRolesWithOriginals = [];

        foreach (RoleEnum::cases() as $role) {
            $encryptedRole = base64_encode(md5($role->value)); // Шифруем каждую роль
            $transformedRolesWithOriginals[$role->value] = $encryptedRole;
        }

        $htmlMessage = "<b>Список уникальных ролей и соответствующих им шифров:</b>\n\n";

        foreach ($transformedRolesWithOriginals as $originalRole => $encryptedRole) {
            $link = "https://t.me/" . env("TELEGRAM_BOT_DOMAIN") . "?start=$encryptedRole";
            $htmlMessage .= "<b>Роль [" . $rolesTitles[$originalRole] . "]:</b> <code>$link</code>\n\n";
        }

        BotManager::bot()
            ->reply($htmlMessage);
    }

    public function aboutCommand(...$data)
    {
        BotManager::bot()
            ->replyPhoto("Хочешь такой же бот для своего бизнеса? ",
                InputFile::create(public_path() . "/images/cashman.jpg"),
                [
                    [
                        [
                            "text" => "🔥Перейти в нашего бота для заявок",
                            "url" => "https://t.me/cashman_dn_bot"
                        ]
                    ],
                    [
                        [
                            "text" => "\xF0\x9F\x8D\x80Написать в тех. поддержку",
                            "url" => "https://t.me/EgorShipilov"
                        ],
                    ],

                ]
            );
    }

    public function helpCommand(...$data)
    {
        BotManager::bot()->reply("Как пользоваться ботом");
    }

    public function homePagePwa(Request $request)
    {

        if (!Auth::check())
            return redirect("/login");

        Inertia::setRootView("pwa");
        return Inertia::render('Main', [
            "api_type" => "api"
        ]);
    }


    public function homePageBot(Request $request)
    {

        /* if (env("APP_DEBUG")) {
             $user = User::query()->first();
             $user->role = RoleEnum::SUPERADMIN->value;
         } else
             $user = BotManager::bot()->currentBotUser();

         if (is_null($user))
             throw new HttpException(404, "Ошибочка");*/

        Inertia::setRootView("bot");
        return Inertia::render('Main', [
            "api_type" => "bot-api"
        ]);
    }

    public function startCommand()
    {

        $botUser = BotManager::bot()
            ->currentBotUser();

        $keyboard = [
            [
                ["text" => "📲Вход в моб. версию", "url" => env("APP_URL")],
            ],
            [
                ["text" => "🎓Инструкция", "web_app" => [
                    "url" => "https://telegra.ph/Instrukciya-dlya-Administratora-01-08"]
                ],
            ],
            [
                ["text" => "💎Войти в систему", "web_app" => [
                    "url" => env("APP_URL") . "/bot#/"]
                ],
            ],
        ];

        \App\Facades\BotManager::bot()
            ->replyInlineKeyboard("Система управления доставками"
                , $keyboard);

        \App\Facades\BotManager::bot()
            ->reply("<b>Ваш логин и пароль для входа в мобверсию:</b>\n"
                . "Логин <code>". ($botUser->email ?? '-') . "</code>\n"
                . "Пароль <code>". ($botUser->telegram_chat_id ?? '-') . "</code>\n"
                . "Ссылка для входа <code>" . env("APP_URL") . "/pwa</code>"
                );


    }

    public function startWithParam(...$data)
    {
        $startCommand = $data[3] ?? null;
        StartCodesService::bot()
            ->handler($startCommand);

        if (is_null($startCommand))
            BotManager::bot()->pushCommand("/start");
    }
}
