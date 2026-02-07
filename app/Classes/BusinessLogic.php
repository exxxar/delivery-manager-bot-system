<?php

namespace App\Classes;

use App\Enums\RoleEnum;
use App\Models\Agent;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;


class BusinessLogic
{
    public function method(): static
    {
        return $this;
    }

    public function truncateTitle($title, $maxLength = 30)
    {
        if (mb_strlen($title) > $maxLength) {
            return mb_substr($title, 0, $maxLength) . '…';
        }
        return $title;
    }

    public function getGeneralSalesSummaryByAgentsAndSuppliers($defaultPercent = 8, $fromDate = null, $toDate = null, $suppliersIds = [], $agentsIds = []): object
    {

        $query = Sale::query()
            ->select([
                'supplier_id',
                'total_price',
                'sale_date',
            ])
            ->where('status', 'completed')
            ->whereNotNull('sale_date')
            ->orderBy('sale_date');

        // Фильтры периода
        if ($fromDate && $toDate) {
            $query->whereBetween('sale_date', [$fromDate, $toDate]);
        }

        // Фильтр по поставщикам
        if (!empty($suppliersIds)) {
            $query->whereIn('supplier_id', $suppliersIds);
        }

        // Фильтр по агентам
        if (!empty($agentsIds)) {
            $query->whereIn('agent_id', $agentsIds);
        }

        // Загружаем только минимально необходимые данные о поставщике
        $sales = $query
            ->with(['supplier' => function ($q) {
                $q->select('id', 'name', 'percent');
            }])
            ->get();

        // Группируем сразу по поставщику
        $grouped = $sales->groupBy('supplier_id');

        $results = [];
        $totalSum = 0;
        $totalPercentage = 0;

        $months = [
            1  => 'january',
            2  => 'february',
            3  => 'march',
            4  => 'april',
            5  => 'may',
            6  => 'june',
            7  => 'july',
            8  => 'august',
            9  => 'september',
            10 => 'october',
            11 => 'november',
            12 => 'december',
        ];

        foreach ($grouped as $supplierId => $salesBySupplier)
        {
            $supplier = $salesBySupplier->first()->supplier;

            $supplierName = $supplier?->name ?? 'Неизвестный поставщик';
            $percent      = $supplier?->percent ?? $defaultPercent;
            $rate         = $percent / 100;

            // Инициализируем все месяцы нулями
            $monthly = array_fill_keys(array_values($months), 0);

            foreach ($salesBySupplier as $sale)
            {
                $monthNum = (int) Carbon::parse($sale->sale_date)->format('n');
                $monthly[$months[$monthNum]] += (float) $sale->total_price;
            }

            $yearTotal = array_sum($monthly);

            $yourPercentage = $yearTotal * $rate;

            $results[] = [
                'supplier'     => $supplierName,
                'base_percent' => $rate,               // в долях (0.08 вместо 8)
                'percentage'   => $yourPercentage,
                'january'      => $monthly['january'],
                'february'     => $monthly['february'],
                'march'        => $monthly['march'],
                'april'        => $monthly['april'],
                'may'          => $monthly['may'],
                'june'         => $monthly['june'],
                'july'         => $monthly['july'],
                'august'       => $monthly['august'],
                'september'    => $monthly['september'],
                'october'      => $monthly['october'],
                'november'     => $monthly['november'],
                'december'     => $monthly['december'],
                'year'         => $yearTotal,
            ];

            $totalSum += $yearTotal;
            $totalPercentage += $yourPercentage;
        }

        return (object) [
            'details'          => $results,
            'total_sum'        => $totalSum,
            'total_percentage' => $totalPercentage,
        ];
    }

    public function getMonthlySalesSummaryForAllAgentsByEachSupplier($from = null, $to = null): array
    {
        $fromDate = $from ?: Carbon::now(); // начальная дата
        $toDate = $to ?: Carbon::now()->addMonth();                   // конечная дата

        // Получаем список поставщиков
        $suppliers = Supplier::query()->take(2)->get();

        $finalResult = [];
        foreach ($suppliers as $supplier) {
            $finalResult[] = $this->getMonthlySalesSummaryForAllAgentsByCurrentSupplier($supplier, $fromDate, $toDate);
        }

        return $finalResult;
    }

