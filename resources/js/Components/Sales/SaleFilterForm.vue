<script setup>
import AgentList from "@/Components/Agents/AgentList.vue";
import CustomerList from "@/Components/Customers/CustomerList.vue";
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
</script>
<template>

<!--    <h5 class="modal-title">
        {{ tab === 'filter' ? 'Фильтрация доставок' : titleMap[tab] }}
    </h5>-->

    <!-- Кнопка фильтра -->
    <div class="mb-2">
        <button class="btn btn-secondary" @click="openFilter">Фильтр</button>

        <!-- Dropdown сортировки -->
        <div class="dropdown d-inline-block ms-2">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                {{ sortableFields[salesStore.sort.field].slice(0, 17) }}
                <span v-if="sortableFields[salesStore.sort.field].length>17">...</span>
                ({{ salesStore.sort.direction }})
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" @click="changeSort('title')">Название</a></li>
                <li><a class="dropdown-item" @click="changeSort('status')">Статус</a></li>
                <li><a class="dropdown-item" @click="changeSort('sale_date')">Дата продажи</a></li>
                <li><a class="dropdown-item" @click="changeSort('total_price')">Сумма</a></li>
            </ul>
        </div>
    </div>



    <!-- Модалка фильтрации -->
    <div class="modal fade" id="saleFilterModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <form @submit.prevent="applyFilters" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Фильтры</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                 <div  class="modal-body">
                    <template v-if="tab === 'filter'">

                        <div
                             class="form-check form-switch mb-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                v-model="onlyMyTask"
                                :id="`only-my-tasks`"
                            />
                            <label class="form-check-label" :for="`only-my-tasks`">
                              Только мои сделки
                            </label>
                        </div>
                        <!-- Номер сделки -->
                        <div class="form-floating mb-2">
                            <input class="form-control"
                                   v-model="filters.number"
                                   id="titleInput" placeholder="Номер сделки"/>
                            <label for="titleInput">Номер сделки</label>
                        </div>


                        <!-- Название -->
                            <div class="form-floating mb-2">
                                <input class="form-control"
                                       v-model="filters.title"
                                       id="titleInput" placeholder="Название"/>
                                <label for="titleInput">Название</label>
                            </div>

                            <!-- Описание -->
                            <div class="form-floating mb-2">
                                <input class="form-control"
                                       v-model="filters.description"
                                       id="descInput" placeholder="Описание"/>
                                <label for="descInput">Описание</label>
                            </div>

                            <!-- Статус -->
                            <div class="form-floating mb-2">
                                <select
                                    v-model="filters.status"
                                    class="form-select" id="statusSelect">
                                    <option value="">Все</option>
                                    <option value="pending">В ожидании</option>
                                    <option value="assigned">Назначено</option>
                                    <option value="completed">Завершено</option>
                                    <option value="delivered">Доставляется</option>
                                    <option value="rejected">Отклонено</option>
                                </select>
                                <label for="statusSelect">Статус</label>
                            </div>

                            <!-- Статус -->
                            <div class="form-floating mb-2">
                                <select
                                    v-model="filters.date_type"
                                    class="form-select" id="dateTypeSelect">
                                    <option :value="null">Не имеет значения</option>
                                    <option value="0">Дата создания задачи</option>
                                    <option value="1">Дата исполнения задания</option>
                                    <option value="2">Дата заключения сделки (продажи)</option>
                                    <option value="3">Дата доставки по договору</option>
                                    <option value="4">Дата доставки фактическая</option>
                                </select>
                                <label for="dateTypeSelect">Тип даты</label>
                            </div>

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
                            <!-- Агент -->
                            <p v-if="selectedAgent" class="mb-0" style="font-size: 10px;">Выбран торговый представитель:
                                {{ selectedAgent.name || 'не указан' }}</p>
                            <div class="input-group mb-2">
                                <div class="form-floating flex-grow-1">
                                    <input
                                        @change="selectedAgent = null"
                                        v-model="filters.agent_id"
                                        class="form-control" id="agentInput" placeholder="Агент"/>
                                    <label for="agentInput">Агент</label>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-outline-light text-primary"
                                    @click="tab='agent'"
                                >
                                    Выбрать
                                </button>
                            </div>
                            <!-- Клиент -->
                            <p v-if="selectedCustomer" class="mb-0" style="font-size: 10px;">Выбран клиент:
                                {{ selectedCustomer.name || 'не указан' }}</p>
                            <div class="input-group mb-2">
                                <div class="form-floating flex-grow-1">
                                    <input class="form-control"
                                           @change="selectedCustomer = null"
                                           v-model="filters.customer_id"
                                           id="customerInput" placeholder="Клиент"/>
                                    <label for="customerInput">Клиент</label>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-outline-light text-primary"
                                    @click="tab='customer'"
                                >
                                    Выбрать
                                </button>
                            </div>

                            <!-- Поставщик -->
                            <p v-if="selectedSupplier" class="mb-0" style="font-size: 10px;">Выбран поставщик:
                                {{ selectedSupplier.name || 'не указан' }}</p>
                            <div class="input-group mb-2">
                                <div class="form-floating flex-grow-1">
                                    <input class="form-control"
                                           @change="selectedSupplier = null"
                                           v-model="filters.supplier_id"
                                           id="supplierInput" placeholder="Поставщик"/>
                                    <label for="supplierInput">Поставщик</label>
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-outline-light text-primary"
                                    @click="tab='supplier'"
                                >
                                    Выбрать
                                </button>
                            </div>

                            <h6>Отображаемые поля</h6>
                            <div v-for="(label, field) in sortableFields" :key="field"
                                 class="form-check form-switch mb-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    v-model="field_visible[field]"
                                    :id="`switch-${field}`"
                                />
                                <label class="form-check-label" :for="`switch-${field}`">
                                    {{ label }}
                                </label>
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
                            </select>
                            <label for="itemsPerPage">Элементов на странице</label>
                        </div>

                        <!-- Поле ввода номера страницы -->
                        <div class="form-floating mb-3">
                            <input
                                type="number"
                                id="pageNumber"
                                class="form-control"
                                v-model="page"
                                min="1"
                            />
                            <label for="pageNumber">Номер страницы</label>
                        </div>

                            <button type="button"
                                    @click="clearFilters"
                                    class="btn btn-outline-light text-secondary mb-2 w-100 p-3">Очистить фильтры
                            </button>


                    </template>
                    <!-- Вкладка выбора агента -->
                    <template v-if="tab === 'agent'">
                        <button
                            class="btn btn-secondary my-3" @click="tab = 'filter'">Назад
                        </button>

                        <button
                            type="button"
                            class="btn btn-outline-secondary p-3 w-100 mb-2"
                            v-if="selectedAgent!=null"
                            @click="selectAgent(null)">Отменить выбор
                        </button>

                        <AgentList :for-select="true" v-on:select-agent="selectAgent"></AgentList>
                        <!-- Заглушка: таблица агентов -->

                    </template>

                    <!-- Вкладка выбора клиента -->
                    <template v-if="tab === 'customer'">
                        <button
                            class="btn btn-secondary my-3" @click="tab = 'filter'">Назад
                        </button>

                        <button
                            type="button"
                            class="btn btn-outline-secondary p-3 w-100 mb-2"
                            v-if="selectedCustomer!=null"
                            @click="selectCustomer(null)">Отменить выбор
                        </button>

                        <CustomerList :for-select="true" v-on:select-customer="selectCustomer"></CustomerList>
                    </template>

                    <!-- Вкладка выбора поставщика -->
                    <template v-if="tab === 'supplier'">
                        <button
                            class="btn btn-secondary my-3" @click="tab = 'filter'">Назад
                        </button>

                        <button
                            type="button"
                            class="btn btn-outline-secondary p-3 w-100 mb-2"
                            v-if="selectedSupplier!=null"
                            @click="selectSupplier(null)">Отменить выбор
                        </button>

                        <SupplierList :for-select="true" v-on:select-supplier="selectSupplier"></SupplierList>

                    </template>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100 p-3">Применить фильтры</button>
                </div>
            </form>
        </div>
    </div>

