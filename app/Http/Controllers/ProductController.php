<?php

namespace App\Http\Controllers;

use App\Exports\ProductCategoriesExport;
use App\Exports\ProductsExport;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Imports\ProductsImport;
use App\Models\Product;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $size = $request->size ?? 10;
        $products = Product::query()
            ->with(["category","supplier"])
            ->paginate($size);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $product = Product::create($request->all());
        return response()->json($product, 201);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product);
    }

    public function destroy($id)
    {
        Product::destroy($id);
        return response()->json(null, 204);
    }

    public function import(Request $request)
    {
        $file = $request->file('excel');

        $spreadsheet = IOFactory::load($file);

        Excel::import(new ProductsImport($spreadsheet->getSheetNames()), $file);

        return response()->json(['message' => 'Импорт завершён']);
    }

    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {

        $type = $request->get("type") ?? 0;
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new HttpException("Пользователь не авторизован", 403);

        switch ($type){
            default:
            case 0:
                $title = "Сводная таблица продуктов";
                $data = Excel::raw(new \App\Exports\ProductsExport(), \Maatwebsite\Excel\Excel::XLSX); break;
            case 1:
                $title = "Таблица продуктов, разделенных по категориям";
                $data = Excel::raw(new \App\Exports\ExportType5\ProductsByCategoryReport(), \Maatwebsite\Excel\Excel::XLSX); break;
            case 2:
                $title = "Таблица продуктов, разделенных по поставщикам";
                $data = Excel::raw(new \App\Exports\ExportType5\ProductsBySupplierReport(), \Maatwebsite\Excel\Excel::XLSX); break;
        }

        $fileName = "export-products-".Carbon::now()->format("Y-m-d H-i-s").".xlsx";

        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id,"$title",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data,$fileName));
        return response()->noContent();
    }
}
