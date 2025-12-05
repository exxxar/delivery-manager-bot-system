<?php

use App\Exports\ExportType4\RevenueExportSheet;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Forms\AdminJobController;
use App\Http\Controllers\Forms\AgentJobController;
use App\Http\Controllers\Forms\ClientJobController;
use App\Http\Controllers\Forms\SupplierJobController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Models\Agent;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get("/test-6", function(Request $request){
    $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
    $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

    return Excel::download(new \App\Exports\ExportType4\SummaryAgentReport($startDate, $endDate), "export.xlsx");

});

Route::get("/test-5", function (Request $request) {

    $agentId = 1;
    $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
    $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

    // Общая сумма продаж
    $totalSales = Sale::whereBetween('sale_date', [$startDate, $endDate])->sum('total_price');

    // Налог и переводы
    $taxPercent = env('TAX_PERCENT', 8); // %
    $transferPercent = env('TRANSFER_PERCENT', 5); // %

    $taxAmount = $totalSales * ($taxPercent / 100);
    $afterTax = $totalSales - $taxAmount;

    $transferFromTotal = $totalSales * ($transferPercent / 100);
    $transferFromAfterTax = $afterTax * ($transferPercent / 100);
    $revenueTotal = 0;
    $revenueWithoutTaxTotal = 0;

    // --- 1. Продажи по датам и поставщикам ---
    $salesByDateSupplier = Sale::query()
        ->whereBetween('sale_date', [$startDate, $endDate])
        ->where("agent_id", $agentId)
        ->orderBy('sale_date')
        ->with('supplier')
        ->get()
        ->map(function ($sale) use ($taxPercent, &$revenueTotal, &$revenueWithoutTaxTotal) {
            $percent = $sale->supplier->percent ?? 0;
            $revenueLocal = $sale->total_price * ($percent / 1);
            $taxAmount = $revenueLocal * ($taxPercent / 100);
            $revenueAfterTax = $revenueLocal - $taxAmount;

            $revenueTotal += $revenueLocal;
            $revenueWithoutTaxTotal += $revenueAfterTax;
            return [
                'date' => $sale->sale_date,
                'supplier_id' => $sale->supplier_id,
                'supplier_name' => $sale->supplier->name ?? 'Unknown',
                'sale_amount' => $sale->total_price,
                'percent' => $percent,
                'revenue_total' => $revenueLocal,
                'revenue_after_tax' => $revenueAfterTax,
            ];
        });


    // --- 2. Доход админов ---
    $adminsRevenue = User::where('is_work', true)
        ->get()
        ->map(function ($user) use ($revenueTotal, $revenueWithoutTaxTotal) {
            $incomeTotal = $revenueTotal * ($user->percent / 100);
            $incomeAfterTax = $revenueWithoutTaxTotal * ($user->percent / 100);

            return [
                'admin_id' => $user->id,
                'admin_name' => $user->name,
                'percent' => $user->percent,
                'income_total' => $incomeTotal,
                'income_after_tax' => $incomeAfterTax,
            ];
        });

    $data = [
        'agent' =>Agent::query()->find($agentId)->toArray(),
        'period' => [
            'start' => $startDate,
            'end' => $endDate,
        ],
        'summary' => [
            'total_sales' => $totalSales,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'after_tax' => $afterTax,
            'transfer_percent' => $transferPercent,
            'transfer_from_total' => $transferFromTotal,
            'transfer_from_after_tax' => $transferFromAfterTax,
            'revenue_total' => $revenueTotal,
            'revenue_without_tax_total' => $revenueWithoutTaxTotal,
        ],
        'sales_by_date_supplier' => $salesByDateSupplier,
        'admins' => $adminsRevenue,
    ];

    return Excel::download(new RevenueExportSheet("test", $data), "export.xlsx");


});

Route::get("/test-bot", function () {
    $data = Excel::raw(new \App\Exports\UsersExport, \Maatwebsite\Excel\Excel::XLSX);
    \App\Facades\BotMethods::bot()
        ->sendDocument("484698703", "test", \Telegram\Bot\FileUpload\InputFile::createFromContents($data, "test.xlsx"));
});

Route::any('/register-webhook', [\App\Http\Controllers\TelegramController::class, "registerWebhooks"]);
Route::post('/webhook', [\App\Http\Controllers\TelegramController::class, "handler"]);
Route::get("/bot", [\App\Http\Controllers\TelegramController::class, "homePage"]);
Route::get("/blocked", [\App\Http\Controllers\TelegramController::class, "blockedPage"])
    ->name("blocked");

