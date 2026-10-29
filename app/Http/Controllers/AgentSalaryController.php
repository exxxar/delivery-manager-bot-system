<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Percentage;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AgentSalaryController extends Controller
{
    /**
     * GET /agents/{agentId}/salary?month=YYYY-MM
     *
     * Расчёт по завершённым сделкам, попавшим в календарный месяц
     * по actual_delivery_date.
     */
    public function show(Request $request, int $agentId)
    {
        $viewer = $request->botUser ?? Auth::user();

        if (!$viewer) {
            return response()->json(['message' => 'Необходима авторизация'], 401);
        }

        if (!in_array((int) $viewer->role, [1, 3, 4], true)) {
            return response()->json(['message' => 'Недостаточно прав для просмотра зарплаты'], 403);
        }

        $agent = Agent::query()->with('user')->findOrFail($agentId);

        // Обычный администратор видит только собственный расчёт.
        // Старший администратор и суперадмин (роли 3 и 4) могут просматривать других.
        if ((int) $viewer->role < 3 && (int) $agent->user_id !== (int) $viewer->id) {
            return response()->json(['message' => 'Можно просматривать только собственную зарплату'], 403);
        }

        $month = (string) $request->query('month', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return response()->json(['message' => 'Неверный формат месяца. Используйте YYYY-MM.'], 422);
        }

        $monthStart = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $nextMonthStart = $monthStart->copy()->addMonth();
        $monthEnd = $nextMonthStart->copy()->subDay();

        $dateFilter = static function ($query) use ($monthStart, $nextMonthStart) {
            $query->where('status', 'completed')
                ->whereNotNull('actual_delivery_date')
                ->where('actual_delivery_date', '>=', $monthStart->toDateString())
                ->where('actual_delivery_date', '<', $nextMonthStart->toDateString());
        };

        $user = $agent->user;
        $ownPercent = (float) ($user?->percent ?? 0);

        // Доли, которые другие пользователи получают с продаж этого администратора.
        $outgoingPercentages = Percentage::query()
            ->with('user')
            ->where('percentable_type', $agent->getMorphClass())
            ->where('percentable_id', $agent->id)
            ->get();

        $ownSales = Sale::query()
            ->with(['product:id,name', 'supplier:id,name', 'agent:id,name'])
            ->where('agent_id', $agent->id)
            ->where($dateFilter)
            ->orderByDesc('actual_delivery_date')
            ->get();

        $ownTurnover = (float) $ownSales->sum('total_price');
        $ownCommission = round($ownTurnover * $ownPercent / 100, 2);

        $outgoing = $outgoingPercentages->map(function ($percentage) use ($ownTurnover) {
            $active = (bool) $percentage->is_active;
            $amount = $active
                ? round($ownTurnover * (float) $percentage->percentage / 100, 2)
                : 0;

            return [
                'id' => $percentage->id,
                'user_id' => $percentage->user_id,
                'user_name' => $percentage->user?->name ?? 'Пользователь удалён',
                'percentage' => (float) $percentage->percentage,
                'is_active' => $active,
                'amount' => $amount,
            ];
        })->values();

        // Активные проценты, по которым текущий администратор получает доход
        // с продаж других Agent и Supplier.
        $agentMorph = (new Agent())->getMorphClass();
        $supplierMorph = (new Supplier())->getMorphClass();

        $incomingPercentages = Percentage::query()
            ->where('user_id', $agent->user_id)
            ->where('is_active', true)
            ->whereIn('percentable_type', [$agentMorph, $supplierMorph])
            ->get();

        $incomingAgentIds = $incomingPercentages
            ->where('percentable_type', $agentMorph)
            ->pluck('percentable_id')->unique()->values()->all();

        $incomingSupplierIds = $incomingPercentages
            ->where('percentable_type', $supplierMorph)
            ->pluck('percentable_id')->unique()->values()->all();

        $incomingSalesQuery = Sale::query()
            ->with(['product:id,name', 'supplier:id,name', 'agent:id,name'])
            ->where($dateFilter)
            ->where(function ($query) use ($incomingAgentIds, $incomingSupplierIds) {
                if (count($incomingAgentIds)) {
                    $query->whereIn('agent_id', $incomingAgentIds);
                }
                if (count($incomingSupplierIds)) {
                    $method = count($incomingAgentIds) ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('supplier_id', $incomingSupplierIds);
                }
            });

        $incomingSales = ($incomingAgentIds || $incomingSupplierIds)
            ? $incomingSalesQuery->get()
            : collect();

        $incomingBySale = [];
        $incomingDistribution = [];
        $allRelevantSales = [];

        foreach ($ownSales as $sale) {
            $allRelevantSales[$sale->id] = $sale;
        }

        foreach ($incomingSales as $sale) {
            $allRelevantSales[$sale->id] = $sale;
        }

        foreach ($incomingPercentages as $percentage) {
            $isAgentOwner = $percentage->percentable_type === $agentMorph;
            $ownerId = (int) $percentage->percentable_id;

            $matchedSales = $incomingSales->filter(function ($sale) use ($isAgentOwner, $ownerId) {
                return $isAgentOwner
                    ? (int) $sale->agent_id === $ownerId
                    : (int) $sale->supplier_id === $ownerId;
            });

            $turnover = (float) $matchedSales->sum('total_price');
            $amount = round($turnover * (float) $percentage->percentage / 100, 2);

            $owner = $isAgentOwner
                ? Agent::query()->with('user')->find($ownerId)
                : Supplier::query()->find($ownerId);

            $ownerName = $owner?->name
                ?? ($isAgentOwner ? ($owner?->user?->name ?? 'Администратор удалён') : 'Поставщик удалён');

            $incomingDistribution[] = [
                'key' => $percentage->id,
                'owner_id' => $ownerId,
                'owner_type' => $isAgentOwner ? 'agent' : 'supplier',
                'owner_type_label' => $isAgentOwner ? 'Администратор' : 'Поставщик',
                'owner_name' => $ownerName,
                'percentage' => (float) $percentage->percentage,
                'turnover' => round($turnover, 2),
                'amount' => $amount,
            ];

            foreach ($matchedSales as $sale) {
                $incomingBySale[$sale->id] = ($incomingBySale[$sale->id] ?? 0)
                    + round((float) $sale->total_price * (float) $percentage->percentage / 100, 2);
            }
        }

        $salesRows = collect(array_values($allRelevantSales))
            ->map(function ($sale) use ($agent, $ownPercent, $incomingBySale) {
                $isOwnSale = (int) $sale->agent_id === (int) $agent->id;
                $ownAmount = $isOwnSale
                    ? round((float) $sale->total_price * $ownPercent / 100, 2)
                    : 0;
                $incomingAmount = round((float) ($incomingBySale[$sale->id] ?? 0), 2);

                return [
                    'id' => $sale->id,
                    'title' => $sale->title,
                    'actual_delivery_date' => $sale->actual_delivery_date,
                    'total_price' => (float) $sale->total_price,
                    'product_name' => $sale->product?->name,
                    'supplier_name' => $sale->supplier?->name,
                    'agent_name' => $sale->agent?->name,
                    'own_commission' => $ownAmount,
                    'incoming_commission' => $incomingAmount,
                    'commission_total' => round($ownAmount + $incomingAmount, 2),
                ];
            })
            ->sortByDesc('actual_delivery_date')
            ->values();

        $incomingTotal = round(array_sum(array_column($incomingDistribution, 'amount')), 2);
        $outgoingTotal = round((float) $outgoing->where('is_active', true)->sum('amount'), 2);
        $involvedTurnover = round((float) $salesRows->sum('total_price'), 2);
        $netTotal = round($ownCommission + $incomingTotal - $outgoingTotal, 2);

        return response()->json([
            'admin' => [
                'agent_id' => $agent->id,
                'user_id' => $agent->user_id,
                'name' => $agent->name ?: ($user?->name ?? 'Администратор'),
                'email' => $agent->email ?: ($user?->email ?? null),
                'phone' => $agent->phone,
                'region' => $agent->region,
                'role' => (int) ($user?->role ?? 1),
                'role_label' => $this->roleLabel((int) ($user?->role ?? 1)),
                'personal_percent' => $ownPercent,
            ],
            'period' => [
                'month' => $month,
                'from' => $monthStart->toDateString(),
                'to' => $monthEnd->toDateString(),
            ],
            'summary' => [
                'own_turnover' => round($ownTurnover, 2),
                'own_commission' => $ownCommission,
                'incoming_commission' => $incomingTotal,
                'outgoing_commission' => $outgoingTotal,
                'net_total' => $netTotal,
                'involved_turnover' => $involvedTurnover,
                'sales_count' => $salesRows->count(),
            ],
            'distribution' => [
                'outgoing' => $outgoing,
                'incoming' => $incomingDistribution,
            ],
            'sales' => $salesRows,
            'calculation' => [
                'sale_status' => 'completed',
                'date_field' => 'actual_delivery_date',
                'personal_percent_source' => 'users.percent',
                'percentage_source' => 'active percentages',
                'note' => 'Используются текущие активные настройки процентов; история изменения ставок не хранится в этой реализации.',
            ],
        ]);
    }

    private function roleLabel(int $role): string
    {
        return [
            1 => 'Администратор',
            3 => 'Старший администратор',
            4 => 'Суперадмин',
        ][$role] ?? 'Пользователь';
    }
}
