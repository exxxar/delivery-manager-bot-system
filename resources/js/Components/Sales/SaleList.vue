<script setup>
import SaleForm from "@/Components/Sales/SaleForm.vue";
import Pagination from "@/Components/Pagination.vue";
import SaleFilterForm from '@/components/Sales/SaleFilterForm.vue'
import TaskCard from "@/Components/Sales/TaskCard.vue";

</script>
<template>


    <!-- Быстрый поиск -->
    <input v-model="search" type="text" class="form-control mb-2" placeholder="Поиск по названию...">

    <SaleFilterForm v-on:apply-filters="applyFilters"></SaleFilterForm>

    <ul class="list-group">
        <li v-for="sale in filteredSales" :key="sale.id"
            class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <p class="fw-bold mb-2"><span class="badge bg-primary" v-if="field_visible?.id||false">#{{
                        sale.id
                    }}</span> {{ sale.title }} </p>
                <p class="fw-bold mb-2" style="font-size:14px;" v-if="field_visible?.due_date||true">Дата задания
                    {{ sale.due_date }}</p>
                <p class="fw-bold mb-2" style="font-size:14px;" v-if="field_visible?.sale_date||false">Дата продажи
                    {{ sale.sale_date }}</p>
                <p class="fw-bold mb-2" style="font-size:14px;" v-if="field_visible?.planned_delivery_date||false">
                    Планируемая дата доставки {{ sale.planned_delivery_date }}</p>
                <p class="fw-bold mb-2" style="font-size:14px;" v-if="field_visible?.actual_delivery_date||false">
                    Фактическая дата доставки {{ sale.actual_delivery_date }}</p>
                <small class="text-muted" v-if="field_visible?.status||false">Статус:
                    <span
                        class="badge"
                        v-bind:class="{
                            'bg-warning':sale.status==='pending',
                            'bg-info':sale.status==='assigned',
                            'bg-primary':sale.status==='delivered',
                            'bg-success':sale.status==='completed',
                            'bg-danger':sale.status==='rejected',
                        }">{{ saleStatuses[sale.status] }}</span>
                </small>
                <p class="mb-2" v-if="field_visible?.description||false">{{ sale.description }}</p>
                <p class="mb-2" v-if="field_visible?.quantity||false">Доставляемое число товара <span
                    class="fw-bold">{{ sale.quantity }}</span> ед.</p>
                <p class="mb-2" v-if="field_visible?.total_price||false">Сумма заказа <span
                    class="fw-bold">{{ sale.total_price }}</span> руб.</p>
                <p class="mb-2" v-if="field_visible?.agent_id||false">Агент <span
                    class="fw-bold">{{ sale.agent?.name || sale.agent_id || '-' }}</span></p>
                <p class="mb-2" v-if="field_visible?.customer_id||false">Клиент <span
                    class="fw-bold">{{ sale.customer?.name || sale.customer_id || '-' }}</span></p>
                <p class="mb-2" v-if="field_visible?.supplier_id||false">Поставщик <span
                    class="fw-bold">{{ sale.supplier?.name || sale.supplier_id || '-' }}</span></p>
                <p class="mb-2" v-if="field_visible?.created_by_id||false">Администратор <span
                    class="fw-bold">{{ sale.creator?.name || sale.created_by_id || '-' }}</span></p>
                <p class="mb-2" v-if="field_visible?.product_id||false">Товар <span
                    class="fw-bold">{{ sale.product?.name || sale.product_id || '-' }}</span></p>
            </div>

            <!-- Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm" type="button"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <template v-if="forSelect">
                        <li><a class="dropdown-item" href="#" @click.prevent="$emit('select', sale)">Выбрать</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    </template>
                    <template v-if="!forSelect">
                        <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="openView(sale)">Просмотреть</a>
                        </li>
                        <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="openEdit(sale)">Редактировать</a>
                        </li>
                        <li v-if="sale.status!=='completed'"><a class="dropdown-item text-success"
                                                                href="javascript:void(0)"

                                                                @click.prevent="openConfirmDeal(sale)">Подтвердить
                            сделку</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="javascript:void(0)"
                               @click.prevent="confirmDelete(sale)">Удалить</a></li>
                        <li><a class="dropdown-item text-danger" href="javascript:void(0)"
                               @click.prevent="cancelDeal(sale)">Отменить
                            сделку</a></li>
                    </template>
                </ul>
            </div>
        </li>
    </ul>

    <Pagination
        v-if="salesStore.items.length > 0"
        :pagination="salesStore.pagination"
        @page-changed="fetchDataByUrl"
    />


    <!-- Сообщение если список пуст -->
    <div v-if="salesStore.items.length === 0" class="alert alert-info mt-3">
        На текущий момент у вас нет продаж.
    </div>


    <!-- Модалка редактирования -->
    <div class="modal fade" id="editSaleModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Редактирование задания</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SaleForm v-if="selectedSale" :initialData="selectedSale" @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>


    <!-- Модалка просмотра -->
    <div class="modal fade" id="viewSaleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Карточка задания</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <TaskCard
                        v-if="selectedSale"
                        :task="selectedSale"></TaskCard>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка подтверждения сделки -->
    <div class="modal fade" id="confirmDealModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Подтверждение сделки</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="confirmDeal">
                        <div class="form-floating mb-2">
                            <input v-model="dealForm.sale_date" type="date" class="form-control" id="sale_date"
                                   required>
                            <label for="sale_date">Дата сделки</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input v-model="dealForm.quantity" type="number" class="form-control" id="quantity"
                                   placeholder="Количество" required>
                            <label for="quantity">Количество</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input v-model="dealForm.total_price" type="number" step="0.01" class="form-control"
                                   id="total_price" placeholder="Сумма" required>
                            <label for="total_price">Сумма сделки</label>
                        </div>
                        <button type="submit" class="btn btn-success w-100 p-3">Подтвердить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


