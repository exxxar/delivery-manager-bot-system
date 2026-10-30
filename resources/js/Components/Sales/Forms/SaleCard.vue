<template>
    <article class="sale-card">
        <div class="sale-card__header" @click="$emit('toggle-selection', sale.id)">
            <div class="sale-card__header-left">
                <div class="sale-card__badges">
                    <!-- Тип расчёта -->
                    <span
                        v-if="sale.payment_type === 0"
                        class="sale-card__payment"
                        :class="sale.sale_date ? 'sale-card__payment--cash' : 'sale-card__payment--pending'"
                        :title="sale.sale_date ? 'Наличный расчёт' : 'Наличный расчёт · продажа не завершена'"
                    >
                        <i class="fa-solid fa-money-bill-wave"></i>
                        <i v-if="!sale.sale_date" class="fa-regular fa-clock"></i>
                    </span>

                    <span
                        v-else-if="sale.payment_type === 1"
                        class="sale-card__payment sale-card__payment--card"
                        title="Безналичный расчёт"
                    >
                        <i class="fa-solid fa-credit-card"></i>
                    </span>

                    <!-- ID -->
                    <span class="sale-card__id">#{{ sale.id }}</span>

                    <!-- Статус -->
                    <span class="sale-card__status" :class="statusClass">
                        <span class="sale-card__status-dot"></span>
                        {{ saleStatuses[sale.status] || sale.status || 'Статус не указан' }}
                    </span>
                </div>
            </div>

            <!-- Меню всегда справа -->
            <div class="sale-card__actions" @click.stop>
                <slot name="actions" />
            </div>
        </div>

        <!-- Название сделки -->
        <h3 class="sale-card__title">
            {{ sale.title || 'Сделка без названия' }}
        </h3>

        <p v-if="field_visible?.description || true" class="sale-card__description">
            {{ sale.description || 'Описание не добавлено' }}
        </p>

        <div class="sale-card__details">
            <div v-if="field_visible?.actual_delivery_date || true" class="sale-card__detail">
                <span class="sale-card__detail-icon sale-card__detail-icon--blue">
                    <i class="fa-solid fa-truck-fast"></i>
                </span>
                <span class="sale-card__detail-content">
                    <span class="sale-card__label">Фактическая дата доставки</span>
                    <strong>{{ sale.actual_delivery_date || 'Не указана' }}</strong>
                </span>
            </div>

            <div v-if="field_visible?.agent_id || true" class="sale-card__detail">
                <span class="sale-card__detail-icon sale-card__detail-icon--violet">
                    <i class="fa-solid fa-user-pen"></i>
                </span>
                <span class="sale-card__detail-content">
                    <span class="sale-card__label">Оформил заказ</span>
                    <strong>{{ sale.agent?.name || sale.agent_id || 'Не указан' }}</strong>
                </span>
            </div>

            <div v-if="field_visible?.payment_type || false" class="sale-card__detail">
                <span class="sale-card__detail-icon sale-card__detail-icon--green">
                    <i class="fa-solid fa-wallet"></i>
                </span>
                <span class="sale-card__detail-content">
                    <span class="sale-card__label">Тип оплаты</span>
                    <strong>
                        {{ sale.payment_type === 0 ? 'Наличный расчёт' : sale.payment_type === 1 ? 'Безналичный расчёт' : 'Не указан' }}
                    </strong>
                </span>
            </div>

            <div v-if="field_visible?.quantity || false" class="sale-card__detail">
                <span class="sale-card__detail-icon sale-card__detail-icon--orange">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
                <span class="sale-card__detail-content">
                    <span class="sale-card__label">Количество товара</span>
                    <strong>{{ sale.quantity ?? 'Не указано' }} ед.</strong>
                </span>
            </div>

            <div v-if="field_visible?.total_price || false" class="sale-card__detail">
                <span class="sale-card__detail-icon sale-card__detail-icon--green">
                    <i class="fa-solid fa-ruble-sign"></i>
                </span>
                <span class="sale-card__detail-content">
                    <span class="sale-card__label">Сумма заказа</span>
                    <strong>{{ sale.total_price ?? '—' }} ₽</strong>
                </span>
            </div>

            <div v-if="field_visible?.customer_id || false" class="sale-card__detail">
                <span class="sale-card__detail-icon sale-card__detail-icon--blue">
                    <i class="fa-solid fa-user"></i>
                </span>
                <span class="sale-card__detail-content">
                    <span class="sale-card__label">Клиент</span>
                    <strong>{{ sale.customer?.name || sale.customer_id || 'Не указан' }}</strong>
                </span>
            </div>

            <div v-if="field_visible?.supplier_id || false" class="sale-card__detail">
                <span class="sale-card__detail-icon sale-card__detail-icon--orange">
                    <i class="fa-solid fa-warehouse"></i>
                </span>
                <span class="sale-card__detail-content">
                    <span class="sale-card__label">Поставщик</span>
                    <strong>{{ sale.supplier?.name || sale.supplier_id || 'Не указан' }}</strong>
                </span>
            </div>

            <div v-if="field_visible?.created_by_id || false" class="sale-card__detail">
                <span class="sale-card__detail-icon sale-card__detail-icon--violet">
                    <i class="fa-solid fa-user-shield"></i>
                </span>
                <span class="sale-card__detail-content">
                    <span class="sale-card__label">Старший администратор</span>
                    <strong>{{ sale.creator?.name || sale.created_by_id || 'Не указан' }}</strong>
                </span>
            </div>

            <div v-if="field_visible?.product_id || false" class="sale-card__detail">
                <span class="sale-card__detail-icon sale-card__detail-icon--blue">
                    <i class="fa-solid fa-box"></i>
                </span>
                <span class="sale-card__detail-content">
                    <span class="sale-card__label">Товар</span>
                    <strong>{{ sale.product?.name || sale.product_id || 'Не указан' }}</strong>
                </span>
            </div>
        </div>

        <div v-if="!sale.payment_document_name && sale.payment_type === 1" class="sale-card__warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>
                <strong>Чек не прикреплён.</strong>
                К сделке не добавлен платёжный документ.
            </span>
        </div>
    </article>
