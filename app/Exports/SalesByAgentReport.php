<?php

namespace App\Exports;


use App\Enums\RoleEnum;
use App\Exports\ExportType4\RevenueExportSheet;
use App\Exports\SalesExport;
use App\Facades\BusinessLogicFacade;
use App\Models\Agent;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesByAgentReport implements WithMultipleSheets
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

        $usersIds = User::query()
            ->where("role", RoleEnum::AGENT->value)
            ->get()
            ->pluck("id");

        $agents = Agent::query()
            ->whereIn("user_id", $usersIds)
            ->where("is_test", false)
            ->get();

        $tmp = [];
        foreach ($agents as $agent) {
            $tmp[] = new SalesExport($this->fromDate, $this->toDate, $agent);
        }

        return $tmp;
    }
}