</template>

<script>
import axios from 'axios'

import {useSalesStore} from '@/stores/sales'
import {useAgentsStore} from "@/stores/agents";
import {useUsersStore} from "@/stores/users";
import {useModalStore} from "@/stores/utillites/useConfitmModalStore";

export default {
    name: 'SaleList',
    props: ["forSelect", "adminId", "agentId", "productId", "customerId", "supplierId"],
    data() {
        return {
            field_visible: null,
            sales: [],
            search: '',
            userStore: useUsersStore(),
            salesStore: useSalesStore(),
            agentStore: useAgentsStore(),
            modalStore: useModalStore(),
            selectedSale: null,
            saleStatuses: {
                pending: "В ожидании",
                assigned: "Назначено",
                completed: "Завершено",
                rejected: "Отклонено",
                delivered: "Доставляется"
            },
            dealForm: {
                sale_date: new Date().toISOString().split('T')[0],
                quantity: 0,
                total_price: 0
            }
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
        filteredSales() {
            return this.salesStore.items.filter(s => s.title.toLowerCase().includes(this.search.toLowerCase()))
        }
    },
    created() {
        this.salesStore.setFilters({
            created_by_id: this.adminId || null,
            customer_id: this.customerId || null,
            product_id: this.productId || null,
            supplier_id: this.supplierId || null,
        })

        if ((this.user?.role || 0) >= 3)
            this.salesStore.fetchFiltered()
        else
            this.salesStore.selfSalesFiltered()
    },
    methods: {
        async fetchData(page = 1) {
            await this.salesStore.fetchAllByPage(page)

            const editModal = bootstrap.Modal.getInstance(document.getElementById('editSaleModal'))
            if (editModal)
                editModal.hide()

            const confirmDealModal = bootstrap.Modal.getInstance(document.getElementById('confirmDealModal'))
            if (confirmDealModal)
                confirmDealModal.hide()
        },
        async fetchDataByUrl(url) {
            await this.salesStore.fetchByUrl(url)
        },

        openEdit(sale) {
            this.selectedSale = null
            this.$nextTick(() => {
                this.selectedSale = sale
                new bootstrap.Modal(document.getElementById('editSaleModal')).show()
            })

        },
        confirmDelete(sale) {
            this.selectedSale = sale
            this.modalStore.open(
                `Вы уверены, что хотите удалить ${this.selectedSale?.title}?`,
                () => this.salesStore.deleteSale(this.selectedSale.id),
                () => this.modalStore.close()
            )
        },
        async deleteSale() {
            try {
                await this.salesStore.deleteSale()
                bootstrap.Modal.getInstance(document.getElementById('deleteSaleModal')).hide()
            } catch (error) {
                console.error('Ошибка удаления продажи:', error)
            }
        },
        openView(sale) {
            this.selectedSale = null
            this.$nextTick(() => {
                this.selectedSale = sale
                new bootstrap.Modal(document.getElementById('viewSaleModal')).show()
            })
        },
        openConfirmDeal(sale) {
            this.selectedSale = null
            this.$nextTick(() => {
                this.selectedSale = sale
                this.dealForm.quantity = sale.quantity
                this.dealForm.id = sale.id
                this.dealForm.total_price = sale.total_price
                new bootstrap.Modal(document.getElementById('confirmDealModal')).show()
            })


        },
        async confirmDeal() {
            this.dealForm.id = this.selectedSale.id
            await this.salesStore.confirmDeal(this.dealForm)
            bootstrap.Modal.getInstance(document.getElementById('confirmDealModal')).hide()

        },
        async cancelDeal(sale) {
            try {
                await this.salesStore.cancelDeal(sale)
            } catch (error) {
                console.error('Ошибка отмены сделки:', error)
            }
        },

        applyFilters(filters) {
            this.field_visible = filters.field_visible
            let size = filters.size || 30
            let page = filters.page || 1
            const only_my_tasks = filters.only_my_tasks || (this.user?.role || 0) < 3
            delete filters.field_visible
            delete filters.only_my_tasks
            this.salesStore.setFilters(filters)
            if (only_my_tasks)
                this.salesStore.selfSalesFiltered(page, size)
            else
                this.salesStore.fetchFiltered(page, size)
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
