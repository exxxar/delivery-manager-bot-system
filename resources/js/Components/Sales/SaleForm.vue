<script setup>
const today = new Date().toISOString().split('T')[0]
</script>

<template>

    <form @submit.prevent="submitForm">

        <template v-if="tab==='main'">

            <div
                class="form-check form-switch mb-2">
                <input
                    v-model="form.need_automatic_naming"
                    class="form-check-input" type="checkbox" role="switch" id="need_automatic_naming">
                <label class="form-check-label" for="need_automatic_naming">Автоматическое название и описание
                    <span class="fw-bold text-primary" v-if="form.need_automatic_naming">включено</span>
                    <span class="fw-bold text-primary" v-else>выключено</span>
                </label>
            </div>


            <template v-if="!form.need_automatic_naming">
                <!-- Название задания -->
                <div class="form-floating mb-2">
                    <input v-model="form.title" type="text" class="form-control" id="title" placeholder="Название"
                           required>
                    <label for="title">Название задания</label>
                </div>

                <!-- Описание -->
                <div class="form-floating mb-2">
                <textarea v-model="form.description" class="form-control" id="description" placeholder="Описание"
                          style="height: 120px" required></textarea>
                    <label for="description">Описание задания</label>
                </div>
            </template>

            <!-- Поставщик -->
            <div class="input-group mb-2">
                <div class="form-floating flex-grow-1">
                    <input
                        required
                        type="text" class="form-control" id="supplier" :value="supplierName" placeholder="Поставщик"
                        readonly>
                    <label for="supplier">Поставщик</label>
                </div>
                <button type="button" class="btn btn-outline-light text-primary find-btn" @click="tab='supplier'">
                    Выбрать
                </button>
            </div>

            <!-- Продукт -->
            <div class="input-group mb-2">
                <div class="form-floating flex-grow-1">
                    <input type="text"
                           required
                           class="form-control" id="product" :value="productName" placeholder="Продукт"
                           readonly>
                    <label for="product">Продукт</label>
                </div>
                <button type="button"
                        :disabled="form.supplier_id == null"
                        class="btn btn-outline-light text-primary find-btn" @click="tab='product'">Выбрать
                </button>
            </div>

            <template v-if="form.supplier_id && form.product_id">
                <!-- Количество -->
                <div class="form-floating mb-2">
                    <input
                        required
                        v-model="form.quantity" type="number" class="form-control" id="quantity"
                        placeholder="Количество">
                    <label for="quantity">Количество</label>
                </div>

                <!-- Сумма -->
                <div class="form-floating mb-2">
                    <input v-model="form.total_price"
                           required
                           type="number" step="0.01" class="form-control" id="total_price"
                           placeholder="Сумма">
                    <label for="total_price">Сумма сделки</label>
                </div>
            </template>


            <div class="form-floating mb-2">
                <select class="form-select"
                        v-model="form.payment_type"
                        id="payment-type" aria-label="Floating label select example">
                    <option :value="'0'">Наличный расчет</option>
                    <option :value="'1'">Безналичный расчет</option>
                </select>
                <label for="payment-type">Тип оплаты</label>
            </div>

            <template v-if="form.payment_type==='1'">
                <h6>Фотография чека</h6>
                <div class="form-floating mb-2">

                    <input
                        type="file"
                        class="form-control"
                        @change="onFileChange"
                        accept=".jpg,.png,.pdf"
                        :required="!form.receipt_is_lost"
                    />
                    <label for="payment-type">Прикрепить</label>
                </div>

                <div
                    class="form-check form-switch mb-2">
                    <input
                        v-model="form.receipt_is_lost"
                        class="form-check-input" type="checkbox" role="switch" id="need_automatic_naming">
                    <label class="form-check-label" for="need_automatic_naming">Чек был утрачен
                    </label>
                </div>

            </template>

            <!-- Статус -->
            <div class="form-floating mb-2" v-if="isEdit">
                <select v-model="form.status" class="form-select" id="status" required>
                    <option value="pending">Ожидает</option>
                    <option value="assigned">Назначено</option>
                    <option value="delivered">Доставляется</option>
                    <option value="completed">Завершено</option>
                    <option value="rejected">Отклонено</option>
                </select>
                <label for="status">Статус</label>
            </div>

            <div
                class="form-check form-switch mb-2">
                <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="can_add_past_assignments"
                    :id="`can-add-past-tasks`"
                />
                <label class="form-check-label" :for="`can-add-past-tasks`">
                    Разрешить вносить за прошедшие дни
                </label>
            </div>

            <!-- Дата задания -->
            <div class="form-floating mb-2">
                <input v-model="form.due_date"
                       required
                       :min="can_add_past_assignments?null:today"
                       type="date" class="form-control" id="due_date">
                <label for="due_date">Дата задания</label>
            </div>

            <!--            <template v-if="isEdit">
                            &lt;!&ndash; Дата сделки &ndash;&gt;
                            <div class="form-floating mb-2">
                                <input v-model="form.sale_date" type="date" class="form-control" id="sale_date">
                                <label for="sale_date">Дата сделки</label>
                            </div>

                            &lt;!&ndash; Дата сделки &ndash;&gt;
                            <div class="form-floating mb-2">
                                <input v-model="form.actual_delivery_date" type="date" class="form-control" id="sale_date">
                                <label for="sale_date">Фактическая дата доставки</label>
                            </div>


                        </template>-->

            <template v-if="user?.role>=3">
                <p class="alert alert-info mb-2">Назначение ответственного по данной задаче</p>
                <!-- Агент -->
                <div class="input-group mb-2">
                    <div class="form-floating flex-grow-1">
                        <input type="text" class="form-control" id="agent" :value="agentName"
                               placeholder="Младший администратор"
                               readonly>
                        <label for="agent">Администратор</label>
                    </div>
                    <button type="button" class="btn btn-outline-light text-primary find-btn" @click="tab='agent'">
                        Выбрать
                    </button>
                </div>

            </template>

            <!--
                        &lt;!&ndash; Клиент &ndash;&gt;
                        <div class="input-group mb-2">
                            <div class="form-floating flex-grow-1">
                                <input type="text" class="form-control" id="customer" :value="customerName" placeholder="Клиент"
                                       readonly>
                                <label for="customer">Клиент</label>
                            </div>
                            <button type="button" class="btn btn-outline-light text-primary find-btn" @click="tab='customer'">Выбрать
                            </button>
                        </div>
            -->


            <template v-if="!isEdit">
                <div
                    class="form-check form-switch mb-2">
                    <input
                        v-model="form.is_already_delivered"
                        class="form-check-input" type="checkbox" role="switch" id="is_already_delivered">
                    <label class="form-check-label" for="is_already_delivered">Товар уже доставлен
                    </label>
                </div>
            </template>

            <!-- Кнопка -->
            <button
                :disabled="spent_time>0"
                type="submit" class="btn btn-primary w-100 p-3">
                <span v-if="spent_time>0">{{ spent_time }} сек.</span>
                <span v-else>
                      {{ isEdit ? 'Сохранить изменения' : 'Создать задание' }}
                </span>
            </button>
        </template>

        <template v-if="tab==='agent'">
            <button
                @click="tab='main'"
                class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
            </button>
            <AgentList :for-select="true" @select="selectAgent"/>
        </template>

        <template v-if="tab==='customer'">
            <button
                @click="tab='main'"
                class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
            </button>
            <CustomerList :for-select="true" @select="selectCustomer"/>
        </template>

        <template v-if="tab==='supplier'">
            <button
                @click="tab='main'"
                class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
            </button>
            <SupplierListGroup :for-select="true" @select="selectSupplier"/>
        </template>

        <template v-if="tab==='product'">
            <button
                @click="tab='main'"
                class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
            </button>
            <ProductList :for-select="true"
                         :filters="product_filters"
                         @select="selectProduct"/>
        </template>
    </form>


