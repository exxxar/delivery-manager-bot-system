<?php

namespace App\Exports\ExportType1;


use App\Enums\RoleEnum;
use App\Models\Agent;
use App\Models\Supplier;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SummarySuppliersReport implements WithMultipleSheets
{

    protected $fromDate;
    protected $toDate;
    protected $suppliersIds;
    protected $agentsIds;

    public function __construct($fromDate, $toDate, $agentsIds = [], $suppliersIds = [])
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->agentsIds = $agentsIds;
        $this->suppliersIds = $suppliersIds;

        if (empty($this->agentsIds)) {
            $usersIds = User::query()
                ->where("role", RoleEnum::AGENT->value)
                ->get()
                ->pluck("id");

            $this->agentsIds = Agent::query()
                ->whereIn("user_id", $usersIds)
                ->where("is_test", false)
                ->get()
                ->pluck("id");
        }

    }

    /**
     * Возвращает массив классов, представляющих отдельные листы.
     *
     * @return array
     */
    public function sheets(): array
    {
        $tmp = [
            new GeneralSummarySheet(
                fromDate: $this->fromDate,
                toDate: $this->toDate,
                agentsIds: $this->agentsIds,
                suppliersIds: $this->suppliersIds),
        ];


        if (empty($this->suppliersIds))
            $suppliers = Supplier::query()
                ->get();
        else
            $suppliers = Supplier::query()
                ->whereIn("id", $this->suppliersIds)
                ->get();

        foreach ($suppliers as $supplier)
            $tmp[] = new MonthlySummarySupplierSheet(
                supplier: $supplier,
                fromDate: $this->fromDate,
                toDate: $this->toDate,
                agentsIds: $this->agentsIds);

        return $tmp;
    }
}
