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
    protected $agentId;
    protected $resultType;

    public function __construct($fromDate = null, $toDate = null, $agentId = null, $resultType)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->agentId = $agentId;
        $this->resultType = $resultType;
    }

    /**
     * Возвращает массив классов, представляющих отдельные листы.
     *
     * @return array
     */
    public function sheets(): array
    {
        set_time_limit(300);
        $agents = is_null($this->agentId ?? null) ? Agent::query()->get() : Agent::query()
            ->where("id", $this->agentId)->get();

        $tmpData = [];
        $tmp = [];
        foreach ($agents as $agent) {
            $data = BusinessLogicFacade::method()
                ->getAdminsMonthlyByAgentRevenue($agent, $this->fromDate, $this->toDate);
            $tmpData[] = $data;
            $tmp[] = new RevenueExportSheet($agent->name, $data, $this->resultType);

        }


        $tmp[] = new SupplierSheet($this->resultType);
        $tmp[] = new ProductsSheet($this->resultType);
        $tmp[] = new SummarySheet($tmpData);


        return $tmp;
    }
}
