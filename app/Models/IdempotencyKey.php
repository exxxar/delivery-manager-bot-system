<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'user_id',
        'endpoint',
        'status_code',
        'response_body',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Актуальна ли запись
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
