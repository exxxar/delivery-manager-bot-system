<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'mentor_id',
        'name',
        'in_learning',
        'is_test',
        'favorite_suppliers',
        'phone',
        'email',
        'region',
        'start_learning_date',
        'end_learning_date',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'mentor_id' => 'integer',
        'in_learning' => 'boolean',
        'is_test' => 'boolean',
        'favorite_suppliers' => 'array',
    ];

    protected $appends = ['user_info', 'registration_at', 'percent', 'total_percent'];

    // 🔴 УБРАЛИ $with = ['percentages'] — загружаем только по необходимости
    // protected $with = ['percentages'];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id', 'id');
    }

    public function percentages(): MorphMany
    {
        return $this->morphMany(Percentage::class, 'percentable');
    }

    public function legacyPercentages(): HasMany
    {
        return $this->hasMany(Percentage::class, 'agent_id', 'id');
    }

    public function totalPercent(): Attribute
    {
        return Attribute::get(function () {
            // Используем relationLoaded, чтобы не делать лишних запросов
            if ($this->relationLoaded('percentages')) {
                return $this->percentages
                    ->where('is_active', true)
                    ->sum('percentage');
            }

            return $this->percentages()
                ->where('is_active', true)
                ->sum('percentage');
        });
    }

    // 🔴 БЕЗОПАСНЫЕ appends — проверяем, загружен ли user
    public function getUserInfoAttribute()
    {
        if (!$this->relationLoaded('user')) {
            return '';
        }
        $user = $this->user;
        return $user ? $user->toHtmlText() : '';
    }

    public function getPercentAttribute()
    {
        if (!$this->relationLoaded('user')) {
            return 0;
        }
        $user = $this->user;
        return $user ? $user->percent : 0;
    }

    public function getRegistrationAtAttribute()
    {
        if (!$this->relationLoaded('user')) {
            return null;
        }
        $user = $this->user;
        return $user ? $user->registration_at : null;
    }
}
