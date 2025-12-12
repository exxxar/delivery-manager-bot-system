<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Exports\SuppliersExport;
use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        // 🔹 Фильтры по текстовым полям
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->filled('address')) {
            $query->where('address', 'like', '%' . $request->address . '%');
        }
        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // 🔹 Фильтр по проценту (диапазон)
        if ($request->filled('percent_min') || $request->filled('percent_max')) {
            $query->whereBetween('percent', [
                    $request->percent_min ?? 0,
                    $request->percent_max ?? 100
            ]);
        }

        // 🔹 Фильтр по дате рождения
        if ($request->filled('birthday_from') || $request->filled('birthday_to')) {
            $query->whereBetween('birthday', [
                    $request->birthday_from ?? '1900-01-01',
                    $request->birthday_to ?? now()->toDateString()
            ]);
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
                'id', 'name', 'address', 'description', 'phone',
                'percent', 'birthday', 'email', 'created_at', 'updated_at'
            ]) && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // 🔹 Пагинация
        $perPage = $request->get('per_page', $request->size ?? 10);
        $suppliers = $query->paginate($perPage);

        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($request->all());
        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());
        return response()->json($supplier);
    }

    public function destroy($id)
    {
        Supplier::destroy($id);
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

        $fileName = "export-sales-".Carbon::now()->format("Y-m-d H-i-s").".xlsx";
        $data = Excel::raw(new \App\Exports\SuppliersExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id,"Экспорт списка поставщиков",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data,$fileName));
        return response()->noContent();
    }

    public function indexWithProducts(Request $request){

        $size = $request->size ?? 10;
        // Список поставщиков с первыми 10 товарами
        $suppliers = Supplier::query()
            ->withCount('products')
            ->paginate($size);

        foreach ($suppliers as $supplier) {
            $supplier->setRelation(
                'products',
                $supplier->products()
                    ->with(['supplier', 'category'])
                    ->take(10)->get()
            );
        }

        return response()->json($suppliers);
    }

    public function nextProducts(Request $request, $supplierId){
        $page = $request->get('page', 1);
        $perPage = 10;

        $products = Product::query()
            ->with(["category","supplier"])
            ->where('supplier_id', $supplierId)
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($products);
    }
}
