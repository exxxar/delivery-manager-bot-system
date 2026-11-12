<?php

namespace App\Services\Reports;

use App\Enums\RoleEnum;
use App\Facades\BusinessLogicFacade;
use App\Models\Agent;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;

/**
 * Формирует JSON-данные сводного отчёта по активным агентам и поставщикам.
 * Активность определяется по завершённым продажам за переданный период.
 */
class SummaryAgentReportService
{
    public function build(
        Carbon $fromDate,
        Carbon $toDate
    ): array {
        $from = $fromDate->copy()->startOfDay();
        $to = $toDate->copy()->endOfDay();

        // Единые условия активности: завершённая продажа с фактической датой доставки в периоде.
        $activeSales = static function ($query) use ($from, $to): void {
            $query
                ->where('status', 'completed')
                ->whereBetween('actual_delivery_date', [$from, $to]);
        };

        // Активные агенты. Тестовые агенты исключаются независимо от количества продаж.
        $agents = Agent::query()
            ->with([
                'user:id,name,fio_from_telegram,percent',
                'percentages.user',
            ])
            ->withCount(['sales as report_sales_count' => $activeSales])
            ->having('report_sales_count', '>', 0)
            ->where('is_test', false)
            ->whereHas('user', function ($query) {
                $query->where('role', RoleEnum::AGENT->value);
            })
            ->orderBy('name')
            ->get();

        // Активные поставщики определяются по всем продажам за период,
        // без ограничения продажами текущего пользователя.
        $suppliers = Supplier::query()
            ->whereHas('sales', $activeSales)
            ->orderBy('name')
            ->get(['id', 'name', 'birthday', 'phone']);

        $activeSupplierIds = $suppliers->pluck('id')->map(fn ($id) => (int) $id)->all();

        $agentReports = [];
        foreach ($agents as $agent) {
            // Расчёты остаются в существующей бизнес-логике проекта.
            // Пустой список поставщиков сохраняет поведение полного Excel-отчёта:
            // учитываются все завершённые продажи агента за период.
            $data = BusinessLogicFacade::method()->getAdminsMonthlyByAgentRevenue(
                $agent,
                $from,
                $to,
                []
            );

            $agentReports[] = [
                'agent' => $data['agent'] ?? [],
                'period' => $data['period'] ?? [
                        'start' => $from->toDateTimeString(),
                        'end' => $to->toDateTimeString(),
                    ],
                'summary' => $data['summary'] ?? [],
                'sales' => collect($data['sales_by_date_supplier'] ?? [])->values()->all(),
                'admins' => collect($data['admins'] ?? [])->values()->all(),
            ];
        }

        $summarySheet = $this->buildSummarySheet($agentReports);
        $summary = $this->buildGlobalSummary($agentReports);

        // Товары только тех поставщиков, которые активны за период.
        $products = empty($activeSupplierIds)
            ? collect()
            : Product::query()
                ->with(['category', 'supplier'])
                ->whereIn('supplier_id', $activeSupplierIds)
                ->orderBy('name')
                ->get();

        return [
            'period' => [
                'start' => $from->toDateString(),
                'end' => $to->toDateString(),
            ],
            'agents' => $agentReports,
            'summary' => $summary,
            'summary_sheet' => $summarySheet,
            'suppliers' => $suppliers->values(),
            'products' => $products->values(),
            'counts' => [
                'agents' => count($agentReports),
                'suppliers' => $suppliers->count(),
                'products' => $products->count(),
            ],
        ];
    }

