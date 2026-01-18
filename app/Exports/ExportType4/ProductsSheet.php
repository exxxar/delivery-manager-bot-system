<?php

namespace App\Exports\ExportType4;

use App\Facades\BusinessLogicFacade;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;


class ProductsSheet implements FromView, WithTitle
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
            $products = Product::query()
                ->with('category', 'supplier')
                ->get();
        else
            $products = Product::query()
                ->with('category', 'supplier')
                ->whereIn("id", $this->suppliersIds)
                ->get();


        return view($this->resultType == 0 ?
            'exports.products-v2' :
            'exports.products',
            ['products' => $products]);
    }

    public function title(): string
    {
        return 'Товары';
    }
}
