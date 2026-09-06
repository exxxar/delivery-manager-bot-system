<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{

    /**
     * Время жизни токена (в часах)
     */
    protected int $ttlHours = 24;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Получаем токен из заголовка
        $key = $request->header('X-Idempotency-Key');

        // Если токена нет — пропускаем как обычный запрос
        if (empty($key)) {
            return $next($request);
        }

        // Валидируем формат (защита от SQL-инъекций и мусора)
        if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $key)) {
            return response()->json([
                'message' => 'Некорректный формат токена идемпотентности'
            ], 422);
        }

        $endpoint = $request->method() . ':' . $request->path();

        // 🔹 Проверяем: был ли уже такой запрос?
        $existing = IdempotencyKey::query()
            ->where('key', $key)
            ->where('endpoint', $endpoint)
            ->where('user_id', $request->user()?->id)
            ->active()
            ->first();

        if ($existing) {
            Log::info("Идемпотентный запрос: ключ {$key} уже обработан");

            // Возвращаем сохранённый ответ без повторного выполнения
            return response()->json(
                json_decode($existing->response_body, true) ?? [],
                $existing->status_code
            )->header('X-Idempotency-Replayed', 'true');
        }

        // 🔹 Блокируем повторные запросы на время обработки
        $lockKey = "idempotency_lock:{$key}";
        $lockAcquired = cache()->add($lockKey, true, 30); // 30 секунд блокировка

        if (!$lockAcquired) {
            return response()->json([
                'message' => 'Запрос уже обрабатывается, подождите...'
            ], 429);
        }

        // Выполняем оригинальный запрос
        $response = $next($request);

        try {
            // Сохраняем результат
            IdempotencyKey::query()
                ->create([
                'key' => $key,
                'user_id' => $request->user()?->id,
                'endpoint' => $endpoint,
                'status_code' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
                'expires_at' => now()->addHours($this->ttlHours),
            ]);
        } catch (\Throwable $e) {
            Log::error("Ошибка сохранения идемпотентного ключа: {$e->getMessage()}");
        } finally {
            // Освобождаем блокировку
            cache()->forget($lockKey);
        }

        return $response;
    }
}
