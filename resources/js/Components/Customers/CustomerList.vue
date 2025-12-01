<script setup>
import CustomerCard from "@/Components/Customers/CustomerCard.vue";
import CustomerForm from "@/Components/Customers/CustomerForm.vue";
import Pagination from "@/Components/Pagination.vue";
</script>

<template>

        <div class="form-floating mb-3">
            <input type="search"
                   v-model="search"
                   class="form-control" id="searchInput" placeholder="Поиск..." />
            <label for="searchInput">Поиск</label>
        </div>

        <ul class="list-group">
            <li
                @click="selectCustomer(customer)"
                v-for="customer in filteredCustomers" :key="customer.id" class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">{{ customer.name }}</div>
                    <small class="text-muted">{{ customer.company_name }}</small>
                </div>

                <!-- Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm" type="button"
                            data-bs-toggle="dropdown">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <template v-if="forSelect">
                            <li><a class="dropdown-item" href="#" @click.prevent="selectCustomer(customer)">Выбрать клиента</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                        </template>
                        <li><a class="dropdown-item" href="#" @click.prevent="openEdit(customer)">Редактировать</a></li>
                        <li><a class="dropdown-item text-danger" href="#" @click.prevent="confirmDelete(customer)">Удалить</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="openView(customer)">Просмотреть</a></li>
                    </ul>
                </div>
            </li>
        </ul>

        <!-- Пагинация -->
        <Pagination
            :pagination="customerStore.pagination"
            @page-changed="fetchDataByUrl"
        />
        <!-- Сообщение если список пуст -->
        <div v-if="customerStore.items.length === 0" class="alert alert-info mt-3">
            Клиентов пока нет.
        </div>

        <!-- Модалка редактирования -->
        <div class="modal fade" id="editCustomerModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Редактирование клиента</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <CustomerForm v-if="selectedCustomer" :initialData="selectedCustomer" @saved="fetchCustomers" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка удаления -->
        <div class="modal fade" id="deleteCustomerModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Удаление клиента</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Вы уверены, что хотите удалить <strong>{{ selectedCustomer?.name }}</strong>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-danger" @click="deleteCustomer">Удалить</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка просмотра -->
        <div class="modal fade" id="viewCustomerModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Карточка клиента</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <CustomerCard v-if="selectedCustomer" :customer="selectedCustomer" @edit="openEdit" />
                    </div>
                </div>
            </div>
        </div>

</template>

<script>
import axios from 'axios'
import {useCustomersStore} from "@/stores/customers";

export default {
    name: 'CustomerList',
    props:["forSelect"],
    data() {
        return {
            search:null,
            customerStore: useCustomersStore(),
            selectedCustomer: null
        }
    },
    computed:{
        filteredCustomers() {
            if (!this.search) return this.customerStore.items || []
            const q = this.search.toLowerCase()
            return this.customerStore.items.filter(customer =>
                Object.values(customer).some(val =>
                    val ? String(val).toLowerCase().includes(q) : false
                )
            )
        }
    },
    created() {
        this.fetchData()
    },
    methods: {
        selectCustomer(customer) {
            if (!this.forSelect)
                return
            this.$emit("select-customer", customer)
        },
        async fetchData(page = 1) {
            await this.customerStore.fetchAllByPage(page)

        },
        async fetchDataByUrl(url) {
            await this.customerStore.fetchByUrl(url)
        },

        openEdit(customer) {
            this.selectedCustomer = customer
            new bootstrap.Modal(document.getElementById('editCustomerModal')).show()
        },
        confirmDelete(customer) {
            this.selectedCustomer = customer
            new bootstrap.Modal(document.getElementById('deleteCustomerModal')).show()
        },
        async deleteCustomer() {
            try {
                await axios.delete(`/api/customers/${this.selectedCustomer.id}`)
                this.customers = this.customers.filter(c => c.id !== this.selectedCustomer.id)
                bootstrap.Modal.getInstance(document.getElementById('deleteCustomerModal')).hide()
            } catch (error) {
                console.error('Ошибка удаления клиента:', error)
            }
        },
        openView(customer) {
            this.selectedCustomer = customer
            new bootstrap.Modal(document.getElementById('viewCustomerModal')).show()
        }
    }
}
</script>
