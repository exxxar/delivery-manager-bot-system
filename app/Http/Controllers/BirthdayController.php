<?php

namespace App\Http\Controllers;

use App\Facades\BusinessLogicFacade;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Supplier;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BirthdayController extends Controller
{
    public function birthdaysNextWeek()
    {
        $today = Carbon::today();
        $endDate = Carbon::today()->addDays(7);

        $result = BusinessLogicFacade::method()
            ->birthdaysNext($today, $endDate);

        return response()->json($result);
    }

    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, "Пользователь не авторизован");

        $fileName = "export-birthdays-" . Carbon::now()->format("Y-m-d-H-i-s") . ".xlsx";

        $report = app(\App\Services\ExportService::class)->saveReport(
            $user,
            "Экспорт дней рождений сотрудников и поставщиков на ближайший месяц",
            $fileName,
            new \App\Exports\BirthdayExport(),
            [],
            'birthdays'
        );

        return response()->json([
            'message' => 'Отчет успешно сформирован',
            'report' => $report
        ]);
    }
}
