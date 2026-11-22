<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleCommentController extends Controller
{
    /**
     * Повторяет базовое правило видимости сделок:
     * SUPERADMIN видит все; остальные — созданные ими или назначенные их агенту.
     * Если scopeFilter в проекте содержит дополнительные правила, синхронизируйте их здесь.
     */
    private function findAccessibleSale(Request $request, int $id): Sale
    {
        $user = $request->botUser;
        abort_unless($user, 401, 'Пользователь не авторизован');

        $query = Sale::query()->whereKey($id);

        if ($user->role < RoleEnum::SUPERADMIN->value) {
            $agentId = $user->agent?->id;

            $query->where(function ($q) use ($user, $agentId) {
                $q->where('created_by_id', $user->id);

                if ($agentId) {
                    $q->orWhere('agent_id', $agentId);
                }
            });
        }

        return $query->firstOrFail();
    }

    public function index(Request $request, int $saleId)
    {
        $sale = $this->findAccessibleSale($request, $saleId);

        $comments = $sale->comments()
            ->with('user:id,name')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'comments' => $comments,
            'count' => $comments->count(),
        ]);
    }

    public function store(Request $request, int $saleId)
    {
        $sale = $this->findAccessibleSale($request, $saleId);
        $user = $request->botUser;

        $validated = $request->validate([
            'comment' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        $comment = $sale->comments()->create([
            'user_id' => $user->id,
            'comment' => trim($validated['comment']),
        ]);

        $comment->load('user:id,name');

        return response()->json([
            'message' => 'Комментарий добавлен',
            'comment' => $comment,
            'count' => $sale->comments()->count(),
        ], 201);
    }
}
