<?php

namespace App\Http\Controllers;


use App\Enums\RoleEnum;
use App\Exports\UsersExport;
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
        }

        // 🔹 Сортировка
        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'asc');
        if (in_array($sortField, [
                'id','name','fio_from_telegram','email','telegram_chat_id',
                'role','percent','is_work','email_verified_at','blocked_at','created_at'
            ]) && in_array($sortDirection, ['asc','desc'])) {
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

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());
        return response()->json($user);
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(null, 204);
    }

    // 🔹 Дополнительные методы
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->input('role');
        $user->save();
        return response()->json($user);
    }

    public function updatePercent(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->percent = $request->input('percent');
        $user->save();
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
        return response()->json($user);
    }

    public function unblock($id)
    {
        $user = User::findOrFail($id);
        $user->blocked_at = null;
        $user->blocked_message = null;
        $user->save();
        return response()->json($user);
    }

    public function exportAdmins(Request $request){

    }

    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new HttpException("Пользователь не авторизован", 403);

        $fileName = "export-users-".Carbon::now()->format("Y-m-d H-i-s").".xlsx";
        $data = Excel::raw(new \App\Exports\UsersExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id,"Экспорт списка пользователей",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data,$fileName));
        return response()->noContent();
    }
}
