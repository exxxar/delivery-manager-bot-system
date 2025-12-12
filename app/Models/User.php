<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        "fio_from_telegram",
        "telegram_chat_id",
        "role",
        "percent",
        "is_work",
        "birthday",
        "email_verified_at",
        "password",
        "blocked_at",
        "registration_at",
        "blocked_message",
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_work' => 'boolean',
        'password' => 'hashed',
        'birthday' => 'date',
    ];

    protected $with = ['agent'];

    protected $appends = ["phone"];

    public function getPhoneAttribute()
    {
        return $this->agent->phone ?? '';
    }

    public function getUserTelegramLink(): string
    {
        return "\n<a href='tg://user?id=" . $this->telegram_chat_id . "'>Перейти к чату с пользователем</a>";
    }

    public function getRoleName(): string
    {
        $roles = [
            'Пользователь',
            'Младший администратор',
            'Поставщик',
            'Администратор',
            'Суперадмин',
        ];

        return $roles[$this->role] ?? 'Неизвестная роль';
    }

    public function toTelegramText(): string
    {
        $fields = [
            'Имя' => $this->name,
            'Email' => $this->email,
            'ФИО из Telegram' => $this->fio_from_telegram,
            'Дата рождения' => $this->birthday ?? '-',
            'ID чата Telegram' => $this->telegram_chat_id,
            'Роль' => $this->getRoleName(),
            'Процент' => $this->percent,
            'Работает' => $this->is_work ? 'Да' : 'Нет',
            'Email подтверждён' => $this->email_verified_at,
            'Дата заполнения профиля' => $this->registration_at ?? 'не заполнен',
            'Дата блокировки' => $this->blocked_at ?? 'не заблокирован',
            'Сообщение блокировки' => $this->blocked_message,
            'Создан' => $this->created_at,
            'Обновлён' => $this->updated_at,
        ];

        $text = "";
        foreach ($fields as $label => $value) {
            if (!empty($value)) {
                $text .= "{$label}: {$value}\n";
            }
        }

        return trim($text);
    }

    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class, 'user_id', 'id');
    }
}
