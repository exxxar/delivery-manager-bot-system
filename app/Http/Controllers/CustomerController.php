<?php

namespace App\Http\Controllers;

use App\Exports\AgentsExport;
use App\Exports\CustomersExport;
use App\Http\Requests\CustomerStoreRequest;
use App\Http\Requests\CustomerUpdateRequest;
use App\Models\Customer;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;


class CustomerController extends Controller
{
    /**
     * Получить список всех клиентов
     */
    public function index(Request $request)
    {
        $size = $request->size ?? 20;
        return response()->json(Customer::query()->paginate($size));
    }

    /**
     * Получить конкретного клиента
     */
    public function show($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json($customer);
    }

    /**
     * Создать нового клиента
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|unique:customers,email',
        ]);

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    /**
     * Обновить клиента
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'company_name' => 'string|max:255',
            'address' => 'string|max:255',
            'phone' => 'string|max:50',
            'email' => 'email|unique:customers,email,' . $customer->id,
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    /**
     * Удалить клиента
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

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

        $fileName = "export-customer-".Carbon::now()->format("Y-m-d H-i-s").".xlsx";
        $data = Excel::raw(new \App\Exports\CustomersExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id,"Экспорт списка клиентов",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data,$fileName));
        return response()->noContent();
    }
}
