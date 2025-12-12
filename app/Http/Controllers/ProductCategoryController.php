<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Exports\ProductCategoriesExport;
use App\Http\Requests\ProductCategoryStoreRequest;
use App\Http\Requests\ProductCategoryUpdateRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductCategory::query();

        // 🔹 Фильтры по текстовым полям
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        // 🔹 Фильтр по created_at / updated_at
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
                'id', 'name', 'description', 'created_at', 'updated_at'
            ]) && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // 🔹 Пагинация
        $perPage = $request->get('per_page', $request->size ?? 10);
        $categories = $query->paginate($perPage);

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $category = ProductCategory::create($request->all());
        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = ProductCategory::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy($id)
    {
        ProductCategory::destroy($id);
        return response()->json(null, 204);
    }

    public function removeAll(Request $request){
        $request->validate([
            "ids"=>"required"
        ]);

        $ids = $request->ids ?? [];

        foreach ($ids as $id)
            ProductCategory::destroy($id);

        return response()->json(null, 204);
    }


    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new HttpException("Пользователь не авторизован", 403);

        $fileName = "export-prod-category-".Carbon::now()->format("Y-m-d H-i-s").".xlsx";
        $data = Excel::raw(new \App\Exports\ProductCategoriesExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id,"Экспорт списка категорий товара",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data,$fileName));
        return response()->noContent();
    }

    public function indexWithProducts(Request $request){

        $size = $request->size ?? 10;
        // Список поставщиков с первыми 10 товарами
        $categories = ProductCategory::query()
            ->withCount('products')
            ->paginate($size);

        foreach ($categories as $category) {
            $category->setRelation(
                'products',
                $category->products()
                    ->with(['supplier', 'category'])
                    ->take(10)->get()
            );
        }

        return response()->json($categories);
    }

    public function nextProducts(Request $request, $categoryId){
        $page = $request->get('page', 1);
        $perPage = 10;

        $products = Product::query()
            ->with(["category","supplier"])
            ->where('product_category_id', $categoryId)
            ->paginate(perPage:$perPage, page: $page);


        return response()->json($products);
    }
}
