<?php

namespace App\Http\Controllers;

use App\Exports\AgentSalesExport;
use App\Exports\AgentsExport;
use App\Facades\UserLog;
use App\Http\Requests\AgentStoreRequest;
use App\Http\Requests\AgentUpdateRequest;
use App\Models\Agent;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AgentController extends Controller
{

    public function active(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = \Carbon\Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

        $query = Agent::query()
            ->withCount(['sales as month_sales_count' => function ($q) use ($monthDate) {
                $q->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateString(),
                    $monthDate->endOfMonth()->toDateString()
                ]);
            }])
            ->withSum(['sales as month_turnover' => function ($q) use ($monthDate) {
                $q->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateString(),
                    $monthDate->endOfMonth()->toDateString()
                ]);
            }], 'total_price')
            ->having('month_sales_count', '>', 0)
            ->orderByDesc('month_turnover');

        // 🔹 Поиск по имени или телефону
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = $request->get('per_page', $request->size ?? 20);
        $agents = $query->paginate($perPage);

        $response = $agents->toArray();
        $response['stats'] = [
            'total_agents' => $agents->total(),
            'total_turnover' => round(collect($agents->items())->sum('month_turnover'), 2),
        ];

        return response()->json($response);
    }

    public function inactive(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = \Carbon\Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный_format месяца'], 422);
        }

        $query = Agent::query()
            ->withCount(['sales as month_sales_count' => function ($q) use ($monthDate) {
                $q->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateString(),
                    $monthDate->endOfMonth()->toDateString()
                ]);
            }])
            ->having('month_sales_count', '=', 0);

        // 🔹 Поиск по имени или телефону
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = $request->get('per_page', $request->size ?? 20);
        $agents = $query->paginate($perPage);

        $response = $agents->toArray();
        $response['stats'] = [
            'total_agents' => $agents->total(),
        ];

        return response()->json($response);
    }

    public function index(Request $request)
    {
        $query = Agent::query()
            ->with(["mentor"]);

        // 🔹 Фильтры по связям
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 🔹 Фильтры по текстовым полям
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('region')) {
            $query->where('region', 'like', '%' . $request->region . '%');
        }

        // 🔹 Фильтр по created_at / updated_at
        if ($request->filled('date_type') && ($request->filled('date_from') || $request->filled('date_to'))) {
            $query->whereBetween($request->date_type, [
                    $request->date_from ?? '1900-01-01',
                    $request->date_to ?? now()->toDateString()
            ]);
        }

        // 🔹 Сортировка
        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        if (in_array($sortField, [
                'id', 'user_id', 'name', 'phone', 'email',
                'region', 'created_at', 'updated_at'
            ]) && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // 🔹 Пагинация
        $perPage = $request->get('per_page', $request->size ?? 10);
        $agents = $query->paginate($perPage);

        return response()->json($agents);
    }


    public function selfSales(Request $request)
    {

        $botUser = $request->botUser;

        $request->only_self_sales = true;

        $sales = Sale::query()
            ->filter($request, $botUser)
            ->sort($request)
            ->paginate($request->get('per_page', 10));

        return response()->json($sales);

    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => "required",
            'phone' => "required",
            'email' => "required",
            'region' => "required"
        ]);

        $botUser = $request->botUser;

        $data = $request->all();
        $data["user_id"] = $botUser->id;

        $agent = Agent::query()
            ->create($data);
        return response()->json($agent, 201);
    }

    public function show($id)
    {
        $agent = Agent::findOrFail($id);
        return response()->json($agent);
    }

    public function update(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);

        $data = $request->all();

        $inLearning = ($data["in_learning"] ?? false) == "true";
        $isTest = ($data["is_test"] ?? false) == "true";
        $percent = $data["percent"] ?? null;


        if ($agent->in_learning && !$inLearning) {
            $data["start_learning_date"] = Carbon::now();
        }

        if (!$agent->in_learning && $inLearning) {
            $data["start_learning_date"] = Carbon::now();
            $data["end_learning_date"] = null;
        }

        $agent->update($data);

        if (!is_null($percent) && $percent != 0) {
            $user = User::query()->findOrFail($agent->user_id);
            $user->percent = $percent;
            $user->save();
        }

        if ($isTest) {
            UserLog::log("Вас отметили как <b>Администратор-Тестировщик</b>, ваши заявки не пойдут в учет.");

        }

        return response()->json($agent);
    }

    public function destroy($id)
    {
        $agent = Agent::query()
            ->with(["user"])
            ->findOrFail($id);


        $user = $agent->user ?? null;

        $agent->delete();

        if (!is_null($user)) {
            $user->blocked_at = Carbon::now();
            $user->blocked_message = "Ваша учетная запись была удалена администратором. Доступ ограничен";
            $user->save();
            $user->delete();
        }


        return response()->json(null, 204);
    }

    public function downloadSelfReport()
    {

    }

    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, "Пользователь не авторизован");

        $fileName = "export-agents-" . Carbon::now()->format("Y-m-d-H-i-s") . ".xlsx";

        $report = app(\App\Services\ExportService::class)->saveReport(
            $user,
            "Экспорт списка торговых представителей",
            $fileName,
            new \App\Exports\AgentsExport(),
            [],
            'agents_list'
        );

        return response()->json([
            'message' => 'Отчет успешно сформирован',
            'report' => $report
        ]);
    }
}
