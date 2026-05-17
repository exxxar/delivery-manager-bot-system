<script setup>
import SaleForm from "@/Components/Sales/SaleForm.vue";
import Pagination from "@/Components/Pagination.vue";
import SaleFilterForm from '@/components/Sales/SaleFilterForm.vue'
import TaskCard from "@/Components/Sales/TaskCard.vue";
import DealForm from "@/Components/Sales/Forms/DealForm.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
</script>
<template>


    <!-- Быстрый поиск -->
    <input v-model="search" type="text" class="form-control mb-2" placeholder="Поиск по названию...">



    <SaleFilterForm v-on:apply-filters="applyFilters"></SaleFilterForm>
    <div class="d-flex justify-content-between my-2" >
        <div>
            <a href="javascript:void(0)"
               @click="selectAll"
               style="font-size:12px;"
               class="small fw-bold">Выделить все</a>
            <template v-if="selection.length>0">
                <a href="javascript:void(0)"
                   @click="acceptAll"
                   style="font-size:12px;"
                   class="small text-danger mx-2 fw-bold">Подтвердить заявки ({{ selection.length }})</a>
            </template>
        </div>

        <span
            class="fw-bold small "
            v-if="salesStore.pagination">Всего {{salesStore.pagination.total}} ед.</span>
    </div>

    <template v-if="filteredBadSales.length>0">
        <h6 class="fw-bold my-3"><i class="fa-solid fa-triangle-exclamation text-danger"></i> Заявки, в которых есть неточности по дате</h6>
        <ul class="list-group">
            <li

                v-bind:class="{'border-primary': selection.indexOf(sale.id)!==-1, 'bg-danger-subtle':sale.status === 'completed'&&(!sale.sale_date||!sale.actual_delivery_date)}"

                v-for="sale in filteredBadSales" :key="sale.id"
                class="list-group-item d-flex justify-content-between align-items-start ">
                <SaleCard
                    :sale="sale"
                    :field_visible="field_visible"
                    :saleStatuses="saleStatuses"
                    @toggle-selection="toggleSelection"
                />

                <!-- Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light text-primary" type="button"
                            data-bs-toggle="dropdown">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <template v-if="forSelect">
                            <li><a class="dropdown-item" href="#" @click.prevent="$emit('select', sale)">Выбрать</a>
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
                                оплату и доставка</a></li>
                            <!--                        <li v-if="sale.payment_type===0&&!sale.sale_date"><a class="dropdown-item text-success"
                                                                                                         href="javascript:void(0)"

                                                                                                         @click.prevent="openConfirmPayment(sale)">Подтвердить
                                                        оплату</a></li>-->

                            <template v-if="sale.payment_document_name">
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-success"
                                       href="javascript:void(0)"
                                       @click.prevent="sendPaymentDocumentToTg(sale.id)">Отправить документ в чат</a></li>
                            </template>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)"
                                   @click.prevent="confirmDelete(sale)">Удалить</a></li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)"
                                   @click.prevent="confirmCancelDeal(sale)">Отменить
                                сделку</a></li>
                        </template>
                    </ul>
                </div>
            </li>
        </ul>
        <h6 class="fw-bold my-3"><i class="fa-solid fa-triangle-exclamation text-danger"></i> Все заявки</h6>
    </template>

    <div class="container-fluid">
        <div class="row g-3">

            <div
                v-for="sale in filteredSales"
                :key="sale.id"
                class="col-12 col-md-6 col-xxl-4"
            >

                <div
                    class="card shadow-sm h-100 sale-card position-relative"

                    :class="{
                    'border-primary border-3': selection.indexOf(sale.id)!==-1,
                    'bg-danger-subtle':
                        sale.status === 'completed' &&
                        (!sale.sale_date || !sale.actual_delivery_date)
                }"
                >

                    <!-- Dropdown -->
                    <div class="dropdown position-absolute top-0 end-0 m-2 z-3">

                        <button
                            class="btn btn-sm btn-light border"
                            type="button"
                            data-bs-toggle="dropdown"
                        >
                            <i class="fas fa-bars text-primary"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <template v-if="forSelect">

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="$emit('select', sale)"
                                    >
                                        Выбрать
                                    </a>
                                </li>

                            </template>

                            <template v-if="!forSelect">

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="javascript:void(0)"
                                        @click.prevent="openView(sale)"
                                    >
                                        Просмотреть
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item"
                                        href="javascript:void(0)"
                                        @click.prevent="openEdit(sale)"
                                    >
                                        Редактировать
                                    </a>
                                </li>

                                <li v-if="sale.status !== 'completed'">
                                    <a
                                        class="dropdown-item text-success"
                                        href="javascript:void(0)"
                                        @click.prevent="openConfirmDeal(sale)"
                                    >
                                        Подтвердить оплату и доставку
                                    </a>
                                </li>

                                <template v-if="sale.payment_document_name">

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <li>
                                        <a
                                            class="dropdown-item text-success"
                                            href="javascript:void(0)"
                                            @click.prevent="sendPaymentDocumentToTg(sale.id)"
                                        >
                                            Отправить документ в чат
                                        </a>
                                    </li>

                                </template>

                                <li>
                                    <hr class="dropdown-divider">
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item text-danger"
                                        href="javascript:void(0)"
                                        @click.prevent="confirmDelete(sale)"
                                    >
                                        Удалить
                                    </a>
                                </li>

                                <li>
                                    <a
                                        class="dropdown-item text-danger"
                                        href="javascript:void(0)"
                                        @click.prevent="confirmCancelDeal(sale)"
                                    >
                                        Отменить сделку
                                    </a>
                                </li>

                            </template>

                        </ul>
                    </div>

                    <!-- Контент карточки -->
                    <div class="card-body">

                        <SaleCard
                            :sale="sale"
                            :field_visible="field_visible"
                            :saleStatuses="saleStatuses"
                            @toggle-selection="toggleSelection"
                        />

                    </div>

                </div>
            </div>

        </div>
    </div>

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
                    <DealForm
                        v-if="selectedSale"
                        v-model="dealForm" @callback="confirmDeal" />
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
import {useAlertStore} from "@/stores/utillites/useAlertStore";

