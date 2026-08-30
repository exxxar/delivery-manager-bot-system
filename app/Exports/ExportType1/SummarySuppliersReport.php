<?php

namespace App\Exports\ExportType1;

use App\Enums\RoleEnum;
use App\Models\Agent;
use App\Models\Sale;
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

        // 🔹 Если агенты не переданы — берём всех не-тестовых
        if (empty($this->agentsIds)) {
            $usersIds = User::query()
                ->where("role", RoleEnum::AGENT->value)
                ->get()
                ->pluck("id");

            $this->agentsIds = Agent::query()
                ->whereIn("user_id", $usersIds)
                ->where("is_test", false)
                ->get()
                ->pluck("id")
                ->all();
        }

        // 🔹 НОВОЕ: Если поставщики не переданы — берём только АКТИВНЫХ за период
        if (empty($this->suppliersIds)) {
            $this->suppliersIds = $this->getActiveSupplierIds();
        }
    }

    /**
     * Получить ID поставщиков, у которых были завершённые продажи за период.
     * Повторяет логику SupplierController::active()
     *
     * @return array
     */
    protected function getActiveSupplierIds(): array
    {
        $from = $this->fromDate->copy()->startOfDay();
        $to   = $this->toDate->copy()->endOfDay();

        return Sale::query()
            ->select('supplier_id')
            ->where('status', 'completed')
            ->whereNotNull('supplier_id')
            ->where('total_price', '>', 0)
            ->whereBetween('actual_delivery_date', [
                $from->toDateString(),
                $to->toDateString()
            ])
            ->distinct()
            ->pluck('supplier_id')
            ->filter()
            ->values()
            ->all();
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

        // 🔹 Если нет активных поставщиков за период — возвращаем только общий лист
        if (empty($this->suppliersIds)) {
            return $tmp;
        }

        // 🔹 Берём только активных поставщиков по их ID
        $suppliers = Supplier::query()
            ->whereIn('id', $this->suppliersIds)
            ->orderBy('name', 'asc') // 🔹 Сортировка для стабильного порядка листов
            ->get();

        foreach ($suppliers as $supplier) {
            $tmp[] = new MonthlySummarySupplierSheet(
                supplier: $supplier,
                fromDate: $this->fromDate,
                toDate: $this->toDate,
                agentsIds: $this->agentsIds);
        }

        return $tmp;
    }
}
