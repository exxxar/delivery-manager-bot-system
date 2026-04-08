<?php

namespace App\Http\Controllers;

use App\Models\Percentage;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PercentageController extends Controller
{
   public function list(Request $request) {
        $request->validate([
            "agent_id"=>"required"
        ]);

        return Percentage::query()
            ->with(["user"])
            ->where("agent_id", $request->agent_id)
            ->orderBy("percentage", "desc")
            ->get();
   }

    public function remove(Request $request) {
        $data = $request->validate([
            'id' => 'nullable|exists:percentages,id',
            'agent_id' => 'required|exists:agents,id',
        ]);

        $percentage = Percentage::query()
            ->where("agent_id", $request->agent_id)
            ->where("id", $request->id)
            ->first();

        if (is_null($percentage))
            throw new HttpException(404, "Не найдено");

        $percentage->delete();

        return response()->noContent();
    }

    public function store(Request $request){
        $data = $request->validate([
            'id' => 'nullable|exists:percentages,id',
            'agent_id' => 'required|exists:agents,id',
            'user_id' => 'required|exists:users,id',
            'percentage' => 'required|numeric',
            'is_active' => 'boolean',
        ]);

        if (!empty($data['id'])) {
            // обновление
            $percentage = Percentage::find($data['id']);
            $percentage->update($data);
        } else {
            // создание
            $percentage = Percentage::create($data);
        }

        return response()->json($percentage);
    }
}
