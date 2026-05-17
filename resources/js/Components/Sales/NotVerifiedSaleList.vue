<script setup>
import SaleForm from "@/Components/Sales/SaleForm.vue";
import Pagination from "@/Components/Pagination.vue";
import SaleFilterForm from '@/components/Sales/SaleFilterForm.vue'
import TaskCard from "@/Components/Sales/TaskCard.vue";
import DealForm from "@/Components/Sales/Forms/DealForm.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
</script>
<template>

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

        <div class="form-floating mb-2">
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
               class="fw-bold text-decoration-underline">{{ sale.creator?.name || sale.created_by_id || '-' }}</a>)
           </p>

            <p class="mb-2 small" >
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
                    class="btn btn-success">Да</button>
                <button
                    :disabled="sale.verified_at"
                    @click="decline(sale)"
                    class="btn btn-danger">Нет</button>
            </div>
        </li>
    </ul>

    <template v-if="salesStore.not_verified_pagination?.current_page<salesStore.not_verified_pagination?.last_page">
        <button
            :disabled="salesStore.loading"
            type="button"
            class="btn btn-primary w-100 p-3 small"
            @click="fetchData(salesStore.not_verified_pagination.current_page+1)">
            Загрузить еще <span class="fw-bold small">{{salesStore.not_verified_pagination.current_page}}</span> /
            <span class="fw-bold small">{{salesStore.not_verified_pagination.last_page}}</span>
        </button>
    </template>

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
            alertStore: useAlertStore(),
            actual_delivery_date_filter: false,
            processingQueue: 0,
            processingTotal: 0,

            filters:{
                date_to:null,
                date_from:null,
            },
            size:50,
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
        approve(item) {

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
                        "Продажа успешно подтверждена!"
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

            this.salesStore.not_verified_items =
                this.salesStore.not_verified_items.filter(
                    sale => sale.id !== item.id
                )

            this.processingQueue++
            this.processingTotal++

            this.salesStore.declineSale(item.id)

                .then(() => {

                    this.alertStore.show(
                        "Продажа отклонена!"
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
                date_to: this.filters.date_to || null
            })
        },



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
