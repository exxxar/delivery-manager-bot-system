<?php

namespace App\Exports;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminsExport implements FromView, WithStyles
{
    public function view(): View
    {
        return view('exports.users', [
            'users' => User::where("role", RoleEnum::ADMIN->value)
                ->get()
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Применяем границы ко всем ячейкам с данными
        $sheet->getStyle('A1:M1000')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // черные границы
                ],
            ],
        ]);

        // Можно задать ширину колонок
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(30);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(10);

        $sheet->getColumnDimension('H')->setWidth(10);

        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(30);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(20);
        $sheet->getColumnDimension('M')->setWidth(20);

        return [];
    }

}
