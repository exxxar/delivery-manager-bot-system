<?php

namespace App\Http\Controllers;

use App\Exports\SalesExport;
use App\Exports\SuppliersExport;
use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
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
        $size = $request->size ?? 10;
        return response()->json(Supplier::query()->paginate($size));
    }

    public function store(Request $request)
    {
        $supplier = Supplier::create($request->all());
        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());
        return response()->json($supplier);
    }

    public function destroy($id)
    {
        Supplier::destroy($id);
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
        $data = Excel::raw(new \App\Exports\SuppliersExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id,"Экспорт списка поставщиков",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data,$fileName));
        return response()->noContent();
    }
}
