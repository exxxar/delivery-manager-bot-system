<script setup>
import AgentInfo from "@/Components/Agents/AgentInfo.vue";
import Pagination from "@/Components/Pagination.vue";
import UserForm from "@/Components/Users/UserForm.vue";
import ReportIndividualGenerator from "@/Components/Admins/ReportIndividualGenerator.vue";
</script>

<template>

        <!-- 🔹 Табы режимов -->
        <ul class="nav nav-pills nav-fill mb-3">
            <li class="nav-item">
                <button class="nav-link" :class="{ active: viewMode === 'all' }" @click="switchMode('all')">
                    <i class="fa-solid fa-list me-1"></i> Все
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" :class="{ active: viewMode === 'active' }" @click="switchMode('active')">
                    <i class="fa-solid fa-bolt text-success me-1"></i> Активные
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" :class="{ active: viewMode === 'inactive' }" @click="switchMode('inactive')">
                    <i class="fa-solid fa-moon text-secondary me-1"></i> Неактивные
                </button>
            </li>
        </ul>

        <!-- 🔹 Селектор месяца и статистика (только для активных/неактивных) -->
        <template v-if="viewMode !== 'all'">
            <div class="form-floating mb-3">
                <select class="form-select" v-model="selectedMonth" @change="loadByMode" id="monthSelect">
                    <option v-for="m in getMonthList()" :key="m.key" :value="m.key">{{ m.label }}</option>
                </select>
                <label for="monthSelect"><i class="fa-solid fa-calendar-days me-1"></i> Месяц</label>
            </div>

            <div v-if="agentStore.stats" class="alert alert-info mb-3 py-2">
                <div class="row text-center small">
                    <div class="col-6">
                        <div class="text-muted">Администраторов</div>
                        <div class="fw-bold fs-5">{{ agentStore.stats.total_agents }}</div>
                    </div>
                    <div class="col-6" v-if="agentStore.stats.total_turnover !== undefined">
                        <div class="text-muted">Общий товарооборот</div>
                        <div class="fw-bold fs-5 text-success">{{ formatMoney(agentStore.stats.total_turnover) }}</div>
                    </div>
                </div>
            </div>
        </template>

        <!-- 🔹 Поиск (теперь работает и с API для активных/неактивных) -->
        <div class="form-floating mb-3">
            <input type="search" v-model="search" class="form-control" id="searchInput" placeholder="Поиск по имени или телефону..."/>
            <label for="searchInput">Поиск</label>
        </div>


    <template v-if="!forSelect">
        <div class="d-flex justify-content-between" v-if="(user?.role || 0) >= 3">
            <a href="javascript:void(0)"
               @click="selectAll"
               class="small">Выделить все</a>

            <template v-if="selection.length>0">
                <span class="small">Выбрано <span class="fw-bold">{{selection.length}}</span> админов</span>
            </template>
        </div>
    </template>

    <ul class="list-group">
        <li

            v-for="agent in filteredAgents" :key="agent.id"
            v-bind:class="{'border-primary': selection.indexOf(agent.id)!==-1,'bg-danger': !agent.registration_at}"
            class="list-group-item d-flex justify-content-between align-items-center">
            <div >
                <div class="fw-bold">
                    <p class="small mb-2">
                        <span class="badge bg-primary">Общ. {{agent.total_percent || 0}}%</span>
                        <span class="badge bg-primary-subtle mx-1">Перс. {{agent.percent}}%</span>
                    </p>
                    <span @click="toggleSelection(agent.id)">{{ agent.name }}</span>
                </div>
                <small class="text-muted">{{ agent.phone || 'телефон не указан'}}</small>

                <!-- 🔹 НОВАЯ: статистика для активных/неактивных -->
                <template v-if="viewMode === 'active' && agent.month_sales_count">
                    <div class="d-flex gap-2 mt-2">
                        <span
                            class="badge bg-success clickable-badge"
                            @click.stop="openAgentSales(agent)"
                            :title="`Посмотреть сделки агента за ${selectedMonth}`"
                        >
                            <i class="fa-solid fa-receipt me-1"></i>
                            {{ agent.month_sales_count }} сделок
                            <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size: 10px;"></i>
                        </span>
                        <span class="badge bg-info text-dark">
                            <i class="fa-solid fa-money-bill-trend-up me-1"></i> {{ formatMoney(agent.month_turnover) }}
                        </span>
                    </div>
                </template>

                <template v-if="viewMode === 'inactive'">
                    <div class="mt-2">
                        <span class="badge bg-secondary">
                            <i class="fa-solid fa-pause me-1"></i> Нет сделок за выбранный месяц
                        </span>
                    </div>
                </template>
            </div>

            <!-- Dropdown кнопка -->
            <div class="dropdown">
                <button class="btn btn-sm" type="button"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <template v-if="forSelect">
                        <li><a class="dropdown-item" href="#" @click.prevent="selectAgent(agent)">Выбрать</a>
                        </li>
                    </template>

                    <template v-if="!forSelect">
                        <li><a class="dropdown-item" href="#" @click.prevent="openAgentInfo(agent)">Просмотр</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="openEditAgent(agent)">Редактировать</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="getPersonalStatistic(agent)">Выгрузка
                            статистики</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="#"
                               @click.prevent="confirmDelete(agent)">Удалить</a></li>
                    </template>

                </ul>
            </div>
        </li>
    </ul>

    <template v-if="agentStore.pagination?.total>0">


        <div class="form-floating my-2">
            <select
                id="itemsPerPage"
                class="form-select"
                v-model="size"
            >
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="500">500</option>
                <option value="1000">1000</option>
                <option value="5000">5000</option>
            </select>
            <label for="itemsPerPage">Элементов на странице</label>
        </div>
        <!-- Пагинация -->
        <Pagination
            :pagination="agentStore.pagination"
            @page-changed="fetchAgentsByUrl"
        />
    </template>


    <!-- Модалка просмотра агента -->
    <div class="modal fade" id="agentInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Информация</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <AgentInfo
                        v-on:edit="openEditAgent"
                        :agent="selectedAgent" v-if="selectedAgent"></AgentInfo>
                </div>
            </div>
        </div>
    </div>


    <!-- Модалка подтверждения удаления -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Удаление</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Вы уверены, что хотите удалить <strong>{{ selectedAgent?.name }}</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" @click="deleteAgent">Удалить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка редактирования агента -->
    <div class="modal fade" id="editAgentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <AgentForm v-if="selectedAgent" :initialData="selectedAgent"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка редактирования -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование администратора</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <UserForm v-if="selectedAdmin" :initialData="selectedAdmin" @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>


    <!-- Модалка редактирования -->
    <div class="modal fade" id="personalStatisticModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Персональная статистика за период</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                <ReportIndividualGenerator
                    v-if="selectedAgent"
                    :agent-id="selectedAgent.id"
                    v-on:generate-report="generateReport"></ReportIndividualGenerator>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка со сделками агента -->
    <div class="modal fade" id="agentSalesModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-user-tie text-primary me-2"></i>
                        Сделки: <span class="text-primary">{{ selectedAgent?.name }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Загрузка -->
                    <div v-if="agentStore.loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                    </div>

                    <template v-else>
                        <!-- Список сделок -->
                        <div v-if="agentStore.agentSales.length > 0">

                            <SaleCard
                                class="shadow-sm mb-2 rounded-2 p-2"
                                :key="sale.id"
                                :field_visible="field_visible"
                                :saleStatuses="saleStatuses"
                                v-for="sale in agentStore.agentSales"
                                :sale="sale"></SaleCard>

                            <!-- Пагинация -->
                            <Pagination
                                v-if="agentStore.agentSalesPagination?.total > 0"
                                :pagination="agentStore.agentSalesPagination"
                                @page-changed="fetchAgentSalesPage"
                            />
                        </div>

                        <!-- Пусто -->
                        <div v-else class="text-center text-muted py-5">
                            <i class="fa-solid fa-inbox fs-1 mb-3 d-block"></i>
                            <p class="mb-0">За выбранный период сделок не найдено</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import AgentForm from './AgentForm.vue'
import {useAgentsStore} from "@/stores/agents";
import {useAdminsStore} from "@/stores/admins";
import {useBaseExports} from "@/stores/baseExports";
import {useUsersStore} from "@/stores/users";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";

export default {
    name: 'AgentList',
    components: {AgentForm, SaleCard},
    props: ["forSelect"],
    data() {
        return {
            field_visible: null,
            saleStatuses: {
                pending: "В ожидании",
                assigned: "Назначено",
                completed: "Завершено",
                rejected: "Отклонено",
                delivered: "Доставляется"
            },
            size:20,
            search: '',
            selectedAdmin: null,
            agentStore: useAgentsStore(),
            userStore: useUsersStore(),
            selection: [],
            selectedAgent: null,
            report: {
                startDate: '',
                endDate: '',
            },

            viewMode: 'all',
            selectedMonth: this.getCurrentMonth(),
        }
    },
    watch:{
      'size':function (){
          this.fetchAgents()
      },
        // 🔹 Обновляем watcher поиска, чтобы он вызывал loadByMode для API-запросов
        search: function (newVal) {
            if (this.viewMode !== 'all') {
                this.loadByMode(1) // Сбрасываем на 1 страницу при новом поиске
            }
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
        filteredAgents() {
            if (!this.search) return this.agentStore.items || []
            const q = this.search.toLowerCase()
            return this.agentStore.items.filter(agent =>
                Object.values(agent).some(val =>
                    val ? String(val).toLowerCase().includes(q) : false
                )
            )
        }
    },
    created() {
        this.fetchAgents()

    },
    methods: {
        async openAgentSales(agent) {
            this.selectedAgent = agent
            try {
                await this.agentStore.fetchAgentSales(agent.id, this.selectedMonth)
                new bootstrap.Modal(document.getElementById('agentSalesModal')).show()
            } catch (e) {
                console.error('Ошибка загрузки сделок агента:', e)
            }
        },

        async fetchAgentSalesPage(url) {
            const match = url.match(/page=(\d+)/)
            const page = match ? parseInt(match[1]) : 1
            if (this.selectedAgent) {
                await this.agentStore.fetchAgentSales(
                    this.selectedAgent.id,
                    this.selectedMonth,
                    page
                )
            }
        },
        getCurrentMonth() {
            const now = new Date()
            return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
        },
        getMonthList() {
            const months = []
            const now = new Date()
            for (let i = 0; i < 12; i++) {
                const date = new Date(now.getFullYear(), now.getMonth() - i, 1)
                const year = date.getFullYear()
                const month = String(date.getMonth() + 1).padStart(2, '0')
                months.push({ key: `${year}-${month}`, label: `${year}-${month}` })
            }
            return months
        },
        switchMode(mode) {
            this.viewMode = mode
            this.loadByMode(1)
        },
        async loadByMode(page = 1) {
            if (this.viewMode === 'active') {
                await this.agentStore.fetchActive(this.selectedMonth, page, this.size, this.search)
            } else if (this.viewMode === 'inactive') {
                await this.agentStore.fetchInactive(this.selectedMonth, page, this.size, this.search)
            } else {
                await this.fetchAgents(page)
            }
        },
        formatMoney(value) {
            return new Intl.NumberFormat('ru-RU').format(value || 0) + ' ₽'
        },
        toggleSelection(id) {
            let index = this.selection.findIndex(i => i === id)
            if (index === -1)
                this.selection.push(id)
            else
                this.selection.splice(index, 1)

            this.$emit("select", this.selection)
        },
        selectAll() {
            if (this.selection.length === 0)
                this.agentStore.items.forEach(i => {
                    if (this.selection.indexOf(i.id) === -1)
                        this.selection.push(i.id)
                })
            else
                this.selection = []

            this.$emit("select", this.selection)
        },
        generateReport() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('personalStatisticModal'))
            modal.hide()
        },
        openEdit(user) {
            this.selectedAdmin = null
            this.$nextTick(() => {
                this.selectedAdmin = user
                new bootstrap.Modal(document.getElementById('editUserModal')).show()
            })

        },
        async fetchAgents(page = 1) {
            await this.agentStore.fetchAllByPage(page, this.size)
        },
        async fetchAgentsByUrl(url) {
            await this.agentStore.fetchByUrl(url)
        },
        selectAgent(agent) {
            if (!this.forSelect)
                return
            this.$emit("select", agent)
        },

        openAgentInfo(agent) {
            // Сбрасываем перед установкой нового
            this.selectedAgent = null

            this.$nextTick(() => {
                // Устанавливаем нового агента
                this.selectedAgent = { ...agent } // Создаём копию, чтобы избежать проблем с реактивностью

                // Показываем модалку только после того, как данные установлены
                this.$nextTick(() => {
                    const modal = new bootstrap.Modal(document.getElementById('agentInfoModal'))
                    modal.show()
                })
            })
        },
        openPercents(agent) {
            this.selectedAgent = agent
            new bootstrap.Modal(document.getElementById('percentsModal')).show()
        },
        confirmDelete(agent) {
            this.selectedAgent = agent
            new bootstrap.Modal(document.getElementById('deleteModal')).show()
        },
        async deleteAgent() {
            try {
                await this.agentStore.remove(this.selectedAgent.id)
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide()
            } catch (error) {
                console.error('Ошибка удаления:', error)
            }
        },
        getPersonalStatistic(agent) {
            this.selectedAgent = null
            this.$nextTick(() => {
                this.selectedAgent = agent
                new bootstrap.Modal(document.getElementById('personalStatisticModal')).show()
            })
        },
        openEditAgent(agent) {
            this.selectedAgent = null
            this.$nextTick(() => {
                this.selectedAgent = agent
                new bootstrap.Modal(document.getElementById('editAgentModal')).show()
            })
        }
    }
}
</script>
<style scoped>
.clickable-badge {
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}

.clickable-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(25, 135, 84, 0.3);
    filter: brightness(1.1);
}

.clickable-badge:active {
    transform: translateY(0);
}
</style>
