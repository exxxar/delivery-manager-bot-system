<?php

namespace App\Exports\ExportType1;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class GeneralSummarySheet implements FromView, WithTitle
{

    protected $fromDate;
    protected $toDate;
    protected $suppliersIds;
    protected $agentsIds;

    public function __construct($fromDate = null, $toDate = null, $agentsIds = [], $suppliersIds = [])
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->agentsIds = $agentsIds;
        $this->suppliersIds = $suppliersIds;
    }


    public function view(): View
    {
        $data = \App\Facades\BusinessLogicFacade::method()
            ->getGeneralSalesSummaryByAgentsAndSuppliers(
                fromDate: $this->fromDate,
                toDate: $this->toDate,
                suppliersIds: $this->suppliersIds,
                agentsIds: $this->agentsIds,
            );

// Передача данных в шаблон
        return view('exports.export-supplier-summary', ['data' => $data]);
    }

    public function title(): string
    {
        return 'Общее';
    }
}
