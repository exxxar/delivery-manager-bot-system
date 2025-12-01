<?php

namespace App\Exports\ExportType5;

use App\Facades\BusinessLogicFacade;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class ProductsSheet implements FromView, WithTitle
{

    public $title;
    public $products;

    public function __construct($title, $products)
    {
        $this->title = $title;
        $this->products = $products;

    }

    public function view(): View
    {

        return view('exports.products', [
            'title' =>  $this->title,
            'products' =>  $this->products
        ]);
    }

    public function title(): string
    {
        return  $this->title ?? 'Товары';
    }
}
