<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Exports\SalesExport;
use App\Exports\SuppliersExport;
use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
use App\Models\Agent;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{

    /**
     * Поставщики, у которых не задан процент.
     */
    public function withoutPercent()
    {
        $suppliers = Supplier::query()
            ->where(function ($query) {
                $query->whereNull('percent')
                    ->orWhere('percent', 0);
            })
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'phone',
                'email',
                'percent',
            ]);

        return response()->json([
            'count' => $suppliers->count(),
            'data' => $suppliers,
        ]);
    }

    public function supplierSales(Request $request, $id)
    {
        $botUser = $request->botUser;
        $agent = Agent::where('user_id', $botUser->id)->first();

        $supplier = Supplier::findOrFail($id);
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

        $sales = Sale::query()
            ->with(['product', 'supplier', 'customer', 'creator', 'agent'])
            ->where('supplier_id', $id)
            ->where('status', 'completed')
            ->whereBetween('actual_delivery_date', [
                $monthDate->startOfMonth()->toDateTimeString(),
                $monthDate->endOfMonth()->toDateTimeString()
            ])
            ->where(function ($q) use ($botUser, $agent) {
                // Супер-админы видят все сделки поставщика
                if ($botUser->role >= RoleEnum::SUPERADMIN->value) {
                    return;
                }
                // Обычные агенты — только свои сделки
                if (is_null($agent)) {
                    $q->where('created_by_id', $botUser->id);
                } else {
                    $q->where('agent_id', $agent->id)
                        ->orWhere('created_by_id', $botUser->id);
                }
            })
            ->orderByDesc('actual_delivery_date')
            ->paginate($request->get('per_page', 20));

        return response()->json($sales);
    }

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
                    $request->birthday_to ?? now()->toDateTimeString()
            ]);
        }

        // 🔹 Фильтр по created_at / updated_at
        if ($request->filled('date_type') && ($request->filled('date_from') || $request->filled('date_to'))) {
            $query->whereBetween($request->date_type, [
                    $request->date_from ?? '1900-01-01',
                    $request->date_to ?? now()->toDateTimeString()
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

    /**
     * Получить доставки текущего авторизованного поставщика
     * Доступно только пользователям с ролью SUPPLIER
     */
    public function myDeliveries(Request $request)
    {
        $botUser = $request->botUser;

        // 🔹 Проверка: пользователь должен быть поставщиком
        if ($botUser->role !== RoleEnum::SUPPLIER->value) {
            return response()->json([
                'message' => 'Доступ только для поставщиков'
            ], 403);
        }

        // 🔹 Получаем поставщика, привязанного к пользователю
        $supplier = Supplier::where('user_id', $botUser->id)->first();

        if (!$supplier) {
            return response()->json([
                'message' => 'Поставщик не найден'
            ], 404);
        }

        $month = $request->get('month', now()->format('Y-m'));
        $status = $request->get('status'); // all, completed, pending, incomplete

        try {
            $monthDate = Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

        // 🔹 Базовый запрос
        $query = Sale::query()
            ->with(['product', 'supplier', 'customer', 'creator', 'agent'])
            ->where('supplier_id', $supplier->id)
            ->whereBetween('actual_delivery_date', [
                $monthDate->startOfMonth()->toDateTimeString(),
                $monthDate->endOfMonth()->toDateTimeString()
            ]);

        // 🔹 Фильтр по статусу
        switch ($status) {
            case 'completed':
                $query->where('status', 'completed');
                break;
            case 'pending':
                $query->whereIn('status', ['pending', 'assigned', 'delivered']);
                break;
            case 'incomplete':
                // Незавершённые: статус не completed ИЛИ нет даты доставки ИЛИ нет суммы
                $query->where(function ($q) {
                    $q->where('status', '!=', 'completed')
                        ->orWhereNull('actual_delivery_date')
                        ->orWhere('total_price', 0)
                        ->orWhereNull('total_price');
                })->where('status', '!=', 'rejected');
                break;
            case 'rejected':
                $query->where('status', 'rejected');
                break;
            // 'all' или ничего — без фильтра
        }

        $sales = $query
            ->orderByDesc('actual_delivery_date')
            ->paginate($request->get('per_page', 20));

        // 🔹 Статистика за месяц (без учёта пагинации)
        $statsQuery = Sale::query()
            ->where('supplier_id', $supplier->id)
            ->where('status', 'completed')
            ->whereBetween('actual_delivery_date', [
                $monthDate->startOfMonth()->toDateTimeString(),
                $monthDate->endOfMonth()->toDateTimeString()
            ]);

        $response = $sales->toArray();
        $response['stats'] = [
            'total_deliveries' => $statsQuery->count(),
            'total_sum' => round($statsQuery->sum('total_price'), 2),
            'pending_count' => Sale::query()
                ->where('supplier_id', $supplier->id)
                ->whereIn('status', ['pending', 'assigned', 'delivered'])
                ->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateTimeString(),
                    $monthDate->endOfMonth()->toDateTimeString()
                ])
                ->count(),
        ];

        return response()->json($response);
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
        $botUser = $request->botUser ?? auth()->user();

        if (!$botUser) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }

        // sales.agent_id хранит ID модели Agent, а не ID пользователя User.
        $agent = \App\Models\Agent::where('user_id', $botUser->id)->first();

        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $month);
            $startDate = (clone $monthDate)->startOfMonth()->toDateTimeString();
            $endDate = (clone $monthDate)->endOfMonth()->toDateTimeString();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

        $onlySelfSales = $request->boolean('only_self_sales')
            || (int) $botUser->role === \App\Enums\RoleEnum::AGENT->value;

        $realSalesFilter = function ($query) use (
            $startDate,
            $endDate,
            $onlySelfSales,
            $botUser,
            $agent
        ) {
            $query
                ->where('status', 'completed')
                ->whereNotNull('supplier_id')
                ->whereBetween('actual_delivery_date', [$startDate, $endDate]);

            if ($onlySelfSales) {
                $query->where(function ($roleQuery) use ($botUser, $agent) {
                    if ($agent) {
                        $roleQuery
                            ->where('agent_id', $agent->id)
                            ->orWhere('created_by_id', $botUser->id);
                    } else {
                        $roleQuery->where('created_by_id', $botUser->id);
                    }
                });
            }
        };

        $query = \App\Models\Supplier::query()
            ->withCount([
                'sales as month_sales_count' => $realSalesFilter,

                'sales as month_cash_sales_count' => function ($salesQuery) use ($realSalesFilter) {
                    $realSalesFilter($salesQuery);
                    $salesQuery->where('payment_type', 0);
                },

                'sales as month_cashless_sales_count' => function ($salesQuery) use ($realSalesFilter) {
                    $realSalesFilter($salesQuery);
                    $salesQuery->where(function ($paymentQuery) {
                        $paymentQuery
                            ->where('payment_type', '!=', 0)
                            ->orWhereNull('payment_type');
                    });
                },
            ])
            ->withSum([
                'sales as month_turnover' => $realSalesFilter,
            ], 'total_price')
            ->having('month_sales_count', '>', 0)
            ->orderByDesc('month_turnover');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $perPage = max(1, (int) $request->get('per_page', $request->get('size', 30)));
        $suppliers = $query->paginate($perPage);

        // Общий оборот по всем поставщикам за выбранный месяц.
        $salesQueryForTotal = \App\Models\Sale::query();
        $realSalesFilter($salesQueryForTotal);
        $totalTurnover = (float) $salesQueryForTotal->sum('total_price');


        $suppliers->getCollection()->transform(function ($supplier) use ($totalTurnover) {
            $total = (int) $supplier->month_sales_count;
            $cash = (int) $supplier->month_cash_sales_count;
            $cashless = (int) $supplier->month_cashless_sales_count;

            $supplier->month_cash_sales_percent = $total > 0
                ? round(($cash / $total) * 100, 1)
                : 0;

            $supplier->month_cashless_sales_percent = $total > 0
                ? round(($cashless / $total) * 100, 1)
                : 0;

            $supplierTurnover = (float) ($supplier->month_turnover ?? 0);
            $supplier->month_turnover_percent = $totalTurnover > 0
                ? round(($supplierTurnover / $totalTurnover) * 100, 1)
                : 0;

            return $supplier;
        });

        $salesQuery = \App\Models\Sale::query();
        $realSalesFilter($salesQuery);

        $totalSalesCount = (clone $salesQuery)->count();
        $cashSalesCount = (clone $salesQuery)
            ->where('payment_type', 0)
            ->count();

        $cashlessSalesCount = (clone $salesQuery)
            ->where(function ($paymentQuery) {
                $paymentQuery
                    ->where('payment_type', '!=', 0)
                    ->orWhereNull('payment_type');
            })
            ->count();

        $totalTurnover = (clone $salesQuery)->sum('total_price');

        $supplierCountQuery = \App\Models\Supplier::query()
            ->whereHas('sales', $realSalesFilter);

        if ($request->filled('name')) {
            $supplierCountQuery->where('name', 'like', '%' . $request->name . '%');
        }

        $totalSuppliers = $supplierCountQuery->count();

        $cashSalesPercent = $totalSalesCount > 0
            ? round(($cashSalesCount / $totalSalesCount) * 100, 1)
            : 0;

        $cashlessSalesPercent = $totalSalesCount > 0
            ? round(($cashlessSalesCount / $totalSalesCount) * 100, 1)
            : 0;

        // ВАЖНО: пагинатор должен быть на верхнем уровне ответа.
        // Pinia ожидает data.data как массив поставщиков.
        $response = $suppliers->toArray();
        $response['stats'] = [
            'total_suppliers' => $totalSuppliers,
            'total_turnover' => $totalTurnover,
            'total_turnover_after_tax' => round($totalTurnover * 0.94, 2),
            'tax_percent' => 6,
            'total_sales_count' => $totalSalesCount,
            'cash_sales_count' => $cashSalesCount,
            'cash_sales_percent' => $cashSalesPercent,
            'cashless_sales_count' => $cashlessSalesCount,
            'cashless_sales_percent' => $cashlessSalesPercent,
        ];

        return response()->json($response);
    }

    public function inactive(Request $request)
    {
        $botUser = $request->botUser;
        $agent = Agent::where('user_id', $botUser->id)->first();
        $month = $request->get('month', now()->format('Y-m'));

        try {
            $monthDate = \Carbon\Carbon::parse($month . '-01');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Неверный формат месяца'], 422);
        }

    /*    $favoriteIds = $agent ? ($agent->favorite_suppliers ?? []) : [];*/

        // 🔹 Для статистики (без избранных)
        $salesQuery = function ($q) use ($monthDate, $botUser, $agent) {
            $q->where('status', 'completed')
                ->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateTimeString(),
                    $monthDate->endOfMonth()->toDateTimeString()
                ]);

         /*   if (!empty($favoriteIds)) {
                $q->whereNotIn('supplier_id', $favoriteIds);
            }*/

            if ($botUser->role < RoleEnum::SUPERADMIN->value) {
                $q->where(function ($subQ) use ($botUser, $agent) {
                    if ($agent) {
                        $subQ->where('agent_id', $agent->id);
                    }
                    $subQ->orWhere('created_by_id', $botUser->id);
                });
            }
        };

        // 🔹 Реальный подсчёт для отображения (не исключает избранных)
        $realSalesQuery = function ($q) use ($monthDate, $botUser, $agent) {
            $q->where('status', 'completed')
                ->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateTimeString(),
                    $monthDate->endOfMonth()->toDateTimeString()
                ]);

            if ($botUser->role < RoleEnum::SUPERADMIN->value) {
                $q->where(function ($subQ) use ($botUser, $agent) {
                    if ($agent) {
                        $subQ->where('agent_id', $agent->id);
                    }
                    $subQ->orWhere('created_by_id', $botUser->id);
                });
            }
        };

        // 🔹 Основной запрос для списка (с пагинацией)
        $query = Supplier::query()
            ->withCount(['sales as month_sales_count' => $realSalesQuery])
            ->having('month_sales_count', '=', 0)
            ->orderBy('name', 'asc');

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $perPage = $request->get('per_page', $request->size ?? 30);
        $suppliers = $query->paginate($perPage);

        // 🔹 Помечаем флагом
       /* $suppliers->getCollection()->transform(function ($supplier) use ($favoriteIds) {
            $supplier->is_favorite = in_array($supplier->id, $favoriteIds);
            return $supplier;
        });*/

        // 🔹 СУММАРНАЯ СТАТИСТИКА (без учёта пагинации)

        // Количество неактивных НЕ избранных поставщиков (отдельный запрос без пагинации)
        $nonFavoriteCountQuery = Supplier::query()
            ->select('suppliers.id')
            ->withCount(['sales as month_sales_count' => $realSalesQuery])
            ->having('month_sales_count', '=', 0);

        /*if (!empty($favoriteIds)) {
            $nonFavoriteCountQuery->whereNotIn('suppliers.id', $favoriteIds);
        }*/

        if ($request->filled('name')) {
            $nonFavoriteCountQuery->where('name', 'like', '%' . $request->name . '%');
        }

        $nonFavoriteCount = $nonFavoriteCountQuery->count();

        $response = $suppliers->toArray();
        $response['stats'] = [
            'total_suppliers' => $nonFavoriteCount,
        ];

        return response()->json($response);
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
