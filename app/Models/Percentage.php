<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Percentage extends Model
{
    use HasFactory;

    protected $fillable = [
        'percentable_type',
        'percentable_id',
        'agent_id',      // оставляем для обратной совместимости
        'user_id',
        'percentage',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'percentage' => 'float',
    ];

    /**
     * 🔹 Полиморфное отношение: владелец процента (Agent или Supplier)
     */
    public function percentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Пользователь, которому назначен процент (менеджер/агент, получающий %)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Legacy: отношение к агенту (для обратной совместимости)
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Scope: только активные проценты
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: для конкретного типа владельца
     */
    public function scopeForType($query, string $type)
    {
        return $query->where('percentable_type', $type);
    }
}
