<?php

namespace App\Http\Controllers;


use App\Enums\RoleEnum;
use App\Exports\UsersExport;
use App\Facades\BotManager;
use App\Facades\BotMethods;
use App\Models\Agent;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // 🔹 Фильтрация
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('fio_from_telegram')) {
            $query->where('fio_from_telegram', 'like', '%' . $request->fio_from_telegram . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('telegram_chat_id')) {
            $query->where('telegram_chat_id', $request->telegram_chat_id);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('percent')) {
            $query->where('percent', $request->percent);
        }
        if ($request->boolean('is_work')) {
            $query->where('is_work', true);
        }
        if ($request->boolean('email_verified')) {
            $query->whereNotNull('email_verified_at');
        }
        if ($request->boolean('blocked')) {
            $query->whereNotNull('blocked_at');
        } else
            $query->whereNull('blocked_at');

        // 🔹 Сортировка
        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'asc');
        if (in_array($sortField, [
                'id', 'name', 'fio_from_telegram', 'email', 'telegram_chat_id',
                'role', 'percent', 'is_work', 'email_verified_at', 'blocked_at', 'created_at'
            ]) && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // 🔹 Пагинация
        $perPage = $request->get('per_page', 30);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }


    public function store(Request $request)
    {
        $user = User::create($request->all());
        return response()->json($user, 201);
    }

    public function requestRole(Request $request)
    {
        $user = $request->botUser;

        $tmpUserLink = $user->getUserTelegramLink();

        $userInfo = $user->toTelegramText();

        $link = "https://t.me/" . env("TELEGRAM_BOT_DOMAIN") . "?start="
            . base64_encode($user->telegram_chat_id . "role");

        BotMethods::bot()->sendMessage(
            $user->telegram_chat_id,
            "Запрос на предоставление доступа успешно отправлен!");
        sleep(1);
        BotMethods::bot()->sendInlineKeyboard(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#запрос_на_предоставление_роли\n<b>Пользователь запрашивает предоставление прав Администратора</b>\n$userInfo\n$tmpUserLink", [
            [
                [
                    "text" => "Выдать роль Администратора",
                    "url" => "$link"
                ]
            ]
        ]);


    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::query()
            ->with(["agent"])
            ->findOrFail($id);


        $data = $request->all();

        $name = $data["name"] ?? null;
        $role = $data["role"] ?? RoleEnum::USER->value;
        $region = $data["region"] ?? null;
        $phone = $data["phone"] ?? null;
        $email = $data["email"] ?? null;

        if (!is_null($phone))
            unset($data["phone"]);

        if (!is_null($region))
            unset($data["region"]);

        if (!is_null($email))
            unset($data["email"]);

        $user->update([
            "name"=>$name,
            "role"=>$role,
        ]);

        $agent = $user->agent;

        $agent->update([
            'name' => $name ?? $user->fio_from_telegram,
            'phone' => $phone ?? '',
            'email' => $email ?? '',
            'region' => $region ?? '',
        ]);

        $tmpUserLink = $user->getUserTelegramLink();

        $userInfo = $user->toTelegramText();

        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#обновление_данных_пользователя\n<b>Пользователю изменены его персональные данные</b>\n$userInfo\n$tmpUserLink"
        );

        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $tmpUserLink = $user->getUserTelegramLink();

        $userInfo = $user->toTelegramText();

        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#удаление_пользователя\n<b>Пользователь был удален</b>\n$userInfo\n$tmpUserLink"
        );

        $user->delete();

        return response()->json(null, 204);
    }

    // 🔹 Дополнительные методы
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldRoleName = $user->getRoleName();
        $user->role = $request->input('role');
        $user->save();

        if ($user->role === RoleEnum::AGENT->value) {
            Agent::query()
                ->updateOrCreate([
                    'user_id' => $user->id,

                ], [
                    'name' => $user->fio_from_telegram ?? $user->name,
                    'phone' => '',
                    'email' => '',
                    'region' => '',
                ]);
        }

        $newRoleName = $user->getRoleName();

        $tmpUserLink = $user->getUserTelegramLink();

        $userInfo = $user->toTelegramText();

        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#смена_роли_пользователя\n<b>Пользователю изменена роль с $oldRoleName на $newRoleName</b>\n$userInfo\n$tmpUserLink"
        );

        sleep(1);

        \App\Facades\BotMethods::bot()->sendMessage(
            $user->telegram_chat_id,
            "Вам была изменена роль в системе с $oldRoleName на $newRoleName"
        );

        return response()->json($user);
    }

    public function updatePercent(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->percent = $request->input('percent');
        $user->save();
        return response()->json($user);
    }

    public function primary(Request $request)
    {

        $user = $request->botUser;

        $data = $request->all();

        $region = $data["region"] ?? null;
        $phone = $data["phone"] ?? null;
        $email = $data["email"] ?? null;

        if (!is_null($phone))
            unset($data["phone"]);

        if (!is_null($region))
            unset($data["region"]);

        if (!is_null($email))
            unset($data["email"]);

        $data["registration_at"] = Carbon::now();
        $user->update($data);

        Agent::query()
            ->updateOrCreate([
                'user_id' => $user->id,

            ], [
                'name' => $user->fio_from_telegram ?? $user->name,
                'phone' => $phone,
                'email' => $email,
                'region' => $region ?? '',
            ]);


        return response()->json($user);
    }

    public function updateWorkStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->is_work = $request->input('is_work');
        $user->save();
        return response()->json($user);
    }

    public function block(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->blocked_at = now();
        $user->blocked_message = $request->input('blocked_message');
        $user->save();

        $tmpUserLink = $user->getUserTelegramLink();

        $userInfo = $user->toTelegramText();

        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#блокировка_пользователя\n<b>Пользователь заблокирован</b>\n$userInfo\n$tmpUserLink"
        );

        sleep(1);

        \App\Facades\BotMethods::bot()->sendMessage(
            $user->telegram_chat_id,
            "Вам ограничили доступ к системе"
        );

        return response()->json($user);
    }

    public function unblock($id)
    {
        $user = User::findOrFail($id);
        $user->blocked_at = null;
        $user->blocked_message = null;
        $user->save();

        $tmpUserLink = $user->getUserTelegramLink();

        $userInfo = $user->toTelegramText();

        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#блокировка_пользователя\n<b>Пользователь разблокирован</b>\n$userInfo\n$tmpUserLink"
        );

        sleep(1);

        \App\Facades\BotMethods::bot()->sendMessage(
            $user->telegram_chat_id,
            "Вам убрали ограничения доступа к системе"
        );

        return response()->json($user);
    }

    /**
     * @throws HttpException
     */
    public function exportAdmins(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new HttpException("Пользователь не авторизован", 403);

        $fileName = "export-admins-" . Carbon::now()->format("Y-m-d H-i-s") . ".xlsx";
        $data = Excel::raw(new \App\Exports\AdminsExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id, "Экспорт списка администраторов",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data, $fileName));
        return response()->noContent();
    }

    public function getTelegramLink(Request $request, $id)
    {

        $user = $request->botUser ?? null;

        $findUser = User::findOrFail($id);

        $tmpUserLink = $findUser->getUserTelegramLink();

        $userInfo = $findUser->toTelegramText();

        \App\Facades\BotMethods::bot()->sendMessage(
            $user->telegram_chat_id,
            "#ссылка_на_пользователя\n<b>Ссылка на пользователя</b>\n$userInfo\n$tmpUserLink"
        );

        return response()->json($user);
    }

    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new HttpException("Пользователь не авторизован", 403);

        $fileName = "export-users-" . Carbon::now()->format("Y-m-d H-i-s") . ".xlsx";
        $data = Excel::raw(new \App\Exports\UsersExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id, "Экспорт списка пользователей",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data, $fileName));
        return response()->noContent();
    }
}
