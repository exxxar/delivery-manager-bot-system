<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'due_date',
        'sale_date',
        'actual_delivery_date',
        'quantity',
        'title',
        'description',
        'status',
        'total_price',
        'payment_type',
        'payment_document_name',
        'product_id',
        'agent_id',
        'customer_id',
        'supplier_id',
        'created_by_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'total_price' => 'float',
        'product_id' => 'integer',
        'payment_type' => 'integer',
        'agent_id' => 'integer',
        'customer_id' => 'integer',
        'supplier_id' => 'integer',
        'created_by_id' => 'integer',
    ];

    protected $with = ["product", "agent", "customer", "supplier", "creator"];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id');
    }

    public function toTelegramText(): string
    {
        $paymentType = $this->payment_type == 0 ? 'Наличный расчет' : 'Безналичный расчет';

        $status = [
            "pending" => "Ожидание",
            "assigned" => "Взят в работу",
            "delivered" => "Доставляется",
            "completed" => "Завершено",
            'rejected' => "Отклонено"][$this->status ?? 'pending'] ?? 'Неизвестно';

        return
            "<b>Сделка #{$this->id}</b>\n" .
            "<b>Название:</b> {$this->title}\n" .
            "<b>Описание:</b> {$this->description}\n" .
            "<b>Статус:</b> {$status}\n" .
            "<b>Дата встречи:</b> " . ($this->due_date ?? '-') . "\n" .
            "<b>Дата продажи (факт. оплаты):</b> " . ($this->sale_date ?? '-') . "\n" .
            "<b>Факт. доставка:</b> " . ($this->actual_delivery_date ?? '-') . "\n" .
            "<b>Количество:</b> {$this->quantity}\n" .
            "<b>Сумма заказа:</b> {$this->total_price}\n" .
            "<b>Тип оплаты:</b> {$paymentType}\n" .
            "<b>Админ:</b> " . ($this->agent?->name ?? '-') . "\n" .
            "<b>Клиент:</b> " . ($this->customer?->name ?? '-') . "\n" .
            "<b>Поставщик:</b> " . ($this->supplier?->name ?? '-') . "\n" .
            "<b>Продукт:</b> " . ($this->product?->name ?? '-') . "\n" .
            "<b>Создан админом:</b> " . ($this->creator?->name ?? '-') . "\n";
    }
}
