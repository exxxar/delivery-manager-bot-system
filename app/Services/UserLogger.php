<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserLogger
{
    /**
     * Срок хранения логов (6 месяцев)
     */
    private const RETENTION_MONTHS = 6;

    /**
     * Записать лог для пользователя
     *
     * @param string $message Текст сообщения
     * @param int|null $userId ID пользователя (если null — берётся текущий)
     * @return UserLog|null
     */
    public function log(string $message, ?int $userId = null): ?UserLog
    {
        $userId = $userId ?? $this->getCurrentUserId();

        if (!$userId) {
            return null;
        }

        // Очищаем старые записи пользователя
        $this->cleanup($userId);

        // Создаём новую запись
        return UserLog::create([
            'user_id' => $userId,
            'message' => $message,
        ]);
    }

    /**
     * Получить логи пользователя с пагинацией
     */
    public function getLogs(?int $userId = null, int $perPage = 20, int $page = 1)
    {
        $userId = $userId ?? $this->getCurrentUserId();

        if (!$userId) {
            return null;
        }

        return UserLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Удалить логи пользователя старше 6 месяцев
     */
    public function cleanup(?int $userId = null): int
    {
        $userId = $userId ?? $this->getCurrentUserId();

        if (!$userId) {
            return 0;
        }

        $cutoffDate = Carbon::now()->subMonths(self::RETENTION_MONTHS);

        return UserLog::where('user_id', $userId)
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Массовая очистка всех старых логов (для cron/команды)
     */
    public function cleanupAll(): int
    {
        $cutoffDate = Carbon::now()->subMonths(self::RETENTION_MONTHS);

        return UserLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Получить ID текущего пользователя
     */
    private function getCurrentUserId(): ?int
    {
        // Сначала пробуем получить из auth (для web)
        if (Auth::check()) {
            return Auth::id();
        }

        // Затем пробуем из request (для API с tg.auth middleware)
        if (request()->has('botUser') && request()->botUser instanceof User) {
            return request()->botUser->id;
        }

        return null;
    }

    /**
     * Записать лог всем суперадминам
     *
     * @param string $message Текст сообщения
     * @param int|null $actorId ID пользователя, совершившего действие (для контекста)
     * @return array Массив созданных записей
     */
    public function logSuper(string $message, ?int $actorId = null): array
    {
        // Получаем всех суперадминов (role = 4)
        $superAdmins = User::where('role', 4)->get();

        if ($superAdmins->isEmpty()) {
            return [];
        }

        // Если указан актор, получаем его данные для сообщения
        $actorInfo = '';
        if ($actorId) {
            $actor = User::find($actorId);
            if ($actor) {
                $actorInfo = " (пользователь: {$actor->name}, ID: {$actor->id})";
            }
        } elseif ($this->getCurrentUserId()) {
            // Если актор не указан, но есть текущий пользователь
            $currentUserId = $this->getCurrentUserId();
            $actor = User::find($currentUserId);
            if ($actor) {
                $actorInfo = " (пользователь: {$actor->name}, ID: {$actor->id})";
            }
        }

        $fullMessage = $message . $actorInfo;
        $createdLogs = [];

        foreach ($superAdmins as $admin) {
            // Очищаем старые записи
            $this->cleanup($admin->id);

            // Создаём запись
            $log = UserLog::create([
                'user_id' => $admin->id,
                'message' => $fullMessage,
            ]);

            $createdLogs[] = $log;
        }

        return $createdLogs;
    }
}
