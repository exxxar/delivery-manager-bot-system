<?php

namespace App\Http\Controllers;

use App\Exports\ProductCategoriesExport;
use App\Exports\ProductsExport;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Models\ProductCategory;
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
        $query = Product::query()
            ->with(["category", "supplier"]);

        // 🔹 Фильтры по тексту
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        // 🔹 Фильтры по числовым значениям
        if ($request->filled('price_min') || $request->filled('price_max')) {
            $query->whereBetween('price', [
                    $request->price_min ?? 0,
                    $request->price_max ?? PHP_INT_MAX
            ]);
        }
        if ($request->filled('count_min') || $request->filled('count_max')) {
            $query->whereBetween('count', [
                    $request->count_min ?? 0,
                    $request->count_max ?? PHP_INT_MAX
            ]);
        }

        // 🔹 Фильтры по связям
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('product_category_id')) {
            $query->where('product_category_id', $request->product_category_id);
        }

        // 🔹 Фильтр по дате (created_at / updated_at)
        if ($request->filled('date_type') && ($request->filled('date_from') || $request->filled('date_to'))) {
            $query->whereBetween($request->date_type, [
                    $request->date_from ?? '1900-01-01',
                    $request->date_to ?? now()->toDateString()
            ]);
        }

        // 🔹 Сортировка
        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        if (in_array($sortField, [
                'id', 'name', 'description', 'price', 'count',
                'supplier_id', 'product_category_id', 'created_at', 'updated_at'
            ]) && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // 🔹 Пагинация
        $perPage = $request->get('per_page', $request->size ?? 10);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data["description"] = $data["description"] ?? $data["name"];
        $data["price"] = $data["price"] ?? 0;
        $data["count"] = $data["count"] ?? 1;

        $category = $data["category"] ?? null;

        if (!is_null($category)) {
            $category = ProductCategory::query()
                ->where("name", $category)
                ->first();

            if (is_null($category))
                $category = ProductCategory::query()
                    ->create([
                        'name' => $category,
                        'description' => $category,
                    ]);
        }

        $data["product_category_id"] = $data["product_category_id"] ?? $category->id ?? null;

        $product = Product::create($data);
        $product->load(['supplier', 'category']);
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

        switch ($type) {
            default:
            case 0:
                $title = "Сводная таблица продуктов";
                $data = Excel::raw(new \App\Exports\ProductsExport(), \Maatwebsite\Excel\Excel::XLSX);
                break;
            case 1:
                $title = "Таблица продуктов, разделенных по категориям";
                $data = Excel::raw(new \App\Exports\ExportType5\ProductsByCategoryReport(), \Maatwebsite\Excel\Excel::XLSX);
                break;
            case 2:
                $title = "Таблица продуктов, разделенных по поставщикам";
                $data = Excel::raw(new \App\Exports\ExportType5\ProductsBySupplierReport(), \Maatwebsite\Excel\Excel::XLSX);
                break;
        }

        $fileName = "export-products-" . Carbon::now()->format("Y-m-d H-i-s") . ".xlsx";

        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id, "$title",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data, $fileName));
        return response()->noContent();
    }
}
