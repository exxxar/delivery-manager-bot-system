<script setup>
import SaleForm from "@/Components/Sales/SaleForm.vue";
import Pagination from "@/Components/Pagination.vue";
import SaleFilterForm from '@/components/Sales/SaleFilterForm.vue'
import TaskCard from "@/Components/Sales/TaskCard.vue";
import DealForm from "@/Components/Sales/Forms/DealForm.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
import AgentList from "@/Components/Agents/AgentList.vue";
</script>
<template>
    <div class="not-verified-sales-page">
        <template v-if="tab==='main'">
            <header class="nvs-page-header">
                <div class="nvs-heading-icon">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div class="nvs-heading-copy">
                    <div class="nvs-eyebrow">Мойпроцент · Контроль продаж</div>
                    <h1>Проверка сделок</h1>
                    <p>Подтверждайте продажи и контролируйте заявки, ожидающие проверки.</p>
                </div>
                <div class="nvs-header-count">
                    <span class="nvs-count-value">{{ salesStore.not_verified_items.length }}</span>
                    <span class="nvs-count-label">на проверке</span>
                </div>
            </header>
            <!-- Progress -->
            <transition name="fade">

                <div
                    v-if="processingQueue > 0"
                    class="mb-3"
                >

                    <div class="d-flex justify-content-between small mb-1">

            <span>
                <i class="fa-solid fa-paper-plane me-1"></i>
                Обработка заявок
            </span>

                        <span>
                {{ processingQueue }} ед.
            </span>

                    </div>

                    <div
                        class="progress"
                        style="height: 12px;"
                    >

                        <div
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar"

                            :style="{
                    width: progressPercent + '%'
                }"
                        >
                        </div>

                    </div>

                    <div class="alert alert-danger my-2">
                        Внимание! Дождитесь завершения процесса обработки заявок!
                    </div>
                </div>

            </transition>


            <div
                class="form-check form-switch mb-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="actual_delivery_date_filter"
                    :id="`actual_delivery_date_filter`"
                />
                <label class="form-check-label" :for="`actual_delivery_date_filter`">
                    Фильтры
                </label>
            </div>

            <template v-if="actual_delivery_date_filter">
                <div class="form-floating mb-2">
                    <input type="date"
                           v-model="filters.date_from"
                           class="form-control" id="dateFromInput"/>
                    <label for="dateFromInput">Дата от</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="date"
                           v-model="filters.date_to"
                           class="form-control" id="dateToInput"/>
                    <label for="dateToInput">Дата до</label>
                </div>

                <div class="input-group mb-0">
                    <div class="form-floating flex-grow-1">
                        <input type="text" class="form-control" id="agent" :value="filters.agentName"
                               placeholder="администратор"
                               readonly>
                        <label for="agent">Администратор</label>
                    </div>
                    <button type="button" class="btn btn-outline-light text-primary find-btn" @click="tab='agent'">
                        Выбрать
                    </button>
                </div>
                <a
                    @click="agentClear"
                    href="javascript:void(0)"
                    v-if="filters.agent_id"
                    class="small fst-italic">Очистить администратора <i class="fa-solid fa-xmark text-danger"></i></a>

                <div class="form-floating my-2">
                    <select
                        id="itemsPerPage"
                        class="form-select"
                        v-model="size"
                    >
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                        <option value="5000">5000</option>
                    </select>
                    <label for="itemsPerPage">Элементов на странице</label>
                </div>

                <button
                    :disabled="salesStore.loading"
                    type="button"
                    class="btn btn-primary w-100 p-3 mb-2"
                    @click="fetchData(1, true)">
                    Применить фильтр
                </button>
            </template>


            <template v-if="salesStore.not_verified_items.length>0">
                <ul class="list-group mb-2">
                    <li class="list-group-item"
                        v-bind:class="{'bg-light': sale.verified_at}"
                        v-for="sale in salesStore.not_verified_items">
                        <p class="fw-bold mb-2 small">

               <span
                   class="badge bg-success"
                   v-if="sale.payment_type === 1"
               >
                <i class="fa-solid fa-credit-card"></i>
            </span>

                            <span
                                class="badge bg-primary mx-1">
                #{{ sale.id }}
            </span>

                            {{ sale.title }} (<a href="javascript:void(0)"
                                                 @click="$emit('agent-info', sale.creator)"
                                                 class="fw-bold text-decoration-underline">{{
                                sale.creator?.name || sale.created_by_id || '-'
                            }}</a>)
                        </p>

                        <p class="mb-2 small">
                            Сумма заказа
                            <span class="fw-bold">{{ sale.total_price }}</span> руб.
                        </p>

                        <p class="fw-bold mb-0 small" style="font-size:14px;">
                            Дата продажи {{ sale.sale_date || 'не указана' }}
                        </p>

                        <div class="w-100 btn-group btn-group-sm">
                            <button
                                :disabled="sale.verified_at"
                                type="button"
                                @click="approve(sale)"
                                class="btn btn-success p-2">Да
                            </button>
                            <button
                                :disabled="sale.verified_at"
                                @click="decline(sale)"
                                class="btn btn-danger">Нет
                            </button>
                        </div>
                    </li>
                </ul>

                <template
                    v-if="salesStore.not_verified_pagination?.current_page<salesStore.not_verified_pagination?.last_page">
                    <button
                        :disabled="salesStore.loading"
                        type="button"
                        class="btn btn-primary w-100 p-3 small"
                        @click="fetchData(salesStore.not_verified_pagination.current_page+1)">
                        Загрузить еще <span
                        class="fw-bold small">{{ salesStore.not_verified_pagination.current_page }}</span> /
                        <span class="fw-bold small">{{ salesStore.not_verified_pagination.last_page }}</span>
                    </button>
                </template>
            </template>

            <p class="alert alert-info" v-else>
                По вашему запросу ничего не найдено
            </p>


        </template>
        <template v-if="tab==='agent'">
            <button
                @click="tab='main'"
                class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
            </button>
            <AgentList :for-select="true" @select="selectAgent"/>
        </template>
    </div>
