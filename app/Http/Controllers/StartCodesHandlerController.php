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

    public function roleInviteAction(...$data){
        $roleMd5 = $data[1] ?? null;

        $selectedRole = null;

        foreach(RoleEnum::cases() as $role) {
            $encryptedRole = md5($role->value); // Шифруем каждую роль
            BotManager::bot()->reply("роль $role->value $encryptedRole $roleMd5");
            if ($encryptedRole == $roleMd5){
                $selectedRole = $role->value;
                break;
            }
        }


        $botUser = BotManager::bot()->currentBotUser();
        $botUser->role = $selectedRole ?? 0;
        $botUser->save();

        $rolesTitles = ["Пользователь","Администратор","Поставщик", "Старший администратор", "Суперадмин"];

        BotManager::bot()->reply("Вам назначена роль <b>".$rolesTitles[$botUser->role ?? 0]."</b>");
    }


}
