<?php

namespace App\Console\Commands;

use App\Enums\RoleEnum;
use App\Exports\ExportType1\SummarySuppliersReport;
use App\Exports\ExportType4\SummaryAgentReport;


use App\Exports\SalesByAgentReport;
use App\Facades\BotMethods;
use App\Models\User;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Telegram\Bot\FileUpload\InputFile;

class GenerateReport extends Command
{
    protected $signature = 'app:generate-report';
    protected $description = 'Генерация отчетов для всех администраторов и суперадминов';

    public function handle()
    {
        ini_set('memory_limit', '3000M');
        ini_set('max_execution_time', 3000);

        $startDate = Carbon::now("+3")->startOfMonth();
        $endDate = Carbon::now("+3")->endOfMonth();
        $yearStart = Carbon::now("+3")->startOfYear();
        $yearEnd = Carbon::now("+3")->endOfYear();

        $this->info('Получение списка администраторов и суперадминов...');

        $admins = User::whereIn('role', [
            RoleEnum::ADMIN->value,
            RoleEnum::SUPERADMIN->value
        ])->get();

        $this->info("Найдено администраторов: " . $admins->count());

        $exportService = app(ExportService::class);

        foreach ($admins as $admin) {
            $this->info("Обработка администратора: {$admin->name} (ID: {$admin->id})");

            try {
                // 1. Продажи за период
                $fileName = "продажи-за-период-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}-{$admin->id}.xlsx";
                $exportService->saveReport(
                    $admin,
                    "Экспорт истории продаж за период {$startDate->format('d.m.Y')} - {$endDate->format('d.m.Y')}",
                    $fileName,
                    new SalesByAgentReport($startDate, $endDate),
                    [],
                    'sales_period',
                    $startDate,
                    $endDate
                );
                $this->info("  ✓ Создан отчет: Продажи за период");

                // 2. Продажи за год
                $fileName = "продажи-за-год-{$yearStart->format('Y')}-{$admin->id}.xlsx";
                $exportService->saveReport(
                    $admin,
                    "Экспорт истории продаж за год {$yearStart->format('Y')}",
                    $fileName,
                    new SalesByAgentReport($yearStart, $yearEnd),
                    [],
                    'sales_year',
                    $yearStart,
                    $yearEnd
                );
                $this->info("  ✓ Создан отчет: Продажи за год");

                // 3. Отчет по зарплатам за период
                $fileName = "отчет-по-зарплатам-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}-{$admin->id}.xlsx";
                $exportService->saveReport(
                    $admin,
                    "Отчет по зарплатам за период {$startDate->format('d.m.Y')} - {$endDate->format('d.m.Y')}",
                    $fileName,
                    new SummaryAgentReport(
                        resultType: 0,
                        fromDate: $startDate,
                        toDate: $endDate,
                    ),
                    [],
                    'salary_period',
                    $startDate,
                    $endDate
                );
                $this->info("  ✓ Создан отчет: Зарплаты за период");

                // 4. Отчет по поставщикам за период
                $fileName = "отчет-по-поставщикам-{$startDate->format('Y-m-d')}-{$endDate->format('Y-m-d')}-{$admin->id}.xlsx";
                $exportService->saveReport(
                    $admin,
                    "Отчет по поставщикам за период {$startDate->format('d.m.Y')} - {$endDate->format('d.m.Y')}",
                    $fileName,
                    new SummarySuppliersReport(
                        fromDate: $startDate,
                        toDate: $endDate
                    ),
                    [],
                    'suppliers_period',
                    $startDate,
                    $endDate
                );
                $this->info("  ✓ Создан отчет: Поставщики за период");

                // 5. Отчет по поставщикам за год
                $fileName = "отчет-по-поставщикам-за-год-{$yearStart->format('Y')}-{$admin->id}.xlsx";
                $exportService->saveReport(
                    $admin,
                    "Отчет по поставщикам за год {$yearStart->format('Y')}",
                    $fileName,
                    new SummarySuppliersReport(
                        fromDate: $yearStart,
                        toDate: $yearEnd
                    ),
                    [],
                    'suppliers_year',
                    $yearStart,
                    $yearEnd
                );
                $this->info("  ✓ Создан отчет: Поставщики за год");

                $this->info("  ✅ Завершено для администратора: {$admin->name}");

            } catch (\Exception $e) {
                $this->error("  ❌ Ошибка для администратора {$admin->name}: " . $e->getMessage());
            }
        }

        $this->info('✅ Генерация отчетов завершена!');
    }
}
