<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'mentor_id' => 'integer',
        'in_learning' => 'boolean',
        'is_test' => 'boolean',
        'favorite_suppliers' => 'array'
    ];

    protected $appends = ["user_info","registration_at", "percent", "total_percent"];

    protected $with = ["percentages"];

    public function getUserInfoAttribute(){
        $user = $this->user()->first();

        if (!is_null($user))
            return $user->toHtmlText();
        else
            return "";
    }

    public function getPercentAttribute(){
        $user = $this->user()->first();

        if (!is_null($user))
            return $user->percent;
        else
            return 0;
    }


    public function getRegistrationAtAttribute(){
        $user = $this->user()->first();

        if (!is_null($user))
            return $user->registration_at;
        else
            return null;
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class,'mentor_id','id');
    }

    public function percentages(): HasMany
    {
        return $this->hasMany(Percentage::class,'agent_id','id');
    }

    public function totalPercent(): Attribute
    {
        return Attribute::get(function () {
            return $this->percentages()
                ->where('is_active', true)
                ->sum('percentage');
        });
    }
}
