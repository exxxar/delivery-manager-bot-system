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

    <ul class="list-group">
        <li

            v-bind:class="{'border-primary': selection.indexOf(sale.id)!==-1}"
            v-for="sale in filteredSales" :key="sale.id"
            class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <p class="fw-bold mb-2" @click="toggleSelection(sale.id)">
                    <span class="badge"
                          v-bind:class="{'bg-warning':!sale.sale_date, 'bg-success':sale.sale_date}"
                          v-if="sale.payment_type===0">
                        <i class="fa-solid fa-money-bill"></i>
                        <i class="fa-solid fa-clock"
                           style="margin-left:8px;"
                           v-if="!sale.sale_date"></i>

                    </span>
                    <span class="badge bg-success"
                          v-if="sale.payment_type===1"><i class="fa-solid fa-credit-card"></i></span>


                    <span class="badge bg-primary" v-if="field_visible?.id||false">#{{
                            sale.id
                        }}</span> {{ sale.title }} </p>
                <p class="fw-bold mb-0 small" style="font-size:14px;" v-if="field_visible?.due_date||true">Дата задания
                    {{ sale.due_date || 'не указана' }}</p>
                <p class="fw-bold mb-0 small" style="font-size:14px;" v-if="field_visible?.sale_date||true">Дата продажи
                    {{ sale.sale_date || 'не указана' }}</p>
                <p class="fw-bold mb-2" style="font-size:14px;" v-if="field_visible?.payment_type||false">
                    Тип оплаты
                    <span v-if="sale.payment_type===0">Наличный расчет</span>
                    <span v-if="sale.payment_type===1">Безналичный расчет</span>
                </p>
                <p class="fw-bold mb-2" style="font-size:14px;" v-if="field_visible?.actual_delivery_date||false">
                    Фактическая дата доставки {{ sale.actual_delivery_date || 'не указана' }}</p>
                <small class="text-muted" v-if="field_visible?.status||true">Статус:
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
                <p class="mb-2" v-if="field_visible?.description||true">{{ sale.description }}</p>
                <p class="mb-2" v-if="field_visible?.quantity||false">Доставляемое число товара <span
                    class="fw-bold">{{ sale.quantity || 'не указана' }}</span> ед.</p>
                <p class="mb-2" v-if="field_visible?.total_price||false">Сумма заказа <span
                    class="fw-bold">{{ sale.total_price }}</span> руб.</p>
                <p class="mb-2" v-if="field_visible?.agent_id||true">Администратор <span
                    class="fw-bold">{{ sale.agent?.name || sale.agent_id || '-' }}</span></p>
                <p class="mb-2" v-if="field_visible?.customer_id||false">Клиент <span
                    class="fw-bold">{{ sale.customer?.name || sale.customer_id || '-' }}</span></p>
                <p class="mb-2" v-if="field_visible?.supplier_id||false">Поставщик <span
                    class="fw-bold">{{ sale.supplier?.name || sale.supplier_id || '-' }}</span></p>
                <p class="mb-2" v-if="field_visible?.created_by_id||false">Старший администратор <span
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
                    </template>
                    <template v-if="!forSelect">
                        <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="openView(sale)">Просмотреть</a>
                        </li>
                        <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="openEdit(sale)">Редактировать</a>
                        </li>
                        <li v-if="sale.status!=='completed'"><a class="dropdown-item text-success"
                                                                href="javascript:void(0)"

                                                                @click.prevent="openConfirmDeal(sale)">Подтвердить
                            доставку</a></li>
                        <li v-if="sale.payment_type===0&&!sale.sale_date"><a class="dropdown-item text-success"
                                                                             href="javascript:void(0)"

                                                                             @click.prevent="openConfirmPayment(sale)">Подтвердить
                            оплату</a></li>

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
                            <label for="sale_date">Дата</label>
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


    <!-- Модалка подтверждения сделки -->
    <div class="modal fade" id="paymentConfirmForm" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Подтверждение оплаты</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="confirmPayment">
                        <div class="form-floating mb-2">
                            <select class="form-select"
                                    v-model="paymentConfirmForm.payment_type"
                                    id="payment-type" aria-label="Floating label select example">
                                <option :value="'0'">Наличный расчет</option>
                                <option :value="'1'">Безналичный расчет</option>
                            </select>
                            <label for="payment-type">Тип оплаты</label>
                        </div>

                        <template v-if="paymentConfirmForm.payment_type==='1'">
                            <h6>Фотография чека</h6>
                            <div class="form-floating mb-2">

                                <input
                                    type="file"
                                    class="form-control"
                                    @change="onFileChange"
                                    accept=".jpg,.png,.pdf"
                                    :required="!paymentConfirmForm.receipt_is_lost"
                                />
                                <label for="payment-type">Прикрепить</label>
                            </div>
                            <div
                                class="form-check form-switch mb-2">
                                <input
                                    v-model="paymentConfirmForm.receipt_is_lost"
                                    class="form-check-input" type="checkbox" role="switch" id="need_automatic_naming">
                                <label class="form-check-label" for="need_automatic_naming">Чек был утрачен
                                </label>
                            </div>

                        </template>
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
            paymentConfirmForm: {
                file: null,
                payment_type: 0,
                receipt_is_lost: false,
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

        this.salesStore.fetchFiltered()
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
        onFileChange(e) {
            this.paymentConfirmForm.file = e.target.files[0]
        },
        async fetchData(page = 1) {
            await this.salesStore.fetchFiltered()

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
            this.$nextTick(() => {
                this.selectedSale = sale
                this.dealForm.quantity = sale.quantity
                this.dealForm.id = sale.id
                this.dealForm.total_price = sale.total_price
                new bootstrap.Modal(document.getElementById('confirmDealModal')).show()
            })


        },
        async confirmPayment() {
            this.paymentConfirmForm.id = this.selectedSale.id
            await this.salesStore.confirmPayment(this.paymentConfirmForm)
            bootstrap.Modal.getInstance(document.getElementById('paymentConfirmForm')).hide()

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
