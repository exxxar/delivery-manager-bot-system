<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Forms\AdminJobController;
use App\Http\Controllers\Forms\AgentJobController;
use App\Http\Controllers\Forms\ClientJobController;
use App\Http\Controllers\Forms\SupplierJobController;
use App\Http\Controllers\PercentageController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/auth/me', [AuthController::class, 'me']);
Route::post('/auth/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/telegram', [\App\Http\Controllers\AuthController::class, 'loginTelegram']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout']);
});

Route::post('/auth/telegram', [AuthController::class, 'telegram']);

Route::middleware(['auth:sanctum','bot.user'])->group(function(){

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
            Route::post('/percentage', [PercentageController::class, 'list']);
            Route::post('/remove-percentage', [PercentageController::class, 'remove']);
            Route::post('/store-percentage', [PercentageController::class, 'store']);

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

    Route::prefix('reports')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ReportController::class, 'index'])->name('reports.index');
            Route::get('/{report}/download', [\App\Http\Controllers\Api\ReportController::class, 'download'])->name('reports.download');
            Route::delete('/{report}', [\App\Http\Controllers\Api\ReportController::class, 'destroy'])->name('reports.destroy');
        });

    Route::prefix('logs')
        ->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\UserLogController::class, 'index'])->name('logs.index');
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
            Route::post('/salary', [AdminController::class, 'downloadReport'])->name('exports.salary');
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

            Route::get('/', [SupplierController::class, 'index']);
            Route::get('/active', [SupplierController::class, 'active'])->name('suppliers.active');
            Route::get('/inactive', [SupplierController::class, 'inactive'])->name('suppliers.inactive');


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
            Route::get('/bad-sales', [SaleController::class, 'getBadSales']);
            // Создать новую продажу
            Route::post('/', [SaleController::class, 'store']);
            Route::post('/not-verified', [SaleController::class, 'notVerified'])
                ->middleware(["tg.role:admin"]);

            Route::get('/approve/{id}', [SaleController::class, 'approve'])
                ->middleware(["tg.role:admin"]);

            Route::get('/decline/{id}', [SaleController::class, 'decline'])
                ->middleware(["tg.role:admin"]);

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