    private function buildSummarySheet(array $agentReports): array
    {
        $adminMap = [];
        $rows = [];
        $totals = [
            'total_sales' => 0.0,
            'after_tax' => 0.0,
            'revenue_without_tax_total' => 0.0,
            'revenue_total' => 0.0,
            'revenue_tax_amount' => 0.0,
            'revenue_after_tax' => 0.0,
            'agent_reward' => 0.0,
            'agent_reward_tax' => 0.0,
            'agent_reward_after_tax' => 0.0,
            'agent_salary' => 0.0,
            'transfer_from_after_tax' => 0.0,
            'admin_incomes' => [],
            'admin_income_details' => [],
        ];

        foreach ($agentReports as $report) {
            $agent = $report['agent'] ?? [];
            $summary = $report['summary'] ?? [];
            $adminIncomes = [];

            foreach (($report['admins'] ?? []) as $admin) {
                $adminId = (int) ($admin['admin_id'] ?? 0);
                if (!$adminId) {
                    continue;
                }

                if (!isset($adminMap[$adminId])) {
                    $adminMap[$adminId] = [
                        'admin_id' => $adminId,
                        'admin_name' => $admin['admin_name'] ?? '—',
                        'income_total' => 0.0,
                        'income_tax' => 0.0,
                        'income_after_tax' => 0.0,
                        'income_with_award_total' => 0.0,
                        'income_with_award_tax' => 0.0,
                        'income_with_award_after_tax' => 0.0,
                    ];
                }

                $incomeAfterTax = (float) ($admin['income_after_tax'] ?? 0);
                $incomeTotal = (float) ($admin['income_total'] ?? 0);
                $incomeTax = max(0.0, $incomeTotal - $incomeAfterTax);
                $incomeWithAwardTotal = (float) ($admin['income_with_award_total'] ?? $incomeTotal);
                $incomeWithAwardAfterTax = (float) ($admin['income_with_award_after_tax'] ?? $incomeAfterTax);
                $incomeWithAwardTax = max(0.0, $incomeWithAwardTotal - $incomeWithAwardAfterTax);

                $adminIncomes[$adminId] = ($adminIncomes[$adminId] ?? 0) + $incomeAfterTax;

                $adminMap[$adminId]['income_total'] += $incomeTotal;
                $adminMap[$adminId]['income_tax'] += $incomeTax;
                $adminMap[$adminId]['income_after_tax'] += $incomeAfterTax;
                $adminMap[$adminId]['income_with_award_total'] += $incomeWithAwardTotal;
                $adminMap[$adminId]['income_with_award_tax'] += $incomeWithAwardTax;
                $adminMap[$adminId]['income_with_award_after_tax'] += $incomeWithAwardAfterTax;
            }

            $rewardGross = (float) ($summary['total_reward'] ?? $agent['reward'] ?? 0);
            $taxPercent = (float) ($summary['tax_percent'] ?? env('TAX_PERCENT', 6));
            $rewardTax = round($rewardGross * $taxPercent / 100, 2);
            $revenueGross = (float) ($summary['revenue_total'] ?? 0);
            $revenueTax = round($revenueGross * $taxPercent / 100, 2);
            $adminIncomeDetails = [];
            foreach (($report['admins'] ?? []) as $admin) {
                $adminId = (int) ($admin['admin_id'] ?? 0);
                if (!$adminId) {
                    continue;
                }
                $gross = (float) ($admin['income_total'] ?? 0);
                $net = (float) ($admin['income_after_tax'] ?? 0);
                $adminIncomeDetails[$adminId] = [
                    'gross' => $gross,
                    'tax' => max(0.0, $gross - $net),
                    'net' => $net,
                ];
            }

            $row = [
                'agent_id' => $agent['id'] ?? null,
                'agent_name' => $agent['name'] ?? '—',
                'total_sales' => (float) ($summary['total_sales'] ?? 0),
                'revenue_total' => $revenueGross,
                'revenue_tax_amount' => $revenueTax,
                'revenue_after_tax' => $revenueGross - $revenueTax,
                'revenue_without_tax_total' => (float) ($summary['revenue_without_tax_total'] ?? 0),
                'agent_reward' => $rewardGross,
                'agent_reward_tax' => $rewardTax,
                'agent_reward_after_tax' => $rewardGross - $rewardTax,
                'admin_income_details' => $adminIncomeDetails,
                'agent_salary' => (float) ($agent['salary'] ?? 0),
                'transfer_from_after_tax' => (float) ($summary['transfer_from_after_tax'] ?? 0),
                'admin_incomes' => $adminIncomes,
            ];
            $rows[] = $row;

            $totals['total_sales'] += $row['total_sales'];
            // Сохраняем смысл поля, используемого старым сводным листом Excel.
            $totals['after_tax'] += (float) ($summary['after_tax'] ?? 0);
            $totals['revenue_without_tax_total'] += $row['revenue_without_tax_total'];
            $totals['revenue_total'] += $row['revenue_total'];
            $totals['revenue_tax_amount'] += $row['revenue_tax_amount'];
            $totals['revenue_after_tax'] += $row['revenue_after_tax'];
            $totals['agent_reward'] += $row['agent_reward'];
            $totals['agent_reward_tax'] += $row['agent_reward_tax'];
            $totals['agent_reward_after_tax'] += $row['agent_reward_after_tax'];
            $totals['agent_salary'] += $row['agent_salary'];
            $totals['transfer_from_after_tax'] += $row['transfer_from_after_tax'];

            foreach ($adminIncomes as $adminId => $income) {
                $totals['admin_incomes'][$adminId] = ($totals['admin_incomes'][$adminId] ?? 0) + $income;
            }
            foreach ($adminIncomeDetails as $adminId => $details) {
                foreach (['gross', 'tax', 'net'] as $key) {
                    $totals['admin_income_details'][$adminId][$key] =
                        ($totals['admin_income_details'][$adminId][$key] ?? 0) + $details[$key];
                }
            }
        }

        return [
            'admins' => array_values($adminMap),
            'rows' => $rows,
            'totals' => $totals,
        ];
    }

