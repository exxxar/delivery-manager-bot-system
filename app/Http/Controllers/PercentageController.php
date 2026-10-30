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

        return $direction === 'incoming'
            ? $this->incomingIndex($type, $ownerId)
            : $this->outgoingIndex($type, $ownerId);
    }

    /**
     * Исходящие: выбранный владелец является плательщиком.
     */
    private function outgoingIndex(string $type, int $ownerId)
    {
        $owner = $this->resolveOwner($type, $ownerId);

        $percentages = Percentage::query()
            ->with(['user', 'percentable'])
            ->where('percentable_type', $owner->getMorphClass())
            ->where('percentable_id', $owner->getKey())
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'direction' => 'outgoing',
            'percentages' => $percentages,
        ]);
    }

    /**
     * Входящие: выбранный владелец является получателем.
     * Для Agent/Supplier получатель определяется через связанный user_id.
     */
    private function incomingIndex(string $type, int $ownerId)
    {
        $recipientUser = $this->resolveRecipientUser($type, $ownerId);

        $percentages = Percentage::query()
            ->with(['user', 'percentable'])
            ->where('user_id', $recipientUser->id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'direction' => 'incoming',
            'recipient_user_id' => $recipientUser->id,
            'percentages' => $percentages,
        ]);
    }

    public function store(Request $request)
    {
        $actor = $request->botUser ?? $request->user();

        abort_unless($actor, 401, 'Необходимо авторизоваться.');
        abort_unless((int) ($actor->role ?? 0) >= 3, 403, 'Недостаточно прав для управления процентами.');

        $data = $request->validate([
            'type' => ['required', 'in:agent,supplier,user,admin'],
            'owner_id' => ['required', 'integer', 'min:1'],
            'direction' => ['required', 'in:outgoing,incoming'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'payer_user_id' => ['nullable', 'integer', 'min:1'],
            'percentage' => ['required', 'numeric', 'gt:0', 'lte:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $type = $data['type'] === 'admin' ? 'user' : $data['type'];
        $isActive = $data['is_active'] ?? true;

        return $data['direction'] === 'incoming'
            ? $this->storeIncomingPercentage($data, $type, $isActive)
            : $this->storeOutgoingPercentage($data, $type, $isActive);
    }

    /**
     * Входящее правило: выбранный User-плательщик -> текущий владелец-получатель.
     */
    private function storeIncomingPercentage(array $data, string $type, bool $isActive)
    {
        return DB::transaction(function () use ($data, $type, $isActive) {
            $recipient = $this->resolveRecipientUser($type, (int) $data['owner_id']);
            $payer = User::query()->findOrFail((int) ($data['payer_user_id'] ?? 0));

            abort_unless(
                (int) ($recipient->role ?? 0) >= 1,
                422,
                'Получателем входящего процента должен быть администратор.'
            );

            if ($payer->is($recipient)) {
                throw ValidationException::withMessages([
                    'payer_user_id' => ['Плательщик и получатель не могут совпадать.'],
                ]);
            }

            $percentage = Percentage::query()->firstOrNew([
                'percentable_type' => $payer->getMorphClass(),
                'percentable_id' => $payer->getKey(),
                'user_id' => $recipient->getKey(),
            ]);

            $percentage->percentage = $data['percentage'];
            $percentage->is_active = $isActive;
            $percentage->save();

            return response()->json(
                $percentage->fresh()->load(['user', 'percentable']),
                $percentage->wasRecentlyCreated ? 201 : 200
            );
        });
    }

    /**
     * Исходящее правило: текущий morph-владелец -> выбранный User-получатель.
     */
    private function storeOutgoingPercentage(array $data, string $type, bool $isActive)
    {
        return DB::transaction(function () use ($data, $type, $isActive) {
            $owner = $this->resolveOwner($type, (int) $data['owner_id']);
            $recipient = User::query()->findOrFail((int) ($data['user_id'] ?? 0));

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

            return response()->json(
                $percentage->fresh()->load(['user', 'percentable']),
                $percentage->wasRecentlyCreated ? 201 : 200
            );
        });
    }

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

    /**
     * Получатель процента всегда User.
     * У Agent и Supplier его User находится через user_id.
     */
    private function resolveRecipientUser(string $type, int $ownerId): User
    {
        if ($type === 'user') {
            return User::query()->findOrFail($ownerId);
        }

        $owner = $this->resolveOwner($type, $ownerId);

        return User::query()->findOrFail((int) $owner->user_id);
    }
}
