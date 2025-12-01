<?php

namespace App\Exports\ExportType5;


use App\Exports\ExportType4\RevenueExportSheet;
use App\Facades\BusinessLogicFacade;
use App\Models\Agent;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductsBySupplierReport implements WithMultipleSheets
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
        $suppliers = Supplier::query()
            ->with(["products","products.category"])
            ->get();

        $tmp = [];
        foreach ($suppliers as $supplier) {
            $tmp[] = new ProductsSheet($supplier->name, $supplier->products ?? []);
        }

        return $tmp;
    }
}
