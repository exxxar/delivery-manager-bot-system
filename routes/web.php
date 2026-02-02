<?php

use App\Exports\ExportType4\RevenueExportSheet;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\BirthdayController;
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
use Illuminate\Support\Facades\Log;
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

Route::get("/upload-suppliers", function () {

    function readCsv(string $path, callable $callback): void
    {
        if (($handle = fopen($path, 'r')) === false) {
            throw new \Exception("Cannot open CSV file");
        }

        $header = fgetcsv($handle, 0, ';');

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $data = array_combine($header, $row);
            $callback($data);
        }

        fclose($handle);
    }

    $path = storage_path('app/data.csv');

    readCsv($path, function ($data) {

        $supplier = \App\Models\Supplier::query()
            ->where("phone", $data["phone"])
            ->first();

        $category = \App\Models\ProductCategory::query()
            ->where("name", $data['category'])
            ->first();

        if (is_null($category))
            $category = \App\Models\ProductCategory::query()
                ->create([
                    'name' => $data["category"],
                    'description' => $data["category"],
                ]);

        if (is_null($supplier))
            $supplier = \App\Models\Supplier::query()->create([
                'name' => $data['supplier'],
                'description' => $data['category'],
                'address' => null,
                'phone' => $data['phone'] ?? null,
                'work_phone' => $data['work_phone'] ?? null,
                'percent' => 8,
                'birthday' => null,
                'email' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        else {
            $tmp = $supplier->description ?? '';

            if (mb_strpos($tmp, mb_trim($data['category']))===false)
                $tmp .= ', ' . (strtolower($data['category']) ?? '');

            $supplier->description = $tmp;
            $supplier->save();
        }

        $product = \App\Models\Product::query()
            ->where("product_category_id", $category->id)
            ->where("supplier_id", $supplier->id)
            ->first();

        if (is_null($product))
            \App\Models\Product::query()
                ->create([
                    'name' => $data["product"],
                    'description' => $data["category"] ?? '',
                    'price' => 0,
                    'count' => 1,
                    'supplier_id' => $supplier->id,
                    'product_category_id' => $category->id,
                ]);
    });
});

Route::any('/register-webhook', [\App\Http\Controllers\TelegramController::class, "registerWebhooks"]);
Route::post('/webhook', [\App\Http\Controllers\TelegramController::class, "handler"]);
Route::get("/bot", [\App\Http\Controllers\TelegramController::class, "homePage"]);
Route::get("/blocked", [\App\Http\Controllers\TelegramController::class, "blockedPage"])
    ->name("blocked");


Route::get('/', function () {
    return "ok";
   /* return Inertia::render('Default/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);*/
});

/*Route::get('/dashboard', function () {
    return Inertia::render('Default/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');*/

/*
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});*/

/*require __DIR__ . '/auth.php';*/

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
            ->group(function () {
                Route::post('/import-products-with-categories', [ProductController::class, 'import'])->name('imports.products');
            });

        Route::prefix("birthdays")
            ->middleware(["tg.role:super"])
            ->group(function () {
                Route::post('/', [BirthdayController::class, 'birthdaysNextWeek'])->name('birthdays.list');
            });


        // 🔹 Экспорты
        Route::prefix('exports')
            ->middleware(["tg.role:agent"])
            ->group(function () {
                Route::get('/agents', [AgentController::class, 'export'])->name('exports.agents');
                Route::get('/birthdays', [BirthdayController::class, 'export'])->name('exports.birthdays');
                Route::get('/admins', [UserController::class, 'exportAdmins'])->name('exports.admins');
                Route::get('/users', [UserController::class, 'export'])->name('exports.users');
                Route::get('/products', [ProductController::class, 'export'])->name('exports.products');
                Route::get('/categories', [ProductCategoryController::class, 'export'])->name('exports.categories');
                Route::get('/clients', [CustomerController::class, 'export'])->name('exports.clients');
                Route::get('/suppliers', [SupplierController::class, 'export'])->name('exports.suppliers');
                Route::get('/sales-history', [SaleController::class, 'export'])->name('exports.salesHistory');
                Route::post('/full', [AdminController::class, 'exportFull'])->name('exports.full');
                Route::post('/individual/{id}', [AdminController::class, 'exportIndividual'])->name('exports.individual');
                //сделать экспорт по конкретному админу
            });

        Route::prefix('suppliers')
            ->middleware(["tg.role:agent"])
            ->group(function () {
                Route::get('/with-products', [SupplierController::class, 'indexWithProducts']);
                Route::get('/fetch-next-products/{supplierId}/products', [SupplierController::class, 'nextProducts']);
                Route::post('/remove-all', [SupplierController::class, 'removeAll'])
                    ->middleware(["tg.role:admin"]);
                Route::post("/toggle-favorite", [SupplierController::class, "toggleSupplierInFavorites"]);

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
                Route::delete('/{id}', [SupplierController::class, 'destroy'])
                    ->middleware(["tg.role:admin"]);

            });

        Route::prefix('products')
            ->middleware(["tg.role:agent"])
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
                Route::delete('/{id}', [ProductController::class, 'destroy'])
                    ->middleware(["tg.role:admin"]);
            });

        Route::prefix('product-categories')
            ->middleware(["tg.role:agent"])
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
                Route::delete('/{id}', [ProductCategoryController::class, 'destroy'])
                    ->middleware(["tg.role:admin"]);

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

                Route::post('/accept-all', [SaleController::class, 'acceptAll'])
                    ->middleware(["tg.role:admin"]);

                Route::post('/confirm-deal-payment', [SaleController::class, 'confirmDealPayment']);
                Route::post('/confirm-payment', [SaleController::class, 'confirmPayment']);
                Route::post('/confirm-deal', [SaleController::class, 'confirmDeal']);
                Route::get('/self-sales', [AgentController::class, 'selfSales']);
                Route::get('/payment-document/{id}', [SaleController::class, 'getPaymentDocument']);
                // Получить конкретную продажу по ID
                Route::get('/{id}', [SaleController::class, 'show']);
                // Обновить данные продажи
                Route::post('/{id}', [SaleController::class, 'update']);
              //  Route::patch('/{id}', [SaleController::class, 'update']);
                // Удалить продажу
                Route::delete('/{id}', [SaleController::class, 'destroy']);
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
            ->middleware(["tg.role:agent"])
            ->group(function () {
                // Список всех пользователей
                Route::get('/', [UserController::class, 'index']);
                // Создать нового пользователя
                Route::post('/', [UserController::class, 'store']);

                Route::get('/request-role', [UserController::class, 'requestRole']);

                // Получить конкретного пользователя по ID
                Route::get('/{id}', [UserController::class, 'show']);


                // Обновить данные пользователя
                Route::put('/{id}', [UserController::class, 'update'])
                    ->middleware(["tg.role:super"]);
                Route::patch('/{id}', [UserController::class, 'update'])
                    ->middleware(["tg.role:super"]);


                // Удалить пользователя
                Route::delete('/{id}', [UserController::class, 'destroy'])
                    ->middleware(["tg.role:super"]);
                // 🔹 Дополнительные маршруты для ролей и статусов

                Route::get('/{id}/tg', [UserController::class, 'getTelegramLink']);
                // Изменить роль пользователя
                Route::post('/{id}/role', [UserController::class, 'updateRole'])
                    ->middleware(["tg.role:super"]);
                // Изменить процент
                Route::get('/{id}/percent', [UserController::class, 'updatePercent']);
                // Изменить статус работы (is_work)
                Route::post('/{id}/work-status', [UserController::class, 'updateWorkStatus'])
                    ->middleware(["tg.role:super"]);
                // Заблокировать пользователя
                Route::get('/{id}/block', [UserController::class, 'block'])
                    ->middleware(["tg.role:super"]);
                // Разблокировать пользователя
                Route::get('/{id}/unblock', [UserController::class, 'unblock'])
                    ->middleware(["tg.role:super"]);
                Route::post('/primary', [UserController::class, 'primary']);
            });
    });



