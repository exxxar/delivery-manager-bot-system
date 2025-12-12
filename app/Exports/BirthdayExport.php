<?php

namespace App\Exports;

use App\Facades\BusinessLogicFacade;
use App\Models\Agent;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BirthdayExport implements FromView, WithStyles
{
    public function view(): View
    {
        $today = Carbon::today();
        $endDate = Carbon::today()->addMonth();

        $result = BusinessLogicFacade::method()
            ->birthdaysNext($today, $endDate);


        return view('exports.birthdays', [
            'items' => $result
        ]);
    }

    /**
     * Настройка стилей (границы колонок)
     */
    public function styles(Worksheet $sheet)
    {
        // Применяем границы ко всем ячейкам с данными
        $sheet->getStyle('A1:D1000')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // черные границы
                ],
            ],
        ]);

        // Можно задать ширину колонок
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(30);


        return [];
    }
}
