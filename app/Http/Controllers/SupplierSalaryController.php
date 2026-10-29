<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Percentage;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierSalaryController extends Controller
{
    /**
     * GET /suppliers/{supplierId}/salary?month=YYYY-MM
     * Расчёт за календарный месяц по завершённым сделкам и actual_delivery_date.
     */
    public function show(Request $request, int $supplierId)
    {
        $viewer = $request->botUser ?? Auth::user();

        if (!$viewer) {
            return response()->json(['message' => 'Необходима авторизация'], 401);
        }

        $role = (int) ($viewer->role ?? 0);
        if (!in_array($role, [1, 2, 3, 4], true)) {
            return response()->json(['message' => 'Недостаточно прав для просмотра расчёта'], 403);
        }

        $supplier = Supplier::query()->with('user')->findOrFail($supplierId);

        // Роли 1 и 2 могут смотреть только поставщика, привязанного к их учётной записи.
        // Старший администратор и суперадмин (3 и 4) могут смотреть любого поставщика.
        if ($role < 3 && (int) $supplier->user_id !== (int) $viewer->id) {
            return response()->json(['message' => 'Можно просматривать только собственный расчёт'], 403);
        }

        $month = (string) $request->query('month', now()->format('Y-m'));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return response()->json(['message' => 'Неверный формат месяца. Используйте YYYY-MM.'], 422);
        }

        $monthStart = Carbon::createFromFormat('!Y-m', $month)->startOfMonth();
        $nextMonthStart = $monthStart->copy()->addMonth();

        $dateFilter = static function ($query) use ($monthStart, $nextMonthStart) {
            $query->where('status', 'completed')
                ->whereNotNull('actual_delivery_date')
                ->where('actual_delivery_date', '>=', $monthStart->toDateString())
                ->where('actual_delivery_date', '<', $nextMonthStart->toDateString());
        };

        $user = $supplier->user;
        $ownPercent = (float) ($supplier->percent ?? 0);

        // Продажи, привязанные к поставщику, за выбранный календарный месяц.
        $ownSales = Sale::query()
            ->with(['product:id,name', 'supplier:id,name', 'agent:id,name'])
            ->where('supplier_id', $supplier->id)
            ->where($dateFilter)
            ->orderByDesc('actual_delivery_date')
            ->get();

        $ownTurnover = (float) $ownSales->sum('total_price');
        $ownCommission = round($ownTurnover * $ownPercent / 100, 2);

        // Доли, назначенные другим пользователям с продаж этого поставщика.
        $outgoingPercentages = Percentage::query()
            ->with('user')
            ->where('percentable_type', $supplier->getMorphClass())
            ->where('percentable_id', $supplier->id)
            ->get();

        $outgoing = $outgoingPercentages->map(function ($percentage) use ($ownTurnover) {
            $active = (bool) $percentage->is_active;
            return [
                'id' => $percentage->id,
                'user_id' => $percentage->user_id,
                'user_name' => $percentage->user?->name ?? 'Пользователь удалён',
                'percentage' => (float) $percentage->percentage,
                'is_active' => $active,
                'amount' => $active
                    ? round($ownTurnover * (float) $percentage->percentage / 100, 2)
                    : 0,
            ];
        })->values();

        // Активные доли, по которым пользователь поставщика получает доход
        // с продаж других поставщиков и администраторов.
        $agentMorph = (new Agent())->getMorphClass();
        $supplierMorph = (new Supplier())->getMorphClass();
        $incomingPercentages = Percentage::query()
            ->where('user_id', $supplier->user_id)
            ->where('is_active', true)
            ->whereIn('percentable_type', [$agentMorph, $supplierMorph])
            ->get();

        $incomingAgentIds = $incomingPercentages
            ->where('percentable_type', $agentMorph)
            ->pluck('percentable_id')->unique()->values()->all();
        $incomingSupplierIds = $incomingPercentages
            ->where('percentable_type', $supplierMorph)
            ->pluck('percentable_id')->unique()->values()->all();

        $incomingSales = collect();
        if (count($incomingAgentIds) || count($incomingSupplierIds)) {
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
            $incomingSales = $incomingSalesQuery->get();
        }

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

            $incomingDistribution[] = [
                'key' => $percentage->id,
                'owner_id' => $ownerId,
                'owner_type' => $isAgentOwner ? 'agent' : 'supplier',
                'owner_type_label' => $isAgentOwner ? 'Администратор' : 'Поставщик',
                'owner_name' => $owner?->name ?? ($isAgentOwner ? 'Администратор удалён' : 'Поставщик удалён'),
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
            ->map(function ($sale) use ($supplier, $ownPercent, $incomingBySale) {
                $isOwnSale = (int) $sale->supplier_id === (int) $supplier->id;
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
            'supplier' => [
                'supplier_id' => $supplier->id,
                'user_id' => $supplier->user_id,
                'name' => $supplier->name ?: ($user?->name ?? 'Поставщик'),
                'email' => $supplier->email ?: ($user?->email ?? null),
                'phone' => $supplier->phone,
                'work_phone' => $supplier->work_phone,
                'address' => $supplier->address,
                'description' => $supplier->description,
                'birthday' => $supplier->birthday?->format('Y-m-d'),
                'personal_percent' => $ownPercent,
            ],
            'period' => [
                'month' => $month,
                'from' => $monthStart->toDateString(),
                'to' => $nextMonthStart->copy()->subDay()->toDateString(),
            ],
            'summary' => [
                'own_turnover' => round($ownTurnover, 2),
                'own_commission' => $ownCommission,
                'incoming_commission' => $incomingTotal,
                'outgoing_commission' => $outgoingTotal,
                'net_total' => $netTotal,
                'sales_count' => $salesRows->count(),
                'involved_turnover' => $involvedTurnover,
            ],
            'distribution' => [
                'outgoing' => $outgoing,
                'incoming' => $incomingDistribution,
            ],
            'sales' => $salesRows,
            'calculation' => [
                'sale_status' => 'completed',
                'date_field' => 'actual_delivery_date',
                'personal_percent_source' => 'suppliers.percent',
                'note' => 'Расчёт использует текущие значения процентов и применяет их к выбранному месяцу.',
            ],
        ]);
    }
}
