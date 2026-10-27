<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'description',
        'phone',
        'work_phone',
        'percent',
        'birthday',
        'email',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'birthday' => 'date',
        'percent' => 'float',
    ];

    protected $appends = ['total_percent'];

    // 🔴 УБРАЛИ $with = ['percentages']
    // protected $with = ['percentages'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function percentages(): MorphMany
    {
        return $this->morphMany(Percentage::class, 'percentable');
    }

    public function totalPercent(): Attribute
    {
        return Attribute::get(function () {
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
}
