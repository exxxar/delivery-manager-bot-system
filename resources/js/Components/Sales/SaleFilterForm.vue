<script setup>
import AgentList from "@/Components/Agents/AgentList.vue";
import CustomerList from "@/Components/Customers/CustomerList.vue";
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
</script>
<template>

    <h5 class="modal-title">
        {{ tab === 'filter' ? 'Фильтрация доставок' : titleMap[tab] }}
    </h5>

    <template v-if="tab === 'filter'">
        <form @submit.prevent="applyFilters">
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

            <!-- Период -->
            <div class="row mb-2">
                <div class="col">
                    <div class="form-floating">
                        <input type="date"
                               v-model="filters.date_from"
                               class="form-control" id="dateFromInput"/>
                        <label for="dateFromInput">Дата от</label>
                    </div>
                </div>
                <div class="col">
                    <div class="form-floating">
                        <input type="date"
                               v-model="filters.date_to"
                               class="form-control" id="dateToInput"/>
                        <label for="dateToInput">Дата до</label>
                    </div>
                </div>
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
                    class="btn btn-outline-secondary"
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
                    class="btn btn-outline-secondary"
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
                    class="btn btn-outline-secondary "
                    @click="tab='supplier'"
                >
                    Выбрать
                </button>
            </div>

            <button type="button"
                    @click="clearFilters"
                    class="btn btn-outline-secondary mb-2 w-100 p-3">Очистить фильтры
            </button>
            <button type="submit" class="btn btn-primary w-100 p-3">Применить фильтры</button>
        </form>
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
</template>

<script>
export default {
    name: 'SaleFilterForm',
    data() {
        return {
            tab: 'filter',
            titleMap: {
                agent: 'Выбор агента',
                customer: 'Выбор клиента',
                supplier: 'Выбор поставщика'
            },
            selectedAgent: null,
            selectedSupplier: null,
            selectedCustomer: null,
            filters: {
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
        this.tab = 'filter'
    },
    methods: {
        applyFilters() {
            this.$emit('apply-filters', this.filters)
        },
        selectAgent(agent) {
            this.selectedAgent = null
            this.$nextTick(() => {
                this.selectedAgent = agent
                this.filters.agent_id = agent?.id || null
                this.tab = 'filter'
            })

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
        clearFilters() {
            const filters = {
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

            this.filters = filters
        }
    }
}
</script>