    public function getAdminsMonthlyByAgentRevenue($agent, $startDate, $endDate, $suppliersIds = [])
    {

        $taxPercent     = (float) env('TAX_PERCENT', 8);
        $transferPercent = (float) env('TRANSFER_PERCENT', 8);
        $agentPercent   = (float) env('AGENT_PERCENT', 4);
        $adminBasePercent = (float) env('ADMIN_BASE_PERCENT', 5); // пример

        // Один запрос — все нужные продажи
        $sales = Sale::query()
            ->where('agent_id', $agent->id)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->when($suppliersIds, fn($q) => $q->whereIn('supplier_id', $suppliersIds))
            ->where('status', 'completed')
            ->select('sale_date', 'supplier_id', 'total_price', 'payment_type', 'mentor_award','id')
            ->with('supplier:id,name,percent')
            ->get();

        $totalSales = $sales->sum('total_price');

        $taxAmount  = $totalSales * ($taxPercent / 100);
        $afterTax   = $totalSales - $taxAmount;

        $revenueTotal = 0.0;
        $revenueWithoutTaxTotal = 0.0;
        $mentorAwards = [];

        $mentorId = $agent->mentor_id; // ← предполагаем relation или поле

        $salesByDateSupplier = $sales->map(function ($sale) use (
            $taxPercent, $transferPercent, $mentorId, &$revenueTotal, &$revenueWithoutTaxTotal, &$mentorAwards
        ) {
            $percent = $sale->supplier?->percent ?? 0;
            $revenueLocal = $sale->total_price * ($percent / 100);

            $taxOnRevenue = $revenueLocal * ($taxPercent / 100);
            $revenueAfterTax = $revenueLocal - $taxOnRevenue;

            $revenueTotal += $revenueLocal;
            $revenueWithoutTaxTotal += $revenueAfterTax;

            if ($mentorId !== null && ($sale->mentor_award ?? 0) > 0) {
                $mentorAwards[$mentorId] = ($mentorAwards[$mentorId] ?? 0) + $sale->mentor_award;
            }

            return [
                'date'              => $sale->sale_date,
                'supplier_id'       => $sale->supplier_id,
                'supplier_name'     => $sale->supplier?->name ?? 'Неизвестный поставщик',
                'sale_amount'       => $sale->total_price,
                'payment_type'      => $sale->payment_type,
                'id'      => $sale->id,
                'percent'           => $percent,
                'transfer'          => $revenueLocal * ($transferPercent / 100),
                'revenue_total'     => $revenueLocal,
                'revenue_after_tax' => $revenueAfterTax,
            ];
        });

        // Админы — кэшировать в продакшене!
        $adminsRevenue = User::query()
            ->where('is_work', true)
            ->whereIn('role', [RoleEnum::SUPERADMIN->value, RoleEnum::ADMIN->value])
            ->get(['id', 'fio_from_telegram', 'name', 'percent'])
            ->map(function ($user) use ($revenueTotal, $revenueWithoutTaxTotal, $mentorAwards, $taxPercent) {
                $mentorAward = $mentorAwards[$user->id] ?? 0;
                $mentorAwardAfterTax = $mentorAward * (1 - $taxPercent / 100);

                $userPercent = $user->percent > 0 ? $user->percent : env('ADMIN_BASE_PERCENT', 4);

                $incomeTotal = $revenueTotal * ($userPercent / 100);
                $incomeAfterTax = $revenueWithoutTaxTotal * ($userPercent / 100) + $mentorAwardAfterTax;

                return [
                    'admin_id'                   => $user->id,
                    'admin_name'                 => $user->fio_from_telegram ?? $user->name ?? '-',
                    'percent'                    => $userPercent,
                    'mentor_award'               => $mentorAward,
                    'income_with_award_total'    => $incomeTotal + $mentorAward,
                    'income_with_award_after_tax'=> $incomeAfterTax + $mentorAwardAfterTax,
                    'income_total'               => $incomeTotal,
                    'income_after_tax'           => $incomeAfterTax,
                ];
            });

        $transferFromTotal     = $revenueTotal * ($transferPercent / 100);
        $transferFromAfterTax  = $revenueWithoutTaxTotal * ($transferPercent / 100);

        return [
            'agent' => [
                'id'     => $agent->id,
                'name'   => $agent->name,
                'salary' => $afterTax * ($agentPercent / 100),
            ],
            'period' => [
                'start' => $startDate,
                'end'   => $endDate,
            ],
            'summary' => [
                'total_sales'               => $totalSales,
                'tax_percent'               => $taxPercent,
                'tax_amount'                => $taxAmount,
                'after_tax'                 => $afterTax,
                'transfer_percent'          => $transferPercent,
                'transfer_from_total'       => $transferFromTotal,
                'transfer_from_after_tax'   => $transferFromAfterTax,
                'revenue_total'             => $revenueTotal,
                'revenue_without_tax_total' => $revenueWithoutTaxTotal,
            ],
            'sales_by_date_supplier' => $salesByDateSupplier,
            'admins' => $adminsRevenue,
        ];
    }

