<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Percentage;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PercentageController extends Controller
{

    public function index(Request $request)
    {
        $request->validate([
            'type' => 'required|in:agent,supplier',
            'id' => 'required|integer',
        ]);

        $modelClass = $request->type === 'agent' ? Agent::class : Supplier::class;
        $owner = $modelClass::findOrFail($request->id);

        return response()->json([
            'owner_type' => $request->type,
            'owner_id' => $owner->id,
            'owner_name' => $owner->name,
            'percentages' => $owner->percentages()->with('user')->get(),
            'total_percent' => $owner->total_percent,
        ]);
    }


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

    /*public function remove(Request $request) {
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
    }*/

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:agent,supplier',
            'owner_id' => 'required|integer',
            'user_id' => 'required|integer|exists:users,id',
            'percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $modelClass = $validated['type'] === 'agent' ? Agent::class : Supplier::class;
        $owner = $modelClass::findOrFail($validated['owner_id']);

        $percentage = $owner->percentages()->create([
            'user_id' => $validated['user_id'],
            'percentage' => $validated['percentage'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($percentage->load('user'), 201);
    }

    /**
     * Обновить процент
     */
    public function update(Request $request, Percentage $percentage)
    {
        $validated = $request->validate([
            'percentage' => 'numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $percentage->update($validated);

        return response()->json($percentage->fresh()->load('user'));
    }

    public function destroy(Percentage $percentage)
    {
        $percentage->delete();
        return response()->json(null, 204);
    }

}
