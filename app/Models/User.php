<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'fio_from_telegram',
        'telegram_chat_id',
        'role',
        'percent',
        'mentor_percent',
        'font_settings',
        'is_work',
        'birthday',
        'email_verified_at',
        'blocked_at',
        'registration_at',
        'blocked_message',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'font_settings' => 'array',
        'is_work' => 'boolean',
        'password' => 'hashed',
        'birthday' => 'date',
    ];

    // 🔴 УБРАЛИ $with = ['agent'] — это главный виновник!
    // protected $with = ['agent'];

    // 🔴 УБРАЛИ 'phone' из appends — он вызывал $this->agent
    protected $appends = ['font_settings'];

    // phone делаем ленивым — вызываем только там, где реально нужен
    public function getPhoneAttribute()
    {
        // Используем relationLoaded, чтобы не делать лишних запросов
        if ($this->relationLoaded('agent')) {
            return $this->agent->phone ?? '';
        }

        // Если агент не загружен — не дёргаем его автоматически
        return '';
    }

    public static function defaultFontSettings(): array
    {
        return [
            'font_family' => 'system',
            'font_size' => 16,
            'line_height' => 1.5,
            'letter_spacing' => 0,
            'high_contrast' => false,
        ];
    }

    public function getFontSettingsAttribute($value): array
    {
        $settings = $value ? json_decode($value, true) : [];
        return array_merge(self::defaultFontSettings(), $settings ?? []);
    }

    public function getUserTelegramLink(): string
    {
        return "<br><p><a href='tg://user?id=" . $this->telegram_chat_id . "'>Перейти к чату с пользователем</a></p>";
    }

    public function getRoleName(): string
    {
        $roles = [
            'Пользователь',
            'Администратор',
            'Поставщик',
            'Старший администратор',
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
                $text .= "<p>{$label}: {$value}</p>";
            }
        }

        return trim($text);
    }

    public function toHtmlText(): string
    {
        $fields = [
            'ФИО из Telegram' => $this->fio_from_telegram,
            'Дата рождения' => !is_null($this->birthday) ? Carbon::parse($this->birthday)->format('Y-m-d') : '-',
            'ID чата Telegram' => $this->telegram_chat_id,
            'Роль' => $this->getRoleName(),
        ];

        $text = "";
        foreach ($fields as $label => $value) {
            if (!empty($value)) {
                $text .= "<p class='mb-0'><strong>{$label}</strong>: {$value}</p>";
            }
        }

        return trim($text);
    }

    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class, 'user_id', 'id');
    }

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class, 'user_id', 'id');
    }
}
