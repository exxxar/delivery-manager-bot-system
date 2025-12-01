<?php

namespace App\Exports\ExportType5;


use App\Exports\ExportType4\RevenueExportSheet;
use App\Facades\BusinessLogicFacade;
use App\Models\Agent;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductsByCategoryReport implements WithMultipleSheets
{


    public function __construct()
    {

    }

    /**
     * Возвращает массив классов, представляющих отдельные листы.
     *
     * @return array
     */
    public function sheets(): array
    {
        $categories = ProductCategory::query()
            ->with(["products","products.supplier"])
            ->get();

        $tmp = [];
        foreach ($categories as $category) {
            $tmp[] = new ProductsSheet($category->name, $category->products ?? []);
        }

        return $tmp;
    }
}
