<?php

namespace App\Http\Controllers;

use App\Exports\CustomersExport;
use App\Exports\ProductCategoriesExport;
use App\Http\Requests\ProductCategoryStoreRequest;
use App\Http\Requests\ProductCategoryUpdateRequest;
use App\Models\ProductCategory;
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
        $size = $request->size ?? 10;
        return response()->json(ProductCategory::query()->paginate($size));
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
}