</template>

<script>
export default {
    name: 'SaleCard',

    emits: ['toggle-selection'],

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
                'status--pending': this.sale.status === 'pending',
                'status--assigned': this.sale.status === 'assigned',
                'status--delivered': this.sale.status === 'delivered',
                'status--completed': this.sale.status === 'completed',
                'status--rejected': this.sale.status === 'rejected',
                'status--default': ![
                    'pending',
                    'assigned',
                    'delivered',
                    'completed',
                    'rejected'
                ].includes(this.sale.status)
            };
        }
    }
};
</script>

<style scoped>
.sale-card {
    --sale-blue: #2457d6;
    --sale-text: #17233b;
    --sale-muted: #778399;
    --sale-border: #e5eaf2;

    position: relative;
    width: 100%;
    overflow: visible;
    color: var(--sale-text);
    border-radius: 18px;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
    border: none;
    background: transparent;
    box-shadow: none;
    padding: 0;
}

.sale-card:hover {
    border-color: #cbd8f4;
    box-shadow: 0 10px 26px rgba(25, 45, 82, .09);
    transform: translateY(-2px);
}

/* Шапка: информация слева, меню справа */
.sale-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    width: 100%;
    cursor: pointer;
}

.sale-card__header-left {
    display: flex;
    align-items: center;
    flex: 1 1 auto;
    min-width: 0;
    overflow: hidden;
}

/* Тип расчёта, ID и статус в одну строку */
.sale-card__badges {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 7px;
    min-width: 0;
    overflow: hidden;
}

/* Фиксированная область меню справа */
.sale-card__actions {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 40px;
    width: 40px;
    height: 40px;
    margin-left: auto;
}

.sale-card__actions :deep(.sale-card__dropdown) {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sale-card__actions :deep(.sale-card__menu-button) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    color: #2457d6;
    background: #f4f7ff;
    border: 1px solid #dfe7fb;
    border-radius: 10px;
    box-shadow: none;
    cursor: pointer;
    transition: all .2s ease;
}

.sale-card__actions :deep(.sale-card__menu-button:hover),
.sale-card__actions :deep(.sale-card__menu-button:focus-visible) {
    color: #173b91;
    background: #eaf0ff;
    border-color: #c5d4fa;
}

.sale-card__actions :deep(.sale-card__dropdown .dropdown-menu) {
    z-index: 1080;
    min-width: 220px;
    max-width: min(280px, calc(100vw - 32px));
    padding: 7px;
    border: 1px solid #e5eaf2;
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(23, 35, 59, .16);
}

