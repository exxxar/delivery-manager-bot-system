<?php

namespace App\Console\Commands;

use App\Exports\ExportType1\SummarySuppliersReport;
use App\Exports\ExportType4\SummaryAgentReport;


use App\Exports\SalesByAgentReport;
use App\Facades\BotMethods;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Telegram\Bot\FileUpload\InputFile;

class GenerateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '3000M');
        ini_set('max_execution_time', 3000); //300 seconds = 5 minutes
        $startDate = Carbon::now("+3")->startOfMonth();
        $endDate = Carbon::now("+3")->endOfMonth();

        $channel = env("TELEGRAM_ADMIN_CHANNEL");

        $currentDayStart = Carbon::now("+3")->startOfDay();
        $currentDayEnd = Carbon::now("+3")->endOfDay();

        $fileName = "продажи за текущий день $currentDayStart - $currentDayEnd.xlsx";
        $data = Excel::raw(new SalesByAgentReport($currentDayStart, $currentDayEnd),
            \Maatwebsite\Excel\Excel::XLSX);

        BotMethods::bot()
            ->sendDocument($channel, "#отчет\nЭкспорт истории продаж за период<b>$startDate</b> - <b>$endDate</b>",
                InputFile::createFromContents($data, $fileName));

        $fileName = "продажи за период $startDate - $endDate.xlsx";
        $data = Excel::raw(new SalesByAgentReport($startDate, $endDate),
            \Maatwebsite\Excel\Excel::XLSX);

        BotMethods::bot()
            ->sendDocument($channel, "#отчет\nЭкспорт истории продаж за период<b>$startDate</b> - <b>$endDate</b>",
                InputFile::createFromContents($data, $fileName));

        $fileName = "продажи за год.xlsx";
        $data = Excel::raw(new SalesByAgentReport(
            Carbon::now("+3")->startOfYear(),
            Carbon::now("+3")->endOfYear()
        ), \Maatwebsite\Excel\Excel::XLSX);

        BotMethods::bot()
            ->sendDocument($channel, "#отчет\nЭкспорт истории продаж за год",
                InputFile::createFromContents($data, $fileName));
        //-----------------------------------------------------------

        $content = Excel::raw(new SummaryAgentReport(
            resultType: 0,
            fromDate: $startDate,
            toDate: $endDate,
        ), \Maatwebsite\Excel\Excel::XLSX);

        $fileName = "Отчет по зарплатам $startDate - $endDate.xlsx";
        BotMethods::bot()
            ->sendDocument($channel,
                "#отчет\nОтчет по зарплатам <b>$startDate</b> - <b>$endDate</b>",
                InputFile::createFromContents($content, $fileName));


        //-----------------------------------------------------

        $content =
            Excel::raw(new SummarySuppliersReport(
                fromDate: $startDate,
                toDate: $endDate
            ), \Maatwebsite\Excel\Excel::XLSX);

        $fileName = "Отчет по поставщикам  $startDate - $endDate.xlsx";
        BotMethods::bot()
            ->sendDocument($channel,
                "#отчет\nОтчет по поставщикам <b>$startDate</b> - <b>$endDate</b>",
                InputFile::createFromContents($content, $fileName));
        //-----------------------------------------------------------

        $content =
            Excel::raw(new SummarySuppliersReport(
                fromDate: Carbon::now("+3")->startOfYear(),
                toDate: Carbon::now("+3")->endOfYear()
            ), \Maatwebsite\Excel\Excel::XLSX);

        $fileName = "#отчет\nОтчет по поставщикам за год.xlsx";
        BotMethods::bot()
            ->sendDocument($channel,
                "Отчет по поставщикам за год",
                InputFile::createFromContents($content, $fileName));

    }
}
