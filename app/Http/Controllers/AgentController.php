<?php

namespace App\Http\Controllers;

use App\Exports\AgentsExport;
use App\Http\Requests\AgentStoreRequest;
use App\Http\Requests\AgentUpdateRequest;
use App\Models\Agent;
use Carbon\Carbon;
use HttpException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $size = $request->size ?? 10;
        return response()->json(Agent::query()->paginate($size));
    }

    public function store(Request $request)
    {
        $agent = Agent::create($request->all());
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

    public function downloadSelfReport(){

    }

    /**
     * @throws HttpException
     */
    public function export(Request $request)
    {
        $user = $request->botUser ?? null;

        if (is_null($user))
            throw new HttpException("Пользователь не авторизован", 403);

        $fileName = "export-agent-".Carbon::now()->format("Y-m-d H-i-s").".xlsx";
        $data = Excel::raw(new \App\Exports\AgentsExport(), \Maatwebsite\Excel\Excel::XLSX);
        \App\Facades\BotMethods::bot()
            ->sendDocument($user->telegram_chat_id,"Экспорт списка торговых представителей",
                \Telegram\Bot\FileUpload\InputFile::createFromContents($data,$fileName));
        return response()->noContent();
    }
}