export default {
    name: 'SaleList',
    props: ["forSelect", "adminId", "agentId", "productId", "customerId", "supplierId"],
    data() {
        return {
            selection: [],
            field_visible: null,
            sales: [],
            search: '',
            alertStore: useAlertStore(),
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
                sale_date: null,
                actual_delivery_date: null,
                quantity: 0,
                total_price: 0,
                files: [] ,
                additional_comment: null,
                payment_document_name: null,
                payment_type: '0',
                receipt_is_lost: false,
                same_sale_delivery_date: true,
                need_additional_comment: false,
            }
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
        filteredSales() {
            return this.salesStore.items.filter(s => s.title.toLowerCase().includes(this.search.toLowerCase()))
        },
        filteredBadSales(){
            return this.salesStore.bad_items ?? []
        }
    },
    created() {
        this.salesStore.setFilters({
            created_by_id: this.adminId || null,
            customer_id: this.customerId || null,
            product_id: this.productId || null,
            supplier_id: this.supplierId || null,
        })

        this.salesStore.fetchFiltered()
        this.salesStore.fetchBadData()
    },
    methods: {
        async sendPaymentDocumentToTg(id) {
            await this.salesStore.sendPaymentDocumentToTg(id).then(() => {
                this.alertStore.show("Чек отправлен вам в телеграм бот!");
            })


        },

        selectAll() {
            if (this.selection.length === 0)
                this.salesStore.items.forEach(i => {
                    if (this.selection.indexOf(i.id) === -1)
                        this.selection.push(i.id)
                })
            else
                this.selection = []
        },
        toggleSelection(id) {
            let index = this.selection.findIndex(i => i === id)
            if (index === -1)
                this.selection.push(id)
            else
                this.selection.splice(index, 1)
        },


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
        confirmCancelDeal(sale) {
            this.selectedSale = sale
            this.modalStore.open(
                `Вы уверены, что хотите отменить сделку ${this.selectedSale?.title}?`,
                () => this.salesStore.cancelDeal(sale),
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
        openConfirmPayment(sale) {
            this.selectedSale = null
            this.$nextTick(() => {
                this.selectedSale = sale
                this.paymentConfirmForm.payment_type = sale.payment_type
                new bootstrap.Modal(document.getElementById('paymentConfirmForm')).show()
            })
        },
        acceptAll() {
            this.modalStore.open(
                `Вы уверены, что хотите подтвердить все выбранные заявки? Будет подтвержден факт доставки и факт оплаты`,
                () => {
                    this.salesStore.acceptAll(this.selection).then(() => {
                        this.salesStore.fetchFiltered()
                    })
                    this.selection = []
                },
                () => {
                    this.modalStore.close()
                    this.selection = []
                }
            )


        },
        openConfirmDeal(sale) {
            this.selectedSale = null
            this.salesStore.progress = 0
            this.$nextTick(() => {
                this.selectedSale = sale
                this.dealForm.quantity = sale.quantity
                this.dealForm.id = sale.id
                this.dealForm.payment_type = ''+sale.payment_type
                this.dealForm.total_price = sale.total_price
                this.dealForm.payment_document_name = sale.payment_document_name
                new bootstrap.Modal(document.getElementById('confirmDealModal')).show()
            })


        },
       /* async confirmPayment() {
            this.paymentConfirmForm.id = this.selectedSale.id
            await this.salesStore.confirmPayment(this.paymentConfirmForm)
            bootstrap.Modal.getInstance(document.getElementById('paymentConfirmForm')).hide()

        },*/
        async confirmDeal() {
            this.dealForm.id = this.selectedSale.id
            await this.salesStore.confirmDealAndPayment(this.dealForm).then(()=>{
                this.dealForm.files = []
                this.dealForm.actual_delivery_date = ''
                this.dealForm.quantity = 1
                this.dealForm.total_price = 0
                this.dealForm.same_sale_delivery_date = true
                this.dealForm.need_additional_comment = false
                this.dealForm.additional_comment = ''
            })
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