Route::get("/test-2", function () {

    $start = Carbon::now()->startOfMonth();
    $end = Carbon::now()->endOfMonth();
// Сумма по каждому агенту (с учётом процента поставщика)
    $byAgents = DB::table('sales as sa')
        ->join('suppliers as sup', 'sa.supplier_id', '=', 'sup.id')
        ->select('sa.agent_id', DB::raw('SUM(sa.total_price * sup.percent / 100) as revenue'))
        ->whereBetween('sa.sale_date', [$start, $end])
        ->where('sa.status', 'completed')
        ->groupBy('sa.agent_id')
        ->get();

    // Общая сумма с учётом процента
    $totalWithPercent = DB::table('sales as sa')
        ->join('suppliers as sup', 'sa.supplier_id', '=', 'sup.id')
        ->whereBetween('sa.sale_date', [$start, $end])
        ->where('sa.status', 'completed')
        ->sum(DB::raw('sa.total_price * sup.percent / 100'));

    // Общая сумма оборота (без учёта процента)
    $totalTurnover = DB::table('sales as sa')
        ->whereBetween('sa.sale_date', [$start, $end])
        ->where('sa.status', 'completed')
        ->sum('sa.total_price');

    return response()->json([
        'period' => compact('start', 'end'),
        'agents' => $byAgents,
        'total_with_percent' => (float)$totalWithPercent,
        'total_turnover' => (float)$totalTurnover,
    ]);

    /*    $title = Carbon::now()->format('Y-m-d H-i-s');

        return Excel::download(new \App\Exports\ExportType2\SupplierSheet(), "export-$title.xlsx");*/
    //return Excel::download(new \App\Exports\ExportType1\SummarySuppliersReport(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()), "export-$title.xlsx");
    // return \App\Facades\BusinessLogicFacade::method()->getMonthlySalesSummaryForAllAgentsByEachSupplier();
});

Route::get('/test-4', function () {
    $admin = \App\Models\User::query()
        ->where("role", \App\Enums\RoleEnum::ADMIN->value)
        ->first();

    return Excel::download(new \App\Exports\ExportType3\AdminWorkReport(
        $admin->id,
        '2025-11-01',
        '2025-11-17'
    ), "test.xlsx");
});
Route::get('/test-3', function () {
    $agent = Agent::query()->find(1);
    return Excel::download(new \App\Exports\ExportType2\SummaryAgentReport(), "test.xlsx");
});

Route::get("/test", function () {

/// Параметры запроса
    $fromDate = request('from') ?: Carbon::now()->subMonths(1); // начальная дата
    $toDate = request('to') ?: Carbon::now();                   // конечная дата

// Загрузка всех продаж за указанный период
    $saleQuery = Sale::with(['agent', 'supplier'])
        ->whereBetween('created_at', [$fromDate, $toDate])
        ->get();

// Матрица данных
    $result = [];

// Итоговые показатели
    $totalPerAgent = []; // итог по каждому агенту
    $totalPerSupplier = []; // итог по каждому поставщику

// Сбор данных
    foreach ($saleQuery as $sale) {
        // Объект с информацией о сделке
        $daysOfWeek = [
            'Sunday' => 'Воскресенье',
            'Monday' => 'Понедельник',
            'Tuesday' => 'Вторник',
            'Wednesday' => 'Среда',
            'Thursday' => 'Четверг',
            'Friday' => 'Пятница',
            'Saturday' => 'Суббота'
        ];

        $selectedDayOfWeek = Carbon::parse($sale->created_at)->translatedFormat('l');
        $detail = [
            'price' => $sale->total_price,
            'agent' => $sale->agent->name,
            'supplier' => $sale->supplier->name,
            'week_day' => $daysOfWeek[$selectedDayOfWeek], // день недели
            'date' => Carbon::parse($sale->created_at)->format('d.m.Y'), // дата
        ];

        // Добавляем детали в матрицу
        $result[$sale->supplier_id][$sale->agent_id][] = $detail;

        // Обновляем итоговые показатели
        $totalPerAgent[$sale->agent_id] = isset($totalPerAgent[$sale->agent_id]) ? $totalPerAgent[$sale->agent_id] + $sale->total_price : $sale->total_price;
        $totalPerSupplier[$sale->supplier_id] = isset($totalPerSupplier[$sale->supplier_id]) ? $totalPerSupplier[$sale->supplier_id] + $sale->total_price : $sale->total_price;
    }

// Пост-обработка данных
    $finalResult = [];

// Обрабатываем матрицу
    foreach ($result as $supplierId => $dataForSupplier) {
        $sumPerSupplier = 0; // накопитель суммы по поставщику
        foreach ($dataForSupplier as $agentId => $deals) {
            // Сумма продаж для данного агента и поставщика
            $sumPerAgentInSupplier = array_sum(array_column($deals, 'price'));
            $finalResult[$supplierId][$agentId] = [
                'sum' => $sumPerAgentInSupplier,
                'details' => $deals, // сохраняем полную информацию о сделках
            ];
            $sumPerSupplier += $sumPerAgentInSupplier;
        }

        // Добавляем итоговую ячейку по поставщику
        $finalResult[$supplierId]['Total'] = $sumPerSupplier;
    }

// Итоговая строка (суммы по каждому агенту)
    foreach ($totalPerAgent as $agentId => $totalSum) {
        $finalResult['Total'][$agentId] = $totalSum;
    }

// Общий итог (нижний правый угол)
    if (isset($finalResult['Total'])) {
        $finalResult['Total']['Total'] = array_sum($totalPerSupplier);
    }

// Возвращаем результат
    return response()->json($finalResult);
    // return Excel::download(new \App\Exports\ExportType1\SummarySuppliersReport(), 'отчёт.xlsx');
});