</template>

<script>
import axios from 'axios'

import {useSalesStore} from '@/stores/sales'
import {useAgentsStore} from "@/stores/agents";
import {useUsersStore} from "@/stores/users";
import {useModalStore} from "@/stores/utillites/useConfitmModalStore";
import {useAlertStore} from "@/stores/utillites/useAlertStore";

export default {
    name: 'NotVerifiedSaleList',
    data() {
        return {
            tab: 'main',
            alertStore: useAlertStore(),
            actual_delivery_date_filter: false,
            processingQueue: 0,
            processingTotal: 0,

            filters: {
                agentName: null,
                agent_id: null,
                date_to: null,
                date_from: null,
            },
            size: 50,
            salesStore: useSalesStore(),
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
        progressPercent() {

            if (this.processingTotal === 0)
                return 0

            return (
                (this.processingQueue / this.processingTotal) * 100
            )
        }
    },
    created() {

        this.salesStore.fetchNotVerified()
    },
    methods: {

        selectAgent(agent) {
            this.filters.agent_id = agent.id
            this.filters.agentName = agent.name
            this.tab = 'main'
        },
        approve(item) {
            this.alertStore.show(
                "Добавлено в очередь на оповещение!"
            )
            // удаляем из списка
            this.salesStore.not_verified_items =
                this.salesStore.not_verified_items.filter(
                    sale => sale.id !== item.id
                )

            // добавляем в очередь
            this.processingQueue++
            this.processingTotal++

            this.salesStore.approveSale(item.id)

                .then(() => {

                    this.alertStore.show(
                        "Продажа успешно подтверждена!",
                        "success"
                    )

                })

                .catch(() => {

                    // возвращаем обратно
                    this.salesStore.not_verified_items.unshift(item)

                    this.alertStore.show(
                        "Ошибка подтверждения!",
                        "error"
                    )

                })

                .finally(() => {

                    // уменьшаем очередь
                    this.processingQueue--

                    // если всё завершено — сбрасываем
                    if (this.processingQueue <= 0) {

                        this.processingQueue = 0
                        this.processingTotal = 0

                    }

                })
        },

        decline(item) {
            this.alertStore.show(
                "Добавлено в очередь на оповещение!"
            )
            this.salesStore.not_verified_items =
                this.salesStore.not_verified_items.filter(
                    sale => sale.id !== item.id
                )

            this.processingQueue++
            this.processingTotal++

            this.salesStore.declineSale(item.id)

                .then(() => {

                    this.alertStore.show(
                        "Продажа отклонена!",
                        "success"
                    )

                })

                .catch(() => {

                    this.salesStore.not_verified_items.unshift(item)

                    this.alertStore.show(
                        "Ошибка отклонения!",
                        "error"
                    )

                })

                .finally(() => {

                    this.processingQueue--

                    if (this.processingQueue <= 0) {

                        this.processingQueue = 0
                        this.processingTotal = 0

                    }

                })
        },
        async fetchData(page = 1, reload = false) {
            if (reload)
                this.salesStore.not_verified_items = []

            await this.salesStore.fetchNotVerified({
                page: page,
                size: this.size,
                date_from: this.filters.date_from || null,
                date_to: this.filters.date_to || null,
                agent_id: this.filters.agent_id || null
            })
        },

        agentClear(){
            this.filters.agent_id = null
            this.filters.agentName = null
        }


    }
}
</script>
<style scoped>
p {
    overflow-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
}

.fade-enter-active,
.fade-leave-active {
    transition: 0.25s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-5px);
}
</style>


