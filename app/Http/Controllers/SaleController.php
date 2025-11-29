<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Exports\SalesExport;
use App\Http\Requests\SaleStoreRequest;
use App\Http\Requests\SaleUpdateRequest;
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

        $query = Sale::query();

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
        if (isset($request->agent_id)) {
            $query->where('agent_id', $request->agent_id);
        }
        if (isset($request->customer_id)) {
            $query->where('customer_id', $request->customer_id);
        }
        if (isset($request->supplier_id)) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if (isset($request->created_by_id)) {
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
        $sortDirection = $request->get('sort_direction', 'asc');
        if (in_array($sortField, [
                'id','title','description','status','due_date','sale_date',
                'quantity','total_price','agent_id','customer_id','supplier_id','product_id'
            ]) && in_array($sortDirection, ['asc','desc'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // 🔹 Пагинация
        $perPage = $request->get('per_page', 10);
        $sales = $query->paginate($perPage);

        return response()->json($sales);
    }

    public function store(Request $request)
    {
        $sale = Sale::create($request->all());
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

        $fileName = "export-sales-".Carbon::now()->format("Y-m-d H-i-s").".xlsx";
        $data = Excel::raw(new \App\Exports\SalesExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id,"Экспорт истории продаж",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data,$fileName));
        return response()->noContent();
    }
}