    public function getMonthlySalesSummaryForAllAgentsByCurrentSupplier(
        $supplier,
        $fromDate,
        $toDate,
        $agentsIds = []
    ): array {

        $from = $fromDate->copy()->startOfDay();
        $to   = $toDate->copy()->endOfDay();

        // 1. Один агрегирующий запрос
        $salesQuery = Sale::query()
            ->where('supplier_id', $supplier->id)
            ->where('status', 'completed')
            ->where('total_price', '>', 0)
            ->whereBetween('sale_date', [$from, $to])
            ->selectRaw('
            DATE(sale_date) as sale_day,
            agent_id,
            SUM(total_price) as daily_total
        ')
            ->groupByRaw('DATE(sale_date), agent_id');

        if (!empty($agentsIds)) {
            $salesQuery->whereIn('agent_id', $agentsIds);
        }

        $rawSales = $salesQuery->get();

        /*
         * 2. Строим быстрый lookup-массив:
         * $salesMap[дата][agent_id] = сумма
         */
        $salesMap = [];

        foreach ($rawSales as $sale) {
            $salesMap[$sale->sale_day][$sale->agent_id] = (float) $sale->daily_total;
        }

        // 3. Загружаем агентов один раз
        $agentsQuery = Agent::query()
            ->where('is_test', false);

        if (!empty($agentsIds)) {
            $agentsQuery->whereIn('id', $agentsIds);
        }

        $agents = $agentsQuery->get()->keyBy('id');

        // 4. Генерация периода
        $period = \Carbon\CarbonPeriod::create($from, $to);

        $report = [];
        $grandTotal = 0.0;

        $daysOfWeek = [
            'Sunday'    => 'Воскресенье',
            'Monday'    => 'Понедельник',
            'Tuesday'   => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday'  => 'Четверг',
            'Friday'    => 'Пятница',
            'Saturday'  => 'Суббота',
        ];

        foreach ($period as $date) {

            $dateStr = $date->format('Y-m-d');
            $dailySum = 0.0;
            $dayDetails = [];

            foreach ($agents as $agent) {

                // ⚡ Мгновенный доступ без firstWhere
                $price = $salesMap[$dateStr][$agent->id] ?? 0.0;

                $dayDetails[] = [
                    'price'       => $price,
                    'supplier_id' => $supplier->id,
                    'agent_id'    => $agent->id,
                ];

                $dailySum += $price;
            }

            $report[] = [
                'date'        => $date->format('d.m.Y'),
                'total'       => $dailySum,
                'day_name'    => $daysOfWeek[$date->englishDayOfWeek] ?? '—',
                'day_details' => $dayDetails,
            ];

            $grandTotal += $dailySum;
        }

        return [
            'id'        => $supplier->id,
            'title'     => $supplier->name ?? 'Неизвестный поставщик',
            'agents'    => $agents->values(),
            'total_sum' => $grandTotal,
            'period'    => $report,
        ];
    }

    public function getPersonalAgentSales(int $agentId, $fromDate = null, $toDate = null)
    {

        $fromDate = $fromDate ?: Carbon::now(); // начальная дата
        $toDate = $toDate ?: Carbon::now()->addMonth();

        // Получаем все продажи агента за указанный период
        $sales = Sale::with('supplier')
            ->where('agent_id', $agentId)
            ->where("status", "completed")
            ->whereBetween('sale_date', [$fromDate, $toDate])
            ->get();

        // Массив для хранения итоговых данных
        $result = [];

        foreach ($sales as $sale) {
            // Извлекаем данные о поставщике
            $supplier = $sale->supplier;

            // Собираем данные для строки отчета
            $result[] = [
                'sale_date' => Carbon::parse($sale->sale_date)->format('d.m.Y'), // дата продажи
                'supplier_name' => $supplier->name ?? 'Неизвестный поставщик',               // название поставщика
                'total_price' => $sale->total_price,              // сумма продажи
                'percent' => $supplier->percent,              // процент с продажи
                'commission' => $supplier->percent * $sale->total_price, // выручка (комиссия)
            ];
        }

        return $result;
    }

    public function getSalesGroupedByStatus(int $createdById, string $dateFrom, string $dateTo): array
    {
        // выборка по created_by_id и периоду
        $query = Sale::query()->where('created_by_id', $createdById)
            ->whereBetween('due_date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ])
            ->orderBy('due_date', 'asc');

        $sales = $query->get();


        // формируем массив групп
        $result = [
            'all' => [],
            'pending' => [],
            'assigned' => [],
            'delivered' => [],
            'completed' => [],
            'rejected' => [],
        ];

        foreach ($sales as $sale) {
            // все заказы
            $result['all'][] = $sale;

            // по статусу
            $result[$sale->status][] = $sale;
        }

        return $result;
    }

    public function getProducts()
    {
        $products = Product::query()
            ->with('category', 'supplier')
            ->get();

        return $products;
    }

    public function getSuppliers()
    {
        $suppliers = Supplier::query()
            ->get();

        return $suppliers;
    }

    public function birthdaysNext($fromDate, $toDate)
    {
        $start = $fromDate->format('m-d');;
        $end = $toDate->format('m-d');;


        // Функция фильтрации по диапазону (учитывает переход через Новый год)
        $filterByRange = function ($query) use ($start, $end) {
            if ($start <= $end) {
                // Обычный диапазон
                return $query->whereRaw("DATE_FORMAT(birthday, '%m-%d') BETWEEN ? AND ?", [$start, $end]);
            } else {
                // Диапазон пересекает Новый год
                return $query->where(function ($q) use ($start, $end) {
                    $q->whereRaw("DATE_FORMAT(birthday, '%m-%d') >= ?", [$start])
                        ->orWhereRaw("DATE_FORMAT(birthday, '%m-%d') <= ?", [$end]);
                });
            }
        };

        // Пользователи
        $users = $filterByRange(User::query())
            ->get()
            ->map(function ($u) {
                $b = Carbon::parse($u->birthday);

                return [
                    'name' => $u->name,
                    'date' => $b->format('Y-m-d'),
                    'weekday' => Carbon::create(now()->year, $b->month, $b->day)
                        ->locale('ru')
                        ->dayName,
                    'type' => 'пользователь',
                ];
            });

        // Поставщики
        $suppliers = $filterByRange(Supplier::query())
            ->get()
            ->map(function ($s) {
                $b = Carbon::parse($s->birthday);

                return [
                    'name' => $s->name,
                    'date' => $b->format('Y-m-d'),
                    'weekday' => Carbon::create(now()->year, $b->month, $b->day)
                        ->locale('ru')
                        ->dayName,
                    'type' => 'поставщик',

                ];
            });

        // Объединяем и сортируем по MM-DD (игнорируя год)
        return collect($users->toArray())
            ->merge($suppliers->toArray())
            ->sortBy(function ($item) {
                return Carbon::parse($item['date'])->format('m-d');
            })
            ->values();


    }
}
