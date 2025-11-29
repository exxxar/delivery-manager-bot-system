<?php

namespace App\Exports\ExportType4;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class RevenueExportSheet implements FromView, WithTitle
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
        return view('exports.admin.revenue', ['revenue' => $this->data, "title"=>$this->title]);
    }

    public function title(): string
    {
        return $this->title;
    }
}
