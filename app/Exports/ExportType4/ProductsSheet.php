<?php

namespace App\Exports\ExportType4;

use App\Facades\BusinessLogicFacade;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class ProductsSheet implements FromView, WithTitle
{

    public function view(): View
    {
        $products = BusinessLogicFacade::method()
            ->getProducts();

        return view('exports.products', ['products' => $products]);
    }

    public function title(): string
    {
        return 'Товары';
    }
}
