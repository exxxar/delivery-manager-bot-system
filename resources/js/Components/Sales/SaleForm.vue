<template>

        <form @submit.prevent="submitForm">

            <!-- Название задания -->
            <div class="form-floating mb-2">
                <input v-model="form.title" type="text" class="form-control" id="title" placeholder="Название" required>
                <label for="title">Название задания</label>
            </div>

            <!-- Описание -->
            <div class="form-floating mb-2">
                <textarea v-model="form.description" class="form-control" id="description" placeholder="Описание" style="height: 120px" required></textarea>
                <label for="description">Описание задания</label>
            </div>

            <!-- Статус -->
            <div class="form-floating mb-2">
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
                <input v-model="form.due_date" type="date" class="form-control" id="due_date">
                <label for="due_date">Дата задания</label>
            </div>

            <!-- Дата сделки -->
            <div class="form-floating mb-2">
                <input v-model="form.sale_date" type="date" class="form-control" id="sale_date">
                <label for="sale_date">Дата сделки</label>
            </div>

            <!-- Количество -->
            <div class="form-floating mb-2">
                <input v-model="form.quantity" type="number" class="form-control" id="quantity" placeholder="Количество">
                <label for="quantity">Количество</label>
            </div>

            <!-- Сумма -->
            <div class="form-floating mb-2">
                <input v-model="form.total_price" type="number" step="0.01" class="form-control" id="total_price" placeholder="Сумма">
                <label for="total_price">Сумма сделки</label>
            </div>

            <template v-if="user.role>=3">
                <!-- Агент -->
                <div class="input-group mb-2">
                    <div class="form-floating flex-grow-1">
                        <input type="text" class="form-control" id="agent" :value="agentName" placeholder="Агент" readonly>
                        <label for="agent">Агент</label>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" @click="openAgentModal">Выбрать</button>
                </div>

            </template>

            <!-- Клиент -->
            <div class="input-group mb-2">
                <div class="form-floating flex-grow-1">
                    <input type="text" class="form-control" id="customer" :value="customerName" placeholder="Клиент" readonly>
                    <label for="customer">Клиент</label>
                </div>
                <button type="button" class="btn btn-outline-secondary" @click="openCustomerModal">Выбрать</button>
            </div>

            <!-- Поставщик -->
            <div class="input-group mb-2">
                <div class="form-floating flex-grow-1">
                    <input type="text" class="form-control" id="supplier" :value="supplierName" placeholder="Поставщик" readonly>
                    <label for="supplier">Поставщик</label>
                </div>
                <button type="button" class="btn btn-outline-secondary" @click="openSupplierModal">Выбрать</button>
            </div>

            <!-- Продукт -->
            <div class="input-group mb-2">
                <div class="form-floating flex-grow-1">
                    <input type="text" class="form-control" id="product" :value="productName" placeholder="Продукт" readonly>
                    <label for="product">Продукт</label>
                </div>
                <button type="button" class="btn btn-outline-secondary" @click="openProductModal">Выбрать</button>
            </div>

            <!-- Кнопка -->
            <button type="submit" class="btn btn-primary w-100 p-3">
                {{ isEdit ? 'Сохранить изменения' : 'Создать задание' }}
            </button>
        </form>

        <!-- Модалки выбора -->
        <div class="modal fade" id="agentModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Выбор агента</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><AgentList @select="selectAgent" /></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="customerModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Выбор клиента</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><CustomerList @select="selectCustomer" /></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="supplierModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Выбор поставщика</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><SupplierListGroup @select="selectSupplier" /></div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="productModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title">Выбор продукта</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><ProductList @select="selectProduct" /></div>
                </div>
            </div>
        </div>

</template>

<script>
import axios from 'axios'
import AgentList from '../Agents/AgentList.vue'
import CustomerList from '../Customers/CustomerList.vue'
import SupplierListGroup from '../Suppliers/SupplierList.vue'
import ProductList from '../Products/ProductList.vue'
import {useUsersStore} from "@/stores/users";
export default {
    name: 'SaleForm',
    components: { AgentList, CustomerList, SupplierListGroup, ProductList },
    props: {
        initialData: {
            type: Object,
            default: null
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
    },
    data() {
        return {
            userStore: useUsersStore(),
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
                product_id: null
            },
            agentName: '',
            customerName: '',
            supplierName: '',
            productName: '',
            isEdit: false
        }
    },
    created() {
        if (this.initialData) {
            this.form = { ...this.initialData }
            this.isEdit = true
            this.agentName = this.initialData.agent?.name || ''
            this.customerName = this.initialData.customer?.name || ''
            this.supplierName = this.initialData.supplier?.name || ''
            this.productName = this.initialData.product?.name || ''
        }
    },
    methods: {
        openAgentModal() { new bootstrap.Modal(document.getElementById('agentModal')).show() },
        openCustomerModal() { new bootstrap.Modal(document.getElementById('customerModal')).show() },
        openSupplierModal() { new bootstrap.Modal(document.getElementById('supplierModal')).show() },
        openProductModal() { new bootstrap.Modal(document.getElementById('productModal')).show() },

        selectAgent(agent) {
            this.form.agent_id = agent.id
            this.agentName = agent.name
            bootstrap.Modal.getInstance(document.getElementById('agentModal')).hide()
        },
        selectCustomer(customer) {
            this.form.customer_id = customer.id
            this.customerName = customer.name
            bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide()
        },
        selectSupplier(supplier) {
            this.form.supplier_id = supplier.id
            this.supplierName = supplier.name
            bootstrap.Modal.getInstance(document.getElementById('supplierModal')).hide()
        },
        selectProduct(product) {
            this.form.product_id = product.id
            this.productName = product.name
            bootstrap.Modal.getInstance(document.getElementById('productModal')).hide()
        },

        async submitForm() {
            try {
                if (this.isEdit) {
                    await axios.put(`/api/sales/${this.form.id}`, this.form)
                    alert('Задание обновлено!')
                } else {
                    await axios.post('/api/sales', this.form)
                    alert('Задание создано!')
                }
                this.$emit('saved')
            } catch (error) {
                console.error('Ошибка сохранения задания:', error)
            }
        }
    }
}
</script>
