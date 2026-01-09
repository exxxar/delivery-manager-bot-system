<template>

    <h6 class="mb-2">{{ task.title }}
        <span class="badge" :class="statusClass(task.status)">
        {{ statusLabels[task.status] }}
      </span>
    </h6>


    <div class="mb-2">
        <p class="text-muted">{{ task.description }}</p>

        <ul class="list-group list-group-flush ">
            <li class="list-group-item">
                <strong>Дата встречи:</strong> {{ formatDate(task.due_date) }}
            </li>
            <li class="list-group-item">
                <strong>Дата продажи:</strong> {{ formatDate(task.sale_date) }}
            </li>
            <li class="list-group-item">
                <strong>Фактическая доставка:</strong> {{ formatDate(task.actual_delivery_date) }}
            </li>
            <li class="list-group-item">
                <strong>Тип оплаты:</strong>
                <span v-if="task.payment_type===0">Наличный расчет</span>
                <span v-if="task.payment_type===1">Безналичный расчет</span>
            </li>
            <li class="list-group-item">
                <strong>Количество:</strong> {{ task.quantity }}
            </li>
            <li class="list-group-item">
                <strong>Сумма заказа:</strong> {{ task.total_price }} ₴
            </li>
        </ul>

        <div class="mt-3">
            <h6 class="fw-bold text-primary">Связанные элементы</h6>
            <p><strong>Отвественный:</strong> {{ task.agent?.name || '—' }}</p>
            <p v-if="task.customer"><strong>Покупатель:</strong> {{ task.customer?.name || '—' }}</p>
            <p><strong>Поставщик:</strong> {{ task.supplier?.name || '—' }}</p>
            <p><strong>Продукт:</strong> {{ task.product?.name || '—' }}</p>
            <p v-if="task.category"><strong>Категория:</strong> {{ task.category?.name || '—' }}</p>
            <p v-if="task.creator"><strong>Создан старшим администратором:</strong> {{ task.creator?.name || '—' }}</p>
        </div>

        <template v-if="task.payment_document_name">
            <button
                @click="sendPaymentDocumentToTg"
                class="btn btn-info p-3 w-100" type="button">Документ чека
            </button>
        </template>
    </div>
    <div class="text-muted small">
        Создано: {{ formatDate(task.created_at) }} |
        Обновлено: {{ formatDate(task.updated_at) }}
    </div>
</template>

<script>
import {useSalesStore} from "@/stores/sales";

export default {
    name: 'TaskCard',
    props: {
        task: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            salesStore: useSalesStore(),
            statusLabels: {
                pending: 'В ожидании',
                assigned: 'Назначено',
                delivered: 'Доставлено',
                completed: 'Завершено',
                rejected: 'Отклонено'
            }
        }
    },
    methods: {
        formatDate(date) {
            if (!date) return '—'
            return new Date(date).toLocaleDateString()
        },
        async sendPaymentDocumentToTg() {
            await this.salesStore.sendPaymentDocumentToTg(this.task.id)
        },
        statusClass(status) {
            switch (status) {
                case 'pending':
                    return 'bg-secondary'
                case 'assigned':
                    return 'bg-info'
                case 'delivered':
                    return 'bg-primary'
                case 'completed':
                    return 'bg-success'
                case 'rejected':
                    return 'bg-danger'
                default:
                    return 'bg-dark'
            }
        }
    }
}
</script>
