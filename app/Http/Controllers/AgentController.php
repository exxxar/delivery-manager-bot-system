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
use App\Models\Percentage;
use Carbon\Carbon;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AgentController extends Controller
{
    public function agentSales(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

        $sales = Sale::query()
            ->with(['product', 'supplier', 'customer', 'creator', 'agent'])
            ->where('agent_id', $id)
            ->whereBetween('actual_delivery_date', [
                $monthDate->startOfMonth()->toDateTimeString(),
                $monthDate->endOfMonth()->toDateTimeString()
            ])
            ->orderByDesc('actual_delivery_date')
            ->paginate($request->get('per_page', 20));

        return response()->json($sales);
    }

    public function active(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = Carbon::parse($month . '-01');

            $startDate = $monthDate->copy()->startOfMonth()->toDateTimeString();
            $endDate = $monthDate->copy()->endOfMonth()->toDateTimeString();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

        // Единое условие для подсчёта продаж за месяц.
        $salesQuery = function ($q) use ($startDate, $endDate) {
            $q->where('status', 'completed');
            $q->whereBetween('actual_delivery_date', [$startDate, $endDate]);
        };

        $query = Agent::query()
            ->with(['user:id,percent'])
            ->withCount(['sales as month_sales_count' => $salesQuery])
            ->withSum(['sales as month_turnover' => $salesQuery], 'total_price')
            ->having('month_sales_count', '>', 0)
            ->orderByDesc('month_turnover');

        // Поиск по имени или телефону.
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = (int) $request->get('per_page', $request->size ?? 20);
        $agents = $query->paginate($perPage);

        /*
         * ВХОДЯЩИЕ ПРОЦЕНТЫ
         *
         * user_id — получатель процента.
         * percentable_id — пользователь, с оборота которого
         * рассчитывается входящий процент.
         */

        $recipientUserIds = $agents->getCollection()
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        $incomingPercentages = Percentage::query()
            ->active()
            ->whereIn('user_id', $recipientUserIds)
            ->where('percentable_type', User::class)
            ->get([
                'user_id',
                'percentable_id',
                'percentage'
            ]);

        // Получаем обороты пользователей-плательщиков.
        $payerTurnovers = Sale::query()
            ->join('agents', 'agents.id', '=', 'sales.agent_id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.actual_delivery_date', [$startDate, $endDate])
            ->whereIn(
                'agents.user_id',
                $incomingPercentages->pluck('percentable_id')->filter()->unique()
            )
            ->selectRaw('agents.user_id as payer_user_id, SUM(sales.total_price) as turnover')
            ->groupBy('agents.user_id')
            ->pluck('turnover', 'payer_user_id');

        // Считаем входящие начисления для каждого получателя.
        $incomingByRecipient = [];

        foreach ($incomingPercentages as $percentageRow) {
            $recipientId = (int) $percentageRow->user_id;
            $payerId = (int) $percentageRow->percentable_id;

            $rate = (float) $percentageRow->percentage;
            $payerTurnover = (float) ($payerTurnovers[$payerId] ?? 0);

            $incomingByRecipient[$recipientId]['percent'] =
                ($incomingByRecipient[$recipientId]['percent'] ?? 0) + $rate;

            $incomingByRecipient[$recipientId]['income'] =
                ($incomingByRecipient[$recipientId]['income'] ?? 0)
                + ($payerTurnover * $rate / 100);
        }

        /*
         * РАСЧЁТ ЗАРПЛАТЫ
         *
         * 1. Собственный доход:
         *    собственный оборот * User.percent / 100
         *
         * 2. Входящий доход:
         *    сумма начислений от других администраторов.
         *
         * 3. Итоговая зарплата:
         *    собственный доход + входящий доход.
         */

        $agents->getCollection()->transform(
            function ($agent) use ($incomingByRecipient) {
                $turnover = (float) ($agent->month_turnover ?? 0);

                // Персональный процент берём только из модели User.
                $percent = (float) ($agent->user?->percent ?? 0);

                // Собственный доход администратора.
                $ownIncome = $turnover * $percent / 100;

                // Входящие начисления.
                $incoming = $incomingByRecipient[(int) $agent->user_id] ?? [
                    'percent' => 0,
                    'income' => 0,
                ];

                $agent->own_percent = $percent;
                $agent->own_income = round($ownIncome, 2);

                $agent->incoming_percent = round(
                    (float) $incoming['percent'],
                    2
                );

                $agent->incoming_income = round(
                    (float) $incoming['income'],
                    2
                );

                // Общая зарплата.
                $agent->month_salary = round(
                    $ownIncome + (float) $incoming['income'],
                    2
                );

                return $agent;
            }
        );

        // Общий оборот за месяц.
        $grandTotalTurnover = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('actual_delivery_date', [$startDate, $endDate])
            ->sum('total_price');

        $response = $agents->toArray();

        $response['stats'] = [
            'total_agents' => $agents->total(),
            'total_turnover' => round((float) $grandTotalTurnover, 2),
        ];

        return response()->json($response);
    }

    public function inactive(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

        $query = Agent::query()
            ->withCount(['sales as month_sales_count' => function ($q) use ($monthDate) {
                $q->whereBetween('actual_delivery_date', [
                    $monthDate->copy()->startOfMonth()->toDateTimeString(),
                    $monthDate->copy()->endOfMonth()->toDateTimeString()
                ]);
            }])
            ->having('month_sales_count', '=', 0);

        // Поиск по имени или телефону.
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
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
            ->with(['mentor', 'user:id,percent']);

        // Фильтры по связям.
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Фильтры по текстовым полям.
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

        // Фильтр по created_at / updated_at.
        if (
            $request->filled('date_type')
            && ($request->filled('date_from') || $request->filled('date_to'))
        ) {
            $query->whereBetween($request->date_type, [
                    $request->date_from ?? '1900-01-01',
                    $request->date_to ?? now()->toDateTimeString()
            ]);
        }

        // Сортировка.
        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');

        if (
            in_array($sortField, [
                'id',
                'user_id',
                'name',
                'phone',
                'email',
                'region',
                'created_at',
                'updated_at'
            ])
            && in_array($sortDirection, ['asc', 'desc'])
        ) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Пагинация.
        $perPage = $request->get('per_page', $request->size ?? 10);
        $agents = $query->paginate($perPage);

        // Входящий процент для каждого администратора.
        $recipientUserIds = $agents->getCollection()
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        $incomingPercentages = Percentage::query()
            ->active()
            ->whereIn('user_id', $recipientUserIds)
            ->where('percentable_type', User::class)
            ->selectRaw('user_id, SUM(percentage) as incoming_percent')
            ->groupBy('user_id')
            ->pluck('incoming_percent', 'user_id');

        $agents->getCollection()->transform(function ($agent) use ($incomingPercentages) {
            $agent->incoming_percent = round(
                (float) ($incomingPercentages[$agent->user_id] ?? 0),
                2
            );

            return $agent;
        });

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
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'region' => 'required'
        ]);

        $botUser = $request->botUser;

        $data = $request->all();
        $data['user_id'] = $botUser->id;

        $agent = Agent::query()->create($data);

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

        $inLearning = ($data['in_learning'] ?? false) == 'true';
        $isTest = ($data['is_test'] ?? false) == 'true';
        $percent = $data['percent'] ?? null;

        if ($agent->in_learning && !$inLearning) {
            $data['start_learning_date'] = Carbon::now();
        }

        if (!$agent->in_learning && $inLearning) {
            $data['start_learning_date'] = Carbon::now();
            $data['end_learning_date'] = null;
        }

        $agent->update($data);

        // Обновляем персональный процент пользователя, в том числе разрешаем 0%.
        if (array_key_exists('percent', $data) && $percent !== null) {
            $user = User::query()->findOrFail($agent->user_id);
            $user->percent = $percent;
            $user->save();
        }

        if ($isTest) {
            UserLog::log(
                'Вас отметили как <b>Администратор-Тестировщик</b>, ваши заявки не пойдут в учет.'
            );
        }

        return response()->json($agent);
    }

    public function destroy($id)
    {
        $agent = Agent::query()
            ->with(['user'])
            ->findOrFail($id);

        $user = $agent->user ?? null;

        $agent->delete();

        if (!is_null($user)) {
            $user->blocked_at = Carbon::now();
            $user->blocked_message = 'Ваша учетная запись была удалена администратором. Доступ ограничен';
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

        if (is_null($user)) {
            throw new HttpException(403, 'Пользователь не авторизован');
        }

        $fileName = 'export-agents-' . Carbon::now()->format('Y-m-d-H-i-s') . '.xlsx';

        $report = app(\App\Services\ExportService::class)->saveReport(
            $user,
            'Экспорт списка торговых представителей',
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
