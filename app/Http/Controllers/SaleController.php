<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Exports\ProductsExport;
use App\Exports\SalesExport;
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


        $sales = Sale::query()
            ->filter($request)
            ->sort($request)
            ->paginate($request->get('per_page', $request->size ?? 10));

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

        $data = $request->all();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename);
            $data["payment_document_name"] = $filename;
            $data["sale_date"] = is_null($date["due_date"] ?? null) ? Carbon::now() : Carbon::parse($data["due_date"]);
        }


        $needAutomaticNaming = $data["need_automatic_naming"] == "true";
        $isAlreadyDelivered = $data["is_already_delivered"] == "true";
        $receiptIsLost = $data["receipt_is_lost"] == "true";
        unset($data["need_automatic_naming"]);
        unset($data["receipt_is_lost"]);
        unset($data["is_already_delivered"]);
        unset($data["file"]);

        if ($isAlreadyDelivered) {
            $data["status"] = "completed";
            $data["sale_date"] = is_null($date["due_date"] ?? null) ? Carbon::now() : Carbon::parse($data["due_date"]);
            $data["actual_delivery_date"] = is_null($date["due_date"] ?? null) ? Carbon::now() : Carbon::parse($data["due_date"]);
        }

        $product = Product::query()->where("id", $data["product_id"])->first();

        if ($data["quantity"] == 0)
            $data["quantity"] = 1;

        $data["product_category_id"] = $product->product_category_id ?? null;
        $data["created_by_id"] = $botUser->id ?? null;

        if ($botUser->role == RoleEnum::AGENT->value)
            $data["agent_id"] = $botUser->agent->id ?? null;

        if ($needAutomaticNaming) {
            $supplier = Supplier::query()->where("id", $data["supplier_id"])->first();


            $data["title"] = "Доставка " . ($product->name ?? 'товара') . " от " . ($supplier->name ?? 'поставщика');
            $data["description"] = "Товар " . ($product->name ?? 'товара')
                . ", поставщик " . ($supplier->name ?? 'поставщика')
                . ", тип оплаты " . ($data["payment_type"] == 0 ? "наличными" : "безналичный расчет")
                . ", кол-во " . ($data["quantity"] ?? 0) . "ед."
                . ", цена " . ($data["total_price"] ?? 0) . "руб. ";
        }

        $sale = Sale::query()->create($data);

        if (is_null($sale->agent_id)) {
            $sale->agent_id = $botUser->agent->id ?? null;
            $sale->save();
        }

        $saleInfo = $sale->toTelegramText($receiptIsLost);

        $userLink = $botUser->getUserTelegramLink();

        if (!is_null($sale->agent_id ?? null)) {
            $agent = Agent::query()
                ->with(["user", "mentor"])
                ->where("id", $sale->agent_id)
                ->first();

            if ($agent->in_learning) {
                $mentorPercent = $agent->mentor->mentor_percent ?? 0;
                $sale->mentor_award = ($sale->total_price ?? 0) * ($mentorPercent / 100);
                $sale->save();

                \App\Facades\BotMethods::bot()->sendMessage(
                    $agent->mentor->telegram_chat_id,
                    "Вам начислен бонус наставника <b> $sale->mentor_award </b> руб. ($mentorPercent %) по сделке #$sale->id (на сумму <b>$sale->total_price </b> руб.) за $agent->name"
                );
                sleep(1);
            }

            if (!is_null($agent->user->telegram_chat_id ?? null)) {
                \App\Facades\BotMethods::bot()->sendMessage(
                    $agent->user->telegram_chat_id,
                    "Вам назначена сделка:\n$saleInfo"
                );
                sleep(1);
            }

        }


        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#создание_сделки\n$saleInfo" . $userLink
        );

        if (!is_null($sale->payment_document_name ?? null)) {

            $slash = env("APP_DEBUG") ? "\\" : "/";
            \App\Facades\BotMethods::bot()->sendDocument(
                env("TELEGRAM_ADMIN_CHANNEL"),
                "Чек к сделке №" . ($sale->id ?? '-'),
                InputFile::create(storage_path("app" . $slash . "uploads" . $slash) . $sale->payment_document_name,
                    $sale->payment_document_name
                )
            );
        }


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

        if (!empty($sale->payment_document_name)) {

            $slash = env('APP_DEBUG') ? "\\" : "/";
            $basePath = storage_path("app{$slash}uploads{$slash}");

            // строка → массив (один или несколько файлов)
            $files = str_contains($sale->payment_document_name, ',')
                ? explode(',', $sale->payment_document_name)
                : [$sale->payment_document_name];

            $total = count($files);
            $index = 1;

            foreach ($files as $filename) {

                $filePath = $basePath . $filename;

                if (!file_exists($filePath)) {
                    continue;
                }

                \App\Facades\BotMethods::bot()->sendDocument(
                    $user->telegram_chat_id,
                    "Чек $index/$total к сделке №" . ($sale->id ?? '-'),
                    InputFile::create($filePath, $filename)
                );

                $index++;
            }

        } else {
            \App\Facades\BotMethods::bot()->sendMessage(
                $user->telegram_chat_id,
                "Чек к сделке №" . ($sale->id ?? '-') . " — не найден!"
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
            $product = Supplier::query()->where("id", $sale->product_id)->first();

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
                    \App\Facades\BotMethods::bot()->sendMessage(
                        $agent->mentor->telegram_chat_id,
                        "Вам изменен бонус наставника <b> $sale->mentor_award </b> руб. ($mentorPercent %) по сделке #$sale->id (на сумму <b>$sale->total_price </b> руб.) за $agent->name"
                    );
                    sleep(1);
                }
            }
        }

        $saleInfo = $sale->toTelegramText();
        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#обновление_данных_сделки\n$saleInfo" . $botUser->getUserTelegramLink()
        );

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
            $product = Supplier::query()->where("id", $sale->product_id)->first();

            if (!is_null($product) && !is_null($supplier)) {
                $sale->title = "Доставка " . ($product->name ?? 'товара') . " от " . ($supplier->name ?? 'поставщика');
                $sale->description = "Товар " . ($product->name ?? 'товара')
                    . ", поставщик " . ($supplier->name ?? 'поставщика')
                    . ", тип оплаты " . ($sale->payment_type == 0 ? "наличными" : "безналичный расчет")
                    . ", кол-во $quantity ед."
                    . ", цена " . ($request->total_price ?? 0) . "руб. ";
            }

        }

        if ($needAdditionalComment)
        {
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
        $sale->sale_date = Carbon::parse($request->sale_date);

        $sale->actual_delivery_date = $sameSaleDeliveryDate ?
            Carbon::parse($request->sale_date) :
            Carbon::parse($request->actual_delivery_date);

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
                    \App\Facades\BotMethods::bot()->sendMessage(
                        $agent->mentor->telegram_chat_id,
                        "Вам изменен бонус наставника <b> $sale->mentor_award </b> руб. ($mentorPercent %) по сделке #$sale->id (на сумму <b>$sale->total_price </b> руб.) за $agent->name"
                    );
                    sleep(1);
                }
            }
        }

        $saleInfo = $sale->toTelegramText($receiptIsLost);
        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#обновление_данных_сделки\n$saleInfo" . $botUser->getUserTelegramLink()
        );

        if ($hasFile && !empty($sale->payment_document_name)) {

            $slash = env('APP_DEBUG') ? "\\" : "/";
            $basePath = storage_path("app{$slash}uploads{$slash}");

            // превращаем строку в массив (1 или несколько файлов)
            $files = str_contains($sale->payment_document_name, ',')
                ? explode(',', $sale->payment_document_name)
                : [$sale->payment_document_name];

            $total = count($files);
            $index = 1;

            foreach ($files as $filename) {

                $filePath = $basePath . $filename;

                if (!file_exists($filePath)) {
                    continue; // если файл вдруг не найден — просто пропускаем
                }

                \App\Facades\BotMethods::bot()->sendDocument(
                    env("TELEGRAM_ADMIN_CHANNEL"),
                    "Чек $index/$total к сделке №" . ($sale->id ?? '-'),
                    InputFile::create($filePath, $filename)
                );
                $index++;
                sleep(1);
            }
        }
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
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename);
            $sale->payment_document_name = $filename;

            $hasFile = true;
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
                    \App\Facades\BotMethods::bot()->sendMessage(
                        $agent->mentor->telegram_chat_id,
                        "Вам изменен бонус наставника <b> $sale->mentor_award </b> руб. ($mentorPercent %) по сделке #$sale->id (на сумму <b>$sale->total_price </b> руб.) за $agent->name"
                    );
                    sleep(1);
                }
            }
        }

        $saleInfo = $sale->toTelegramText($receiptIsLost);
        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#обновление_данных_сделки\n$saleInfo" . $botUser->getUserTelegramLink()
        );

        if ($hasFile) {
            $slash = env("APP_DEBUG") ? "\\" : "/";
            \App\Facades\BotMethods::bot()->sendDocument(
                env("TELEGRAM_ADMIN_CHANNEL"),
                "Чек к сделке №" . ($sale->id ?? '-'),
                InputFile::create(storage_path("app" . $slash . "uploads" . $slash) . $sale->payment_document_name,
                    $sale->payment_document_name
                )
            );

        }
        return response()->json($sale);

    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        $needAutomaticNaming = $data["need_automatic_naming"] == "true";
        unset($data["need_automatic_naming"]);

        $botUser = $request->botUser ?? null;

        if ($data["quantity"] == 0)
            $data["quantity"] = 1;

        $hasFile = false;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename);
            $data["payment_document_name"] = $filename;
            $data["sale_date"] = Carbon::now();
            $hasFile = true;
        }

        if ($needAutomaticNaming) {

            $supplier = Supplier::query()->where("id", $data["supplier_id"])->first();

            $data["title"] = "Доставка " . ($product->name ?? 'товара') . " от " . ($supplier->name ?? 'поставщика');
            $data["description"] = "Товар " . ($product->name ?? 'товара')
                . ", поставщик " . ($supplier->name ?? 'поставщика')
                . ", тип оплаты " . ($data["payment_type"] == 0 ? "наличными" : "безналичный расчет")
                . ", кол-во " . ($data["quantity"] ?? 0) . "ед."
                . ", цена " . ($data["total_price"] ?? 0) . "руб. ";
        }
        $sale = Sale::findOrFail($id);


        $data["due_date"] = Carbon::parse($data["due_date"] ?? $sale->due_date ?? Carbon::now());
        $data["mentor_award"] = 0;
        $data["payment_type"] = isset($data["payment_type"]) ? ($data["payment_type"] ?? 0) : $sale->payment_type;

        $priceIsChange = $sale->total_price != ($data["total_price"] ?? 0);

        $product = Product::query()->where("id", $data["product_id"] ?? $sale->product_id ?? null)->first();

        if ($product->id != $sale->product_id) {
            $data["product_category_id"] = $product->product_category_id ?? null;
        }

        if (is_null($sale->agent_id)) {
            $sale->agent_id = $botUser->agent->id ?? null;
        }

        $sale->update($data);

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
                    \App\Facades\BotMethods::bot()->sendMessage(
                        $agent->mentor->telegram_chat_id,
                        "Вам изменен бонус наставника <b> $sale->mentor_award </b> руб. ($mentorPercent %) по сделке #$sale->id (на сумму <b>$sale->total_price </b> руб.) за $agent->name"
                    );
                    sleep(1);
                }
            }
        }

        $saleInfo = $sale->toTelegramText();
        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#обновление_данных_сделки\n$saleInfo" . $botUser->getUserTelegramLink()
        );

        if ($hasFile) {
            $slash = env("APP_DEBUG") ? "\\" : "/";
            \App\Facades\BotMethods::bot()->sendDocument(
                env("TELEGRAM_ADMIN_CHANNEL"),
                "Чек к сделке №" . ($sale->id ?? '-'),
                InputFile::create(storage_path("app" . $slash . "uploads" . $slash) . $sale->payment_document_name,
                    $sale->payment_document_name
                )
            );
        }

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

        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
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
            throw new HttpException("Пользователь не авторизован", 403);

        $fileName = "export-sales-" . Carbon::now()->format("Y-m-d H-i-s") . ".xlsx";
        $data = Excel::raw(new \App\Exports\SalesExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id, "Экспорт истории продаж",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data, $fileName));
        return response()->noContent();
    }
}
