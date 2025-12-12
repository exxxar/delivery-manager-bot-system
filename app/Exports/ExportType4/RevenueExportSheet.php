<?php

namespace App\Exports\ExportType4;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class RevenueExportSheet implements FromView, WithTitle
{
    protected $title;

    protected $data;

    protected $resultType;

    public function __construct($title, $data, $resultType = 0)
    {
        $this->title = $title;

        $this->data = $data;

        $this->resultType = $resultType;
    }

    public function view(): View
    {
        return view($this->resultType == 0 ?
            'exports.admin.revenue-v2' :
            'exports.admin.revenue',
            ['revenue' => $this->data, "title" => $this->title]);
    }

    public function title(): string
    {
        return $this->title;
    }
}
