<?php

namespace App\Exports\ExportType4;

use App\Facades\BusinessLogicFacade;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class SummarySheet implements FromView, WithTitle
{

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.admin.summary', [
            "data"=> $this->data
        ]);
    }

    public function title(): string
    {
        return 'Итого';
    }
}
