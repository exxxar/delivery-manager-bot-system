<script setup>
const today = new Date().toISOString().split('T')[0]
</script>

<template>

    <form @submit.prevent="submitForm">

        <template v-if="tab==='main'">

            <div
                v-if="!isEdit"
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
                <button type="button" class="btn btn-outline-light text-primary" @click="tab='supplier'">Выбрать
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
                <button type="button" class="btn btn-outline-light text-primary" @click="tab='product'">Выбрать</button>
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
                <div class="card mb-2 rounded-0">
                    <div class="card-body">
                        <h6>Фотография чека</h6>
                        <div class="form-floating ">

                            <input
                                type="file"
                                class="form-control"
                                @change="onFileChange"
                                accept=".jpg,.png,.pdf"
                                required
                            />
                            <label for="payment-type">Прикрепить</label>
                        </div>

                    </div>
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

            <!-- Дата задания -->
            <div class="form-floating mb-2">
                <input v-model="form.due_date"
                       required
                       :min="today"
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
                <!-- Агент -->
                <div class="input-group mb-2">
                    <div class="form-floating flex-grow-1">
                        <input type="text" class="form-control" id="agent" :value="agentName" placeholder="Агент"
                               readonly>
                        <label for="agent">Младший админ</label>
                    </div>
                    <button type="button" class="btn btn-outline-light text-primary" @click="tab='agent'">Выбрать
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
                            <button type="button" class="btn btn-outline-light text-primary" @click="tab='customer'">Выбрать
                            </button>
                        </div>
            -->


            <!-- Кнопка -->
            <button type="submit" class="btn btn-primary w-100 p-3">
                {{ isEdit ? 'Сохранить изменения' : 'Создать задание' }}
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
            <ProductList :for-select="true" @select="selectProduct"/>
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
            salesStore: useSalesStore(),
            userStore: useUsersStore(),
            file: null,
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
            this.isEdit = true
            this.agentName = this.initialData.agent?.name || ''
            this.customerName = this.initialData.customer?.name || ''
            this.supplierName = this.initialData.supplier?.name || ''
            this.product = this.initialData.product || null
        }
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
            this.form.supplier_id = supplier.id
            this.supplierName = supplier.name
            this.tab = 'main'
        },
        selectProduct(product) {
            this.form.product_id = product.id
            this.product = product
            this.tab = 'main'
            this.form.quantity = 1
            this.form.total_price = product.price || 0
        },

        async submitForm() {
            if (this.isEdit) {
                await this.salesStore.update(this.form.id, this.form, this.file)
            } else {
                await this.salesStore.create(this.form, this.file)
            }
            this.$emit('saved')
        }
    }
}
</script>
