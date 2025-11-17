<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
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

        $admin = \App\Models\User::query()
            ->where("role", \App\Enums\RoleEnum::ADMIN->value)
            ->where("id", $validate["user_id"])
            ->first();

        if (is_null($admin))
            throw new \HttpException("Администратор не найден", 404);

        $fromDate = $validate["startDate"];
        $toDate = $validate["endDate"];
        $content =
            Excel::raw(new \App\Exports\ExportType3\AdminWorkReport(
                $admin->id,
                $fromDate ?? Carbon::now()->startOfMonth(),
                $toDate ?? Carbon::now()->endOfMonth()
            ), \Maatwebsite\Excel\Excel::XLSX);

        $fileName = "report-" . Carbon::now()->format('Y-m-d H-i-s') . ".xlsx";
        \App\Facades\BotMethods::bot()
            ->sendDocument(env("TELEGRAM_ADMIN_CHANNEL"),
                "Отчет работы администратора за период <b>$fromDate</b> - <b>$toDate</b>",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($content, $fileName));

        return response()->noContent();

    }

}
