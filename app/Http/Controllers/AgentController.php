<?php

namespace App\Http\Controllers;

use App\Exports\AgentsExport;
use App\Http\Requests\AgentStoreRequest;
use App\Http\Requests\AgentUpdateRequest;
use App\Models\Agent;
use App\Models\Sale;
use Carbon\Carbon;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $size = $request->size ?? 10;
        return response()->json(Agent::query()->paginate($size));
    }


    public function selfSales(Request $request)
    {

        $botUser = $request->botUser;

        $agent = Agent::query()
            ->where("user_id", $botUser->id)
            ->first();

        if (is_null($agent))
            throw new HttpException(404, "Агент не найден");


        $query = Sale::query()
            ->where(function ($q) use ($botUser, $agent) {
                return $q->where("agent_id", $agent->id)
                    ->orWhere("created_by_id", $botUser->id);
            });

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
                'id', 'title', 'description', 'status', 'due_date', 'sale_date',
                'quantity', 'total_price', 'agent_id', 'customer_id', 'supplier_id', 'product_id'
            ]) && in_array($sortDirection, ['asc', 'desc'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // 🔹 Пагинация
        $perPage = $request->get('per_page', 10);
        $sales = $query->paginate($perPage);

        return response()->json($sales);


    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => "required",
            'phone' => "required",
            'email' => "required",
            'region' => "required"
        ]);

        $botUser = $request->botUser;

        $data = $request->all();
        $data["user_id"] = $botUser->id;

        $agent = Agent::query()
            ->create($data);
        return response()->json($agent, 201);
    }

    public function show($id)
    {
        $agent = Agent::findOrFail($id);
        return response()->json($agent);
    }

    public function update(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);
        $agent->update($request->all());
        return response()->json($agent);
    }

    public function destroy($id)
    {
        Agent::destroy($id);
        return response()->json(null, 204);
    }

    public function downloadSelfReport()
    {

    }

    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new HttpException("Пользователь не авторизован", 403);

        $fileName = "export-agent-" . Carbon::now()->format("Y-m-d H-i-s") . ".xlsx";
        $data = Excel::raw(new \App\Exports\AgentsExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id, "Экспорт списка торговых представителей",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data, $fileName));
        return response()->noContent();
    }
}
