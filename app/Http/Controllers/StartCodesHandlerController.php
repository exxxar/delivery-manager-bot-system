<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Enums\RoleEnum;
use App\Facades\BotManager;
use App\Facades\BotMethods;
use App\Facades\BusinessLogic;
use App\Models\Bot;
use App\Models\BotPage;
use App\Models\BotUser;
use App\Models\ManagerProfile;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReferralHistory;
use App\Models\Table;
use App\Models\TrafficSource;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Style\Tab;
use Telegram\Bot\FileUpload\InputFile;

class StartCodesHandlerController extends Controller
{

    public function acceptRoleChange(...$data){

        $adminBotUser = BotManager::bot()->currentBotUser();

        if ($adminBotUser->role != RoleEnum::SUPERADMIN->value)
        {
            BotManager::bot()
                ->reply("Недостаточно прав для этого действия!");
            return;
        }

        $telegramChatId = $data[1] ?? null;

        $user = User::query()->where("telegram_chat_id", $telegramChatId)->first();

        if (is_null($user)){
            BotManager::bot()
                ->reply("Пользователь не найден!");
            return;
        }

        $user->role = RoleEnum::AGENT->value;
        $user->save();

        BotManager::bot()->reply("Вы успешно изменили роль пользователю:\n".$user->toTelegramText());

    }
    public function roleInviteAction(...$data){
        $roleMd5 = $data[1] ?? null;

        $selectedRole = null;

        foreach(RoleEnum::cases() as $role) {
            $encryptedRole = md5($role->value); // Шифруем каждую роль

            if ($encryptedRole == $roleMd5){
                $selectedRole = $role->value;
                break;
            }
        }


        $botUser = BotManager::bot()->currentBotUser();

        $rolesTitles = ["Пользователь","Администратор","Поставщик", "Старший администратор", "Суперадмин"];

        if ($botUser->role != RoleEnum::USER->value)
        {
            BotManager::bot()
                ->reply("У вас уже установлена роль <b>".$rolesTitles[$botUser->role ?? 0]."</b>. Ваша роль изменена не будет!");
            return;
        }
        $botUser->role = $selectedRole ?? 0;
        $botUser->save();

        BotManager::bot()->reply("Вам назначена роль <b>".$rolesTitles[$botUser->role ?? 0]."</b>");
        sleep(1);
        BotManager::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "Пользователю назначена роль <b>".$rolesTitles[$botUser->role ?? 0]."</b>\n".$botUser->toTelegramText());
    }


}
