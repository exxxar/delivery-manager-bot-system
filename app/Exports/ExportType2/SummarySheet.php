<?php

namespace App\Exports\ExportType2;

use App\Facades\BusinessLogicFacade;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class SummarySheet implements FromView, WithTitle
{

    public function view(): View
    {
       /* $products = BusinessLogicFacade::method()
            ->getSummaryByAgents();*/

        return view('exports.summary');
    }

    public function title(): string
    {
        return 'Товары';
    }
}
