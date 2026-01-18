<?php

namespace App\Exports\ExportType1;

use App\Facades\BusinessLogicFacade;
use App\Models\Agent;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class MonthlySummarySupplierSheet implements FromView, WithTitle
{
    protected string $title;
    protected $supplier;
    protected $fromDate;
    protected $toDate;
    protected $agentsIds;

    public function __construct($supplier, $fromDate = null, $toDate = null, $agentsIds = [])
    {
        $this->title = $supplier->name;
        $this->supplier = $supplier;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->agentsIds = $agentsIds;
    }

    public function view(): View
    {

        $result = BusinessLogicFacade::method()
            ->getMonthlySalesSummaryForAllAgentsByCurrentSupplier($this->supplier, $this->fromDate, $this->toDate, $this->agentsIds);

        return view('exports.export-agent-to-supplier', $result);
    }



    public function title(): string
    {
        $newTitle = \App\Facades\BusinessLogicFacade::method()
            ->truncateTitle($this->title);
        return $newTitle ;
    }
}
