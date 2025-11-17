<?php

namespace App\Exports\ExportType3;


use App\Models\Agent;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdminWorkReport implements WithMultipleSheets
{

    protected $fromDate;
    protected $toDate;
    protected int $adminId;

    public function __construct(int $adminId, $fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->adminId = $adminId;

    }

    /**
     * Возвращает массив классов, представляющих отдельные листы.
     *
     * @return array
     */
    public function sheets(): array
    {
        $statusMap = [
            'pending' => 'Ожидает',
            'assigned' => 'Назначено',
            'delivered' => 'Доставлено',
            'completed' => 'Завершено',
            'rejected' => "Отклонено"
        ];
        set_time_limit(300);
        $data = \App\Facades\BusinessLogicFacade::method()
            ->getSalesGroupedByStatus(
                $this->adminId,
                $this->fromDate,
                $this->toDate
            );

        $tmp = [];
        $tmp[] = new SalesExportSheet("Все", $data["all"]);
        $tmp[] = new SalesExportSheet($statusMap['completed'], $data["completed"]);
        $tmp[] = new SalesExportSheet($statusMap['pending'], $data["pending"]);
        $tmp[] = new SalesExportSheet($statusMap['assigned'], $data["assigned"]);
        $tmp[] = new SalesExportSheet($statusMap['delivered'], $data["delivered"]);
        $tmp[] = new SalesExportSheet($statusMap['rejected'], $data["rejected"]);

        return $tmp;
    }
}
