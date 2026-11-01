<template>
    <section class="admin-list-page">
        <header class="admin-list-heading">
            <div class="heading-icon"><i class="fa-solid fa-user-shield"></i></div>
            <div class="heading-copy">
                <span class="eyebrow">УПРАВЛЕНИЕ ДОСТУПОМ</span>
                <h2>Старшие администраторы</h2>
                <p>Управление сотрудниками, доступом и рабочей активностью</p>
            </div>
            <div class="heading-count" v-if="adminsStore.pagination?.total != null">
                <span>{{ adminsStore.pagination.total }}</span>
                <small>всего</small>
            </div>
        </header>

        <div class="admin-list-content">
            <div class="list-section-title">
                <div>
                    <h3>Список старших администраторов</h3>
                    <p>Поиск, фильтрация и управление учётными записями</p>
                </div>
                <span class="section-icon"><i class="fa-solid fa-users-gear"></i></span>
            </div>

            <!-- Режимы списка -->
            <div class="admin-tabs" v-if="!forSelect">
                <button type="button" :class="{ active: viewMode === 'all' }" @click="viewMode = 'all'">
                    <i class="fa-solid fa-list"></i> Все
                </button>
                <button type="button" :class="{ active: viewMode === 'active' }" @click="viewMode = 'active'">
                    <i class="fa-solid fa-bolt"></i> Работающие
                </button>
                <button type="button" :class="{ active: viewMode === 'inactive' }" @click="viewMode = 'inactive'">
                    <i class="fa-solid fa-moon"></i> Неактивные
                </button>
            </div>

            <!-- Поиск и сортировка -->
            <div class="admin-controls" v-if="!forSelect">
                <div class="admin-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input v-model.trim="search" type="search" placeholder="Поиск по имени, телефону или ФИО...">
                    <button v-if="search" type="button" aria-label="Очистить поиск" @click="search = ''">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="admin-sort">
                    <select v-model="sortBy" class="form-select" aria-label="Сортировка">
                        <option value="id">По ID</option>
                        <option value="name">По имени</option>
                        <option value="created_at">По дате регистрации</option>
                    </select>
                    <button type="button" class="sort-direction" @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                            :title="sortDirection === 'asc' ? 'По возрастанию' : 'По убыванию'">
                        <i :class="sortDirection === 'asc' ? 'fa-solid fa-arrow-up-wide-short' : 'fa-solid fa-arrow-down-wide-short'"></i>
                    </button>
                </div>
            </div>

            <ul v-if="sortedAdmins.length" class="admin-items">
                <li v-for="user in sortedAdmins" :key="user.id" class="admin-item">
                    <div class="admin-avatar" :class="{ 'is-offline': !user.is_work }">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>

                    <div class="admin-main">
                        <button type="button" class="admin-name" @click="forSelect ? selectAdmin(user) : openView(user)">
                            {{ user.name || 'Без имени' }}
                        </button>
                        <div v-if="user.fio_from_telegram" class="admin-fullname">{{ user.fio_from_telegram }}</div>
                        <div class="admin-phone">
                            <i class="fa-solid fa-phone"></i>
                            {{ user.phone || 'Телефон не указан' }}
                        </div>
                        <div class="admin-meta" v-if="user.email">
                            <i class="fa-solid fa-envelope"></i> {{ user.email }}
                        </div>
                        <div class="admin-status-row">
                            <span class="status-pill" :class="user.is_work ? 'status-working' : 'status-paused'">
                                <span class="status-dot"></span>
                                {{ user.is_work ? 'Работает' : 'Не работает' }}
                            </span>
                            <div class="form-check form-switch status-switch mb-0" v-if="!forSelect">
                                <input
                                    @change="changeAdminWorkStatus(user)"
                                    class="form-check-input"
                                    type="checkbox"
                                    v-model="user.is_work"
                                    :id="`is-work-${user.id}`"
                                />
                                <label class="form-check-label" :for="`is-work-${user.id}`">Рабочий статус</label>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-sm admin-menu-btn" type="button"
                            aria-label="Действия с администратором"
                            @click.stop="openAdminActions(user)">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                </li>
            </ul>

            <div v-else class="empty-state">
                <div class="empty-icon"><i class="fa-solid fa-users-slash"></i></div>
                <h4>{{ search ? 'Ничего не найдено' : 'Администраторов в этой категории нет' }}</h4>
                <p>{{ search ? 'Измените поисковый запрос или очистите фильтр.' : 'Когда администраторы появятся, они будут отображаться здесь.' }}</p>
                <button v-if="search" type="button" class="btn btn-sm btn-outline-primary mt-2" @click="search = ''">Очистить поиск</button>
            </div>

            <div v-if="adminsStore.items.length > 0" class="pagination-panel">
                <div class="pagination-caption"><i class="fa-solid fa-list-ol me-2"></i>Страницы списка</div>
                <Pagination :pagination="adminsStore.pagination" @page-changed="fetchDataByUrl" />
            </div>
        </div>

        <!-- Нижняя модалка действий старшего администратора -->
        <div class="modal fade admin-actions-modal" id="adminActionsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-bottom-sheet">
                <div class="modal-content">
                    <div class="admin-sheet-handle" aria-hidden="true"></div>
                    <div class="modal-header">
                        <div class="admin-sheet-person">
                            <span class="admin-sheet-avatar"><i class="fa-solid fa-user-tie"></i></span>
                            <div>
                                <h5 class="modal-title">Действия</h5>
                                <div class="admin-sheet-name">{{ selectedAdmin?.name || 'Администратор' }}</div>
                                <small class="text-muted">{{ selectedAdmin?.phone || 'Телефон не указан' }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body admin-actions-list">
                        <template v-if="forSelect">
                            <button type="button" class="admin-action" @click="runAdminAction('select')">
                                <span class="admin-action-icon is-blue"><i class="fa-solid fa-check"></i></span>
                                <span><strong>Выбрать администратора</strong><small>Использовать выбранную запись</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                        </template>
                        <template v-else>
                            <button
                                v-if="!getAgentId(selectedAdmin)"
                                type="button"
                                class="admin-action"
                                @click="runAdminAction('createAgentProfile')"
                            >
                                <span class="admin-action-icon is-green"><i class="fa-solid fa-user-plus"></i></span>
                                <span><strong>Создать профиль</strong><small>Создать профиль Agent для этой учётной записи</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('view')">
                                <span class="admin-action-icon is-blue"><i class="fa-solid fa-eye"></i></span>
                                <span><strong>Просмотр</strong><small>Открыть карточку администратора</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('edit')">
                                <span class="admin-action-icon is-indigo"><i class="fa-solid fa-pen-to-square"></i></span>
                                <span><strong>Редактировать</strong><small>Изменить данные администратора</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('salary')">
                                <span class="admin-action-icon is-green"><i class="fa-solid fa-wallet"></i></span>
                                <span><strong>Доход администратора</strong><small>Посмотреть зарплату и начисления</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('report')">
                                <span class="admin-action-icon is-green"><i class="fa-solid fa-file-lines"></i></span>
                                <span><strong>Сформировать отчёт</strong><small>Выгрузить отчёт по администратору</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('sales')">
                                <span class="admin-action-icon is-blue"><i class="fa-solid fa-receipt"></i></span>
                                <span><strong>Сделки</strong><small>Посмотреть сделки администратора</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('statistics')">
                                <span class="admin-action-icon is-green"><i class="fa-solid fa-chart-column"></i></span>
                                <span><strong>Персональная статистика</strong><small>Сформировать статистику за период</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('percentages')">
                                <span class="admin-action-icon is-violet"><i class="fa-solid fa-percent"></i></span>
                                <span><strong>Проценты</strong><small>Настроить распределение выплат</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('role')">
                                <span class="admin-action-icon is-indigo"><i class="fa-solid fa-user-tag"></i></span>
                                <span><strong>Сменить роль</strong><small>Изменить уровень доступа</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('impersonate')">
                                <span class="admin-action-icon is-blue"><i class="fa-solid fa-user-secret"></i></span>
                                <span><strong>Войти от имени</strong><small>Открыть приложение под этой учётной записью</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <button type="button" class="admin-action" @click="runAdminAction('resetPassword')">
                                <span class="admin-action-icon is-indigo"><i class="fa-solid fa-key"></i></span>
                                <span><strong>Сбросить пароль</strong><small>Установить временный пароль 12345678</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                            <div class="admin-action-divider"></div>
                            <button type="button" class="admin-action is-danger" @click="runAdminAction('delete')">
                                <span class="admin-action-icon is-red"><i class="fa-solid fa-trash"></i></span>
                                <span><strong>Удалить</strong><small>Удалить администратора из списка</small></span>
                                <i class="fa-solid fa-chevron-right admin-action-chevron"></i>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Просмотр старшего администратора -->
        <div class="modal fade" id="adminInfoModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content redesigned-modal">
                    <div class="modal-header">
                        <div class="modal-title-wrap">
                            <span class="modal-icon"><i class="fa-solid fa-user"></i></span>
                            <h5 class="modal-title">Информация об администраторе</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body" v-if="selectedAdmin">
                        <div class="admin-info-card">
                            <div class="admin-info-avatar"><i class="fa-solid fa-user-tie"></i></div>
                            <h4>{{ selectedAdmin.name || 'Без имени' }}</h4>
                            <p v-if="selectedAdmin.fio_from_telegram">{{ selectedAdmin.fio_from_telegram }}</p>
                            <span class="status-pill" :class="selectedAdmin.is_work ? 'status-working' : 'status-paused'">
                                <span class="status-dot"></span>{{ selectedAdmin.is_work ? 'Работает' : 'Не работает' }}
                            </span>
                        </div>
                        <dl class="admin-details">
                            <div><dt>ID</dt><dd>{{ selectedAdmin.id }}</dd></div>
                            <div><dt>Телефон</dt><dd>{{ selectedAdmin.phone || 'Не указан' }}</dd></div>
                            <div><dt>Email</dt><dd>{{ selectedAdmin.email || 'Не указан' }}</dd></div>
                            <div><dt>Роль</dt><dd>{{ roleLabel(selectedAdmin.role) }}</dd></div>
                            <div><dt>Дата регистрации</dt><dd>{{ formatDate(selectedAdmin.created_at) }}</dd></div>
                        </dl>
                        <button type="button" class="btn btn-primary action-submit" @click="editFromInfo">
                            <i class="fa-solid fa-pen-to-square me-2"></i>Редактировать
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка отчёта -->
        <div class="modal fade" id="reportModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content redesigned-modal">
                    <div class="modal-header">
                        <div class="modal-title-wrap">
                            <span class="modal-icon"><i class="fa-solid fa-file-chart-column"></i></span>
                            <h5 class="modal-title">Отчёт по администратору</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <ReportGenerator v-if="selectedAdmin" :type="'admins'" :is-simple="true"
                                         :object-id="selectedAdmin.id" @generate-report="handleReport" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка смены роли -->
        <div class="modal fade" id="roleSwitcherUserModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content redesigned-modal">
                    <div class="modal-header">
                        <div class="modal-title-wrap">
                            <span class="modal-icon"><i class="fa-solid fa-user-tag"></i></span>
                            <h5 class="modal-title">Изменение роли</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body" v-if="selectedAdmin">
                        <p class="modal-description">Настройте уровень доступа для <strong>{{ selectedAdmin.name }}</strong>.</p>
                        <form @submit.prevent="changeRole">
                            <div class="form-floating mb-3">
                                <select v-model.number="selectedAdmin.role" class="form-select" id="adminRole" required>
                                    <option :value="0">Пользователь</option>
                                    <option :value="1">Администратор</option>
                                    <option :value="2">Поставщик</option>
                                    <option :value="3">Старший администратор</option>
                                </select>
                                <label for="adminRole">Роль</label>
                            </div>
                            <button type="submit" class="btn btn-primary action-submit">
                                <i class="fa-solid fa-check me-2"></i>Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка редактирования -->
        <div class="modal fade" id="editUserModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content redesigned-modal">
                    <div class="modal-header">
                        <div class="modal-title-wrap">
                            <span class="modal-icon"><i class="fa-solid fa-user-pen"></i></span>
                            <h5 class="modal-title">Редактирование администратора</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <UserForm v-if="selectedAdmin" :initialData="selectedAdmin" @saved="fetchData" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Персональная статистика: тот же генератор, что и для агентов -->
        <div class="modal fade" id="adminPersonalStatisticModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Персональная статистика за период</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ReportIndividualGenerator v-if="selectedAdmin && getAgentId(selectedAdmin)" :agent-id="getAgentId(selectedAdmin)" @generate-report="closePersonalStatistic" />
                        <div v-else-if="selectedAdmin" class="alert alert-warning mb-0">
                            <div>У этой учётной записи не найден связанный профиль администратора (Agent). Проверьте связь users.id → agents.user_id.</div>
                            <button type="button" class="agent-profile-create-btn mt-3" :disabled="isCreatingAgentProfile" @click="createAgentProfile(selectedAdmin)">
                                <span class="agent-profile-create-icon"><i class="fa-solid fa-user-plus" :class="{ 'fa-spin': isCreatingAgentProfile }"></i></span>
                                <span class="agent-profile-create-copy">
                                    <strong>{{ isCreatingAgentProfile ? 'Создаём профиль...' : 'Создать профиль Agent' }}</strong>
                                    <small>Связать профиль с этой учётной записью</small>
                                </span>
                                <i class="fa-solid fa-arrow-right agent-profile-create-arrow"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Сделки: agentStore.fetchAgentSales получает именно agents.id -->
        <div class="modal fade" id="adminSalesModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Сделки: {{ selectedAdmin?.name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="agentStore.loading" class="text-center py-4"><div class="spinner-border text-primary"></div></div>
                        <template v-else>
                            <div v-if="agentStore.agentSales?.length">
                                <SaleCard v-for="sale in agentStore.agentSales" :key="sale.id" class="shadow-sm mb-2 rounded-2 p-2"
                                          :field_visible="field_visible" :saleStatuses="saleStatuses" :sale="sale" />
                                <Pagination v-if="agentStore.agentSalesPagination?.total > 0"
                                            :pagination="agentStore.agentSalesPagination" @page-changed="fetchAdminSalesPage" />
                            </div>
                            <div v-else class="text-center text-muted py-5">За выбранный период сделок не найдено</div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Управление процентами: передаём agents.id, а не users.id -->
        <div class="modal fade" id="adminPercentagesModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Управление процентами</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <PercentageManager v-if="selectedAdmin && getAgentId(selectedAdmin)" owner-type="agent" :owner-id="getAgentId(selectedAdmin)"
                                           :owner-name="selectedAdmin.name" :owner-percent="selectedAdmin.agent?.percent ?? selectedAdmin.percent ?? 0"
                                           @saved="fetchData" />
                        <div v-else-if="selectedAdmin" class="alert alert-warning mb-0">
                            <div>Нельзя открыть проценты: у учётной записи отсутствует связанный профиль Agent. Для этого действия нужен agents.id, а не users.id.</div>
                            <button type="button" class="agent-profile-create-btn mt-3" :disabled="isCreatingAgentProfile" @click="createAgentProfile(selectedAdmin)">
                                <span class="agent-profile-create-icon"><i class="fa-solid fa-user-plus" :class="{ 'fa-spin': isCreatingAgentProfile }"></i></span>
                                <span class="agent-profile-create-copy">
                                    <strong>{{ isCreatingAgentProfile ? 'Создаём профиль...' : 'Создать профиль Agent' }}</strong>
                                    <small>Связать профиль с этой учётной записью</small>
                                </span>
                                <i class="fa-solid fa-arrow-right agent-profile-create-arrow"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Удаление Agent-профиля через agentStore.remove -->
        <div class="modal fade" id="adminDeleteModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header"><h5 class="modal-title text-danger">Удаление администратора</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">Вы уверены, что хотите удалить <strong>{{ selectedAdmin?.name }}</strong>?</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-danger" @click="deleteAdmin">Удалить</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from "axios";
import { defineAsyncComponent } from "vue";
import Pagination from "@/Components/Pagination.vue";
import UserForm from "@/Components/Users/UserForm.vue";
const ReportGenerator = defineAsyncComponent(() =>
    import("@/Components/Admins/ReportGenerator.vue")
);
import ReportIndividualGenerator from "@/Components/Admins/ReportIndividualGenerator.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
import PercentageManager from "@/Components/Percentage/PercentageManager.vue";
import { useAgentsStore } from "@/stores/agents";
import { useAdminsStore } from "@/stores/admins";
import { useUsersStore } from "@/stores/users";

export default {
    name: "AdminList",
    components: {
        Pagination,
        UserForm,
        ReportGenerator,
        ReportIndividualGenerator,
        SaleCard,
        PercentageManager,
    },
    props: ["forSelect"],
    data() {
        return {
            adminsStore: useAdminsStore(),
            agentStore: useAgentsStore(),
            userStore: useUsersStore(),
            selectedAdmin: null,
            isRefreshingAgentProfile: false,
            isCreatingAgentProfile: false,
            search: "",
            sortBy: "id",
            sortDirection: "desc",
            viewMode: "all",
            selectedMonth: this.getCurrentMonth(),
            field_visible: null,
            saleStatuses: {
                pending: "В ожидании",
                assigned: "Назначено",
                completed: "Завершено",
                rejected: "Отклонено",
                delivered: "Доставляется",
            },
        };
    },
    computed: {
        visibleAdmins() {
            const items = this.adminsStore.items || [];
            const query = this.search.toLocaleLowerCase("ru").trim();

            return items.filter((user) => {
                if (this.viewMode === "active" && !user.is_work) return false;
                if (this.viewMode === "inactive" && user.is_work) return false;
                if (!query) return true;

                const searchable = [
                    user.name,
                    user.phone,
                    user.email,
                    user.fio_from_telegram,
                    user.id,
                ];
                return searchable.some((value) =>
                    value != null && String(value).toLocaleLowerCase("ru").includes(query)
                );
            });
        },
        sortedAdmins() {
            const items = [...this.visibleAdmins];
            const direction = this.sortDirection === "asc" ? 1 : -1;

            return items.sort((a, b) => {
                if (this.sortBy === "name") {
                    return String(a.name || "").localeCompare(
                        String(b.name || ""),
                        "ru",
                        { sensitivity: "base" }
                    ) * direction;
                }

                if (this.sortBy === "created_at") {
                    const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
                    const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
                    return (dateA - dateB) * direction;
                }

                return ((Number(a.id) || 0) - (Number(b.id) || 0)) * direction;
            });
        },
    },
    created() {
        this.fetchData();
    },
    methods: {
        // В списке администраторов id — это users.id.
        // Для операций с моделью Agent используем только связанный agents.id.
        getAgentId(admin) {
            const value = admin?.agent?.id ?? admin?.agent_id ?? admin?.agentId ?? null;
            const id = Number(value);
            return Number.isInteger(id) && id > 0 ? id : null;
        },

        openAdminActions(admin) {
            this.selectedAdmin = { ...admin };
            this.$nextTick(() => {
                const element = document.getElementById("adminActionsModal");
                if (element) bootstrap.Modal.getOrCreateInstance(element).show();
            });
        },

        closeAdminActions() {
            const element = document.getElementById("adminActionsModal");
            if (element) bootstrap.Modal.getInstance(element)?.hide();
        },

        runAdminAction(action) {
            const admin = this.selectedAdmin;
            this.closeAdminActions();

            if (!admin) return;

            // Открываем новую сессию сразу из пользовательского действия,
            // чтобы браузер не блокировал переход.
            if (action === "impersonate") {
                this.impersonateAdmin(admin);
                return;
            }

            this.$nextTick(() => {
                switch (action) {
                    case "select":
                        this.selectAdmin(admin);
                        break;
                    case "view":
                        this.openView(admin);
                        break;
                    case "edit":
                        this.openEdit(admin);
                        break;
                    case "salary":
                        this.openAdminSalary(admin);
                        break;
                    case "report":
                        this.openReport(admin);
                        break;
                    case "sales":
                        this.openAdminSales(admin);
                        break;
                    case "statistics":
                        this.openPersonalStatistic(admin);
                        break;
                    case "percentages":
                        this.openPercentages(admin);
                        break;
                    case "createAgentProfile":
                        this.createAgentProfile(admin);
                        break;
                    case "role":
                        this.openRoleSwitcher(admin);
                        break;
                    case "resetPassword":
                        this.resetAdminPassword(admin);
                        break;
                    case "delete":
                        this.confirmDeleteAdmin(admin);
                        break;
                }
            });
        },

        selectAdmin(admin) {
            if (!this.forSelect) return;
            this.$emit("select", admin);
        },

        openView(user) {
            this.selectedAdmin = { ...user };
            this.$nextTick(() => {
                const element = document.getElementById("adminInfoModal");
                if (element) bootstrap.Modal.getOrCreateInstance(element).show();
            });
        },

        editFromInfo() {
            const infoModal = bootstrap.Modal.getInstance(document.getElementById("adminInfoModal"));
            infoModal?.hide();
            this.$nextTick(() => this.openEdit(this.selectedAdmin));
        },

        openRoleSwitcher(user) {
            this.selectedAdmin = { ...user };
            this.$nextTick(() => {
                const element = document.getElementById("roleSwitcherUserModal");
                if (element) bootstrap.Modal.getOrCreateInstance(element).show();
            });
        },

        openEdit(user) {
            this.selectedAdmin = { ...user };
            this.$nextTick(() => {
                const element = document.getElementById("editUserModal");
                if (element) bootstrap.Modal.getOrCreateInstance(element).show();
            });
        },

        openReport(user) {
            this.selectedAdmin = { ...user };
            this.$nextTick(() => {
                const element = document.getElementById("reportModal");
                if (element) bootstrap.Modal.getOrCreateInstance(element).show();
            });
        },

        async changeAdminWorkStatus(user) {
            const previousValue = !user.is_work;
            try {
                await this.userStore.updateWorkStatus(user.id, user.is_work);
            } catch (error) {
                user.is_work = previousValue;
                console.error("Не удалось изменить рабочий статус:", error);
                window.alert(error?.response?.data?.message || "Не удалось изменить рабочий статус.");
            }
        },

        async handleReport(form) {
            if (!this.selectedAdmin?.id) return;
            const payload = { ...form, user_id: this.selectedAdmin.id };
            await this.adminsStore.downloadPersonalAdminReport(payload);
        },

        async fetchData(page = 1) {
            try {
                await this.adminsStore.fetchAdminsByPage(page);

                if (this.selectedAdmin?.id) {
                    const updated = (this.adminsStore.items || []).find(
                        (item) => Number(item.id) === Number(this.selectedAdmin.id)
                    );
                    if (updated) this.selectedAdmin = { ...updated };
                }

                const editModal = bootstrap.Modal.getInstance(document.getElementById("editUserModal"));
                if (editModal) editModal.hide();
            } catch (error) {
                console.error("Ошибка загрузки старших администраторов:", error);
            }
        },

        async fetchDataByUrl(url) {
            try {
                await this.adminsStore.fetchByUrl(url);
            } catch (error) {
                console.error("Ошибка переключения страницы:", error);
            }
        },

        async changeRole() {
            if (!this.selectedAdmin?.id) return;

            try {
                await this.userStore.updateRole(this.selectedAdmin.id, this.selectedAdmin.role);
                await this.fetchData();

                const modal = bootstrap.Modal.getInstance(document.getElementById("roleSwitcherUserModal"));
                if (modal) modal.hide();
            } catch (error) {
                console.error("Ошибка изменения роли:", error);
                window.alert(error?.response?.data?.message || "Не удалось изменить роль.");
            }
        },

        async impersonateAdmin(user) {
            if (!user?.id) return;

            const confirmed = window.confirm(
                `Войти под учётной записью «${user.name || "администратора"}»?`
            );
            if (!confirmed) return;

            try {
                // Используется существующий метод авторизации от имени пользователя.
                await this.userStore.impersonateAgent(user);
                window.location.href = "/pwa";
            } catch (error) {
                console.error("Ошибка входа от имени администратора:", error);
                window.alert(error?.response?.data?.message || "Не удалось войти от имени администратора.");
            }
        },

        getCurrentMonth() {
            const now = new Date();
            return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}`;
        },

        // Повторно загружает пользователя вместе с relation agent.
        // Используем users.id только для обновления учётной записи; для
        // статистики и процентов далее будет взят исключительно agents.id.
        async refreshSelectedAdminProfile() {
            const userId = Number(this.selectedAdmin?.id);
            if (!Number.isInteger(userId) || userId <= 0 || this.isRefreshingAgentProfile) return;

            this.isRefreshingAgentProfile = true;
            try {
                await this.fetchData();

                const refreshed = (this.adminsStore.items || []).find(
                    item => Number(item.id) === userId
                );
                if (refreshed) {
                    this.selectedAdmin = { ...refreshed };
                }

                if (this.getAgentId(this.selectedAdmin)) {
                    window.alert("Связанный профиль Agent найден. Данные обновлены.");
                } else {
                    window.alert("Данные учётной записи обновлены, но связанный профиль Agent по-прежнему не найден. Проверьте наличие записи agents с user_id = users.id.");
                }
            } catch (error) {
                console.error("Ошибка обновления профиля администратора:", error);
                window.alert(error?.response?.data?.message || "Не удалось обновить профиль администратора.");
            } finally {
                this.isRefreshingAgentProfile = false;
            }
        },

        async createAgentProfile(admin) {
            const userId = Number(admin?.id);
            if (!Number.isInteger(userId) || userId <= 0 || this.isCreatingAgentProfile) return;

            if (this.getAgentId(admin)) {
                this.selectedAdmin = { ...admin };
                window.alert("Профиль Agent уже существует.");
                return;
            }

            const confirmed = window.confirm(
                `Создать профиль Agent для «${admin.name || "этого администратора"}»?

Будет создана запись agents с user_id = ${userId}. Учётная запись users изменена не будет.`
            );
            if (!confirmed) return;

            this.isCreatingAgentProfile = true;
            try {
                // Отдельный backend endpoint. В URL передаётся users.id;
                // сервер сам создаёт Agent и связывает его через user_id.
                const { data } = await axios.post(`/api/users/${userId}/create-agent-profile`);
                const updatedAdmin = data?.user || data;

                if (updatedAdmin?.id) {
                    this.selectedAdmin = { ...updatedAdmin };
                    const index = (this.adminsStore.items || []).findIndex(
                        item => Number(item.id) === userId
                    );
                    if (index !== -1) this.adminsStore.items.splice(index, 1, { ...updatedAdmin });
                } else {
                    await this.fetchData();
                    const refreshed = (this.adminsStore.items || []).find(item => Number(item.id) === userId);
                    if (refreshed) this.selectedAdmin = { ...refreshed };
                }

                if (!this.getAgentId(this.selectedAdmin)) {
                    throw new Error("Сервер ответил успешно, но не вернул связанный профиль Agent.");
                }

                window.alert(`Профиль Agent успешно создан (agents.id = ${this.getAgentId(this.selectedAdmin)}).`);
            } catch (error) {
                console.error("Ошибка создания профиля Agent:", error);
                window.alert(error?.response?.data?.message || error.message || "Не удалось создать профиль Agent.");
            } finally {
                this.isCreatingAgentProfile = false;
            }
        },

        openAdminSalary(admin) {
            const agentId = this.getAgentId(admin);
            if (!agentId) {
                window.alert("У администратора не найден профиль Agent. Для перехода к зарплате требуется agents.id.");
                return;
            }
            this.$router.push({ name: "agent-salary", params: { agentId } });
        },

        async openAdminSales(admin) {
            const agentId = this.getAgentId(admin);
            if (!agentId) {
                window.alert("У администратора не найден профиль Agent. Для загрузки сделок требуется agents.id.");
                return;
            }
            this.selectedAdmin = { ...admin };
            try {
                await this.agentStore.fetchAgentSales(agentId, this.selectedMonth);
                this.$nextTick(() => bootstrap.Modal.getOrCreateInstance(document.getElementById("adminSalesModal")).show());
            } catch (error) {
                console.error("Ошибка загрузки сделок администратора:", error);
                window.alert(error?.response?.data?.message || "Не удалось загрузить сделки.");
            }
        },

        async fetchAdminSalesPage(url) {
            const match = String(url || "").match(/page=(\d+)/);
            const page = match ? parseInt(match[1], 10) : 1;
            const agentId = this.getAgentId(this.selectedAdmin);
            if (agentId) {
                await this.agentStore.fetchAgentSales(agentId, this.selectedMonth, page);
            }
        },

        openPersonalStatistic(admin) {
            this.selectedAdmin = { ...admin };
            this.$nextTick(() => bootstrap.Modal.getOrCreateInstance(document.getElementById("adminPersonalStatisticModal")).show());
        },

        closePersonalStatistic() {
            bootstrap.Modal.getInstance(document.getElementById("adminPersonalStatisticModal"))?.hide();
        },

        openPercentages(admin) {
            this.selectedAdmin = { ...admin };
            this.$nextTick(() => bootstrap.Modal.getOrCreateInstance(document.getElementById("adminPercentagesModal")).show());
        },

        async resetAdminPassword(admin) {
            const agentId = this.getAgentId(admin);
            if (!agentId) {
                window.alert("У администратора не найден профиль Agent. Для сброса пароля требуется agents.id.");
                return;
            }
            const confirmed = window.confirm(`Сбросить пароль администратора ${admin.name || ""}? Будет установлен временный пароль: 12345678`);
            if (!confirmed) return;
            try {
                const response = await axios.post(`/api/agents/${agentId}/reset-password`);
                window.alert(`${response?.data?.message || "Пароль успешно сброшен."} Новый пароль: 12345678`);
            } catch (error) {
                console.error("Ошибка сброса пароля:", error);
                window.alert(error?.response?.data?.message || "Не удалось сбросить пароль.");
            }
        },

        confirmDeleteAdmin(admin) {
            this.selectedAdmin = { ...admin };
            this.$nextTick(() => bootstrap.Modal.getOrCreateInstance(document.getElementById("adminDeleteModal")).show());
        },

        async deleteAdmin() {
            const agentId = this.getAgentId(this.selectedAdmin);
            if (!agentId) {
                window.alert("У администратора не найден профиль Agent. Удаление через Agent API невозможно.");
                return;
            }
            try {
                await this.agentStore.remove(agentId);
                bootstrap.Modal.getInstance(document.getElementById("adminDeleteModal"))?.hide();
                this.selectedAdmin = null;
                await this.fetchData();
            } catch (error) {
                console.error("Ошибка удаления администратора:", error);
                window.alert(error?.response?.data?.message || "Не удалось удалить администратора.");
            }
        },

        roleLabel(role) {
            return ({
                0: "Пользователь",
                1: "Администратор",
                2: "Поставщик",
                3: "Старший администратор",
            })[Number(role)] || "Не указана";
        },

        formatDate(value) {
            if (!value) return "Не указана";
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return "Не указана";
            return new Intl.DateTimeFormat("ru-RU").format(date);
        },
    },
};
</script>

<style scoped>
.admin-list-page {
    --admin-blue: #175cd3;
    --admin-blue-dark: #123b75;
    --admin-red: #e5484d;
    --admin-text: #172b4d;
    --admin-muted: #718096;
    color: var(--admin-text);
    padding: clamp(12px, 2.5vw, 24px);
    min-width: 0;
}
.admin-list-heading {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: clamp(18px, 3vw, 28px);
    margin-bottom: 22px;
    border-radius: 22px;
    background: linear-gradient(120deg, #f0f6ff 0%, #fff 68%, #fff4f4 100%);
    border: 1px solid #e4ebf5;
    box-shadow: 0 8px 28px rgba(28, 57, 100, .06);
}
.heading-icon, .modal-icon {
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    width: 54px;
    height: 54px;
    border-radius: 17px;
    color: var(--admin-blue);
    background: #e3edff;
    font-size: 23px;
}
.heading-copy { min-width: 0; flex: 1; }
.eyebrow { display: block; margin-bottom: 5px; color: var(--admin-blue); font-size: 10px; font-weight: 800; letter-spacing: .14em; }
.heading-copy h2 { margin: 0; color: var(--admin-blue-dark); font-size: clamp(22px, 3vw, 30px); font-weight: 800; letter-spacing: -.035em; }
.heading-copy p { margin: 5px 0 0; color: var(--admin-muted); font-size: 13px; }
.heading-count { display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 70px; padding: 10px 14px; border: 1px solid #dce7fa; border-radius: 15px; background: #fff; }
.heading-count span { color: var(--admin-blue); font-size: 24px; font-weight: 800; line-height: 1.1; }
.heading-count small { margin-top: 3px; color: var(--admin-muted); font-size: 11px; }
.admin-list-content { padding: clamp(14px, 2.5vw, 24px); border: 1px solid #e8edf4; border-radius: 22px; background: #fff; box-shadow: 0 8px 28px rgba(28, 57, 100, .045); }
.list-section-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
.list-section-title h3 { margin: 0; color: var(--admin-blue-dark); font-size: 18px; font-weight: 800; }
.list-section-title p { margin: 4px 0 0; color: var(--admin-muted); font-size: 12px; }
.section-icon { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 13px; color: var(--admin-blue); background: #f0f5ff; font-size: 18px; }
.admin-items { display: grid; gap: 12px; margin: 0; padding: 0; list-style: none; }
.admin-item { display: flex; align-items: flex-start; gap: 14px; min-width: 0; padding: 17px; border: 1px solid #e7edf5; border-radius: 17px; background: #fff; transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease; }
.admin-item:hover { border-color: #c8d9f5; box-shadow: 0 8px 20px rgba(28, 57, 100, .07); transform: translateY(-1px); }
.admin-avatar { display: grid; place-items: center; flex: 0 0 auto; width: 46px; height: 46px; border-radius: 15px; color: var(--admin-blue); background: #eaf1ff; font-size: 19px; }
.admin-avatar.is-offline { color: #7d8796; background: #f0f2f5; }
.admin-main { flex: 1; min-width: 0; }
.admin-name { display: block; max-width: 100%; padding: 0; border: 0; background: transparent; color: var(--admin-blue-dark); text-align: left; font-size: 15px; font-weight: 800; overflow-wrap: anywhere; }
.admin-name:hover { color: var(--admin-blue); }
.admin-fullname { margin-top: 2px; color: #52627a; font-size: 12px; overflow-wrap: anywhere; }
.admin-phone { display: flex; align-items: center; gap: 7px; margin-top: 7px; color: var(--admin-muted); font-size: 12px; }
.admin-phone i { color: var(--admin-blue); font-size: 11px; }
.admin-status-row { display: flex; align-items: center; flex-wrap: wrap; gap: 10px 14px; margin-top: 12px; }
.status-pill { display: inline-flex; align-items: center; gap: 7px; padding: 5px 9px; border-radius: 100px; font-size: 11px; font-weight: 700; }
.status-working { color: #137547; background: #e8f8ef; }
.status-paused { color: #667085; background: #f0f2f5; }
.status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.status-switch { display: inline-flex; align-items: center; gap: 7px; padding-left: 2.5em; }
.status-switch .form-check-input { cursor: pointer; margin-top: 0; }
.status-switch .form-check-input:checked { background-color: var(--admin-blue); border-color: var(--admin-blue); }
.status-switch .form-check-label { color: var(--admin-muted); font-size: 11px; cursor: pointer; }
.admin-actions { flex: 0 0 auto; }
.action-menu-button { display: grid; place-items: center; width: 38px; height: 38px; border: 1px solid #e5ebf3; border-radius: 12px; background: #fff; color: #51627b; transition: all .2s ease; }
.action-menu-button:hover, .action-menu-button[aria-expanded="true"] { color: var(--admin-blue); border-color: #c7d8f7; background: #f2f6ff; }
.admin-actions .dropdown-menu { min-width: 220px; padding: 7px; border: 1px solid #e8edf4; border-radius: 13px; box-shadow: 0 12px 30px rgba(20, 40, 70, .12); }
.admin-actions .dropdown-item { padding: 10px 11px; border-radius: 8px; color: #33445d; font-size: 13px; }
.admin-actions .dropdown-item:hover { color: var(--admin-blue); background: #f0f5ff; }
.empty-state { display: flex; align-items: center; flex-direction: column; padding: 48px 16px; text-align: center; border: 1px dashed #d9e2ef; border-radius: 17px; background: #fbfcff; }
.empty-icon { display: grid; place-items: center; width: 60px; height: 60px; margin-bottom: 14px; border-radius: 19px; color: var(--admin-blue); background: #eaf1ff; font-size: 24px; }
.empty-state h4 { margin: 0; color: var(--admin-blue-dark); font-size: 16px; font-weight: 800; }
.empty-state p { margin: 6px 0 0; color: var(--admin-muted); font-size: 13px; }
.pagination-panel { margin-top: 18px; padding: 14px 14px 4px; border-radius: 14px; background: #f7f9fc; }
.pagination-caption { margin-bottom: 10px; color: #52627a; font-size: 12px; font-weight: 700; }
.redesigned-modal { overflow: hidden; border: 1px solid #e5ebf3; border-radius: 20px; box-shadow: 0 20px 60px rgba(15, 35, 65, .18); }
.redesigned-modal .modal-header { gap: 12px; padding: 20px 22px; border-bottom: 1px solid #e9eef5; background: linear-gradient(110deg, #f0f6ff, #fff); }
.modal-title-wrap { display: flex; align-items: center; gap: 12px; min-width: 0; }
.modal-title-wrap .modal-icon { width: 42px; height: 42px; border-radius: 13px; font-size: 17px; }
.redesigned-modal .modal-title { color: var(--admin-blue-dark); font-size: 17px; font-weight: 800; }
.redesigned-modal .modal-body { padding: 22px; }
.modal-description { margin-bottom: 18px; color: var(--admin-muted); font-size: 13px; }
.action-submit { width: 100%; padding: 12px 16px; border: 0; border-radius: 12px; background: var(--admin-blue); font-weight: 700; }
.action-submit:hover { background: #1047a6; }
@media (max-width: 575.98px) {
    .admin-list-page { padding: 10px; }
    .admin-list-heading { gap: 12px; padding: 16px; border-radius: 17px; }
    .heading-icon { width: 44px; height: 44px; border-radius: 13px; font-size: 19px; }
    .heading-count { min-width: 55px; padding: 8px; }
    .heading-count span { font-size: 20px; }
    .heading-copy p { font-size: 11px; }
    .admin-list-content { padding: 13px; border-radius: 17px; }
    .admin-item { gap: 10px; padding: 12px; border-radius: 14px; }
    .admin-avatar { width: 38px; height: 38px; border-radius: 12px; font-size: 16px; }
    .admin-name { font-size: 14px; }
    .status-switch { flex-wrap: wrap; }
    .status-switch .form-check-label { font-size: 10px; }
    .action-menu-button { width: 34px; height: 34px; }
    .redesigned-modal .modal-header, .redesigned-modal .modal-body { padding: 16px; }
}

.admin-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-bottom: 16px;
    padding: 5px;
    border-radius: 14px;
    background: #f1f5fb;
}
.admin-tabs button {
    flex: 1 1 120px;
    min-height: 40px;
    border: 0;
    border-radius: 10px;
    background: transparent;
    color: #64758e;
    font-size: 12px;
    font-weight: 700;
    transition: .2s ease;
}
.admin-tabs button.active {
    color: var(--admin-blue);
    background: #fff;
    box-shadow: 0 3px 12px rgba(24, 63, 124, .08);
}
.admin-controls {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 10px;
    margin-bottom: 14px;
}
.admin-search {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    min-height: 48px;
    padding: 0 13px;
    border: 1px solid #e0e8f3;
    border-radius: 13px;
    background: #fff;
    color: #7b8aa1;
}
.admin-search input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    color: var(--admin-text);
    background: transparent;
    font-size: 13px;
}
.admin-search button {
    border: 0;
    background: transparent;
    color: #7b8aa1;
}
.admin-sort {
    display: flex;
    gap: 7px;
}
.admin-sort select {
    min-width: 150px;
    border-radius: 12px;
    border-color: #e0e8f3;
    font-size: 12px;
}
.sort-direction {
    width: 46px;
    border: 1px solid #dce6f4;
    border-radius: 12px;
    color: var(--admin-blue);
    background: #f7faff;
}
.admin-meta {
    margin-top: 5px;
    color: var(--admin-muted);
    font-size: 12px;
    overflow-wrap: anywhere;
}
.admin-meta i { width: 14px; color: var(--admin-blue); }
.admin-info-card {
    display: flex;
    align-items: center;
    flex-direction: column;
    padding: 8px 0 20px;
    text-align: center;
}
.admin-info-avatar {
    display: grid;
    place-items: center;
    width: 64px;
    height: 64px;
    margin-bottom: 12px;
    border-radius: 20px;
    color: var(--admin-blue);
    background: #eaf1ff;
    font-size: 25px;
}
.admin-info-card h4 {
    margin: 0;
    color: var(--admin-blue-dark);
    font-size: 18px;
    font-weight: 800;
}
.admin-info-card p {
    margin: 5px 0 10px;
    color: var(--admin-muted);
    font-size: 12px;
}
.admin-details {
    margin: 0 0 18px;
    border-top: 1px solid #edf1f6;
}
.admin-details > div {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    padding: 11px 0;
    border-bottom: 1px solid #edf1f6;
    font-size: 13px;
}
.admin-details dt {
    margin: 0;
    color: var(--admin-muted);
    font-weight: 500;
}
.admin-details dd {
    margin: 0;
    color: var(--admin-text);
    font-weight: 700;
    text-align: right;
    overflow-wrap: anywhere;
}
@media (max-width: 575.98px) {
    .admin-controls { grid-template-columns: 1fr; }
    .admin-sort select { flex: 1; min-width: 0; }
    .admin-tabs button { flex-basis: calc(33.333% - 7px); font-size: 11px; }
    .admin-details > div { align-items: flex-start; }
}



/* Нижнее меню действий — в том же стиле, что и меню обычных администраторов */
.admin-menu-btn {
    display: grid;
    place-items: center;
    min-width: 36px;
    min-height: 36px;
    border: 1px solid #e1e9f4;
    border-radius: 10px;
    background: #f7f9fd;
    color: #42618c;
    font-size: 16px;
}
.admin-menu-btn:hover {
    background: #e9f0fb;
    color: var(--admin-blue);
}
.admin-actions-modal {
    --bs-modal-bg: #ffffff;
    --bs-modal-color: #172d50;
    background: transparent;
}

.admin-actions-modal .modal-dialog-bottom-sheet {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    max-width: none;
    margin: 0;
    transform: translateY(100%);
    transition: transform .28s ease-out;
}

.admin-actions-modal.show .modal-dialog-bottom-sheet {
    transform: translateY(0);
}

.admin-actions-modal .modal-content {
    display: block;
    background: #ffffff !important;
    color: #172d50;
    opacity: 1 !important;
    border-radius: 24px 24px 0 0;
    border: 1px solid #e5ebf4;
    box-shadow: 0 -12px 45px #142c5033;
    padding-bottom: env(safe-area-inset-bottom, 0px);
    overflow: hidden;
}

.admin-sheet-handle {
    width: 42px;
    height: 5px;
    border-radius: 999px;
    background: #d6deea;
    margin: 10px auto 0;
}

.admin-actions-modal .modal-header {
    display: flex;
    background: #ffffff !important;
    border-bottom: 1px solid #e8edf5;
    padding: 18px 20px 14px;
}

.admin-sheet-person {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.admin-sheet-avatar {
    display: grid;
    place-items: center;
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    border-radius: 15px;
    background: #eaf1ff;
    color: var(--admin-blue);
    font-size: 19px;
}

.admin-sheet-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--admin-text);
    margin-top: 3px;
    overflow-wrap: anywhere;
}

.admin-actions-modal .modal-body {
    display: block;
    background: #ffffff !important;
    padding: 10px 14px 18px;
}

.admin-actions-list {
    display: flex;
    flex-direction: column;
    gap: 5px;
    max-height: min(68vh, 620px);
    overflow-y: auto;
    overscroll-behavior: contain;
}

.admin-action {
    appearance: none;
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 12px 10px;
    border: 0;
    border-radius: 14px;
    background: #ffffff !important;
    color: var(--admin-text);
    text-align: left;
    transition: background .18s, transform .18s;
}

.admin-action:hover, .admin-action:focus-visible {
    background: #f3f6fc;
    outline: none;
}

.admin-action:active {
    transform: scale(.99);
}

.admin-action > span:nth-child(2) {
    display: flex;
    flex: 1;
    min-width: 0;
    flex-direction: column;
    gap: 3px;
}

.admin-action strong {
    font-size: 14px;
    font-weight: 700;
}

.admin-action small {
    color: #8190a7;
    font-size: 12px;
    line-height: 1.35;
}

.admin-action-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: grid;
    place-items: center;
    border-radius: 13px;
    font-size: 16px;
}

.admin-action-icon.is-blue {
    background: #e8f0ff;
    color: #2457a7;
}

.admin-action-icon.is-indigo {
    background: #eeedff;
    color: #6254c7;
}

.admin-action-icon.is-green {
    background: #e5f7ef;
    color: #16845c;
}

.admin-action-icon.is-violet {
    background: #f3eaff;
    color: #8652bf;
}

.admin-action-icon.is-red {
    background: #fff0f2;
    color: #c73d56;
}

.admin-action-chevron {
    color: #a1aec1;
    font-size: 12px;
}

.admin-action-divider {
    height: 1px;
    margin: 4px 8px;
    background: #edf0f5;
}

.admin-action.is-danger strong {
    color: #c73d56;
}

@media (min-width: 576px) {
    .admin-actions-modal .modal-dialog-bottom-sheet {
        left: 50%;
        right: auto;
        bottom: 24px;
        width: min(100% - 32px, 480px);
        transform: translate(-50%, 30px);
    }

    .admin-actions-modal.show .modal-dialog-bottom-sheet {
        transform: translate(-50%, 0);
    }

    .admin-actions-modal .modal-content {
        border-radius: 24px;
    }
}



/* Кнопка создания связанного Agent-профиля */
.agent-profile-create-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    min-height: 68px;
    padding: 12px 15px;
    border: 1px solid rgba(37, 99, 235, .22);
    border-radius: 16px;
    color: #163b73;
    text-align: left;
    background: linear-gradient(115deg, #eef5ff 0%, #f8fbff 58%, #fff 100%);
    box-shadow: 0 5px 16px rgba(37, 99, 235, .08);
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}
.agent-profile-create-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    border-color: rgba(37, 99, 235, .48);
    box-shadow: 0 9px 22px rgba(37, 99, 235, .15);
}
.agent-profile-create-btn:focus-visible { outline: 3px solid rgba(37, 99, 235, .25); outline-offset: 3px; }
.agent-profile-create-btn:disabled { opacity: .7; cursor: wait; }
.agent-profile-create-icon {
    display: grid;
    place-items: center;
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    border-radius: 13px;
    color: #fff;
    background: linear-gradient(145deg, #2563eb, #1746a2);
    box-shadow: 0 5px 12px rgba(37, 99, 235, .22);
    font-size: 16px;
}
.agent-profile-create-copy { display: flex; flex: 1; min-width: 0; flex-direction: column; gap: 3px; }
.agent-profile-create-copy strong { color: #173b70; font-size: 13px; font-weight: 800; line-height: 1.25; }
.agent-profile-create-copy small { color: #71829b; font-size: 11px; line-height: 1.3; }
.agent-profile-create-arrow { color: #5a7fb9; font-size: 13px; transition: transform .2s ease; }
.agent-profile-create-btn:hover .agent-profile-create-arrow { transform: translateX(3px); }
</style>

