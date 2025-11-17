<?php

namespace App\Exports\ExportType2;


use App\Models\Agent;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SummaryAgentReport implements WithMultipleSheets
{

    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate= null, $toDate = null)
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

        $tmp = [];
        foreach ($agents as $agent)
            $tmp[] = new AgentPersonalResultSheet($agent,  $this->fromDate, $this->toDate);

        $tmp[] = new SupplierSheet();
        $tmp[] = new ProductsSheet();
        $tmp[] = new SummarySheet();

        return $tmp;
    }
}
