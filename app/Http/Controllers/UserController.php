<?php

namespace App\Http\Controllers;


use App\Enums\RoleEnum;
use App\Exports\UsersExport;
use App\Facades\BotManager;
use App\Facades\BotMethods;
use App\Facades\UserLog;
use App\Models\Agent;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function resetPassword(Request $request, User $user)
    {
        $admin = $request->user();

        if (!$admin || (int) $admin->role < 3) {
            return response()->json([
                'message' => 'Недостаточно прав для сброса пароля.',
            ], 403);
        }

        $user->password = bcrypt('12345678');
        $user->save();

        return response()->json([
            'message' => 'Пароль пользователя успешно сброшен.',
        ]);
    }

    public function index(Request $request)
    {
        $query = User::query()
            ->with(["agent","supplier"]);

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

    /**
     * Создаёт отдельный профиль Agent для существующей учётной записи User.
     * В URL приходит users.id, связь создаётся строго через agents.user_id.
     */
    public function createAgentProfile(Request $request, $id)
    {
        $actor = $request->user();

        if (!$actor || (int) $actor->role < 3) {
            return response()->json([
                'message' => 'Недостаточно прав для создания профиля администратора.',
            ], 403);
        }

        $user = User::query()->with('agent')->findOrFail($id);

        if ((int) $user->role < 3) {
            return response()->json([
                'message' => 'Профиль Agent можно создать только для старшего администратора.',
            ], 422);
        }

        $agent = DB::transaction(function () use ($user) {
            // Повторный вызов безопасен: существующая запись не дублируется.
            return Agent::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->fio_from_telegram ?: $user->name ?: 'Администратор',
                    'phone' => $user->phone ?? '',
                    'email' => $user->email ?? '',
                    'region' => $user->region ?? '',
                ]
            );
        });

        $user->load(['agent', 'supplier']);

        return response()->json([
            'message' => 'Профиль Agent успешно создан или уже существовал.',
            'user' => $user,
            'agent' => $agent,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::query()
            ->with(["agent"])
            ->findOrFail($id);

        $data = $request->all();

        $name = $data["name"] ?? null;
        // Если роль не передана, сохраняем текущую, а не сбрасываем её до USER.
        $role = array_key_exists("role", $data) ? (int) $data["role"] : (int) $user->role;
        $region = $data["region"] ?? null;
        $phone = $data["phone"] ?? null;
        $email = $data["email"] ?? null;

        DB::transaction(function () use ($user, $name, $role, $region, $phone, $email) {
            $user->update([
                "name" => $name ?? $user->name,
                "role" => $role,
            ]);

            $agent = $user->agent;

            // Старший администратор (роль 3 и выше) должен иметь связанную
            // агентскую сущность. firstOrCreate защищает от дубликатов.
            if ($role >= 3 && !$agent) {
                $agent = Agent::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $name ?? $user->fio_from_telegram ?? $user->name,
                        'phone' => $phone ?? $user->fio_from_telegram ?? '',
                        'email' => $email ?? $user->email ?? '',
                        'region' => $region ?? '',
                    ]
                );
            }

            // Если агентская запись уже существует, обновляем только те
            // поля, которые действительно передали, не затирая остальные.
            if ($agent) {
                $agent->update([
                    'name' => $name ?? $agent->name ?? $user->fio_from_telegram ?? $user->name,
                    'phone' => $phone ?? $agent->phone ?? $user->fio_from_telegram ?? '',
                    'email' => $email ?? $agent->email ?? $user->email ?? '',
                    'region' => $region ?? $agent->region ?? '',
                ]);
            }
        });

        $user->load(["agent", "supplier"]);

        $tmpUserLink = $user->getUserTelegramLink();
        $userInfo = $user->toTelegramText();

        UserLog::logSuper("#обновление_данных_пользователя\n<b>Пользователю изменены его персональные данные</b>\n$userInfo\n$tmpUserLink");

        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $tmpUserLink = $user->getUserTelegramLink();

        $userInfo = $user->toTelegramText();

        UserLog::logSuper("#удаление_пользователя\n<b>Пользователь был удален</b>\n$userInfo\n$tmpUserLink");

        $user->delete();

        return response()->json(null, 204);
    }

    // 🔹 Дополнительные методы
    public function updateRole(Request $request, $id)
    {
        $validated = $request->validate([
            'role' => ['required', 'integer'],
        ]);

        $user = User::query()->with('agent')->findOrFail($id);
        $oldRoleName = $user->getRoleName();
        $newRole = (int) $validated['role'];

        DB::transaction(function () use ($user, $newRole) {
            $user->role = $newRole;
            $user->save();

            // Роли 3+ — старший администратор / суперадминистратор.
            // При назначении такой роли гарантируем наличие Agent.
            // Существующую запись не перезаписываем и не дублируем.
            if ($newRole >= 3 && !$user->agent()->exists()) {
                Agent::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $user->fio_from_telegram ?: $user->name,
                        'phone' => $user->fio_from_telegram ?? '',
                        'email' => $user->email ?? '',
                        'region' => '',
                    ]
                );
            }
        });

        $user->load('agent');
        $newRoleName = $user->getRoleName();

        $tmpUserLink = $user->getUserTelegramLink();
        $userInfo = $user->toTelegramText();

        UserLog::logSuper("#смена_роли_пользователя\n<b>Пользователю изменена роль с $oldRoleName на $newRoleName</b>\n$userInfo\n$tmpUserLink");
        UserLog::log("Вам была изменена роль в системе с $oldRoleName на $newRoleName", $user->id);

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

        UserLog::logSuper("#блокировка_пользователя\n<b>Пользователь заблокирован</b>\n$userInfo\n$tmpUserLink");
        UserLog::log(
            "Вам ограничили доступ к системе", $user->id
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

        UserLog::logSuper("#блокировка_пользователя\n<b>Пользователь разблокирован</b>\n$userInfo\n$tmpUserLink");
        UserLog::log("Вам убрали ограничения доступа к системе", $user->id);

        return response()->json($user);
    }

    /**
     * @throws HttpException
     */
    public function exportAdmins(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, "Пользователь не авторизован");

        $fileName = "export-admins-" . Carbon::now()->format("Y-m-d-H-i-s") . ".xlsx";

        $report = app(\App\Services\ExportService::class)->saveReport(
            $user,
            "Экспорт списка администраторов",
            $fileName,
            new \App\Exports\AdminsExport(),
            [],
            'admins_list'
        );

        return response()->json([
            'message' => 'Отчет успешно сформирован',
            'report' => $report
        ]);
    }

    public function getTelegramLink(Request $request, $id)
    {

        $user = $request->botUser ?? null;

        $findUser = User::findOrFail($id);

        $tmpUserLink = $findUser->getUserTelegramLink();

        $userInfo = $findUser->toTelegramText();

        UserLog::log("#ссылка_на_пользователя\n<b>Ссылка на пользователя</b>\n$userInfo\n$tmpUserLink", $user->id);

        return response()->json($user);
    }

    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, "Пользователь не авторизован");

        $fileName = "export-users-" . Carbon::now()->format("Y-m-d-H-i-s") . ".xlsx";

        $report = app(\App\Services\ExportService::class)->saveReport(
            $user,
            "Экспорт списка пользователей",
            $fileName,
            new \App\Exports\UsersExport(),
            [],
            'users_list'
        );

        return response()->json([
            'message' => 'Отчет успешно сформирован',
            'report' => $report
        ]);
    }
}