</template>

<script>

import AgentList from '../Agents/AgentList.vue'
import CustomerList from '../Customers/CustomerList.vue'
import SupplierListGroup from '../Suppliers/SupplierList.vue'
import ProductList from '../Products/ProductList.vue'
import {useUsersStore} from "@/stores/users";
import {useSalesStore} from "@/stores/sales";
import {startTimer, checkTimer, getSpentTimeCounter} from "@/utilites/commonMethods.js";
import {useAlertStore} from "@/stores/utillites/useAlertStore";

export default {
    name: 'SaleForm',
    components: {AgentList, CustomerList, SupplierListGroup, ProductList},
    props: {
        initialData: {
            type: Object,
            default: null
        }
    },
    watch: {
        'form.quantity': function (newVal, oldVal) {
            if (this.form.total_price === 0)
                this.form.total_price = (this.product.price * this.form.quantity).toFixed(2)
        },
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
        productName() {
            return this.product?.name || ''
        }
    },
    data() {
        return {
            tab: 'main',
            spent_time: 0,
            alertStore: useAlertStore(),
            salesStore: useSalesStore(),
            userStore: useUsersStore(),
            can_add_past_assignments: false,
            file: null,
            product_filters: {
                supplier_id: null,
            },
            form: {
                title: '',
                description: '',
                status: 'pending',
                due_date: '',
                sale_date: '',
                quantity: 0,
                total_price: 0,
                agent_id: null,
                customer_id: null,
                supplier_id: null,
                product_id: null,
                payment_type: '0',
                payment_document_name: null,
                need_automatic_naming: true,
                receipt_is_lost: false,
                is_already_delivered: false
            },
            agentName: '',
            customerName: '',
            supplierName: '',
            product: null,
            isEdit: false
        }
    },
    created() {
        if (this.initialData) {
            this.form = {...this.initialData}
            this.form.need_automatic_naming = true

            this.form.payment_type = "" + this.form.payment_type

            this.isEdit = true
            this.agentName = this.initialData.agent?.name || ''
            this.customerName = this.initialData.customer?.name || ''
            this.supplierName = this.initialData.supplier?.name || ''
            this.product = this.initialData.product || null
        }
    },
    mounted() {
        checkTimer();

        window.addEventListener("trigger-spent-timer", (event) => { // (1)
            this.spent_time = event.detail
        });
    },
    methods: {
        onFileChange(e) {
            this.file = e.target.files[0]
        },
        selectAgent(agent) {
            this.form.agent_id = agent.id
            this.agentName = agent.name
            this.tab = 'main'
        },
        selectCustomer(customer) {
            this.form.customer_id = customer.id
            this.customerName = customer.name
            this.tab = 'main'
        },
        selectSupplier(supplier) {
            const oldSupplierId = this.form.supplier_id
            this.form.supplier_id = null
            this.supplierName = null
            this.product_filters.supplier_id = null

            if (oldSupplierId !== supplier.id) {
                this.form.product_id = null
                this.product = null
            }

            this.$nextTick(() => {
                this.form.supplier_id = supplier.id
                this.supplierName = supplier.name
                this.tab = 'main'

                this.product_filters.supplier_id = supplier.id
            })
        },
        selectProduct(product) {
            this.form.product_id = product.id
            this.product = product
            this.tab = 'main'
            this.form.quantity = 1
            this.form.total_price = product.price || 0
        },

        async submitForm() {
            this.alertStore.show("Началось добавление заявки");
            startTimer(10)

            this.isEdit ?
                await this.salesStore.update(this.form.id, this.form, this.file) :
                await this.salesStore.create(this.form, this.file)

            this.$emit('saved')
        }
    }
}
</script>
