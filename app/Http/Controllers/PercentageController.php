<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Percentage;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PercentageController extends Controller
{
    /**
     * GET /percentages?type=agent|supplier|user&id=123&direction=outgoing|incoming
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:agent,supplier,user,admin'],
            'id' => ['required', 'integer', 'min:1'],
            'direction' => ['nullable', 'in:outgoing,incoming'],
        ]);

        $type = $data['type'] === 'admin' ? 'user' : $data['type'];
        $ownerId = (int) $data['id'];
        $direction = $data['direction'] ?? 'outgoing';

        $query = Percentage::query()->with(['user', 'percentable']);

        if ($type === 'user') {
            User::query()->findOrFail($ownerId);

            if ($direction === 'incoming') {
                // Получатель — выбранный администратор; плательщик хранится в percentable.
                $query->where('user_id', $ownerId)
                    ->where('percentable_type', (new User())->getMorphClass());
            } else {
                // Плательщик — выбранный пользователь; получатель хранится в user_id.
                $query->where('percentable_type', (new User())->getMorphClass())
                    ->where('percentable_id', $ownerId);
            }
        } else {
            $owner = $this->resolveOwner($type, $ownerId);
            $query->where('percentable_type', $owner->getMorphClass())
                ->where('percentable_id', $owner->getKey());
        }

        return response()->json([
            'percentages' => $query->orderByDesc('id')->get(),
        ]);
    }

    /**
     * POST /percentages
     *
     * Outgoing user rule:
     * { type:user, owner_id: payerUserId, direction:outgoing, user_id:recipientUserId, percentage }
     *
     * Incoming user rule:
     * { type:user, owner_id: payerUserId, direction:incoming, percentage }
     * The receiving admin is resolved from recipient_admin_id / owner_admin_id, or actor.
     */
    public function store(Request $request)
    {
        $actor = $request->botUser ?? $request->user();
        abort_unless($actor, 401, 'Необходимо авторизоваться.');
        abort_unless((int) ($actor->role ?? 0) >= 3, 403, 'Недостаточно прав для управления процентами.');

        $data = $request->validate([
            'type' => ['required', 'in:agent,supplier,user,admin'],
            'owner_id' => ['required', 'integer', 'min:1'],
            'direction' => ['nullable', 'in:outgoing,incoming'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'recipient_admin_id' => ['nullable', 'integer', 'min:1'],
            'owner_admin_id' => ['nullable', 'integer', 'min:1'],
            'percentage' => ['required', 'numeric', 'gt:0', 'lte:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $type = $data['type'] === 'admin' ? 'user' : $data['type'];
        $direction = $data['direction'] ?? 'outgoing';
        $isActive = $data['is_active'] ?? true;

        return DB::transaction(function () use ($data, $type, $direction, $isActive, $actor) {
            if ($type === 'user' && $direction === 'incoming') {
                // Для обратного сценария owner_id — выбранный плательщик.
                $payer = User::query()->findOrFail((int) $data['owner_id']);
                $recipientId = (int) ($data['recipient_admin_id'] ?? $data['owner_admin_id'] ?? $actor->id);
                $recipient = User::query()->findOrFail($recipientId);

                // Не позволяем создавать входящие начисления на обычного пользователя.
                abort_unless((int) ($recipient->role ?? 0) >= 3, 422, 'Получателем входящего процента должен быть администратор.');

                $percentage = Percentage::query()->firstOrNew([
                    'percentable_type' => $payer->getMorphClass(),
                    'percentable_id' => $payer->getKey(),
                    'user_id' => $recipient->getKey(),
                ]);
                $percentage->percentage = $data['percentage'];
                $percentage->is_active = $isActive;
                $percentage->save();
            } else {
                $owner = $this->resolveOwner($type, (int) $data['owner_id']);
                $recipientId = (int) ($data['user_id'] ?? 0);
                $recipient = User::query()->findOrFail($recipientId);

                if ($type === 'user' && $recipient->is($owner)) {
                    throw ValidationException::withMessages([
                        'user_id' => ['Плательщик и получатель не могут совпадать.'],
                    ]);
                }

                $percentage = Percentage::query()->firstOrNew([
                    'percentable_type' => $owner->getMorphClass(),
                    'percentable_id' => $owner->getKey(),
                    'user_id' => $recipient->getKey(),
                ]);
                $percentage->percentage = $data['percentage'];
                $percentage->is_active = $isActive;
                $percentage->save();
            }

            return response()->json(
                $percentage->fresh()->load(['user', 'percentable']),
                $percentage->wasRecentlyCreated ? 201 : 200
            );
        });
    }

    /**
     * PUT /percentages/{id} — currently used by the frontend to toggle is_active.
     */
    public function update(Request $request, int $id)
    {
        $actor = $request->botUser ?? $request->user();
        abort_unless($actor, 401, 'Необходимо авторизоваться.');
        abort_unless((int) ($actor->role ?? 0) >= 3, 403, 'Недостаточно прав для управления процентами.');

        $data = $request->validate([
            'percentage' => ['sometimes', 'numeric', 'gt:0', 'lte:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $percentage = Percentage::query()->findOrFail($id);
        $percentage->fill($data);
        $percentage->save();

        return response()->json($percentage->fresh()->load(['user', 'percentable']));
    }

    public function destroy(Request $request, int $id)
    {
        $actor = $request->botUser ?? $request->user();
        abort_unless($actor, 401, 'Необходимо авторизоваться.');
        abort_unless((int) ($actor->role ?? 0) >= 3, 403, 'Недостаточно прав для управления процентами.');

        Percentage::query()->findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    private function resolveOwner(string $type, int $id)
    {
        return match ($type) {
            'agent' => Agent::query()->findOrFail($id),
            'supplier' => Supplier::query()->findOrFail($id),
            'user' => User::query()->findOrFail($id),
            default => abort(422, 'Неизвестный тип владельца.'),
        };
    }
}
