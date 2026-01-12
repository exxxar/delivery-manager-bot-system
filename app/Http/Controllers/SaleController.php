<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\SalesExport;
use App\Http\Requests\SaleStoreRequest;
use App\Http\Requests\SaleUpdateRequest;
use App\Models\Agent;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
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
            ->paginate($request->get('per_page', 10));

        return response()->json($sales);
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
            $data["sale_date"] = Carbon::now();
        }


        $needAutomaticNaming = $data["need_automatic_naming"] == "true";
        $receiptIsLost = $data["receipt_is_lost"] == "true";
        unset($data["need_automatic_naming"]);

        $product = Product::query()->where("id", $data["product_id"])->first();

        $data["product_category_id"] = $product->product_category_id ?? null;
        $data["created_by_id"] = $botUser->id ?? null;

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

        $saleInfo = $sale->toTelegramText($receiptIsLost);

        if (!is_null($sale->agent_id ?? null)) {
            $agent = Agent::query()
                ->with(["user"])
                ->where("id", $sale->agent_id)
                ->first();

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
            "#создание_сделки\n$saleInfo"
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

        if (!is_null($sale->payment_document_name ?? null)) {
            $slash = env("APP_DEBUG") ? "\\" : "/";
            \App\Facades\BotMethods::bot()->sendDocument(
                $user->telegram_chat_id,
                "Чек к сделке №" . ($sale->id ?? '-'),
                InputFile::create(storage_path("app" . $slash . "uploads" . $slash) . $sale->payment_document_name,
                    $sale->payment_document_name
                )
            );

        } else {
            \App\Facades\BotMethods::bot()->sendMessage(
                $user->telegram_chat_id,
                "Чек к сделке №" . ($sale->id ?? '-') . " - не найден!"
            );
        }
        return response()->noContent();
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            "id" => "required",
            "payment_type" => "required"
        ]);

        $receiptIsLost = ($request->receipt_is_lost ?? false) == "true";


        $sale = Sale::findOrFail($request->id);

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

        $saleInfo = $sale->toTelegramText($receiptIsLost);
        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#обновление_данных_сделки\n$saleInfo"
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

        $hasFile = false;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename);
            $data["payment_document_name"] = $filename;
            $data["sale_date"] = Carbon::now();
            $hasFile = true;
        }

        $sale = Sale::findOrFail($id);

        $product = Product::query()->where("id", $data["product_id"] ?? $sale->product_id ?? null)->first();

        if ($product->id != $sale->product_id) {
            $data["product_category_id"] = $product->product_category_id ?? null;
        }

        $sale->update($data);

        $saleInfo = $sale->toTelegramText();
        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#обновление_данных_сделки\n$saleInfo"
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

    public function destroy($id)
    {
        Sale::destroy($id);
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
