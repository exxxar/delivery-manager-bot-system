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
            throw new HttpException("Пользователь не авторизован", 403);

        $fileName = "export-birthdays-" . Carbon::now()->format("Y-m-d H-i-s") . ".xlsx";
        $data = Excel::raw(new \App\Exports\BirthdayExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id, "Экспорт дней рождений сотрудников и поставщиков на ближайший месяц",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data, $fileName));
        return response()->noContent();
    }
}
