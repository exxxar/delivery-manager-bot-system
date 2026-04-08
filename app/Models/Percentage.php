<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Percentage extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'user_id',
        'percentage',
        "is_active"
    ];

/*    protected $with = ['user'];*/

    protected $casts = [
      "is_active"=>"boolean"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