Route::get('/', function () {
    return Inertia::render('Default/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Default/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::prefix("bot-api")
    ->middleware(["tg.auth"])
    ->group(function () {


        Route::prefix('forms')
            ->middleware(["tg.role:user"])
            ->group(function () {
                // Заявка администратора
                Route::post('/admin-job', [AdminJobController::class, 'store']);
                // Заявка торгового агента
                Route::post('/agent-job', [AgentJobController::class, 'store']);
                // Заявка поставщика
                Route::post('/supplier-job', [SupplierJobController::class, 'store']);
                // Заявка клиента (оптовое сотрудничество)
                Route::post('/client-job', [ClientJobController::class, 'store']);
            });

        Route::prefix("agents")
            ->middleware(["tg.role:admin"])
            ->group(function () {
                // Получить список всех агентов
                Route::get('/', [AgentController::class, 'index']);
                // Создать нового агента
                Route::post('/', [AgentController::class, 'store']);

                // Получить конкретного агента по ID
                Route::get('/{id}', [AgentController::class, 'show']);
                Route::put('/{id}', [AgentController::class, 'update']);
                Route::patch('/{id}', [AgentController::class, 'update']); // частичное обновление
                // Удалить агента
                Route::delete('/{id}', [AgentController::class, 'destroy']);
            });

        Route::prefix("imports")
            ->middleware(["tg.role:super"])
            ->group(function(){
                Route::post('/import-products-with-categories', [ProductController::class, 'import'])->name('imports.products');
            });

        // 🔹 Экспорты
        Route::prefix('exports')
            ->middleware(["tg.role:super"])
            ->group(function () {
                Route::get('/agents', [AgentController::class, 'export'])->name('exports.agents');
                Route::get('/admins', [UserController::class, 'exportAdmins'])->name('exports.admins');
                Route::get('/users', [UserController::class, 'export'])->name('exports.users');
                Route::get('/products', [ProductController::class, 'export'])->name('exports.products');
                Route::get('/categories', [ProductCategoryController::class, 'export'])->name('exports.categories');
                Route::get('/clients', [CustomerController::class, 'export'])->name('exports.clients');
                Route::get('/suppliers', [SupplierController::class, 'export'])->name('exports.suppliers');
                Route::get('/sales-history', [SaleController::class, 'export'])->name('exports.salesHistory');
                Route::post('/full', [AdminController::class, 'exportFull'])->name('exports.full');
            });

        Route::prefix('suppliers')
            ->middleware(["tg.role:admin"])
            ->group(function () {
                Route::get('/with-products', [SupplierController::class, 'indexWithProducts']);
                Route::get('/fetch-next-products/{supplierId}/products', [SupplierController::class, 'nextProducts']);

                // Список всех поставщиков
                Route::get('/', [SupplierController::class, 'index']);
                // Создать нового поставщика
                Route::post('/', [SupplierController::class, 'store']);
                // Получить конкретного поставщика по ID
                Route::get('/{id}', [SupplierController::class, 'show']);
                // Обновить данные поставщика
                Route::put('/{id}', [SupplierController::class, 'update']);
                Route::patch('/{id}', [SupplierController::class, 'update']); // частичное обновление
                // Удалить поставщика
                Route::delete('/{id}', [SupplierController::class, 'destroy']);

                 });

        Route::prefix('products')
            ->middleware(["tg.role:admin"])
            ->group(function () {
                // Список всех продуктов
                Route::get('/', [ProductController::class, 'index']);
                // Создать новый продукт
                Route::post('/', [ProductController::class, 'store']);
                // Получить конкретный продукт по ID
                Route::get('/{id}', [ProductController::class, 'show']);
                // Обновить данные продукта
                Route::put('/{id}', [ProductController::class, 'update']);
                Route::patch('/{id}', [ProductController::class, 'update']); // частичное обновление
                // Удалить продукт
                Route::delete('/{id}', [ProductController::class, 'destroy']);
            });

        Route::prefix('product-categories')
            ->middleware(["tg.role:admin"])
            ->group(function () {
                Route::get('/with-products', [ProductCategoryController::class, 'indexWithProducts']);
                Route::get('/fetch-next-products/{categoryId}/products', [ProductCategoryController::class, 'nextProducts']);
                Route::post('/remove-all', [ProductCategoryController::class, 'removeAll']);

                // Список всех категорий товаров
                Route::get('/', [ProductCategoryController::class, 'index']);
                // Создать новую категорию
                Route::post('/', [ProductCategoryController::class, 'store']);
                // Получить конкретную категорию по ID
                Route::get('/{id}', [ProductCategoryController::class, 'show']);
                // Обновить данные категории
                Route::put('/{id}', [ProductCategoryController::class, 'update']);
                Route::patch('/{id}', [ProductCategoryController::class, 'update']); // частичное обновление
                // Удалить категорию
                Route::delete('/{id}', [ProductCategoryController::class, 'destroy']);

            });

        Route::prefix('customers')
            ->middleware(["tg.role:admin"])
            ->group(function () {
                // Список всех клиентов
                Route::get('/', [CustomerController::class, 'index']);
                // Создать нового клиента
                Route::post('/', [CustomerController::class, 'store']);
                // Получить конкретного клиента по ID
                Route::get('/{id}', [CustomerController::class, 'show']);
                // Обновить данные клиента
                Route::put('/{id}', [CustomerController::class, 'update']);
                Route::patch('/{id}', [CustomerController::class, 'update']); // частичное обновление
                // Удалить клиента
                Route::delete('/{id}', [CustomerController::class, 'destroy']);
            });

        Route::prefix('sales')
            ->middleware(["tg.role:agent"])
            ->group(function () {
                // Список всех продаж
                Route::get('/', [SaleController::class, 'index']);
                // Создать новую продажу
                Route::post('/', [SaleController::class, 'store']);
                Route::get('/self-sales', [AgentController::class, 'selfSales']);
                // Получить конкретную продажу по ID
                Route::get('/{id}', [SaleController::class, 'show']);
                // Обновить данные продажи
                Route::put('/{id}', [SaleController::class, 'update']);
                Route::patch('/{id}', [SaleController::class, 'update']); // частичное обновление
                // Удалить продажу
                Route::delete('/{id}', [SaleController::class, 'destroy'])
                    ->middleware(["tg.role:admin"]);
            });

        Route::prefix('admins')
            ->middleware(["tg.role:agent"])
            ->group(function () {
                // Список всех продаж
                Route::get('/', [AdminController::class, 'index']);
                // Создать новую продажу
                Route::post('/download-report', [AdminController::class, 'downloadReport']);
                Route::post('/download-personal-report', [AdminController::class, 'downloadPersonalReport']);
                // Получить конкретную продажу по ID
            });

        Route::post('/users/self', [\App\Http\Controllers\TelegramController::class, "getSelf"]);

        Route::prefix('users')
            ->middleware(["tg.role:super"])
            ->group(function () {
                // Список всех пользователей
                Route::get('/', [UserController::class, 'index']);
                // Создать нового пользователя
                Route::post('/', [UserController::class, 'store']);
                // Получить конкретного пользователя по ID
                Route::get('/{id}', [UserController::class, 'show']);

                // Обновить данные пользователя
                Route::put('/{id}', [UserController::class, 'update']);
                Route::patch('/{id}', [UserController::class, 'update']);


                // Удалить пользователя
                Route::delete('/{id}', [UserController::class, 'destroy']);
                // 🔹 Дополнительные маршруты для ролей и статусов

                Route::get('/{id}/tg', [UserController::class, 'getTelegramLink']);
                // Изменить роль пользователя
                Route::post('/{id}/role', [UserController::class, 'updateRole']);
                // Изменить процент
                Route::get('/{id}/percent', [UserController::class, 'updatePercent']);
                // Изменить статус работы (is_work)
                Route::post('/{id}/work-status', [UserController::class, 'updateWorkStatus']);
                // Заблокировать пользователя
                Route::get('/{id}/block', [UserController::class, 'block']);
                // Разблокировать пользователя
                Route::get('/{id}/unblock', [UserController::class, 'unblock']);
            });
    });



