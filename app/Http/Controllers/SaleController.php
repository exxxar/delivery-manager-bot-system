<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Exports\ProductsExport;
use App\Exports\SalesExport;
use App\Facades\BotMethods;
use App\Facades\UserLog;
use App\Http\Requests\SaleStoreRequest;
use App\Http\Requests\SaleUpdateRequest;
use App\Models\Agent;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use http\Client\Curl\User;
use HttpException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Telegram\Bot\FileUpload\InputFile;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::query()
            ->filter($request)
            ->sort($request);

        // 🔹 Если запрошена группировка по месяцу
        if ($request->filled('month')) {
            $month = $request->month; // формат: "2026-07"

            // Парсим месяц
            try {
                $monthDate = \Carbon\Carbon::parse($month . '-01');
            } catch (\Exception $e) {
                return response()->json(['message' => 'Неверный формат месяца'], 422);
            }

            // Фильтруем по месяцу (фактическая дата доставки или дата встречи)
            $query->where(function($q) use ($monthDate) {
                $q->whereBetween('actual_delivery_date', [
                    $monthDate->startOfMonth()->toDateString(),
                    $monthDate->endOfMonth()->toDateString()
                ])->orWhere(function($q2) use ($monthDate) {
                    $q2->whereNull('actual_delivery_date')
                        ->whereBetween('due_date', [
                            $monthDate->startOfMonth()->toDateString(),
                            $monthDate->endOfMonth()->toDateString()
                        ]);
                });
            });

            $sales = $query->get();

            // Группируем по дням
            $byDays = $sales->groupBy(function ($sale) {
                $date = $sale->actual_delivery_date
                    ?? $sale->due_date
                    ?? $sale->created_at;
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            })->map(function ($dayItems, $dayKey) {
                return [
                    'date' => $dayKey,
                    'label' => \Carbon\Carbon::parse($dayKey)->translatedFormat('D, d M'),
                    'weekday' => \Carbon\Carbon::parse($dayKey)->dayName,
                    'count' => $dayItems->count(),
                    'total' => round($dayItems->sum('total_price'), 2),
                    'items' => $dayItems->values(),
                ];
            })->sortByDesc('date')->values();

            // Пагинация по дням
            $daysPerPage = $request->get('days_per_page', 7); // по умолчанию 7 дней на страницу
            $currentPage = $request->get('page', 1);
            $totalDays = $byDays->count();
            $totalPages = ceil($totalDays / $daysPerPage);

            $paginatedDays = $byDays->forPage($currentPage, $daysPerPage)->values();

            return response()->json([
                'grouped' => true,
                'month' => $month,
                'month_label' => $monthDate->translatedFormat('F Y'),
                'days' => $paginatedDays,
                'pagination' => [
                    'current_page' => $currentPage,
                    'per_page' => $daysPerPage,
                    'total' => $totalDays,
                    'last_page' => $totalPages,
                    'from' => ($currentPage - 1) * $daysPerPage + 1,
                    'to' => min($currentPage * $daysPerPage, $totalDays),
                ],
                'month_stats' => [
                    'total_sales' => $sales->count(),
                    'total_sum' => round($sales->sum('total_price'), 2),
                    'avg_sum' => $sales->count() > 0 ? round($sales->avg('total_price'), 2) : 0,
                ],
            ]);
        }

        // 🔹 Обычная пагинация (если месяц не указан)
        $sales = $query->paginate($request->get('per_page', $request->size ?? 10));

        return response()->json($sales);
    }

    public function approve(Request $request, $id)
    {
        $sale = Sale::query()
            ->findOrFail($id);

        $sale->verified_at = Carbon::now();
        $sale->save();

        $agent = Agent::query()
            ->with(["user"])
            ->where("id", $sale->agent_id)
            ->first();

        UserLog::log("✅ Старший администратор подтвердил оплату по вашей заявке №$sale->id", $agent->user->id);

        return response()->noContent();

    }

    public function decline(Request $request, $id)
    {
        $sale = Sale::query()
            ->findOrFail($id);

        $agent = Agent::query()
            ->with(["user"])
            ->where("id", $sale->agent_id)
            ->first();

        UserLog::log(
            "❌ Старший администратор выявил ошибку в вашей заявке №$sale->id ($sale->total_price руб.)",
            $agent->user->id
        );


        return response()->noContent();
    }

    public function notVerified(Request $request)
    {
        $sales = Sale::query()
            ->without([
                "product", "agent", "customer", "supplier", "category"
            ])
            ->where("payment_type", 1)
            ->whereNull("verified_at");

        if ($request->date_from || $request->date_to) {
            $sales = $sales->whereBetween('actual_delivery_date', [
                    $request->date_from ?? '1900-01-01',
                    $request->date_to ?? now()->toDateString()
            ]);
        }


        if ($request->agent_id) {
            $sales = $sales->where('agent_id', $request->agent_id);
        }

        $sales = $sales
            ->orderBy("actual_delivery_date", "desc")
            ->paginate($request->get('per_page',
                $request->size ?? 50));

        return response()->json($sales);
    }

    public function getBadSales(Request $request)
    {

        $botUser = $request->botUser;

        $agent = Agent::where('user_id', $botUser->id)->first();

        $sales = Sale::query()
            ->where("status", "completed")
            ->where(function ($q) use ($botUser, $agent) {
                if (is_null($agent)) {
                    $q->where('created_by_id', $botUser->id);
                } else {
                    $q->where('agent_id', $agent->id)
                        ->orWhere('created_by_id', $botUser->id);
                }
            })
            ->where(function ($q) {
                $q->orWhereNull("actual_delivery_date");
            })
            ->get();

        return response()->json($sales);
    }

    public function acceptAll(Request $request)
    {
        $request->validate([
            "ids" => "required"
        ]);

        $ids = $request->ids ?? [];


        $sales = Sale::query()
            ->whereIn("id", $ids)
            ->get();

        foreach ($sales as $sale) {
            if (!is_null($sale->agent_id ?? null)) {
                $agent = Agent::query()
                    ->with(["user", "mentor"])
                    ->where("id", $sale->agent_id)
                    ->first();

                if ($agent->in_learning) {
                    $mentorPercent = $agent->mentor->mentor_percent ?? 0;
                    $sale->mentor_award = ($sale->total_price ?? 0) * ($mentorPercent / 100);
                    $sale->save();
                }


            } else {
                $user = \App\Models\User::query()
                    ->find($sale->created_by_id);

                if ($user && $user->role === RoleEnum::AGENT->value) {
                    $agent = Agent::query()
                        ->where("user_id", $user->id)
                        ->first();

                    $sale->agent_id = $agent->id ?? null;
                }

            }

            $sale->actual_delivery_date = is_null($sale->actual_delivery_date) ?
                Carbon::parse($sale->due_date) :
                $sale->actual_delivery_date;
            $sale->sale_date = is_null($sale->sale_date) ?
                Carbon::parse($sale->due_date) :
                $sale->sale_date;
            $sale->status = "completed";
            $sale->save();
        }

        return response()->noContent();
    }

    public function store(Request $request)
    {
        $botUser = $request->botUser;

        if (!$botUser) {
            return response()->json(['message' => 'Пользователь не авторизован'], 401);
        }

        $data = $request->all();

        // 🔹 Валидация обязательных полей
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'total_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0',
            'payment_type' => 'nullable|in:0,1',
            'sale_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
            'agent_id' => 'nullable|exists:agents,id',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        // 🔹 Обработка файла
        $fileLink = "";
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename);

            $fileLink = env("APP_URL") . "/storage/app/$path";
            $data["payment_document_name"] = $filename;
        }

        // 🔹 Надёжное преобразование булевых значений
        $needAutomaticNaming = filter_var($data["need_automatic_naming"] ?? false, FILTER_VALIDATE_BOOLEAN);
        $isAlreadyDelivered = filter_var($data["is_already_delivered"] ?? false, FILTER_VALIDATE_BOOLEAN);
        $receiptIsLost = filter_var($data["receipt_is_lost"] ?? false, FILTER_VALIDATE_BOOLEAN);

        unset($data["need_automatic_naming"], $data["receipt_is_lost"], $data["is_already_delivered"], $data["file"]);

        // 🔹 Даты
        $data["due_date"] = Carbon::now("+3")->format('Y-m-d H:i:s');
        if (!empty($data["sale_date"])) {
            $data["sale_date"] = Carbon::parse($data["sale_date"])->format('Y-m-d H:i:s');
        }
        if (!empty($data["actual_delivery_date"])) {
            $data["actual_delivery_date"] = Carbon::parse($data["actual_delivery_date"])->format('Y-m-d H:i:s');
        }

        if ($isAlreadyDelivered) {
            $data["status"] = "completed";
        }

        // 🔹 🔥 ГЛАВНЫЙ ФИКС: безопасная загрузка продукта
        $product = Product::find($data["product_id"]);

        if (!$product) {
            return response()->json(['message' => 'Продукт не найден'], 422);
        }

        $data["quantity"] = ($data["quantity"] ?? 0) == 0 ? 1 : $data["quantity"];
        $data["product_category_id"] = $product->product_category_id;
        $data["created_by_id"] = $botUser->id;
        $data["total_price"] = $data["total_price"] ?? 0;

        // 🔹 Назначение агента
        if ($botUser->role == RoleEnum::AGENT->value) {
            $data["agent_id"] = $data["agent_id"] ?? $botUser->agent?->id ?? null;
        }

        // 🔹 🔥 Автоматическое именование — с защитой от null
        if ($needAutomaticNaming) {
            $supplier = Supplier::find($data["supplier_id"]);

            $productName = $product->name ?? 'товара';
            $supplierName = $supplier?->name ?? 'поставщика';

            $data["title"] = "Доставка {$productName} от {$supplierName}";
            $data["description"] = "Товар {$productName}"
                . ", поставщик {$supplierName}"
                . ", тип оплаты " . (($data["payment_type"] ?? 0) == 0 ? "наличными" : "безналичный расчет")
                . ", кол-во {$data["quantity"]}ед."
                . ", цена {$data["total_price"]}руб. ";
        }

        $sale = Sale::create($data);

        // 🔹 Если агент не назначен — пробуем назначить от ботюзера
        if (is_null($sale->agent_id)) {
            $sale->agent_id = $botUser->agent?->id ?? null;
            $sale->save();
        }

        $saleInfo = $sale->toTelegramText($receiptIsLost);
        $userLink = $botUser->getUserTelegramLink();

        // 🔹 🔥 Обработка агента и ментора — с защитой от null
        if (!is_null($sale->agent_id)) {
            $agent = Agent::query()
                ->with(["user", "mentor"])
                ->find($sale->agent_id);

            if ($agent) {
                // 🔹 Начисление бонуса наставника
                if ($agent->in_learning && $agent->mentor) {
                    $mentorPercent = $agent->mentor->mentor_percent ?? 0;
                    $sale->mentor_award = ($sale->total_price ?? 0) * ($mentorPercent / 100);
                    $sale->save();

                    UserLog::log(
                        "Вам начислен бонус наставника <b>{$sale->mentor_award}</b> руб. ({$mentorPercent}%) по сделке #{$sale->id} (на сумму <b>{$sale->total_price}</b> руб.) за {$agent->name}",
                        $agent->mentor->id
                    );
                }

                // 🔹 Уведомление агенту
                if ($agent->user?->telegram_chat_id) {
                    UserLog::log(
                        "Вам назначена сделка:\n{$saleInfo}",
                        $agent->user->id
                    );
                }
            }
        }

        UserLog::logSuper(
            "#создание_сделки\n{$saleInfo}{$userLink}" .
            (!empty($sale->payment_document_name) ? "<p>Чек к сделке №{$sale->id} <a href='{$fileLink}' target='_blank'>Ссылка на чек</a></p>" : "")
        );

        return response()->json($sale, 201);
    }

    public function show($id)
    {
        $sale = Sale::findOrFail($id);
        return response()->json($sale);
    }

    public function getPaymentDocument(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $user = $request->botUser;

        $fileLinks = "";
        if (!empty($sale->payment_document_name)) {

            /* $slash = env('APP_DEBUG') ? "\\" : "/";
             $basePath = storage_path("app{$slash}uploads{$slash}");*/

            // строка → массив (один или несколько файлов)
            $files = str_contains($sale->payment_document_name, ',')
                ? explode(',', $sale->payment_document_name)
                : [$sale->payment_document_name];

            $total = count($files);
            $index = 1;

            foreach ($files as $filename) {
                $fileLinks .= "<p class='mb-2'><a href='" . env("APP_URL") . "/storage/app/uploads/$filename' target='_blank'>Ссылка на чек</a> </p>";
                $index++;
            }

            UserLog::log(
                "Чек к сделке №" . ($sale->id ?? '-') . ($total > 0 ? $fileLinks : ""),
                $user->id
            );


        } else {

            UserLog::log(
                "Чек к сделке №" . ($sale->id ?? '-') . " — не найден!",
                $user->id
            );
        }

        return response()->noContent();
    }

    public function confirmDeal(Request $request)
    {

        $request->validate([
            "id" => "required",
            "sale_date" => "required",
            "status" => "required",
            "total_price" => "required",
        ]);

        $botUser = $request->botUser ?? null;

        $sale = Sale::findOrFail($request->id);

        $priceIsChange = $sale->total_price != $request->total_price;

        $quantity = $request->quantity ?? 1;

        if ($quantity == 0) {
            $quantity = 1;
            $sale->quantity = $quantity;
        }

        if ($priceIsChange) {
            $supplier = Supplier::query()->where("id", $sale->supplier_id)->first();
            $product = Product::query()->where("id", $sale->product_id)->first();

            if (!is_null($product) && !is_null($supplier)) {
                $sale->title = "Доставка " . ($product->name ?? 'товара') . " от " . ($supplier->name ?? 'поставщика');
                $sale->description = "Товар " . ($product->name ?? 'товара')
                    . ", поставщик " . ($supplier->name ?? 'поставщика')
                    . ", тип оплаты " . ($sale->payment_type == 0 ? "наличными" : "безналичный расчет")
                    . ", кол-во $quantity ед."
                    . ", цена " . ($request->total_price ?? 0) . "руб. ";
            }

        }

        $sale->status = $request->status ?? 'completed';
        $sale->sale_date = Carbon::parse($request->sale_date);
        $sale->save();

        if (!is_null($sale->agent_id ?? null)) {
            $agent = Agent::query()
                ->with(["user", "mentor"])
                ->where("id", $sale->agent_id)
                ->first();

            if ($agent->in_learning) {
                $mentorPercent = $agent->mentor->mentor_percent ?? 0;
                $sale->mentor_award = ($sale->total_price ?? 0) * ($mentorPercent / 100);
                $sale->save();

                if ($priceIsChange) {

                    UserLog::log(
                        "Вам изменен бонус наставника <b> $sale->mentor_award </b> руб. ($mentorPercent %) по сделке #$sale->id (на сумму <b>$sale->total_price </b> руб.) за $agent->name",
                        $agent->mentor->id
                    );

                }
            }
        }

        $saleInfo = $sale->toTelegramText();

        UserLog::logSuper("#обновление_данных_сделки\n$saleInfo" . $botUser->getUserTelegramLink());

        return response()->json($sale);
    }

    public function confirmDealPayment(Request $request)
    {
        $request->validate([
            "id" => "required",
            "payment_type" => "required"
        ]);

        $botUser = $request->botUser ?? null;

        $receiptIsLost = ($request->receipt_is_lost ?? false) == "true";
        $sameSaleDeliveryDate = ($request->same_sale_delivery_date ?? false) == "true";
        $needAdditionalComment = ($request->need_additional_comment ?? false) == "true";

        $sale = Sale::findOrFail($request->id);

        $priceIsChange = $sale->total_price != $request->total_price;

        $price = ($request->total_price ?? 0) == 0 ? $sale->total_price : $request->total_price;

        $sale->total_price = $price;

        $quantity = $request->quantity ?? 1;

        if ($quantity == 0) {
            $quantity = 1;
            $sale->quantity = $quantity;
        }

        if (is_null($sale->agent_id)) {
            $sale->agent_id = $botUser->agent->id ?? null;
        }


        if ($priceIsChange) {

            $supplier = Supplier::query()->where("id", $sale->supplier_id)->first();
            $product = Product::query()->where("id", $sale->product_id)->first();

            if (!is_null($product) && !is_null($supplier)) {
                $sale->title = "Доставка " . ($product->name ?? 'товара') . " от " . ($supplier->name ?? 'поставщика');
                $sale->description = "Товар " . ($product->name ?? 'товара')
                    . ", поставщик " . ($supplier->name ?? 'поставщика')
                    . ", тип оплаты " . ($sale->payment_type == 0 ? "наличными" : "безналичный расчет")
                    . ", кол-во $quantity ед."
                    . ", цена " . ($price ?? 0) . "руб. ";
            }

        }

        if ($needAdditionalComment) {
            $comment = $request->additional_comment ?? '';
            $sale->description .= "\n<b>Комментарий:</b> <em>$comment</em>";
        }

        $hasFile = false;
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if (!$file->isValid()) {
                    continue;
                }

                $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('uploads', $filename);

                $filenames[] = $filename;
            }

            if (!empty($filenames)) {
                // сохраняем строку: file1.jpg,file2.png,file3.pdf
                $sale->payment_document_name = implode(',', $filenames);
                $hasFile = true;
            }
        }

        $sale->status = $request->status ?? 'completed';
        $sale->payment_type = $request->payment_type ?? 0;
        $sale->actual_delivery_date = Carbon::parse($request->actual_delivery_date);

        $sale->sale_date = $sameSaleDeliveryDate ?
            Carbon::parse($request->actual_delivery_date) :
            Carbon::parse($request->sale_date);

        $sale->save();

        if (!is_null($sale->agent_id ?? null)) {
            $agent = Agent::query()
                ->with(["user", "mentor"])
                ->where("id", $sale->agent_id)
                ->first();

            if ($agent->in_learning) {
                $mentorPercent = $agent->mentor->mentor_percent ?? 0;
                $sale->mentor_award = ($sale->total_price ?? 0) * ($mentorPercent / 100);
                $sale->save();

                if ($priceIsChange) {
                    UserLog::log(
                        "Вам изменен бонус наставника <b> $sale->mentor_award </b> руб. ($mentorPercent %) по сделке #$sale->id (на сумму <b>$sale->total_price </b> руб.) за $agent->name",
                        $agent->mentor->id
                    );
                }
            }
        }


        $fileLinks = "";
        if ($hasFile && !empty($sale->payment_document_name)) {

            /*  $slash = env('APP_DEBUG') ? "\\" : "/";
              $basePath = storage_path("app{$slash}uploads{$slash}");*/

            // превращаем строку в массив (1 или несколько файлов)
            $files = str_contains($sale->payment_document_name, ',')
                ? explode(',', $sale->payment_document_name)
                : [$sale->payment_document_name];

            $total = count($files);
            $index = 1;

            foreach ($files as $filename) {

                //  $filePath = $basePath . $filename;

                /*   if (!file_exists($filePath)) {
                       continue; // если файл вдруг не найден — просто пропускаем
                   }*/

                $fileLinks .= "<p><a href='" . env("APP_URL") . "/storage/app/uploads/$filename' target='_blank'>Ссылка на чек</a> </p>";
                /*\App\Facades\BotMethods::bot()->sendDocument(
                    env("TELEGRAM_ADMIN_CHANNEL"),
                    "Чек $index/$total к сделке №" . ($sale->id ?? '-'),
                    InputFile::create($filePath, $filename)
                );*/
                $index++;
                sleep(1);
            }
        }

        $saleInfo = $sale->toTelegramText($receiptIsLost);

        UserLog::logSuper(
            "#обновление_данных_сделки\n$saleInfo" . $botUser->getUserTelegramLink() . ($hasFile && !empty($sale->payment_document_name) ? "\nЧек к сделке №" . ($sale->id ?? '-') . "\n$fileLinks" : "")
        );

        return response()->json($sale);
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            "id" => "required",
            "payment_type" => "required"
        ]);

        $botUser = $request->botUser ?? null;

        $receiptIsLost = ($request->receipt_is_lost ?? false) == "true";

        $sale = Sale::findOrFail($request->id);

        $priceIsChange = $sale->total_price != $request->total_price;

        $hasFile = false;
        $fileLink = "";
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename);
            $sale->payment_document_name = $filename;

            $hasFile = true;

            $fileLink = env("APP_URL") . "/storage/app/$path";
        }

        $sale->status = 'pending';
        $sale->payment_type = $request->payment_type ?? 0;
        $sale->sale_date = Carbon::now();
        $sale->save();

        if (!is_null($sale->agent_id ?? null)) {
            $agent = Agent::query()
                ->with(["user", "mentor"])
                ->where("id", $sale->agent_id)
                ->first();

            if ($agent->in_learning) {
                $mentorPercent = $agent->mentor->mentor_percent ?? 0;
                $sale->mentor_award = ($sale->total_price ?? 0) * ($mentorPercent / 100);
                $sale->save();

                if ($priceIsChange) {

                    UserLog::log(
                        "Вам изменен бонус наставника <b> $sale->mentor_award </b> руб. ($mentorPercent %) по сделке #$sale->id (на сумму <b>$sale->total_price </b> руб.) за $agent->name",
                        $agent->mentor->id
                    );
                }
            }
        }

        $saleInfo = $sale->toTelegramText($receiptIsLost);

        UserLog::logSuper(
            "#обновление_данных_сделки\n$saleInfo" . $botUser->getUserTelegramLink() . ($hasFile ? "\nЧек к сделке №" . ($sale->id ?? '-') .  "<p>Чек к сделке №{$sale->id}<a href='{$fileLink}' target='_blank'>Ссылка на чек</a></p>" : "")

        );

        /* if ($hasFile) {
             $slash = env("APP_DEBUG") ? "\\" : "/";
             \App\Facades\BotMethods::bot()->sendDocument(
                 env("TELEGRAM_ADMIN_CHANNEL"),
                 "Чек к сделке №" . ($sale->id ?? '-'),
                 InputFile::create(storage_path("app" . $slash . "uploads" . $slash) . $sale->payment_document_name,
                     $sale->payment_document_name
                 )
             );

         }*/
        return response()->json($sale);

    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        $needAutomaticNaming = ($data["need_automatic_naming"] ?? false) == "true"
            || ($data["need_automatic_naming"] ?? false) === true;
        unset($data["need_automatic_naming"]);

        $botUser = $request->botUser ?? null;

        $data["total_price"] = $data["total_price"] ?? 0;

        if (($data["quantity"] ?? 0) == 0) {
            $data["quantity"] = 1;
        }

        $hasFile = false;
        $fileLink = "";
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename);
            $data["payment_document_name"] = $filename;
            $data["sale_date"] = Carbon::now();
            $hasFile = true;

            $fileLink = env("APP_URL") . "/storage/app/$path";
        }

        $sale = Sale::findOrFail($id);

        // 🔹 Автоматическое именование — с защитой от null
        if ($needAutomaticNaming) {
            $supplier = !empty($data["supplier_id"])
                ? Supplier::find($data["supplier_id"])
                : null;
            $product = !empty($sale->product_id)
                ? Product::find($sale->product_id)
                : null;

            $productName = $product?->name ?? 'товара';
            $supplierName = $supplier?->name ?? 'поставщика';

            $data["title"] = "Доставка {$productName} от {$supplierName}";
            $data["description"] = "Товар {$productName}"
                . ", поставщик {$supplierName}"
                . ", тип оплаты " . (($data["payment_type"] ?? 0) == 0 ? "наличными" : "безналичный расчет")
                . ", кол-во " . $data["quantity"] . "ед."
                . ", цена " . $data["total_price"] . "руб. ";
        }

        $data["due_date"] = Carbon::parse($data["due_date"] ?? $sale->due_date ?? Carbon::now());
        $data["mentor_award"] = 0;
        $data["payment_type"] = $data["payment_type"] ?? $sale->payment_type ?? 0;

        $priceIsChange = $sale->total_price != $data["total_price"];

        // 🔹 🔥 ГЛАВНЫЙ ФИКС: защита от null при поиске продукта
        $productId = $data["product_id"] ?? $sale->product_id ?? null;
        $product = $productId ? Product::find($productId) : null;

        // 🔹 Используем null-safe оператор
        if ($product && $product->id != $sale->product_id) {
            $data["product_category_id"] = $product->product_category_id ?? null;
        }

        // 🔹 Защита: если у ботюзера нет агента
        if (is_null($sale->agent_id) && $botUser) {
            $sale->agent_id = $botUser->agent?->id ?? null;
        }

        $sale->update($data);

        // 🔹 Защита: проверка всех связей перед использованием
        if (!is_null($sale->agent_id)) {
            $agent = Agent::query()
                ->with(["user", "mentor"])
                ->where("id", $sale->agent_id)
                ->first();

            if ($agent && $agent->in_learning && $agent->mentor) {
                $mentorPercent = $agent->mentor->mentor_percent ?? 0;
                $sale->mentor_award = ($sale->total_price ?? 0) * ($mentorPercent / 100);
                $sale->save();

                if ($priceIsChange) {
                    UserLog::log(
                        "Вам изменен бонус наставника <b>{$sale->mentor_award}</b> руб. ({$mentorPercent}%) по сделке #{$sale->id} (на сумму <b>{$sale->total_price}</b> руб.) за {$agent->name}",
                        $agent->mentor->id
                    );
                }
            }
        }

        // 🔹 Защита: botUser может быть null
        $telegramLink = $botUser ? $botUser->getUserTelegramLink() : '';
        $saleInfo = $sale->toTelegramText();

        UserLog::logSuper(
            "#обновление_данных_сделки\n{$saleInfo}{$telegramLink}" .
            ($hasFile ? "\nЧек к сделке №" . ($sale->id ?? '-') .  "<p>Чек к сделке №{$sale->id}<a href='{$fileLink}' target='_blank'>Ссылка на чек</a></p>" : "")
        );

        $sale->load(["product", "agent", "customer", "supplier", "creator", "category"]);
        return response()->json($sale);
    }

    public function destroy(Request $request, $id)
    {
        $sale = Sale::query()->find($id);

        $botUser = $request->botUser;

        if (is_null($sale))
            return response()->json(null, 404);

        $saleInfo = $sale->toTelegramText();

        $sale->delete();

        UserLog::logSuper(
            "#удаление_сделки\n$saleInfo" . $botUser->getUserTelegramLink()
        );

        return response()->json(null, 204);
    }


    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, "Пользователь не авторизован");

        $fileName = "export-sales-" . Carbon::now()->format("Y-m-d-H-i-s") . ".xlsx";

        $report = app(\App\Services\ExportService::class)->saveReport(
            $user,
            "Экспорт истории продаж",
            $fileName,
            new \App\Exports\SalesExport(),
            [],
            'sales_history'
        );

        return response()->json([
            'message' => 'Отчет успешно сформирован',
            'report' => $report
        ]);
    }
}
