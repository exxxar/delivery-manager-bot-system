<?php

namespace App\Exports\ExportType3;

use App\Facades\BusinessLogicFacade;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class SalesExportSheet implements FromView, WithTitle
{
    protected $title;

    protected $data;

    public function __construct($title, $data)
    {
        $this->title = $title;
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.admin.sales', ['sales' => $this->data, "title"=>$this->title]);
    }

    public function title(): string
    {
        return $this->title;
    }
}
