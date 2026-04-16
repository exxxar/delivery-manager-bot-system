<script setup>
import SaleForm from "@/Components/Sales/SaleForm.vue";
import Pagination from "@/Components/Pagination.vue";
import SaleFilterForm from '@/components/Sales/SaleFilterForm.vue'
import TaskCard from "@/Components/Sales/TaskCard.vue";
import DealForm from "@/Components/Sales/Forms/DealForm.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
</script>
<template>

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

               {{ sale.title }}
           </p>

            <p class="mb-2 small" >
                Сумма заказа
                <span class="fw-bold">{{ sale.total_price }}</span> руб.
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

    },
    created() {

        this.salesStore.fetchNotVerified()
    },
    methods: {
        approve(item){
            item.verified_at = new Date()
            this.salesStore.approveSale(item.id).then(()=>{
                this.alertStore.show("Продажа успешно подтверждена!");
            }).catch(()=>{
                item.verified_at = null
                this.alertStore.show("Ошибка подтверждения!","error");
            })
        },
        decline(item){
            this.salesStore.declineSale(item.id).then(()=>{
                this.alertStore.show("Продажа отклонена. Администратор оповещен!");
            }).catch(()=>{
                this.alertStore.show("Ошибка отклонения продажи!","error");
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
</style>
