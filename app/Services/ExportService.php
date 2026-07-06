<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ExportService
{
    public function saveReport(
        User $user,
        string $title,
        string $fileName,
        $exportClass,
        array $exportParams = [],
        ?string $reportType = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): Report {
        $data = Excel::raw($exportClass, \Maatwebsite\Excel\Excel::XLSX, $exportParams);

        $path = 'exports/' . $fileName;
        Storage::put($path, $data);

        return Report::create([
            'user_id' => $user->id,
            'title' => $title,
            'file_name' => $fileName,
            'file_path' => $path,
            'report_type' => $reportType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'file_size' => strlen($data),
        ]);
    }
}
