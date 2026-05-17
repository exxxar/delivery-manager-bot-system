<template>
    <div>
        <p class="fw-bold mb-2 small" @click="$emit('toggle-selection', sale.id)">
            <span
                class="badge"
                :class="{'bg-warning': !sale.sale_date, 'bg-success': sale.sale_date}"
                v-if="sale.payment_type === 0"
            >
                <i class="fa-solid fa-money-bill"></i>
                <i
                    class="fa-solid fa-clock"
                    style="margin-left:8px;"
                    v-if="!sale.sale_date"
                ></i>
            </span>

            <span
                class="badge bg-success"
                v-if="sale.payment_type === 1"
            >
                <i class="fa-solid fa-credit-card"></i>
            </span>

            <span
                class="badge bg-primary mx-1"
                v-if="field_visible?.id || true"
            >
                #{{ sale.id }}
            </span>

            {{ sale.title }}
        </p>

<!--        <p class="fw-bold mb-0 small" style="font-size:14px;" v-if="field_visible?.due_date || true">
            Дата задания {{ sale.due_date || 'не указана' }}
        </p>-->

        <p class="fw-bold mb-0 small" style="font-size:14px;" v-if="field_visible?.sale_date || true">
            Дата продажи {{ sale.sale_date || 'не указана' }}
        </p>

        <p class="fw-bold mb-2" style="font-size:14px;" v-if="field_visible?.payment_type || false">
            Тип оплаты
            <span v-if="sale.payment_type === 0">Наличный расчет</span>
            <span v-if="sale.payment_type === 1">Безналичный расчет</span>
        </p>

        <p class="fw-bold mb-2" style="font-size:14px;" v-if="field_visible?.actual_delivery_date || true">
            Фактическая дата доставки {{ sale.actual_delivery_date || 'не указана' }}
        </p>

        <small class="text-muted small" v-if="field_visible?.status || true">
            Статус:
            <span
                class="badge"
                :class="statusClass"
            >
                {{ saleStatuses[sale.status] }}
            </span>
        </small>

        <p class="mb-2 small" v-if="field_visible?.description || true">
            {{ sale.description }}
        </p>

        <p class="mb-2" v-if="field_visible?.quantity || false">
            Доставляемое число товара
            <span class="fw-bold">{{ sale.quantity || 'не указана' }}</span> ед.
        </p>

        <p class="mb-2" v-if="field_visible?.total_price || false">
            Сумма заказа
            <span class="fw-bold">{{ sale.total_price }}</span> руб.
        </p>

        <p class="mb-2 small" v-if="field_visible?.agent_id || true">
            Оформил заказ
            <span class="fw-bold">{{ sale.agent?.name || sale.agent_id || '-' }}</span>
        </p>

        <p class="mb-2" v-if="field_visible?.customer_id || false">
            Клиент
            <span class="fw-bold">{{ sale.customer?.name || sale.customer_id || '-' }}</span>
        </p>

        <p class="mb-2" v-if="field_visible?.supplier_id || false">
            Поставщик
            <span class="fw-bold">{{ sale.supplier?.name || sale.supplier_id || '-' }}</span>
        </p>

        <p class="mb-2" v-if="field_visible?.created_by_id || false">
            Старший администратор
            <span class="fw-bold">{{ sale.creator?.name || sale.created_by_id || '-' }}</span>
        </p>

        <p class="mb-2" v-if="field_visible?.product_id || false">
            Товар
            <span class="fw-bold">{{ sale.product?.name || sale.product_id || '-' }}</span>
        </p>

        <p
            v-if="!sale.payment_document_name && sale.payment_type === 1"
            class="mb-0 w-100 badge bg-danger"
            style="cursor:pointer;text-align:left;"
        >
            <i class="fa-solid fa-triangle-exclamation" style="margin-right:5px;"></i>
            Чек не прикреплен к сделке.
        </p>
    </div>
</template>

<script>
export default {
    name: 'SaleCard',

    props: {
        sale: {
            type: Object,
            required: true
        },
        field_visible: {
            type: Object,
            default: () => ({})
        },
        saleStatuses: {
            type: Object,
            required: true
        }
    },

    computed: {
        statusClass() {
            return {
                'bg-warning': this.sale.status === 'pending',
                'bg-info': this.sale.status === 'assigned',
                'bg-primary': this.sale.status === 'delivered',
                'bg-success': this.sale.status === 'completed',
                'bg-danger': this.sale.status === 'rejected',
            };
        }
    }
};
</script>
