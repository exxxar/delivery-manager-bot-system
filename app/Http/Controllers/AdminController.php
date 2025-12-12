<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
use App\Models\Agent;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Writer;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        $query->where('role', RoleEnum::ADMIN->value);

        $perPage = $request->get('per_page', 30);
        $users = $query->paginate($perPage);
        return response()->json($users);
    }

    public function downloadReport(Request $request)
    {

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

        $fromDate = Carbon::parse($validate["startDate"]);
        $toDate = Carbon::parse($validate["endDate"]);
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

    public function exportFull(Request $request)
    {
        $validate = $request->validate([
            "startDate" => "required",
            "endDate" => "required",
        ]);

        $resultType = $request->result_type ?? 0;

        $admin = $request->botUser;

        $agent = $admin->role < 3 ? Agent::query()
            ->where("user_id", $admin->id)->first() : null;

        $fromDate = Carbon::parse($validate["startDate"]);
        $toDate = Carbon::parse($validate["endDate"]);

        \App\Facades\BotMethods::bot()
            ->sendMessage($admin->telegram_chat_id,
                "Внимание! Готовим отчет по зарплатам, это займет какое-то время!"
            );

        $content =
            Excel::raw(new \App\Exports\ExportType4\SummaryAgentReport(
                $fromDate ?? Carbon::now()->startOfMonth(),
                $toDate ?? Carbon::now()->endOfMonth(),
                $agent,
                $resultType
            ), \Maatwebsite\Excel\Excel::XLSX);

        $fileName = "report-" . Carbon::now()->format('Y-m-d H-i-s') . ".xlsx";
        \App\Facades\BotMethods::bot()
            ->sendDocument($admin->telegram_chat_id,
                "Отчет по зарплатам <b>$fromDate</b> - <b>$toDate</b>",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($content, $fileName));

        if ($admin->role < 3)
            return response()->noContent();

        \App\Facades\BotMethods::bot()
            ->sendMessage($admin->telegram_chat_id,
                "Внимание! Готовим отчет по поставщикам, это займет какое-то время!"
            );
        $content =
            Excel::raw(new \App\Exports\ExportType1\SummarySuppliersReport(
                $fromDate ?? Carbon::now()->startOfMonth(),
                $toDate ?? Carbon::now()->endOfMonth()
            ), \Maatwebsite\Excel\Excel::XLSX);

        $fileName = "report-" . Carbon::now()->format('Y-m-d H-i-s') . ".xlsx";
        \App\Facades\BotMethods::bot()
            ->sendDocument($admin->telegram_chat_id,
                "Отчет по поставщикам <b>$fromDate</b> - <b>$toDate</b>",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($content, $fileName));

        return response()->noContent();
    }
}
