<?php

namespace App\Exports\ExportType4;

use App\Facades\BusinessLogicFacade;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class SupplierSheet implements FromView, WithTitle
{

    protected $resultType;

    public function __construct($resultType = 0)
    {
        $this->resultType = $resultType;
    }

    public function view(): View
    {
        $suppliers = BusinessLogicFacade::method()
            ->getSuppliers();

        return view($this->resultType == 0?
            'exports.suppliers-v2' :
            'exports.suppliers',
            ['suppliers' => $suppliers]);
    }

    public function title(): string
    {
        return 'Поставщики';
    }
}
