<script setup>
import SaleForm from "@/Components/Sales/SaleForm.vue";
import Pagination from "@/Components/Pagination.vue";
import SaleFilterForm from '@/components/Sales/SaleFilterForm.vue'
</script>
<template>


    <!-- Быстрый поиск -->
    <input v-model="search" type="text" class="form-control mb-2" placeholder="Поиск по названию...">

    <!-- Кнопка фильтра -->
    <div class="mb-2">
        <button class="btn btn-secondary" @click="openFilter">Фильтр</button>

        <!-- Dropdown сортировки -->
        <div class="dropdown d-inline-block ms-2">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Сортировка: {{ salesStore.sort.field }} ({{ salesStore.sort.direction }})
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" @click="changeSort('title')">Название</a></li>
                <li><a class="dropdown-item" @click="changeSort('status')">Статус</a></li>
                <li><a class="dropdown-item" @click="changeSort('sale_date')">Дата продажи</a></li>
                <li><a class="dropdown-item" @click="changeSort('total_price')">Сумма</a></li>
            </ul>
        </div>
    </div>

    <ul class="list-group">
        <li v-for="sale in filteredSales" :key="sale.id"
            class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-bold">{{ sale.title }}</div>
                <div class="fw-bold" style="font-size:14px;">Дата задания {{ sale.due_date }}</div>
                <small class="text-muted">Статус: {{ saleStatuses[sale.status] }}</small>
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
                    <li><a class="dropdown-item" href="#" @click.prevent="openEdit(sale)">Редактировать</a></li>
                    <li><a class="dropdown-item text-danger" href="#"
                           @click.prevent="confirmDelete(sale)">Удалить</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="openView(sale)">Просмотреть</a></li>
                    <li><a class="dropdown-item text-success" href="#" @click.prevent="openConfirmDeal(sale)">Подтвердить
                        сделку</a></li>
                    <li><a class="dropdown-item text-warning" href="#" @click.prevent="cancelDeal(sale)">Отменить
                        сделку</a></li>
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
    <div class="modal fade" id="newSaleModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Создание задания</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SaleForm @saved="fetchData"/>
                </div>
            </div>
        </div>
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
                        <div class="form-floating mb-3">
                            <input v-model="dealForm.sale_date" type="date" class="form-control" id="sale_date"
                                   required>
                            <label for="sale_date">Дата сделки</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input v-model="dealForm.quantity" type="number" class="form-control" id="quantity"
                                   placeholder="Количество" required>
                            <label for="quantity">Количество</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input v-model="dealForm.total_price" type="number" step="0.01" class="form-control"
                                   id="total_price" placeholder="Сумма" required>
                            <label for="total_price">Сумма сделки</label>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Подтвердить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка фильтрации -->
    <div class="modal fade" id="saleFilterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Фильтры</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SaleFilterForm @apply-filters="applyFilters"/>
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
    props: ["forSelect","adminId", "agentId", "productId", "customerId", "supplierId"],
    data() {
        return {
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
        },
        async fetchDataByUrl(url) {
            await this.salesStore.fetchByUrl(url)
        },
        addSale() {
            new bootstrap.Modal(document.getElementById('newSaleModal')).show()
        },
        openEdit(sale) {
            this.selectedSale = sale
            new bootstrap.Modal(document.getElementById('editSaleModal')).show()
        },
        confirmDelete(sale) {
            this.selectedSale = sale
            this.modalStore.open(
                `Вы уверены, что хотите удалить ${this.selectedSale?.title}?`,
                () =>  this.salesStore.deleteSale(this.selectedSale.id),
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
            this.selectedSale = sale
            new bootstrap.Modal(document.getElementById('viewSaleModal')).show()
        },
        openConfirmDeal(sale) {
            this.modalStore.open(
                `Подтвердить сделку ${this.selectedSale?.title}?`,
                () =>  this.salesStore.confirmDeal(this.selectedSale),
                () => this.modalStore.close()
            )

            new bootstrap.Modal(document.getElementById('confirmDealModal')).show()
        },
        async confirmDeal() {
            try {
                await this.salesStore.confirmDeal()
                bootstrap.Modal.getInstance(document.getElementById('confirmDealModal')).hide()
            } catch (error) {
                console.error('Ошибка подтверждения сделки:', error)
            }
        },
        async cancelDeal(sale) {
            try {
                await this.salesStore.cancelDeal(sale)
            } catch (error) {
                console.error('Ошибка отмены сделки:', error)
            }
        },
        openFilter() {
            new bootstrap.Modal(document.getElementById('saleFilterModal')).show()
        },
        applyFilters(filters) {
            this.salesStore.setFilters(filters)
            this.salesStore.fetchFiltered()
        },
        changeSort(field) {
            const current = this.salesStore.sort
            const direction = current.field === field && current.direction === 'asc' ? 'desc' : 'asc'
            this.salesStore.setSort(field, direction)
            this.salesStore.fetchFiltered()
        }
    }
}
</script>
