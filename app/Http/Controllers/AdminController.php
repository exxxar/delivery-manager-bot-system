<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Exports\AgentSalesExport;
use App\Exports\ExportType4\RevenueExportSheet;
use App\Exports\ExportType4\SummaryAgentReport;
use App\Facades\BotMethods;
use App\Facades\BusinessLogicFacade;
use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
use App\Models\Agent;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Writer;
use Telegram\Bot\FileUpload\InputFile;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        $query->whereIn('role', [
            RoleEnum::ADMIN->value,
            RoleEnum::SUPERADMIN->value
        ]);

        $perPage = $request->get('per_page', 30);
        $users = $query->paginate($perPage);
        return response()->json($users);
    }

    public function downloadReport(Request $request)
    {
        $validate = $request->validate([
            "startDate" => "required",
            "endDate" => "required",
        ]);

        $botUser = $request->botUser;

        $fromDate = Carbon::parse($request->startDate)->startOfDay();
        $toDate = Carbon::parse($request->endDate)->endOfDay();

        $exportService = app(\App\Services\ExportService::class);

        // Отчет по зарплатам
        $fileName1 = "Отчет по зарплатам $fromDate - $toDate.xlsx";
        $report1 = $exportService->saveReport(
            $botUser,
            "Отчет по зарплатам за период $fromDate - $toDate",
            $fileName1,
            new SummaryAgentReport(
                resultType: 0,
                fromDate: $fromDate,
                toDate: $toDate,
            ),
            [],
            'salary_report',
            $fromDate,
            $toDate
        );

        // Отчет по поставщикам
        $fileName2 = "report-" . Carbon::now()->format('Y-m-d-H-i-s') . ".xlsx";
        $report2 = $exportService->saveReport(
            $botUser,
            "Отчет по поставщикам за период $fromDate - $toDate",
            $fileName2,
            new \App\Exports\ExportType1\SummarySuppliersReport(
                fromDate: $fromDate,
                toDate: $toDate,
            ),
            [],
            'suppliers_report',
            $fromDate,
            $toDate
        );

        return response()->json([
            'message' => 'Отчеты успешно сформированы',
            'reports' => [$report1, $report2]
        ]);
    }


    /**
     * @throws \HttpException
     */
    public function downloadPersonalReport(Request $request)
    {

        $validate = $request->validate([
            "user_id" => "required",
            "startDate" => "required",
            "endDate" => "required",
        ]);

        $botUser = $request->botUser;

        $admin = \App\Models\User::query()
            ->where("role", \App\Enums\RoleEnum::ADMIN->value)
            ->where("id", $validate["user_id"])
            ->first();

        if (is_null($admin))
            throw new \HttpException("Администратор не найден", 404);

        $fromDate = Carbon::parse($validate["startDate"])->startOfDay();
        $toDate = Carbon::parse($validate["endDate"])->endOfDay();


        $content =
            Excel::raw(new \App\Exports\ExportType3\AdminWorkReport(
                $admin->id,
                $fromDate ?? Carbon::now()->startOfMonth(),
                $toDate ?? Carbon::now()->endOfMonth()
            ), \Maatwebsite\Excel\Excel::XLSX);

        $fileName = "report-" . Carbon::now()->format('Y-m-d H-i-s') . ".xlsx";
        \App\Facades\BotMethods::bot()
            ->sendDocument($botUser->telegram_chat_id,
                "Отчет работы администратора за период <b>$fromDate</b> - <b>$toDate</b>",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($content, $fileName));

        return response()->noContent();

    }

    public function exportIndividual(Request $request, $agentId)
    {
        $validate = $request->validate([
            "startDate" => "required",
            "endDate" => "required",
        ]);

        $admin = $request->botUser;

        $fromDate = Carbon::parse($validate["startDate"])->startOfDay();
        $toDate = Carbon::parse($validate["endDate"])->endOfDay();

        $agent = Agent::query()->where("id", $agentId)->first();

        $fileName = "export-self-sales-" . Carbon::now()->format("Y-m-d-H-i-s") . ".xlsx";

        $report = app(\App\Services\ExportService::class)->saveReport(
            $admin,
            "Отчет по работе Администратора " . ($agent->name ?? 'не указано') . " за период $fromDate - $toDate",
            $fileName,
            new \App\Exports\AgentSalesExport($agent->id ?? null, $fromDate, $toDate),
            [],
            'individual_sales',
            $fromDate,
            $toDate
        );

        return response()->json([
            'message' => 'Отчет успешно сформирован',
            'report' => $report
        ]);
    }

    public function exportFull(Request $request, $agentId = null)
    {
        $validate = $request->validate([
            "startDate" => "required",
            "endDate" => "required",
        ]);

        $supplierIds = $request->selected_suppliers ?? [];
        $agentIds = $request->selected_agents ?? [];
        $resultType = $request->result_type ?? 0;

        $admin = $request->botUser;

        $agent = is_null($agentId) ?
            Agent::query()->where("user_id", $admin->id)->first() :
            Agent::query()->where("id", $agentId)->first();

        $fromDate = Carbon::parse($validate["startDate"])->startOfDay();
        $toDate = Carbon::parse($validate["endDate"])->endOfDay();

        $exportService = app(\App\Services\ExportService::class);
        $reports = [];

        // Отчет по зарплатам
        $title = is_null($agentId) ?
            "Отчет по зарплатам за период $fromDate - $toDate" :
            "Отчет по зарплате Администратора " . ($agent->name ?? 'не указано') . " за период $fromDate - $toDate";

        $fileName1 = "report-" . Carbon::now()->format('Y-m-d-H-i-s') . ".xlsx";
        $reports[] = $exportService->saveReport(
            $admin,
            $title,
            $fileName1,
            new \App\Exports\ExportType4\SummaryAgentReport(
                resultType: $resultType,
                fromDate: $fromDate,
                toDate: $toDate,
                agentsIds: $agentIds,
                suppliersIds: $supplierIds,
            ),
            [],
            'full_salary_report',
            $fromDate,
            $toDate
        );

        // Отчет по поставщикам (только для ролей >= 3)
        if ($admin->role >= 3) {
            $fileName2 = "report-" . Carbon::now()->format('Y-m-d-H-i-s') . "-suppliers.xlsx";
            $reports[] = $exportService->saveReport(
                $admin,
                "Отчет по поставщикам за период $fromDate - $toDate",
                $fileName2,
                new \App\Exports\ExportType1\SummarySuppliersReport(
                    fromDate: $fromDate,
                    toDate: $toDate,
                    agentsIds: $agentIds,
                    suppliersIds: $supplierIds
                ),
                [],
                'full_suppliers_report',
                $fromDate,
                $toDate
            );
        }

        return response()->json([
            'message' => 'Отчеты успешно сформированы',
            'reports' => $reports
        ]);
    }
}
