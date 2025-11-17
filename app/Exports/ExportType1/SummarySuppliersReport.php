<?php

namespace App\Exports\ExportType1;


use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SummarySuppliersReport implements WithMultipleSheets
{

    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate, $toDate)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    /**
     * Возвращает массив классов, представляющих отдельные листы.
     *
     * @return array
     */
    public function sheets(): array
    {
        $tmp = [
            new GeneralSummarySheet(),
        ];

        $suppliers = Supplier::query()->get();

        foreach ($suppliers as $supplier)
            $tmp[] = new MonthlySummarySupplierSheet($supplier,  $this->fromDate, $this->toDate);

        return $tmp;
    }
}