</template>

<script>

import {useSalesStore} from '@/stores/sales'

export default {
    name: 'SaleFilterForm',
    data() {
        return {
            page:1,
            size:20,
            onlyMyTask:false,
            salesStore:useSalesStore(),
            tab: 'filter',
            titleMap: {
                agent: 'Выбор агента',
                customer: 'Выбор клиента',
                supplier: 'Выбор поставщика'
            },
            selectedAgent: null,
            selectedSupplier: null,
            selectedCustomer: null,
            field_visible: [],
            sortableFields: {
                id: "№",
                title: 'Название',
                description: 'Описание',
                status: 'Статус',
                due_date: "Дата назначения встречи",
                sale_date: 'Дата продажи',
                planned_delivery_date: 'Планируемая дата доставки',
                actual_delivery_date: 'Фактическая дата доставки',
                quantity: 'Доставляемое число товара',
                total_price: 'Сумма заказа',
                agent_id: 'Торговый представитель',
                customer_id: 'Клиент',
                supplier_id: 'Поставщик',
                created_by_id: 'Создатель заявки',
                product_id: 'Продукт',
            },
            filters: {
                number:'',
                title: '',
                description: '',
                status: '',
                date_type: null,
                date_from: '',
                date_to: '',
                agent_id: '',
                customer_id: '',
                supplier_id: '',
                created_by_id: '',
                quantity: '',
                total_price: ''
            }
        }
    },

    mounted() {
        let tmpVisibleFields = [ 'title', 'description','status','due_date']
        for (const field in this.sortableFields) {
            this.field_visible[field] = tmpVisibleFields.indexOf(field) !== -1
        }

        this.tab = 'filter'
    },
    methods: {
        applyFilters() {
            const filter = {
                ...this.filters,
                size: this.size,
                page: this.page,
                only_my_tasks:this.onlyMyTask,
                field_visible: this.field_visible
            }

            const filterModal = bootstrap.Modal.getInstance(document.getElementById('saleFilterModal'))
            if (filterModal)
                filterModal.hide()

            this.$emit('apply-filters', filter)
        },
        selectAgent(agent) {
            this.selectedAgent = null
            this.$nextTick(() => {
                this.selectedAgent = agent
                this.filters.agent_id = agent?.id || null
                this.tab = 'filter'
            })

        },
        openFilter() {
            new bootstrap.Modal(document.getElementById('saleFilterModal')).show()
        },
        selectCustomer(customer) {

            this.selectedCustomer = null
            this.$nextTick(() => {
                this.selectedCustomer = customer
                this.filters.customer_id = customer?.id || null
                this.tab = 'filter'
            })
        },

        selectSupplier(supplier) {
            this.selectedSupplier = null
            this.$nextTick(() => {
                this.selectedSupplier = supplier
                this.filters.supplier_id = supplier?.id || null
                this.tab = 'filter'
            })

        },
        changeSort(field) {
            const current = this.salesStore.sort
            const direction = current.field === field && current.direction === 'asc' ? 'desc' : 'asc'
            this.salesStore.setSort(field, direction)
            this.salesStore.fetchFiltered()
        },
        clearFilters() {
            const filters = {
                number:'',
                title: '',
                description: '',
                status: '',
                date_type: null,
                date_from: '',
                date_to: '',
                agent_id: '',
                customer_id: '',
                supplier_id: '',
                created_by_id: '',
                quantity: '',
                total_price: ''
            }

            this.size = 20
            this.page = 1
            this.onlyMyTask = false
            let tmpVisibleFields = [ 'title', 'description','status','due_date']
            for (const field in this.sortableFields) {
                this.field_visible[field] = tmpVisibleFields.indexOf(field) !== -1
            }

            this.filters = filters
        }
    }
}
</script>
