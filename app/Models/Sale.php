<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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
        'mentor_award',
        'payment_type',
        'payment_document_name',
        'payed_at',
        'product_id',
        'product_category_id',
        'agent_id',
        'customer_id',
        'supplier_id',
        'created_by_id',
        'created_at',
        'updated_at',
        'verified_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'total_price' => 'float',
        'mentor_award' => 'float',
        'product_id' => 'integer',
        'product_category_id' => 'integer',
        'payment_type' => 'integer',
        'agent_id' => 'integer',
        'customer_id' => 'integer',
        'supplier_id' => 'integer',
        'created_by_id' => 'integer',
    ];

    protected $with = ["product", "agent", "customer", "supplier", "creator", "category"];

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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id');
    }

    public function toTelegramText($receiptIsLost = false): string
    {

        $paymentType = ($this->payment_type == 0 ? 'Наличный расчет' : 'Безналичный расчет')
            . ($receiptIsLost ? " (Чек утрачен)" : "");


        $status = [
            "pending" => "Ожидание",
            "assigned" => "Взят в работу",
            "delivered" => "Доставляется",
            "completed" => "Завершено",
            'rejected' => "Отклонено"][$this->status ?? 'pending'] ?? 'Неизвестно';

        return
            "<p class='mb-2'><b>Сделка #{$this->id}</b></p>" .
            "<p class='mb-2'><b>Название:</b> {$this->title}</p>" .
            "<p class='mb-2'><b>Описание:</b> {$this->description}</p>" .
            "<p class='mb-2'><b>Статус:</b> {$status}</p>" .
            "<p class='mb-2'><b>Дата встречи:</b> " . ($this->due_date ?? '-') . "</p>" .
            "<p class='mb-2'><b>Дата продажи (факт. оплаты):</b> " . ($this->sale_date ?? '-') . "</p>" .
            "<p class='mb-2'><b>Факт. доставка:</b> " . ($this->actual_delivery_date ?? '-') . "</p>" .
            "<p class='mb-2'><b>Количество:</b> {$this->quantity}</p>" .
            "<p class='mb-2'><b>Сумма заказа:</b> {$this->total_price}</p>" .
            "<p class='mb-2'><b>Тип оплаты:</b> {$paymentType}</p>" .
            "<p class='mb-2'><b>Отвественный:</b> " . ($this->agent?->name ?? '-') . "</p>" .
          //  "<p class='mb-2'><b>Клиент:</b> " . ($this->customer?->name ?? '-') . "</p>" .
            "<p class='mb-2'><b>Поставщик:</b> " . ($this->supplier?->name ?? '-') . "</p>" .
            "<p class='mb-2'><b>Продукт:</b> " . ($this->product?->name ?? '-') . "</p>" .
            "<p class='mb-2'><b>Создан админом:</b> " . ($this->creator?->fio_from_telegram ?? '-') . "</p>";
    }

    public function scopeFilter(Builder $query, $request)
    {
        $botUser = $request->botUser;

        $onlySelfSales = ($request->only_self_sales ?? false) || $botUser->role == RoleEnum::AGENT->value;

        $agent = Agent::where('user_id', $botUser->id)->first();

        if ($onlySelfSales)
            // 🔹 Ограничение доступа
            $query->where(function ($q) use ($botUser, $agent) {
                if (is_null($agent)) {
                    $q->where('created_by_id', $botUser->id);
                } else {
                    $q->where('agent_id', $agent->id)
                        ->orWhere('created_by_id', $botUser->id);
                }
            });


        // 🔹 Простые фильтры
        $query->when($request->number, fn($q) => $q->where('id', $request->number)
        );

        $query->when($request->title, fn($q) => $q->where('title', 'like', "%{$request->title}%")
        );

        $query->when($request->description, fn($q) => $q->where('description', 'like', "%{$request->description}%")
        );

        if (isset($request->status))
            $query->where('status', $request->status);
        else
            $query->whereIn('status', [
                "pending",
                "assigned" ,
                "delivered",
                "completed"
            ]);

        /*$query->when($request->status, fn($q) => $q->whereIn('status', $request->status)
        );*/

        $query->when($request->payment_type, fn($q) => $q->where('payment_type', $request->payment_type)
        );

        // 🔹 Foreign keys
        $query->when($request->product_category_id, fn($q) => $q->where('product_category_id', $request->product_category_id)
        );


        $query->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id)
        );

        $query->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id)
        );

        $query->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id)
        );

        // 🔹 Даты
        if ($request->date_from || $request->date_to) {
            $query->whereBetween('actual_delivery_date', [
                    $request->date_from ?? '1900-01-01',
                    $request->date_to ?? now()->toDateString()
            ]);
        }

        // 🔹 Роли
        if ($botUser->role >= 3) {
            $query->when($request->agent_id, fn($q) => $q->where('agent_id', $request->agent_id)
            );

            $query->when($request->created_by_id, fn($q) => $q->where('created_by_id', $request->created_by_id)
            );
        }

        // 🔹 Количество и цена
        $query->when($request->quantity, fn($q) => $q->where('quantity', $request->quantity)
        );

        $query->when($request->total_price, fn($q) => $q->where('total_price', $request->total_price)
        );


        return $query;
    }

    public function scopeSort(Builder $query, $request)
    {
        $allowedFields = [
            'id', 'title', 'description', 'status', 'due_date', 'sale_date',
            'quantity', 'total_price', 'agent_id', 'customer_id',
            'supplier_id', 'product_id'
        ];

        $field = $request->get('sort_field', 'id');
        $direction = $request->get('sort_direction', 'desc');

        // 🔹 Если пользователь НЕ указал поле сортировки — применяем дефолтную
        if (!$field || $field == "id") {
            return $query
                ->orderByRaw("CASE WHEN status = 'completed' THEN 1 ELSE 0 END ASC")
                ->orderBy('due_date', 'desc');
        }

        if (in_array($field, $allowedFields) && in_array($direction, ['asc', 'desc'])) {
            $query->orderBy($field, $direction);
        }

        return $query;
    }
}
