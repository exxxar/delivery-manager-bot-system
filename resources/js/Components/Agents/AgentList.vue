<template>
    <div class="agent-list-redesign">
        <div class="agent-toolbar">
            <div class="agent-toolbar-title"><i class="fa-solid fa-users-gear"></i><div><strong>Администраторы</strong><small>Управление списком и активностью</small></div></div>
        </div>
        <!-- 🔹 Табы режимов -->
        <ul class="nav nav-pills nav-fill mb-3 agent-tabs">
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
            <div class="form-floating mb-3 agent-month">
                <select class="form-select" v-model="selectedMonth" @change="loadByMode" id="monthSelect">
                    <option v-for="m in getMonthList()" :key="m.key" :value="m.key">{{ m.label }}</option>
                </select>
                <label for="monthSelect"><i class="fa-solid fa-calendar-days me-1"></i> Месяц</label>
            </div>

            <div v-if="agentStore.stats" class="agent-stats mb-3 py-3">
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
        <div class="form-floating mb-3 agent-search">
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

        <ul class="list-group agent-cards">
            <li
                v-for="agent in filteredAgents" :key="agent.id"
                v-bind:class="{'border-primary': selection.indexOf(agent.id)!==-1,'bg-danger': !agent.registration_at}"
                class="list-group-item d-flex justify-content-between align-items-center agent-card">
                <div>
                    <div class="fw-bold">
                        <p class="small mb-2">
                            <span class="badge agent-badge-primary">Общ. {{agent.total_percent || 0}}%</span>
                            <span class="badge agent-badge-soft mx-1">Перс. {{agent.percent}}%</span>
                        </p>
                        <span @click="toggleSelection(agent.id)">{{ agent.name }}</span>
                    </div>
                    <small class="text-muted">{{ agent.phone || 'телефон не указан'}}</small>

                    <!-- 🔹 Статистика для активных/неактивных -->
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

                <!-- Кнопка действий открывает нижнюю модалку -->
                <button class="btn btn-sm agent-menu-btn" type="button"
                        aria-label="Действия с администратором"
                        @click.stop="openAgentActions(agent)">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>
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

        <div v-if="filteredAgents.length === 0" class="agent-empty"><i class="fa-solid fa-user-slash"></i><p>Администраторы не найдены</p></div>
        <!-- Нижняя модалка действий администратора -->
        <div class="modal fade agent-actions-modal" id="agentActionsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom-sheet">
                <div class="modal-content">
                    <div class="agent-sheet-handle" aria-hidden="true"></div>
                    <div class="modal-header">
                        <div class="agent-sheet-person">
                            <span class="agent-sheet-avatar"><i class="fa-solid fa-user-tie"></i></span>
                            <div>
                                <h5 class="modal-title">Действия</h5>
                                <div class="agent-sheet-name">{{ selectedAgent?.name || 'Администратор' }}</div>
                                <small class="text-muted">{{ selectedAgent?.phone || 'Телефон не указан' }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body agent-actions-list">
                        <template v-if="forSelect">
                            <button type="button" class="agent-action" @click="runAgentAction('select')">
                                <span class="agent-action-icon is-blue"><i class="fa-solid fa-check"></i></span>
                                <span><strong>Выбрать администратора</strong><small>Использовать выбранную запись</small></span>
                                <i class="fa-solid fa-chevron-right agent-action-chevron"></i>
                            </button>
                        </template>
                        <template v-else>
                            <button type="button" class="agent-action" @click="runAgentAction('view')">
                                <span class="agent-action-icon is-blue"><i class="fa-solid fa-eye"></i></span>
                                <span><strong>Просмотр</strong><small>Открыть карточку администратора</small></span>
                                <i class="fa-solid fa-chevron-right agent-action-chevron"></i>
                            </button>
                            <button type="button" class="agent-action" @click="runAgentAction('edit')">
                                <span class="agent-action-icon is-indigo"><i class="fa-solid fa-pen-to-square"></i></span>
                                <span><strong>Редактировать</strong><small>Изменить данные администратора</small></span>
                                <i class="fa-solid fa-chevron-right agent-action-chevron"></i>
                            </button>
                            <button type="button" class="agent-action" @click="runAgentAction('statistics')">
                                <span class="agent-action-icon is-green"><i class="fa-solid fa-chart-column"></i></span>
                                <span><strong>Выгрузка статистики</strong><small>Сформировать персональный отчёт</small></span>
                                <i class="fa-solid fa-chevron-right agent-action-chevron"></i>
                            </button>
                            <button type="button" class="agent-action" @click="runAgentAction('percentages')">
                                <span class="agent-action-icon is-violet"><i class="fa-solid fa-percent"></i></span>
                                <span><strong>Проценты</strong><small>Настроить распределение выплат</small></span>
                                <i class="fa-solid fa-chevron-right agent-action-chevron"></i>
                            </button>
                            <div class="agent-action-divider"></div>
                            <button type="button" class="agent-action is-danger" @click="runAgentAction('delete')">
                                <span class="agent-action-icon is-red"><i class="fa-solid fa-trash"></i></span>
                                <span><strong>Удалить</strong><small>Удалить администратора из списка</small></span>
                                <i class="fa-solid fa-chevron-right agent-action-chevron"></i>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

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

        <!-- Модалка редактирования пользователя -->
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

        <!-- Модалка персональной статистики -->
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

        <!-- 🔹 Модалка управления процентами -->
        <div class="modal fade" id="percentagesModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-percent text-primary me-2"></i>
                            Управление процентами
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <PercentageManager
                            v-if="selectedAgent"
                            owner-type="agent"
                            :owner-id="selectedAgent.id"
                            :owner-name="selectedAgent.name"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import AgentForm from './AgentForm.vue'
import AgentInfo from "@/Components/Agents/AgentInfo.vue";
import Pagination from "@/Components/Pagination.vue";
import UserForm from "@/Components/Users/UserForm.vue";
import ReportIndividualGenerator from "@/Components/Admins/ReportIndividualGenerator.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
import PercentageManager from "@/Components/Percentage/PercentageManager.vue";
import {useAgentsStore} from "@/stores/agents";
import {useAdminsStore} from "@/stores/admins";
import {useBaseExports} from "@/stores/baseExports";
import {useUsersStore} from "@/stores/users";

export default {
    name: 'AgentList',
    components: {
        AgentForm,
        AgentInfo,
        Pagination,
        UserForm,
        ReportIndividualGenerator,
        SaleCard,
        PercentageManager
    },
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
            size: 20,
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
    watch: {
        size: function () {
            this.fetchAgents()
        },
        search: function (newVal) {
            if (this.viewMode !== 'all') {
                this.loadByMode(1)
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
        openAgentActions(agent) {
            this.selectedAgent = agent
            this.$nextTick(() => {
                const element = document.getElementById('agentActionsModal')
                if (element) bootstrap.Modal.getOrCreateInstance(element).show()
            })
        },

        closeAgentActions() {
            const element = document.getElementById('agentActionsModal')
            if (element) bootstrap.Modal.getInstance(element)?.hide()
        },

        runAgentAction(action) {
            const agent = this.selectedAgent
            this.closeAgentActions()
            if (!agent) return
            this.$nextTick(() => {
                switch (action) {
                    case 'select': this.selectAgent(agent); break
                    case 'view': this.openAgentInfo(agent); break
                    case 'edit': this.openEditAgent(agent); break
                    case 'statistics': this.getPersonalStatistic(agent); break
                    case 'percentages': this.openPercentages(agent); break
                    case 'delete': this.confirmDelete(agent); break
                }
            })
        },

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
                months.push({key: `${year}-${month}`, label: `${year}-${month}`})
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
            this.selectedAgent = null
            this.$nextTick(() => {
                this.selectedAgent = {...agent}
                this.$nextTick(() => {
                    const modal = new bootstrap.Modal(document.getElementById('agentInfoModal'))
                    modal.show()
                })
            })
        },

        openPercentages(agent) {
            this.selectedAgent = null
            this.$nextTick(() => {
                this.selectedAgent = {...agent}
                this.$nextTick(() => {
                    const modal = new bootstrap.Modal(document.getElementById('percentagesModal'))
                    modal.show()
                })
            })
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
        },

        fetchData() {
            this.fetchAgents()
        }
    }
}
</script>

<style scoped>

.agent-list-redesign { --agent-blue:#2056b4; --agent-ink:#172d50; --agent-red:#dc3b52; color:var(--agent-ink); padding:8px 0 24px; }
.agent-toolbar { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
.agent-toolbar-title { display:flex; align-items:center; gap:12px; }
.agent-toolbar-title > i { width:46px; height:46px; display:grid; place-items:center; background:#eaf1ff; color:var(--agent-blue); border-radius:14px; font-size:20px; }
.agent-toolbar-title strong { display:block; font-size:19px; letter-spacing:-.3px; }
.agent-toolbar-title small { display:block; color:#7b8aa1; font-size:12px; margin-top:2px; }
.agent-tabs { display:flex; gap:8px; padding:5px; background:#f1f5fb; border-radius:15px; }
.agent-tabs .nav-item { min-width:0; }
.agent-tabs .nav-link { width:100%; color:#64758e; border-radius:11px; font-weight:650; padding:12px 6px; font-size:13px; transition:.2s; }
.agent-tabs .nav-link.active { background:#fff; color:var(--agent-blue); box-shadow:0 3px 12px #183f7c15; }
.agent-tabs .nav-link.active i { color:inherit !important; }
.agent-month .form-select,.agent-search .form-control { border:1px solid #e0e8f3; border-radius:13px; min-height:54px; background-color:#fff; }
.agent-month .form-select:focus,.agent-search .form-control:focus { border-color:#7ca7e8; box-shadow:0 0 0 3px #2056b41a; }
.agent-stats { background:#f1f6ff; border:1px solid #dfeaff; border-radius:15px; }
.agent-stats .fw-bold { color:var(--agent-blue); }
.agent-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,340px),1fr)); gap:12px; padding:0; }
.agent-card { min-width:0; border:1px solid #e5ebf4 !important; border-radius:17px !important; padding:17px !important; background:#fff; box-shadow:0 4px 18px #162f5610; align-items:flex-start !important; transition:transform .2s,box-shadow .2s; }
.agent-card:hover { transform:translateY(-2px); box-shadow:0 8px 26px #162f561c; }
.agent-card > div:first-child { min-width:0; overflow-wrap:anywhere; }
.agent-card.border-primary { border-color:var(--agent-blue) !important; box-shadow:0 0 0 2px #2056b421; }
.agent-card.bg-danger { background:#fff4f5 !important; border-color:#f2cbd2 !important; }
.agent-card .fw-bold > span:not(.badge) { cursor:pointer; font-size:16px; }
.agent-card .fw-bold > span:not(.badge):hover { color:var(--agent-blue); }
.agent-card small.text-muted { display:block; margin-top:7px; font-size:13px; }
.agent-badge-primary,.agent-badge-soft { display:inline-block; border-radius:8px; padding:6px 9px; font-size:11px; }
.agent-badge-primary { background:#e8f0ff; color:#2457a7; }
.agent-badge-soft { background:#fff0f2; color:#c73d56; }
.agent-card .badge.bg-success { background:#e5f7ef !important; color:#16845c; }
.agent-card .badge.bg-info { background:#e9f2ff !important; color:#2457a7 !important; }
.agent-card .badge.bg-secondary { background:#eef0f5 !important; color:#66738b; }
.agent-card .badge { padding:7px 9px; border-radius:8px; white-space:normal; }
.agent-menu-btn { border:1px solid #e1e9f4; background:#f7f9fd; color:#42618c; border-radius:10px; min-width:36px; min-height:36px; }
.agent-menu-btn:hover { background:#e9f0fb; color:var(--agent-blue); }
.agent-menu-btn { display:grid; place-items:center; font-size:16px; }
.agent-actions-modal { --bs-modal-bg: #ffffff; --bs-modal-color: #172d50; background: transparent; }
.agent-actions-modal .modal-dialog-bottom-sheet { position:fixed; left:0; right:0; bottom:0; width:100%; max-width:none; margin:0; transform:translateY(100%); transition:transform .28s ease-out; }
.agent-actions-modal.show .modal-dialog-bottom-sheet { transform:translateY(0); }
.agent-actions-modal .modal-content { display:block; background:#ffffff !important; color:#172d50; opacity:1 !important; border-radius:24px 24px 0 0; border:1px solid #e5ebf4; box-shadow:0 -12px 45px #142c5033; padding-bottom:env(safe-area-inset-bottom, 0px); overflow:hidden; }
.agent-sheet-handle { width:42px; height:5px; border-radius:999px; background:#d6deea; margin:10px auto 0; }
.agent-actions-modal .modal-header { display:flex; background:#ffffff !important; border-bottom:1px solid #e8edf5; padding:18px 20px 14px; }
.agent-sheet-person { display:flex; align-items:center; gap:12px; min-width:0; }
.agent-sheet-avatar { display:grid; place-items:center; width:46px; height:46px; flex:0 0 46px; border-radius:15px; background:#eaf1ff; color:var(--agent-blue); font-size:19px; }
.agent-sheet-name { font-size:14px; font-weight:700; color:var(--agent-ink); margin-top:3px; overflow-wrap:anywhere; }
.agent-actions-modal .modal-body { display:block; background:#ffffff !important; padding:10px 14px 18px; }
.agent-actions-list { display:flex; flex-direction:column; gap:5px; }
.agent-action { appearance:none; display:flex; align-items:center; gap:12px; width:100%; padding:12px 10px; border:0; border-radius:14px; background:#ffffff !important; color:var(--agent-ink); text-align:left; transition:background .18s, transform .18s; }
.agent-action:hover,.agent-action:focus-visible { background:#f3f6fc; outline:none; }
.agent-action:active { transform:scale(.99); }
.agent-action > span:nth-child(2) { display:flex; flex:1; min-width:0; flex-direction:column; gap:3px; }
.agent-action strong { font-size:14px; font-weight:700; }
.agent-action small { color:#8190a7; font-size:12px; line-height:1.35; }
.agent-action-icon { width:42px; height:42px; flex:0 0 42px; display:grid; place-items:center; border-radius:13px; font-size:16px; }
.agent-action-icon.is-blue { background:#e8f0ff; color:#2457a7; }
.agent-action-icon.is-indigo { background:#eeedff; color:#6254c7; }
.agent-action-icon.is-green { background:#e5f7ef; color:#16845c; }
.agent-action-icon.is-violet { background:#f3eaff; color:#8652bf; }
.agent-action-icon.is-red { background:#fff0f2; color:#c73d56; }
.agent-action-chevron { color:#a1aec1; font-size:12px; }
.agent-action-divider { height:1px; margin:4px 8px; background:#edf0f5; }
.agent-action.is-danger strong { color:#c73d56; }
@media(min-width:576px) { .agent-actions-modal .modal-dialog-bottom-sheet { left:50%; right:auto; bottom:24px; width:min(100% - 32px, 480px); transform:translate(-50%, 30px); } .agent-actions-modal.show .modal-dialog-bottom-sheet { transform:translate(-50%, 0); } .agent-actions-modal .modal-content { border-radius:24px; } }
.agent-list-redesign .modal-content { border:0; border-radius:20px; overflow:hidden; }
.agent-list-redesign .modal-header { background:#f7faff; border-bottom:1px solid #e7edf5; padding:18px 22px; }
.agent-list-redesign .modal-title { font-weight:700; color:var(--agent-ink); }
.agent-list-redesign .modal-body { padding:22px; }
.agent-list-redesign .modal-footer { border-top:1px solid #e7edf5; padding:15px 22px; }
.agent-list-redesign .btn-primary { background:var(--agent-blue); border-color:var(--agent-blue); }
.agent-list-redesign .btn-danger { background:var(--agent-red); border-color:var(--agent-red); }
.agent-empty { text-align:center; padding:40px 12px; color:#8190a7; }
.agent-empty i { font-size:30px; margin-bottom:10px; }
.agent-empty p { margin:0; }
.clickable-badge { cursor:pointer; display:inline-flex; align-items:center; transition:transform .2s,box-shadow .2s; }
.clickable-badge:hover { transform:translateY(-2px); box-shadow:0 3px 12px #1d9d7233; }
@media(max-width:575px) { .agent-toolbar-title strong { font-size:17px; } .agent-tabs .nav-link { font-size:12px; padding:11px 3px; } .agent-tabs .nav-link i { margin-right:3px !important; } .agent-card { padding:14px !important; } .agent-list-redesign .modal-body { padding:16px; } }
</style>