    private function buildGlobalSummary(array $agentReports): array
    {
        $summary = [
            'agents_count' => count($agentReports),
            'total_sales' => 0.0,
            'total_reward' => 0.0,
            'total_reward_tax' => 0.0,
            'total_reward_after_tax' => 0.0,
            'agent_salary' => 0.0,
            'tax_amount' => 0.0,
            'after_tax' => 0.0,
            'revenue_total' => 0.0,
            'revenue_tax_amount' => 0.0,
            'revenue_after_tax' => 0.0,
            'revenue_without_tax_total' => 0.0,
            'transfer_from_total' => 0.0,
            'transfer_from_after_tax' => 0.0,
        ];

        // Ставка налога едина для отчёта и берётся из той же бизнес-логики,
        // которая рассчитывает налог по каждому агенту.
        $summary['tax_percent'] = (float) ($agentReports[0]['summary']['tax_percent'] ?? env('TAX_PERCENT', 6));

        foreach ($agentReports as $report) {
            $agentSummary = $report['summary'] ?? [];
            foreach ([
                         'total_sales', 'total_reward', 'tax_amount', 'after_tax',
                         'revenue_total', 'revenue_without_tax_total',
                         'transfer_from_total', 'transfer_from_after_tax',
                     ] as $key) {
                $summary[$key] += (float) ($agentSummary[$key] ?? 0);
            }
            $rewardGross = (float) ($agentSummary['total_reward'] ?? $report['agent']['reward'] ?? 0);
            $rewardTax = round($rewardGross * $summary['tax_percent'] / 100, 2);
            $summary['total_reward_tax'] += $rewardTax;
            $summary['total_reward_after_tax'] += ($rewardGross - $rewardTax);

            $revenueGross = (float) ($agentSummary['revenue_total'] ?? 0);
            $revenueTax = round($revenueGross * $summary['tax_percent'] / 100, 2);
            $summary['revenue_tax_amount'] += $revenueTax;
            $summary['revenue_after_tax'] += ($revenueGross - $revenueTax);

            $summary['agent_salary'] += (float) ($report['agent']['salary'] ?? 0);
        }

        return $summary;
    }
}
