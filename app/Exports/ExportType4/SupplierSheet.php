<?php

namespace App\Exports\ExportType4;

use App\Facades\BusinessLogicFacade;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class SupplierSheet implements FromView, WithTitle
{

    protected $resultType;
    protected $suppliersIds;

    public function __construct($resultType = 0, $suppliersIds = [])
    {
        $this->resultType = $resultType;
        $this->suppliersIds = $suppliersIds;
    }

    public function view(): View
    {
        if (empty($this->suppliersIds))
            $suppliers = Supplier::query()
                ->get();
        else
            $suppliers = Supplier::query()
                ->whereIn("id", $this->suppliersIds)
                ->get();

        return view($this->resultType == 0 ?
            'exports.suppliers-v2' :
            'exports.suppliers',
            ['suppliers' => $suppliers]);
    }

    public function title(): string
    {
        return 'Поставщики';
    }
}
