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

            <!-- Массовое выделение -->
            <div class="selection-toolbar" v-if="!forSelect && Number(currentUser?.role || 0) >= 3">
                <button type="button" class="select-all-link" @click="selectAll">
                    <i :class="selection.length && selection.length === visibleAdmins.length ? 'fa-regular fa-square-check' : 'fa-regular fa-square'"></i>
                    {{ selection.length && selection.length === visibleAdmins.length ? 'Снять выделение' : 'Выделить отображаемых' }}
                </button>
                <span v-if="selection.length" class="selection-count">Выбрано: <strong>{{ selection.length }}</strong></span>
            </div>

            <ul v-if="sortedAdmins.length" class="admin-items">
                <li v-for="user in sortedAdmins" :key="user.id" class="admin-item"
                    :class="{ 'is-selected': selection.includes(user.id) }">
                    <div class="admin-avatar" :class="{ 'is-offline': !user.is_work }">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>

                    <div class="admin-main">
                        <div class="admin-name-row">
                            <input
                                v-if="!forSelect && Number(currentUser?.role || 0) >= 3"
                                type="checkbox"
                                class="admin-select-checkbox"
                                :checked="selection.includes(user.id)"
                                @change="toggleSelection(user.id)"
                                :aria-label="`Выбрать ${user.name || 'администратора'}`"
                            >
                            <button type="button" class="admin-name" @click="forSelect ? selectAdmin(user) : openView(user)">
                                {{ user.name || 'Без имени' }}
                            </button>
                        </div>
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

                    <div class="dropdown admin-actions">
                        <button class="action-menu-button" type="button" data-bs-toggle="dropdown"
                                aria-label="Действия с администратором">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <template v-if="forSelect">
                                <li><button class="dropdown-item" type="button" @click="selectAdmin(user)">
                                    <i class="fa-solid fa-check me-2"></i>Выбрать
                                </button></li>
                            </template>
                            <template v-else>
                                <li><button class="dropdown-item" type="button" @click="openView(user)">
                                    <i class="fa-solid fa-eye me-2"></i>Просмотр
                                </button></li>
                                <li><button class="dropdown-item" type="button" @click="openEdit(user)">
                                    <i class="fa-solid fa-pen-to-square me-2"></i>Редактировать
                                </button></li>
                                <li><button class="dropdown-item" type="button" @click="openReport(user)">
                                    <i class="fa-solid fa-file-lines me-2"></i>Сформировать отчёт
                                </button></li>
                                <li><button class="dropdown-item" type="button" @click="openRoleSwitcher(user)">
                                    <i class="fa-solid fa-user-tag me-2"></i>Сменить роль
                                </button></li>
                                <li><button class="dropdown-item" type="button" @click="impersonateAdmin(user)">
                                    <i class="fa-solid fa-user-secret me-2"></i>Войти от имени
                                </button></li>
                            </template>
                        </ul>
                    </div>
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
    </section>
</template>

<script>
import Pagination from "@/Components/Pagination.vue";
import UserForm from "@/Components/Users/UserForm.vue";
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";
import { useAdminsStore } from "@/stores/admins";
import { useUsersStore } from "@/stores/users";

export default {
    name: "AdminList",
    components: {
        Pagination,
        UserForm,
        ReportGenerator,
    },
    props: ["forSelect"],
    data() {
        return {
            adminsStore: useAdminsStore(),
            userStore: useUsersStore(),
            selectedAdmin: null,
            search: "",
            sortBy: "id",
            sortDirection: "desc",
            viewMode: "all",
            selection: [],
        };
    },
    computed: {
        currentUser() {
            return this.userStore.self || null;
        },
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
        selectAdmin(admin) {
            if (!this.forSelect) return;
            this.$emit("select", admin);
        },

        toggleSelection(id) {
            const index = this.selection.indexOf(id);
            if (index === -1) this.selection.push(id);
            else this.selection.splice(index, 1);

            this.$emit("select", [...this.selection]);
        },

        selectAll() {
            const ids = this.visibleAdmins.map((item) => item.id);
            const allSelected = ids.length > 0 && ids.every((id) => this.selection.includes(id));

            if (allSelected) {
                this.selection = this.selection.filter((id) => !ids.includes(id));
            } else {
                this.selection = [...new Set([...this.selection, ...ids])];
            }

            this.$emit("select", [...this.selection]);
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
                this.selection = [];
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
.selection-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0 0 12px;
    color: var(--admin-muted);
    font-size: 12px;
}
.select-all-link {
    padding: 4px 0;
    border: 0;
    background: transparent;
    color: var(--admin-blue);
    font-weight: 700;
}
.selection-count { white-space: nowrap; }
.admin-name-row {
    display: flex;
    align-items: center;
    gap: 9px;
}
.admin-select-checkbox {
    width: 16px;
    height: 16px;
    flex: 0 0 auto;
    accent-color: var(--admin-blue);
    cursor: pointer;
}
.admin-item.is-selected {
    border-color: #8fb4ed;
    background: #f7faff;
    box-shadow: 0 0 0 2px rgba(23, 92, 211, .08);
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

</style>
