<?php

namespace App\Exports\ExportType4;


use App\Enums\RoleEnum;
use App\Exports\ExportType4\RevenueExportSheet;
use App\Facades\BusinessLogicFacade;
use App\Models\Agent;
use App\Models\Supplier;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SummaryAgentReport implements WithMultipleSheets
{

    protected $fromDate;
    protected $toDate;
    protected $agentsIds;
    protected $suppliersIds;
    protected $resultType;

    public function __construct($resultType, $fromDate = null, $toDate = null, $agentsIds = [], $suppliersIds = [])
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->agentsIds = $agentsIds;
        $this->suppliersIds = $suppliersIds;
        $this->resultType = $resultType;
    }

    /**
     * Возвращает массив классов, представляющих отдельные листы.
     *
     * @return array
     */
    public function sheets(): array
    {

        if (empty($this->agentsIds)) {
            $usersIds = User::query()
                ->where("role", RoleEnum::AGENT->value)
                ->get()
                ->pluck("id");

            $agents = Agent::query()
                ->whereIn("user_id", $usersIds)
                ->where("is_test", false)
                ->get();
        } else
            $agents = Agent::query()
                ->whereIn("id", $this->agentsIds)->get();


        $tmpData = [];
        $tmp = [];
        foreach ($agents as $agent) {
            $data = BusinessLogicFacade::method()
                ->getAdminsMonthlyByAgentRevenue($agent, $this->fromDate, $this->toDate, $this->suppliersIds);
            $tmpData[] = $data;
            $tmp[] = new RevenueExportSheet($agent->name, $data, $this->resultType);

        }


        $tmp[] = new SupplierSheet($this->resultType, $this->suppliersIds);
        $tmp[] = new ProductsSheet($this->resultType, $this->suppliersIds);
        $tmp[] = new SummarySheet($tmpData);


        return $tmp;
    }
}
