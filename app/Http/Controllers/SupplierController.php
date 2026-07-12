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

        $botUser = $request->botUser;

        $agent = $botUser->agent ?? null;


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

            if ($sortField === 'id' && !empty($agent->favorite_suppliers)) {
                $ids = $agent->favorite_suppliers;

                $query->orderByRaw(
                    'FIELD(id, ' . implode(',', $ids) . ") $sortDirection"
                );
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        }


        // 🔹 Пагинация
        $perPage = $request->get('per_page', $request->size ?? 10);
        $suppliers = $query
            ->paginate($perPage);

        return response()->json($suppliers);
    }

    public function toggleSupplierInFavorites(Request $request)
    {
        //favorite_suppliers

        $request->validate([
            "id" => "required"
        ]);

        $id = $request->id;

        $botUser = $request->botUser;

        $agent = $botUser->agent ?? null;

        if (is_null($agent))
            return response()->noContent(403);

        $favoriteSuppliers = $agent->favorite_suppliers ?? [];


        if (in_array($id, $favoriteSuppliers)) {
            $favoriteSuppliers = array_values(array_diff($favoriteSuppliers, [$id]));
        } else {

            $favoriteSuppliers[] = $id;
        }

        $agent->favorite_suppliers = $favoriteSuppliers;
        $agent->save();

        return response()->json($agent->favorite_suppliers);

    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|unique:suppliers,phone',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'percent' => 'nullable|numeric|min:0|max:100',
            'birthday' => 'nullable|date',
        ]);

        $data = $request->all();
        $data["percent"] = $data["percent"] ?? 8;

        // 🔹 Проверка на дубликат по phone
        if (!empty($data['phone'])) {
            $existing = Supplier::where('phone', $data['phone'])->first();

            if ($existing) {
                // Если поставщик с таким телефоном уже есть — обновляем его
                $existing->update($data);
                return response()->json([
                    'message' => 'Поставщик с таким телефоном уже существует, данные обновлены',
                    'supplier' => $existing,
                    'is_updated' => true
                ], 200);
            }
        }

        $supplier = Supplier::create($data);
        return response()->json($supplier, 201);
    }


    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {


        $data = $request->all();
        $data["percent"] = $data["percent"] ?? 8;

        $supplier = Supplier::findOrFail($id);

        // 🔹 Проверка на дубликат по phone (исключая текущего поставщика)
        if (!empty($data['phone'])) {
            $existing = Supplier::where('phone', $data['phone'])
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Поставщик с таким телефоном уже существует',
                    'existing_supplier' => $existing
                ], 422);
            }
        }

        $supplier->update($data);
        return response()->json($supplier);
    }

    public function destroy($id)
    {
        Supplier::destroy($id);
        return response()->json(null, 204);
    }


    public function active(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = \Carbon\Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

        $query = Supplier::query()
            ->withCount(['sales as month_sales_count' => function ($q) use ($monthDate) {
                $q->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateString(),
                    $monthDate->endOfMonth()->toDateString()
                ]);
            }])
            ->withSum(['sales as month_turnover' => function ($q) use ($monthDate) {
                $q->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateString(),
                    $monthDate->endOfMonth()->toDateString()
                ]);
            }], 'total_price')
            ->having('month_sales_count', '>', 0)
            ->orderByDesc('month_turnover');

        // 🔹 Поиск по имени
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $perPage = $request->get('per_page', $request->size ?? 30);
        $suppliers = $query->paginate($perPage);

        return response()->json([
            'data' => $suppliers->items(),
            'pagination' => [
                'current_page' => $suppliers->currentPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
                'last_page' => $suppliers->lastPage(),
                'from' => $suppliers->firstItem(),
                'to' => $suppliers->lastItem(),
            ],
            'month' => $month,
            'stats' => [
                'total_suppliers' => $suppliers->total(),
                'total_turnover' => round(collect($suppliers->items())->sum('month_turnover'), 2),
            ]
        ]);
    }

    public function inactive(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = \Carbon\Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

        $query = Supplier::query()
            ->withCount(['sales as month_sales_count' => function ($q) use ($monthDate) {
                $q->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateString(),
                    $monthDate->endOfMonth()->toDateString()
                ]);
            }])
            ->having('month_sales_count', '=', 0);

        // 🔹 Поиск по имени
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $perPage = $request->get('per_page', $request->size ?? 30);
        $suppliers = $query->paginate($perPage);

        return response()->json([
            'data' => $suppliers->items(),
            'pagination' => [
                'current_page' => $suppliers->currentPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
                'last_page' => $suppliers->lastPage(),
                'from' => $suppliers->firstItem(),
                'to' => $suppliers->lastItem(),
            ],
            'month' => $month,
            'stats' => [
                'total_suppliers' => $suppliers->total(),
            ]
        ]);
    }

    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, "Пользователь не авторизован");

        $fileName = "export-suppliers-" . Carbon::now()->format("Y-m-d-H-i-s") . ".xlsx";

        $report = app(\App\Services\ExportService::class)->saveReport(
            $user,
            "Экспорт списка поставщиков",
            $fileName,
            new \App\Exports\SuppliersExport(),
            [],
            'suppliers_list'
        );

        return response()->json([
            'message' => 'Отчет успешно сформирован',
            'report' => $report
        ]);
    }

    public function removeAll(Request $request)
    {
        $request->validate([
            "ids" => "required"
        ]);

        $ids = $request->ids ?? [];

        foreach ($ids as $id)
            Supplier::destroy($id);

        return response()->json(null, 204);
    }

    public function indexWithProducts(Request $request)
    {

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

    public function nextProducts(Request $request, $supplierId)
    {
        $page = $request->get('page', 1);
        $perPage = 10;

        $products = Product::query()
            ->with(["category", "supplier"])
            ->where('supplier_id', $supplierId)
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($products);
    }
}
