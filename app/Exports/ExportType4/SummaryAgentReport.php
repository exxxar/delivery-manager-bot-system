<?php

namespace App\Exports\ExportType4;


use App\Exports\ExportType4\RevenueExportSheet;
use App\Facades\BusinessLogicFacade;
use App\Models\Agent;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SummaryAgentReport implements WithMultipleSheets
{

    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate = null, $toDate = null)
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

        $agents = Agent::query()->get();

        $tmpData = [];
        $tmp = [];
        foreach ($agents as $agent) {
            $data = BusinessLogicFacade::method()
                ->getAdminsMonthlyByAgentRevenue($agent, $this->fromDate, $this->toDate);
            $tmpData[] = $data;
            $tmp[] = new RevenueExportSheet($agent->name, $data);

        }



        $tmp[] = new SupplierSheet();
        $tmp[] = new ProductsSheet();
        $tmp[] = new SummarySheet($tmpData);


        return $tmp;
    }
}