<style scoped>
.not-verified-sales-page {
    --nvs-blue: #2457d6;
    --nvs-blue-dark: #173b91;
    --nvs-red: #df3548;
    --nvs-text: #17233b;
    --nvs-muted: #778399;
    --nvs-border: #e5eaf2;
    color: var(--nvs-text);
    width: 100%;
    min-width: 0;
}
.nvs-page-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
    padding: 22px 24px;
    border: 1px solid var(--nvs-border);
    border-radius: 20px;
    background: linear-gradient(135deg, #fff 0%, #f7f9ff 100%);
    box-shadow: 0 8px 24px rgba(23, 59, 145, .06);
}
.nvs-heading-icon {
    display: grid;
    place-items: center;
    flex: 0 0 54px;
    width: 54px;
    height: 54px;
    border-radius: 16px;
    color: var(--nvs-blue);
    background: #eaf0ff;
    font-size: 23px;
}
.nvs-heading-copy { min-width: 0; flex: 1; }
.nvs-eyebrow {
    margin-bottom: 4px;
    color: var(--nvs-blue);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}
.nvs-heading-copy h1 {
    margin: 0;
    color: var(--nvs-text);
    font-size: clamp(21px, 3vw, 28px);
    font-weight: 800;
    letter-spacing: -.035em;
}
.nvs-heading-copy p {
    margin: 6px 0 0;
    color: var(--nvs-muted);
    font-size: 13px;
}
.nvs-header-count {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 92px;
    padding: 10px 14px;
    border: 1px solid #dbe5ff;
    border-radius: 14px;
    background: #fff;
}
.nvs-count-value { color: var(--nvs-blue); font-size: 24px; font-weight: 800; line-height: 1.1; }
.nvs-count-label { margin-top: 4px; color: var(--nvs-muted); font-size: 11px; font-weight: 700; text-align: center; }

