<?php

namespace App\Exports;

use App\Models\Agent;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgentsExport implements FromView, WithStyles
{
    public function view(): View
    {
        return view('exports.agents', [
            'agents' => Agent::with('user')->get()
        ]);
    }

    /**
     * Настройка стилей (границы колонок)
     */
    public function styles(Worksheet $sheet)
    {
        // Применяем границы ко всем ячейкам с данными
        $sheet->getStyle('A1:J1000')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // черные границы
                ],
            ],
        ]);

        // Можно задать ширину колонок
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(30);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);

        return [];
    }
}
