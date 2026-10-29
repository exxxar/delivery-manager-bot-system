<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Percentage;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyIncomeController extends Controller
{
    /**
     * GET /my-income?from=YYYY-MM-DD&to=YYYY-MM-DD
     * Показывает только активные доли, назначенные текущему пользователю,
     * и начисления с завершённых сделок за выбранный календарный период.
     */
    public function index(Request $request)
    {
        $viewer = $request->botUser ?? Auth::user();

        if (!$viewer) {
            return response()->json(['message' => 'Необходима авторизация'], 401);
        }

        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $from = Carbon::createFromFormat('!Y-m-d', $validated['from'] ?? now()->startOfMonth()->toDateString());
        $to = Carbon::createFromFormat('!Y-m-d', $validated['to'] ?? now()->endOfMonth()->toDateString());

        if ($from->gt($to)) {
            return response()->json(['message' => 'Дата начала периода не может быть позже даты окончания.'], 422);
        }

        // Percentages.user_id — пользователь, которому начисляется доля.
        // Владелец оборота определяется через polymorphic percentable relation.
        $agentMorph = (new Agent())->getMorphClass();
        $supplierMorph = (new Supplier())->getMorphClass();

        $percentages = Percentage::query()
            ->where('user_id', $viewer->id)
            ->where('is_active', true)
            ->whereIn('percentable_type', [$agentMorph, $supplierMorph])
            ->orderBy('percentable_type')
            ->orderBy('percentable_id')
            ->get();

        $rows = $percentages->map(function (Percentage $percentage) use (
            $agentMorph,
            $from,
            $to
        ) {
            $isAgent = $percentage->percentable_type === $agentMorph;
            $ownerId = (int) $percentage->percentable_id;
            $owner = $isAgent
                ? Agent::query()->with('user')->find($ownerId)
                : Supplier::query()->find($ownerId);

            $salesQuery = Sale::query()
                ->where('status', 'completed')
                ->whereNotNull('actual_delivery_date')
                ->where('actual_delivery_date', '>=', $from->toDateString())
                ->where('actual_delivery_date', '<', $to->copy()->addDay()->toDateString());

            if ($isAgent) {
                $salesQuery->where('agent_id', $ownerId);
            } else {
                $salesQuery->where('supplier_id', $ownerId);
            }

            $sales = $salesQuery->get(['id', 'total_price']);
            $turnover = (float) $sales->sum('total_price');
            $rate = (float) $percentage->percentage;

            $ownerName = $owner?->name;
            if (!$ownerName && $isAgent) {
                $ownerName = $owner?->user?->name;
            }

            return [
                'id' => $percentage->id,
                'owner_id' => $ownerId,
                'owner_type' => $isAgent ? 'agent' : 'supplier',
                'owner_type_label' => $isAgent ? 'Администратор' : 'Поставщик',
                'owner_name' => $ownerName ?: ($isAgent ? 'Администратор удалён' : 'Поставщик удалён'),
                'percentage' => $rate,
                'turnover' => round($turnover, 2),
                'income' => round($turnover * $rate / 100, 2),
                'sales_count' => $sales->count(),
            ];
        })->values();

        return response()->json([
            'user' => [
                'id' => $viewer->id,
                'name' => $viewer->name ?? 'Пользователь',
            ],
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'summary' => [
                'sources_count' => $rows->count(),
                'total_income' => round((float) $rows->sum('income'), 2),
                'total_turnover' => round((float) $rows->sum('turnover'), 2),
                'sales_count' => (int) $rows->sum('sales_count'),
            ],
            'sources' => $rows,
        ]);
    }
}
