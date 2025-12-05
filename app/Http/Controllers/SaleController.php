<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\SalesExport;
use App\Http\Requests\SaleStoreRequest;
use App\Http\Requests\SaleUpdateRequest;
use App\Models\Agent;
use App\Models\Sale;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $botUser = $request->botUser;


        $query = Sale::query();

        if (isset($request->number)) {
            $query->where('id', $request->number);
        }
        // 🔹 Фильтры по тексту и статусу
        if (isset($request->title)) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if (isset($request->description)) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }

        // 🔹 Фильтр по типу даты и периоду
        if ($request->date_type && ($request->date_from || $request->date_to)) {
            $query->whereBetween($request->date_type, [
                    $request->date_from ?? '1900-01-01',
                    $request->date_to ?? now()->toDateString()
            ]);
        }

        // 🔹 Фильтры по связям
        if (isset($request->agent_id) && $botUser->role>=3) {
            $query->where('agent_id', $request->agent_id);
        }
        else {
            $agent = Agent::query()
                ->where("user_id", $botUser->id)
                ->first();

            $query
                ->where(function ($q) use ($botUser, $agent) {
                return $q->where("agent_id", $agent->id)
                    ->orWhere("created_by_id", $botUser->id);
            });
        }
        if (isset($request->customer_id)) {
            $query->where('customer_id', $request->customer_id);
        }
        if (isset($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if (isset($request->created_by_id) && $botUser->role>=3 ) {
            $query->where('created_by_id', $request->created_by_id);
        }

        // 🔹 Фильтры по количеству и цене
        if (isset($request->quantity)) {
            $query->where('quantity', $request->quantity);
        }
        if (isset($request->total_price)) {
            $query->where('total_price', $request->total_price);
        }


        // 🔹 Сортировка
        $sortField = $request->get('sort_field', 'id');
        $sortDirection = $request->get('sort_direction', 'desc');
        if (in_array($sortField, [
                'id', 'title', 'description', 'status', 'due_date', 'sale_date',
                'quantity', 'total_price', 'agent_id', 'customer_id', 'supplier_id', 'product_id'
            ]) && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // 🔹 Пагинация
        $perPage = $request->get('per_page', $request->size ?? 10);
        $sales = $query->paginate($perPage);

        return response()->json($sales);
    }

    public function store(Request $request)
    {

        $sale = Sale::query()->create($request->all());

        $saleInfo = $sale->toTelegramText();

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

        return response()->json($sale, 201);
    }

    public function show($id)
    {
        $sale = Sale::findOrFail($id);
        return response()->json($sale);
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $sale->update($request->all());

        $saleInfo = $sale->toTelegramText();
        \App\Facades\BotMethods::bot()->sendMessage(
            env("TELEGRAM_ADMIN_CHANNEL"),
            "#обновление_данных_сделки\n$saleInfo"
        );
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
