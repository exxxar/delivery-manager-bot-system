<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromView, WithStyles
{
    protected $fromDate;
    protected $toDate;
    protected $agent;

    public function __construct($fromDate = null, $toDate = null, $agent = null)
    {
        $this->toDate = $toDate;
        $this->fromDate = $fromDate;
        $this->agent = $agent;
    }

    public function view(): View
    {
        $query = Sale::query()
            ->select([
                'id',
                'title',
                'status',
                'due_date',
                'sale_date',
                'actual_delivery_date',
                'total_price',
                'payment_type',
                'agent_id',
                'supplier_id',
                'product_id',
                // 'customer_id',  // если клиент не нужен — убираем
                // 'creator_id',   // если не нужен — убираем
            ])
            ->whereBetween('sale_date', [$this->fromDate, $this->toDate])
            ->orderBy('sale_date', 'asc');

        if ($this->agent) {
            $query->where('agent_id', $this->agent->id);
        }

        $sales = $query
            ->with([
                'agent:id,name',           // только id и name
                'supplier:id,name',
                'product:id,name',
            ])
            ->get(['id', 'agent_id', 'supplier_id', 'product_id']); // повтор select для безопасности

        return view('exports.sales', [
            'sales' => $sales,
            'title' => $this->agent->name ?? "Общее",
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Применяем границы ко всем ячейкам с данными
        $sheet->getStyle('A1:L1000')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // черные границы
                ],
            ],
        ]);

        // Можно задать ширину колонок
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(17);
        $sheet->getColumnDimension('E')->setWidth(17);
        $sheet->getColumnDimension('F')->setWidth(17);
        $sheet->getColumnDimension('G')->setWidth(17);
        $sheet->getColumnDimension('H')->setWidth(12);

        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(25);

        $sheet->getColumnDimension('K')->setWidth(25);
        $sheet->getColumnDimension('L')->setWidth(25);



        return [];
    }
}