.sale-card__actions :deep(.sale-card__dropdown .dropdown-item) {
    padding: 9px 11px;
    border-radius: 8px;
    font-size: 13px;
    white-space: normal;
}

.sale-card__actions :deep(.sale-card__dropdown .dropdown-divider) {
    margin: 6px 4px;
}

/* Тип расчёта */
.sale-card__payment,
.sale-card__id {
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 27px;
    padding: 5px 9px;
    border-radius: 8px;
    font-size: 12px;
    white-space: nowrap;
}

.sale-card__payment--cash {
    color: #18794e;
    background: #e7f7ef;
}

.sale-card__payment--pending {
    color: #9a6700;
    background: #fff3cd;
}

.sale-card__payment--card {
    color: #2457d6;
    background: #eaf0ff;
}

.sale-card__id {
    color: #596780;
    background: #f1f4f9;
    font-weight: 700;
}

/* Статус */
.sale-card__status {
    display: inline-flex;
    flex: 0 1 auto;
    align-items: center;
    gap: 7px;
    min-width: 0;
    padding: 7px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sale-card__status-dot {
    flex: 0 0 7px;
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
}

.status--pending {
    color: #916000;
    background: #fff2cc;
}

.status--assigned {
    color: #176b9b;
    background: #e3f4ff;
}

.status--delivered {
    color: #2457d6;
    background: #e9efff;
}

.status--completed {
    color: #18794e;
    background: #e4f7ed;
}

.status--rejected {
    color: #bd293b;
    background: #ffeaed;
}

.status--default {
    color: #596780;
    background: #eef1f6;
}

/* Название сделки */
.sale-card__title {
    margin: 10px 0 0;
    font-size: 16px;
    line-height: 1.4;
    font-weight: 750;
    overflow-wrap: anywhere;
}

.sale-card__description {
    margin: 14px 0 0;
    padding: 12px 14px;
    color: #596780;
    background: #f7f9fc;
    border-radius: 10px;
    font-size: 13px;
    line-height: 1.55;
    overflow-wrap: anywhere;
}

/* Детали */
.sale-card__details {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px 18px;
    margin-top: 16px;
}

.sale-card__detail {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.sale-card__detail-icon {
    display: flex;
    flex: 0 0 36px;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 11px;
    font-size: 14px;
}

.sale-card__detail-icon--blue {
    color: #2457d6;
    background: #eaf0ff;
}

.sale-card__detail-icon--violet {
    color: #7651c8;
    background: #f0eaff;
}

.sale-card__detail-icon--green {
    color: #168252;
    background: #e4f7ed;
}

.sale-card__detail-icon--orange {
    color: #b66a10;
    background: #fff1dc;
}

.sale-card__detail-content {
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 0;
}

.sale-card__label {
    color: var(--sale-muted);
    font-size: 11px;
    line-height: 1.3;
}

.sale-card__detail strong {
    color: var(--sale-text);
    font-size: 13px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

/* Предупреждение */
.sale-card__warning {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin-top: 16px;
    padding: 11px 13px;
    color: #a52b3b;
    background: #fff0f1;
    border: 1px solid #ffd9de;
    border-radius: 11px;
    font-size: 12px;
    line-height: 1.45;
}

.sale-card__warning > i {
    margin-top: 2px;
}

/* Мобильная версия */
@media (max-width: 576px) {
    .sale-card__header {
        flex-direction: row;
        align-items: center;
        gap: 8px;
    }

    .sale-card__header-left {
        min-width: 0;
    }

    .sale-card__badges {
        gap: 5px;
    }

    .sale-card__payment,
    .sale-card__id {
        min-height: 25px;
        padding: 4px 7px;
        font-size: 11px;
    }

    .sale-card__status {
        padding: 6px 8px;
        font-size: 10px;
    }

    .sale-card__actions {
        flex-basis: 36px;
        width: 36px;
        height: 36px;
    }

    .sale-card__actions :deep(.sale-card__menu-button) {
        width: 34px;
        height: 34px;
    }

    .sale-card__title {
        font-size: 15px;
    }

    .sale-card__actions :deep(.sale-card__dropdown .dropdown-menu) {
        max-width: calc(100vw - 48px);
    }

    .sale-card__details {
        grid-template-columns: minmax(0, 1fr);
        gap: 13px;
    }
}
</style>
