<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Percentage;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyIncomeController extends Controller
{
    /**
     * GET /api/my-income?from=YYYY-MM-DD&to=YYYY-MM-DD
     *
     * Percentage.user_id         — получатель процента.
     * Percentage.percentable_*   — плательщик (владелец оборота).
     *
     * Поддерживаем все типы, которые создаёт PercentageController:
     * User, Agent и Supplier. Legacy-поле percentages.agent_id здесь
     * намеренно не используется: актуальный PercentageController сохраняет
     * правила через percentable_type / percentable_id.
     */
    public function index(Request $request)
    {
        $viewer = $request->botUser ?? $request->user() ?? Auth::user();

        if (!$viewer) {
            return response()->json(['message' => 'Необходима авторизация.'], 401);
        }

        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $from = Carbon::createFromFormat(
            '!Y-m-d',
            $validated['from'] ?? now()->startOfMonth()->toDateString()
        );
        $to = Carbon::createFromFormat(
            '!Y-m-d',
            $validated['to'] ?? now()->endOfMonth()->toDateString()
        );

        if ($from->gt($to)) {
            return response()->json([
                'message' => 'Дата начала периода не может быть позже даты окончания.',
            ], 422);
        }

        // Интервал [начало дня, начало следующего дня): включает весь последний день.
        $dateFrom = $from->toDateString();
        $dateToExclusive = $to->copy()->addDay()->toDateString();

        // Используем morph-классы моделей, чтобы учитывать Laravel morphMap.
        $userMorph = (new User())->getMorphClass();
        $agentMorph = (new Agent())->getMorphClass();
        $supplierMorph = (new Supplier())->getMorphClass();

        $percentages = Percentage::query()
            ->active()
            ->where('user_id', $viewer->id)
            ->whereIn('percentable_type', array_values(array_unique([
                $userMorph,
                $agentMorph,
                $supplierMorph,
            ])))
            ->orderBy('percentable_type')
            ->orderBy('percentable_id')
            ->orderBy('id')
            ->get();

        $rows = $percentages->map(function (Percentage $percentage) use (
            $userMorph,
            $agentMorph,
            $supplierMorph,
            $dateFrom,
            $dateToExclusive
        ) {
            $ownerId = (int) $percentage->percentable_id;
            $morph = $percentage->percentable_type;

            $ownerName = null;
            $ownerType = 'unknown';
            $ownerTypeLabel = 'Неизвестный источник';

            if ($morph === $userMorph) {
                // Плательщик — User. Его сделки принадлежат профилю Agent:
                // sales.agent_id -> agents.id -> agents.user_id.
                $payer = User::query()->find($ownerId);
                $agentName = Agent::query()
                    ->where('user_id', $ownerId)
                    ->orderBy('id')
                    ->value('name');

                $ownerName = $agentName ?: ($payer?->name ?? $payer?->fio_from_telegram);
                $ownerType = 'agent'; // для существующего UI используем карточку агента.
                $ownerTypeLabel = 'Администратор / пользователь';

                // Подзапрос Agent сохраняет scope SoftDeletes и не размножает
                // строки продаж даже при нескольких профилях Agent у одного User.
                $salesAggregate = $this->salesAggregate(
                    $dateFrom,
                    $dateToExclusive,
                    function ($query) use ($ownerId) {
                        $query->whereIn('sales.agent_id', Agent::query()
                            ->select('id')
                            ->where('user_id', $ownerId));
                    }
                );
            } elseif ($morph === $agentMorph) {
                // Поддержка правил, где плательщиком сохранён непосредственно Agent.
                $agent = Agent::query()->with('user')->find($ownerId);
                $ownerName = $agent?->name ?: ($agent?->user?->name ?? $agent?->user?->fio_from_telegram);
                $ownerType = 'agent';
                $ownerTypeLabel = 'Агент';

                $salesAggregate = $this->salesAggregate(
                    $dateFrom,
                    $dateToExclusive,
                    fn ($query) => $query->where('sales.agent_id', $ownerId)
                );
            } elseif ($morph === $supplierMorph) {
                $supplier = Supplier::query()->find($ownerId);
                $ownerName = $supplier?->name;
                $ownerType = 'supplier';
                $ownerTypeLabel = 'Поставщик';

                $salesAggregate = $this->salesAggregate(
                    $dateFrom,
                    $dateToExclusive,
                    fn ($query) => $query->where('sales.supplier_id', $ownerId)
                );
            } else {
                // Неизвестный morph не должен ошибочно считаться поставщиком.
                $salesAggregate = (object) ['sales_count' => 0, 'turnover' => 0];
            }

            $turnover = (float) ($salesAggregate->turnover ?? 0);
            $salesCount = (int) ($salesAggregate->sales_count ?? 0);
            $rate = (float) $percentage->percentage;

            return [
                'id' => $percentage->id,
                'owner_id' => $ownerId,
                'owner_type' => $ownerType,
                'owner_type_label' => $ownerTypeLabel,
                'owner_name' => $ownerName ?: $this->fallbackOwnerName($ownerType),
                'percentage' => $rate,
                'turnover' => round($turnover, 2),
                'income' => round($turnover * $rate / 100, 2),
                'sales_count' => $salesCount,
            ];
        })->values();

        return response()->json([
            'user' => [
                'id' => $viewer->id,
                'name' => $viewer->name ?? $viewer->fio_from_telegram ?? 'Пользователь',
            ],
            'period' => [
                'from' => $dateFrom,
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

    /**
     * Агрегирует завершённые продажи за период без загрузки отношений Sale::$with.
     * Sale::query() сохраняет глобальный SoftDeletes scope модели Sale.
     */
    private function salesAggregate(string $dateFrom, string $dateToExclusive, callable $constraint)
    {
        $query = Sale::query()
            ->without(['product', 'agent', 'customer', 'supplier', 'creator', 'category'])
            ->where('sales.status', 'completed')
            ->whereNotNull('sales.actual_delivery_date')
            ->where('sales.actual_delivery_date', '>=', $dateFrom)
            ->where('sales.actual_delivery_date', '<', $dateToExclusive);

        $constraint($query);

        return $query
            ->selectRaw('COUNT(sales.id) as sales_count, COALESCE(SUM(sales.total_price), 0) as turnover')
            ->first();
    }

    private function fallbackOwnerName(string $ownerType): string
    {
        return match ($ownerType) {
            'supplier' => 'Поставщик не найден',
            'agent' => 'Плательщик не найден',
            default => 'Источник не распознан',
        };
    }
}