.not-verified-sales-page .progress {
    overflow: hidden;
    border-radius: 999px;
    background: #e9eef7;
}
.not-verified-sales-page .progress-bar { background-color: var(--nvs-blue); }
.not-verified-sales-page .alert {
    border: 1px solid transparent;
    border-radius: 12px;
}
.not-verified-sales-page .alert-danger {
    color: #a92536;
    border-color: #f6cdd2;
    background: #fff1f2;
}
.not-verified-sales-page .alert-info {
    padding: 18px;
    color: #31548f;
    border-color: #dbe6ff;
    background: #f3f7ff;
    text-align: center;
}
.not-verified-sales-page .form-check.form-switch {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 13px 15px 13px 2.8rem;
    border: 1px solid var(--nvs-border);
    border-radius: 13px;
    background: #fff;
}
.not-verified-sales-page .form-check-input {
    cursor: pointer;
}
.not-verified-sales-page .form-check-input:checked {
    border-color: var(--nvs-blue);
    background-color: var(--nvs-blue);
}
.not-verified-sales-page .form-check-label { color: var(--nvs-text); font-size: 13px; font-weight: 650; }
.not-verified-sales-page .form-control,
.not-verified-sales-page .form-select {
    min-height: 56px;
    border-color: #dfe5ef;
    border-radius: 12px;
    color: var(--nvs-text);
    background-color: #fff;
    box-shadow: none;
}
.not-verified-sales-page .form-control:focus,
.not-verified-sales-page .form-select:focus {
    border-color: #8aa9ff;
    box-shadow: 0 0 0 3px rgba(36, 87, 214, .1);
}
.not-verified-sales-page .find-btn {
    border: 1px solid #dbe4fb !important;
    border-radius: 0 12px 12px 0;
    background: #f5f8ff !important;
    font-weight: 700;
}
.not-verified-sales-page .btn-primary {
    border-color: var(--nvs-blue);
    border-radius: 12px;
    background: var(--nvs-blue);
    font-weight: 750;
    box-shadow: 0 5px 12px rgba(36, 87, 214, .14);
}
.not-verified-sales-page .btn-primary:hover {
    border-color: var(--nvs-blue-dark);
    background: var(--nvs-blue-dark);
}
.not-verified-sales-page .btn-success,
.not-verified-sales-page .btn-danger {
    min-height: 42px;
    border-radius: 10px;
    font-weight: 750;
}
.not-verified-sales-page .list-group {
    gap: 12px;
}
.not-verified-sales-page .list-group-item {
    padding: 17px;
    border: 1px solid var(--nvs-border) !important;
    border-radius: 16px !important;
    background: #fff;
    box-shadow: 0 5px 18px rgba(23, 35, 59, .045);
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}
.not-verified-sales-page .list-group-item:hover {
    border-color: #cbd8fb !important;
    box-shadow: 0 9px 22px rgba(23, 59, 145, .08);
    transform: translateY(-1px);
}
.not-verified-sales-page .badge {
    padding: 6px 9px;
    border-radius: 7px;
    font-weight: 750;
}
.not-verified-sales-page .text-muted { color: var(--nvs-muted) !important; }
.not-verified-sales-page a { cursor: pointer; }
@media (max-width: 640px) {
    .nvs-page-header {
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 18px;
        padding: 16px;
        border-radius: 16px;
    }
    .nvs-heading-icon {
        flex-basis: 42px;
        width: 42px;
        height: 42px;
        border-radius: 13px;
        font-size: 19px;
    }
    .nvs-heading-copy p { font-size: 12px; }
    .nvs-header-count {
        min-width: 64px;
        padding: 8px;
    }
    .nvs-count-value { font-size: 20px; }
    .nvs-count-label { font-size: 10px; }
    .not-verified-sales-page .list-group-item { padding: 13px; }
}
@media (max-width: 420px) {
    .nvs-page-header { flex-wrap: wrap; }
    .nvs-heading-copy { flex-basis: calc(100% - 58px); }
    .nvs-header-count { margin-left: 54px; flex-direction: row; gap: 7px; }
}
</style>
