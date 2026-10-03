<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AdminInviteToken extends Model
{
    protected $fillable = [
        'token',
        'created_by_id',
        'used_by_id',
        'used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_id');
    }

    /**
     * Сгенерировать новый активный токен
     */
    public static function generate(int $createdById, int $ttlHours = 168): self
    {
        // Деактивируем все предыдущие токены этого создателя
        self::where('created_by_id', $createdById)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return self::create([
            'token' => Str::random(64),
            'created_by_id' => $createdById,
            'expires_at' => now()->addHours($ttlHours),
            'is_active' => true,
        ]);
    }

    /**
     * Получить актуальный активный токен
     */
    public static function getCurrentActive(): ?self
    {
        return self::where('is_active', true)
            ->whereNull('used_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();
    }

    /**
     * Использован ли токен
     */
    public function isUsed(): bool
    {
        return !is_null($this->used_at);
    }

    /**
     * Истёк ли срок действия
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Валиден ли токен для использования
     */
    public function isValid(): bool
    {
        return $this->is_active && !$this->isUsed() && !$this->isExpired();
    }

    /**
     * Пометить как использованный
     */
    public function markAsUsed(int $userId): void
    {
        $this->update([
            'used_by_id' => $userId,
            'used_at' => now(),
            'is_active' => false,
        ]);
    }

    /**
     * Полный URL для регистрации
     */
    public function getRegistrationUrl(): string
    {
        return url("/admin/register/{$this->token}");
    }
}
