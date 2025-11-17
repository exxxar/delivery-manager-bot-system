<?php

namespace App\Exports\ExportType1;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class GeneralSummarySheet implements FromView, WithTitle
{

    public function view(): View
    {
        $data = \App\Facades\BusinessLogicFacade::method()->getGeneralSalesSummaryByAgentsAndSuppliers();

// Передача данных в шаблон
        return view('exports.export-supplier-summary', ['data' => $data]);
    }

    public function title(): string
    {
        return 'Общее';
    }
}
