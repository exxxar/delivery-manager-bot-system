<script setup>
import UserForm from "@/Components/Users/UserForm.vue";
import UserCard from "@/Components/Users/UserCard.vue";
import Pagination from "@/Components/Pagination.vue";
import UserFilter from "@/Components/Users/UserFilter.vue";
</script>

<template>
    <section class="users-page">
        <div v-if="!forSelect" class="users-filter-panel">
            <div class="section-heading">
                <div class="section-icon"><i class="fa-solid fa-users"></i></div>
                <div>
                    <h4 class="section-title">Пользователи</h4>
                    <p class="section-subtitle">Управление учетными записями и доступом</p>
                </div>
            </div>
            <UserFilter @apply-filters="applyFilter" />
        </div>

        <div v-else class="selection-heading">
            <i class="fa-solid fa-user-check"></i>
            <span>Выберите пользователя</span>
        </div>

        <div v-if="usersStore.items?.length" class="users-grid">
            <article
                v-for="user in usersStore.items"
                :key="user.id"
                class="user-card"
                :class="{ 'user-card-blocked': user.blocked_at }"
            >
                <div class="user-card-top">
                    <button type="button" class="user-identity" @click.prevent="$emit('select', user)">
                        <span class="user-avatar"><i class="fa-solid fa-user"></i></span>
                        <span class="user-title-wrap">
                            <span class="user-name">{{ user.name || 'Без имени' }}</span>
                            <span v-if="user.fio_from_telegram" class="user-full-name">{{ user.fio_from_telegram }}</span>
                            <!-- 🔹 ID теперь всегда виден -->
                            <span class="user-id">ID: {{ user.id }}</span>
                        </span>
                    </button>
                    <!-- 🔹 Кнопка открытия нижней модалки (вместо dropdown) -->
                    <button
                        type="button"
                        class="action-menu-btn"
                        aria-label="Действия с пользователем"
                        @click.prevent="openActionsModal(user)"
                    >
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                </div>

                <div class="user-badges">
                    <span class="role-badge"><i class="fa-solid fa-shield-halved"></i>{{ roles[user.role || 0] || 'Неизвестная роль' }}</span>
                    <span v-if="user.blocked_at" class="status-badge status-danger"><i class="fa-solid fa-lock"></i>Заблокирован</span>
                    <span v-else class="status-badge status-success"><i class="fa-solid fa-circle-check"></i>Активен</span>
                </div>

                <div class="user-details">
                    <div v-if="field_visible?.phone || true" class="detail-row">
                        <i class="fa-solid fa-phone detail-icon"></i>
                        <span class="detail-label">Телефон</span>
                        <span class="detail-value">{{ user.agent?.phone || user.supplier?.phone || 'Не указан' }}</span>
                    </div>
                    <div v-if="field_visible?.email" class="detail-row">
                        <i class="fa-solid fa-envelope detail-icon"></i>
                        <span class="detail-label">Почта</span>
                        <span class="detail-value">{{ user.email || 'Не указана' }}</span>
                    </div>
                    <div v-if="field_visible?.telegram_chat_id" class="detail-row">
                        <i class="fa-brands fa-telegram detail-icon"></i>
                        <span class="detail-label">Telegram ID</span>
                        <a class="detail-value detail-link" href="javascript:void(0)" @click.prevent="getTelegramLink(user)">{{ user.telegram_chat_id || 'Получить ссылку' }}</a>
                    </div>
                    <div v-if="field_visible?.percent" class="detail-row">
                        <i class="fa-solid fa-percent detail-icon"></i>
                        <span class="detail-label">Процент</span>
                        <span class="detail-value">{{ user.percent ?? 0 }}%</span>
                    </div>
                    <div v-if="field_visible?.is_work" class="detail-row">
                        <i class="fa-solid fa-briefcase detail-icon"></i>
                        <span class="detail-label">Работа</span>
                        <span class="detail-value">{{ user.is_work ? 'Работает' : 'Не работает' }}</span>
                    </div>
                    <div v-if="field_visible?.email_verified_at" class="detail-row">
                        <i class="fa-solid fa-circle-check detail-icon"></i>
                        <span class="detail-label">Верификация</span>
                        <span class="detail-value">{{ user.email_verified_at || 'Не подтверждена' }}</span>
                    </div>
                    <div v-if="field_visible?.blocked_at" class="detail-row">
                        <i class="fa-solid fa-lock detail-icon"></i>
                        <span class="detail-label">Блокировка</span>
                        <span class="detail-value">{{ user.blocked_at || 'Нет' }}</span>
                    </div>
                    <!-- 🔹 Дата регистрации теперь всегда видна -->
                    <div class="detail-row">
                        <i class="fa-regular fa-calendar detail-icon"></i>
                        <span class="detail-label">Регистрация</span>
                        <span class="detail-value">{{ formatDate(user.created_at) }}</span>
                    </div>
                    <div v-if="field_visible?.updated_at" class="detail-row">
                        <i class="fa-solid fa-rotate detail-icon"></i>
                        <span class="detail-label">Обновлён</span>
                        <span class="detail-value">{{ user.updated_at || '—' }}</span>
                    </div>
                </div>
            </article>
        </div>

        <div v-else class="empty-state">
            <span class="empty-icon"><i class="fa-solid fa-users-slash"></i></span>
            <h5>Пользователей пока нет</h5>
            <p>Попробуйте изменить параметры фильтрации.</p>
        </div>

        <div v-if="usersStore.items?.length" class="pagination-panel">
            <div class="pagination-caption"><i class="fa-solid fa-list-ol me-2"></i>Навигация по пользователям</div>
            <Pagination :pagination="usersStore.pagination" @page-changed="fetchUsersByUrl" />
        </div>

        <!-- Модалка редактирования -->
        <div class="modal fade" id="editUserModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Редактирование пользователя</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <UserForm v-if="selectedUser" :initialData="selectedUser" @saved="fetchUsers"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка смены роли -->
        <div class="modal fade" id="roleSwitcherUserModal" tabindex="-1">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Редактирование роли</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form v-on:submit.prevent="changeRole" v-if="selectedUser">
                            <div class="form-floating mb-2">
                                <select v-model="selectedUser.role" class="form-select" id="role" required>
                                    <option :value="0">Пользователь</option>
                                    <option :value="1">Администратор</option>
                                    <option :value="2">Поставщик</option>
                                    <option :value="3">Старший администратор</option>
                                    <option :value="4">Суперадмин</option>
                                </select>
                                <label for="role">Роль</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 p-3">
                                Сохранить изменения
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка просмотра -->
        <div class="modal fade" id="viewUserModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Информация о пользователе</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <UserCard v-if="selectedUser" :user="selectedUser" @edit="openEdit"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Нижняя модалка с действиями (bottom sheet) -->
        <div class="modal fade bottom-sheet-modal" id="userActionsModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-bottom">
                <div class="modal-content">
                    <div class="bottom-sheet-handle">
                        <div class="handle-bar"></div>
                    </div>

                    <!-- Заголовок с информацией о пользователе -->
                    <div class="modal-header border-0 pb-0">
                        <div class="d-flex align-items-center gap-3 w-100">
                            <div class="user-avatar-large">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <h6 class="modal-title mb-0 text-truncate">
                                    {{ selectedUser?.name || 'Без имени' }}
                                </h6>
                                <small class="text-muted">
                                    <i class="fa-solid fa-hashtag me-1"></i>
                                    ID: {{ selectedUser?.id }}
                                    <span v-if="selectedUser?.phone" class="ms-2">
                                        <i class="fa-solid fa-phone me-1"></i>
                                        {{ selectedUser.phone }}
                                    </span>
                                </small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                    </div>

                    <!-- Пункты меню -->
                    <div class="modal-body pt-3">
                        <div class="actions-list">
                            <!-- Выбор пользователя (режим forSelect) -->
                            <button
                                v-if="forSelect"
                                class="action-item action-item-primary"
                                @click="handleAction('select')"
                            >
                                <span class="action-icon select-icon">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                                <div class="action-content">
                                    <div class="action-title">Выбрать пользователя</div>
                                    <div class="action-desc">Использовать этого пользователя</div>
                                </div>
                            </button>

                            <!-- Сменить роль -->
                            <button class="action-item" @click="handleAction('role')">
                                <span class="action-icon role-icon">
                                    <i class="fa-solid fa-user-shield"></i>
                                </span>
                                <div class="action-content">
                                    <div class="action-title">Сменить роль</div>
                                    <div class="action-desc">Текущая: {{ roles[selectedUser?.role || 0] || 'Неизвестная' }}</div>
                                </div>
                            </button>

                            <!-- Редактировать -->
                            <button class="action-item" @click="handleAction('edit')">
                                <span class="action-icon edit-icon">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </span>
                                <div class="action-content">
                                    <div class="action-title">Редактировать</div>
                                    <div class="action-desc">Изменить данные пользователя</div>
                                </div>
                            </button>

                            <!-- Просмотреть -->
                            <button class="action-item" @click="handleAction('view')">
                                <span class="action-icon view-icon">
                                    <i class="fa-regular fa-eye"></i>
                                </span>
                                <div class="action-content">
                                    <div class="action-title">Просмотреть</div>
                                    <div class="action-desc">Подробная информация</div>
                                </div>
                            </button>

                            <!-- Ссылка на Telegram -->
                            <button class="action-item" @click="handleAction('telegram')">
                                <span class="action-icon telegram-icon">
                                    <i class="fa-brands fa-telegram"></i>
                                </span>
                                <div class="action-content">
                                    <div class="action-title">Ссылка на Telegram</div>
                                    <div class="action-desc">Открыть чат с пользователем</div>
                                </div>
                            </button>

                            <!-- Заблокировать / Разблокировать -->
                            <button
                                v-if="!selectedUser?.blocked_at"
                                class="action-item action-item-warning"
                                @click="handleAction('block')"
                            >
                                <span class="action-icon block-icon">
                                    <i class="fa-solid fa-ban"></i>
                                </span>
                                <div class="action-content">
                                    <div class="action-title">Заблокировать</div>
                                    <div class="action-desc">Ограничить доступ к системе</div>
                                </div>
                            </button>
                            <button
                                v-else
                                class="action-item action-item-success"
                                @click="handleAction('unblock')"
                            >
                                <span class="action-icon unblock-icon">
                                    <i class="fa-solid fa-unlock"></i>
                                </span>
                                <div class="action-content">
                                    <div class="action-title">Разблокировать</div>
                                    <div class="action-desc">Восстановить доступ к системе</div>
                                </div>
                            </button>

                            <!-- Удалить -->
                            <button class="action-item action-item-danger" @click="handleAction('delete')">
                                <span class="action-icon delete-icon">
                                    <i class="fa-solid fa-trash"></i>
                                </span>
                                <div class="action-content">
                                    <div class="action-title">Удалить</div>
                                    <div class="action-desc">Полное удаление пользователя</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Кнопка закрытия -->
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">
                            Отмена
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import { useUsersStore } from '@/stores/users'
import { useModalStore } from '@/stores/utillites/useConfitmModalStore'

export default {
    name: 'UserList',
    props: ["forSelect"],
    data() {
        return {
            roles: [
                'Пользователь',
                'Администратор',
                'Поставщик',
                'Старший администратор',
                'Суперадмин'
            ],
            modalStore: useModalStore(),
            usersStore: useUsersStore(),
            selectedUser: null,
            field_visible: null,
        }
    },

    created() {
        this.fetchUsers()
    },

    methods: {
        async fetchUsers(page = 1) {
            await this.usersStore.fetchAllByPage(page)
        },

        async getTelegramLink(user) {
            await this.usersStore.getTelegramLink(user.id)
        },

        async fetchUsersByUrl(url) {
            await this.usersStore.fetchByUrl(url)
        },

        applyFilter(payload) {
            this.field_visible = payload.field_visible
            this.usersStore.setFilters(payload.filters)
            this.usersStore.setSort(payload.sort.field, payload.sort.direction)
            this.usersStore.fetchFiltered()
        },

        /**
         * 🔹 Форматирование даты регистрации
         */
        formatDate(dateString) {
            if (!dateString) return '—'
            try {
                return new Date(dateString).toLocaleDateString('ru-RU', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                })
            } catch {
                return dateString
            }
        },

        /**
         * 🔹 Открывает нижнюю модалку с действиями
         */
        openActionsModal(user) {
            this.selectedUser = { ...user }
            this.$nextTick(() => {
                const modal = new bootstrap.Modal(document.getElementById('userActionsModal'))
                modal.show()
            })
        },

        /**
         * 🔹 Обрабатывает выбор действия из нижней модалки
         */
        handleAction(action) {
            const modalEl = document.getElementById('userActionsModal')
            const modal = bootstrap.Modal.getInstance(modalEl)
            modal?.hide()

            // Ждём закрытия модалки и выполняем действие
            setTimeout(() => {
                switch (action) {
                    case 'select':
                        this.$emit('select', this.selectedUser)
                        break
                    case 'role':
                        this.openRoleSwitcher(this.selectedUser)
                        break
                    case 'edit':
                        this.openEdit(this.selectedUser)
                        break
                    case 'view':
                        this.openView(this.selectedUser)
                        break
                    case 'telegram':
                        this.getTelegramLink(this.selectedUser)
                        break
                    case 'block':
                        this.confirmBlocked(this.selectedUser)
                        break
                    case 'unblock':
                        this.confirmUnBlocked(this.selectedUser)
                        break
                    case 'delete':
                        this.confirmDelete(this.selectedUser)
                        break
                }
            }, 300)
        },

        openRoleSwitcher(user) {
            this.selectedUser = user
            new bootstrap.Modal(document.getElementById('roleSwitcherUserModal')).show()
        },

        openEdit(user) {
            this.selectedUser = null
            this.$nextTick(() => {
                this.selectedUser = user
                const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewUserModal'))
                if (viewModal) viewModal.hide()
                new bootstrap.Modal(document.getElementById('editUserModal')).show()
            })
        },

        confirmDelete(user) {
            this.selectedUser = user
            this.modalStore.open(
                `Вы уверены, что хотите удалить ${this.selectedUser?.name}?`,
                () => this.usersStore.remove(this.selectedUser.id),
                () => this.modalStore.close()
            )
        },

        confirmUnBlocked(user) {
            this.selectedUser = user
            this.modalStore.open(
                `Вы уверены, что хотите разблокировать ${this.selectedUser?.name}?`,
                () => this.usersStore.unblock(this.selectedUser.id),
                () => this.modalStore.close()
            )
        },

        confirmBlocked(user) {
            this.selectedUser = user
            this.modalStore.open(
                `Вы уверены, что хотите заблокировать ${this.selectedUser?.name}?`,
                () => this.usersStore.block(this.selectedUser.id, ''),
                () => this.modalStore.close()
            )
        },

        openView(user) {
            this.selectedUser = user
            new bootstrap.Modal(document.getElementById('viewUserModal')).show()
        },

        async changeRole() {
            await this.usersStore.updateRole(this.selectedUser.id, this.selectedUser.role)
            const modal = bootstrap.Modal.getInstance(document.getElementById('roleSwitcherUserModal'))
            if (modal) modal.hide()
        },
    }
}
</script>

<style scoped>
.users-page { --user-blue: #175cd3; --user-navy: #14213d; --user-red: #dc3545; color: var(--user-navy); padding: 0 0 1.5rem; }
.users-filter-panel { background: linear-gradient(135deg, #fff 0%, #f5f8ff 100%); border: 1px solid #e5ebf5; border-radius: 20px; padding: 1rem; margin-bottom: 1.1rem; box-shadow: 0 8px 24px rgba(20, 33, 61, .05); }
.section-heading { display: flex; align-items: center; gap: .85rem; margin-bottom: 1rem; }
.section-icon { display: grid; place-items: center; width: 46px; height: 46px; border-radius: 14px; background: #eaf1ff; color: var(--user-blue); font-size: 1.2rem; }
.section-title { font-size: 1.2rem; font-weight: 800; margin: 0; letter-spacing: -.02em; }
.section-subtitle { margin: .2rem 0 0; color: #718096; font-size: .86rem; }
.selection-heading { display: flex; gap: .65rem; align-items: center; padding: 1rem; margin-bottom: 1rem; border-radius: 14px; background: #edf4ff; color: var(--user-blue); font-weight: 700; }
.users-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr)); gap: 1rem; }
.user-card { min-width: 0; padding: 1rem; border: 1px solid #e5ebf4; border-radius: 18px; background: #fff; box-shadow: 0 5px 18px rgba(20, 33, 61, .045); transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
.user-card:hover { transform: translateY(-2px); border-color: #cbdaf7; box-shadow: 0 12px 28px rgba(20, 33, 61, .09); }
.user-card-blocked { border-left: 4px solid var(--user-red); }
.user-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
.user-identity { display: flex; align-items: center; gap: .75rem; min-width: 0; padding: 0; border: 0; background: transparent; color: inherit; text-align: left; flex: 1; }
.user-avatar { flex: 0 0 46px; width: 46px; height: 46px; display: grid; place-items: center; border-radius: 15px; background: linear-gradient(145deg, #eaf1ff, #f4f7ff); color: var(--user-blue); font-size: 1.15rem; }
.user-title-wrap { display: flex; flex-direction: column; min-width: 0; gap: .15rem; }
.user-name { font-size: 1rem; font-weight: 800; line-height: 1.25; overflow-wrap: anywhere; }
.user-full-name { color: #718096; font-size: .78rem; overflow-wrap: anywhere; }
/* 🔹 Стилизация ID */
.user-id {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    color: var(--user-blue);
    background: #eaf1ff;
    font-size: .72rem;
    font-weight: 700;
    padding: .15rem .5rem;
    border-radius: 6px;
    margin-top: .2rem;
    width: fit-content;
    font-family: 'Courier New', monospace;
}
.action-menu-btn { display: grid; place-items: center; width: 36px; height: 36px; border: 1px solid #e6ebf3; border-radius: 11px; color: #596579; background: #fff; cursor: pointer; flex-shrink: 0; transition: all .2s ease; }
.action-menu-btn:hover, .action-menu-btn:focus { color: var(--user-blue); border-color: #bfd1f8; background: #f4f7ff; }
.user-badges { display: flex; flex-wrap: wrap; gap: .45rem; margin: 1rem 0 .8rem; }
.role-badge, .status-badge { display: inline-flex; align-items: center; gap: .4rem; border-radius: 999px; padding: .35rem .65rem; font-size: .75rem; font-weight: 700; }
.role-badge { color: #174ea6; background: #eaf1ff; }
.status-success { color: #147447; background: #e8f7ef; }
.status-danger { color: #b42332; background: #fff0f1; }
.user-details { border-top: 1px solid #edf0f5; padding-top: .45rem; }
.detail-row { display: grid; grid-template-columns: 18px minmax(76px, auto) minmax(0, 1fr); align-items: start; gap: .5rem; padding: .4rem 0; font-size: .83rem; }
.detail-icon { color: #7b8ba5; text-align: center; padding-top: .15rem; }
.detail-label { color: #778399; }
.detail-value { color: #26344d; text-align: right; overflow-wrap: anywhere; }
.detail-link { color: var(--user-blue); text-decoration: none; }
.detail-link:hover { text-decoration: underline; }
.pagination-panel { margin-top: 1.2rem; padding: 1rem; border: 1px solid #e5ebf4; border-radius: 16px; background: #fff; }
.pagination-caption { margin-bottom: .75rem; color: #62718a; font-size: .85rem; font-weight: 700; }
.empty-state { text-align: center; padding: 3rem 1rem; margin-top: .75rem; border: 1px dashed #d8e1ef; border-radius: 18px; background: #fafcff; }
.empty-icon { display: grid; place-items: center; width: 64px; height: 64px; margin: 0 auto 1rem; border-radius: 20px; background: #edf3ff; color: var(--user-blue); font-size: 1.5rem; }
.empty-state h5 { font-weight: 800; margin-bottom: .35rem; }
.empty-state p { color: #7a879b; margin: 0; font-size: .9rem; }

/* 🔹 Обычные модалки */
:deep(.modal-content) { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 20px 60px rgba(20, 33, 61, .2); }
:deep(.modal-header) { background: #f7f9fd; border-bottom-color: #e8edf5; padding: 1rem 1.25rem; }
:deep(.modal-title) { color: var(--user-navy); font-weight: 800; }
:deep(.modal-footer) { border-top-color: #e8edf5; }

/* 🔹 Нижняя модалка (bottom sheet) */
.bottom-sheet-modal .modal-dialog {
    margin: 0;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    max-width: 100%;
    height: auto;
    transform: translateY(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.bottom-sheet-modal.show .modal-dialog {
    transform: translateY(0);
}

.bottom-sheet-modal .modal-content {
    border-radius: 24px 24px 0 0 !important;
    border: none;
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
    max-height: 85vh;
    overflow-y: auto;
}

@media (min-width: 768px) {
    .bottom-sheet-modal .modal-dialog {
        position: fixed;
        bottom: 20px;
        left: 50%;
        right: auto;
        transform: translateX(-50%) translateY(100%);
        max-width: 500px;
        width: 100%;
    }
    .bottom-sheet-modal.show .modal-dialog {
        transform: translateX(-50%) translateY(0);
    }
    .bottom-sheet-modal .modal-content {
        border-radius: 24px !important;
        max-height: 80vh;
    }
}

.bottom-sheet-handle { display: flex; justify-content: center; padding: 12px 0 4px; cursor: grab; }
.handle-bar { width: 40px; height: 4px; background: #d1d9e6; border-radius: 2px; transition: background 0.2s ease; }
.bottom-sheet-handle:hover .handle-bar { background: #a8b5c9; }

.user-avatar-large {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: linear-gradient(135deg, #eaf1ff, #dceaff);
    color: var(--user-blue);
    font-size: 20px;
}

.min-width-0 { min-width: 0; }

.actions-list { display: flex; flex-direction: column; gap: 6px; }

.action-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border: none;
    border-radius: 14px;
    background: #f7faff;
    color: #20334f;
    text-align: left;
    width: 100%;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.action-item:hover { background: #edf4ff; transform: translateX(2px); }
.action-item:active { transform: scale(0.98); }
.action-item:disabled { opacity: 0.5; cursor: not-allowed; }

.action-item-primary {
    background: linear-gradient(135deg, #e1f7ed, #f0fff5);
    border: 1px solid #b8e6cc;
}
.action-item-primary:hover { background: linear-gradient(135deg, #d1f0e0, #e1f7ed); }

.action-item-warning {
    background: #fff8e7;
    border: 1px solid #ffe4a3;
}
.action-item-warning:hover { background: #ffefc2; }
.action-item-warning .action-title { color: #b45309; }
.action-item-warning .action-icon { background: #fef3c7; color: #b45309; }

.action-item-success {
    background: #e8f7ef;
    border: 1px solid #b8e6cc;
}
.action-item-success:hover { background: #d1f0e0; }
.action-item-success .action-title { color: #147447; }
.action-item-success .action-icon { background: #d1f0e0; color: #147447; }

.action-item-danger {
    background: #fff5f5;
    border: 1px solid #ffd4d4;
}
.action-item-danger:hover { background: #ffe8e8; }
.action-item-danger .action-title { color: #b42332; }
.action-item-danger .action-icon { background: #fee; color: #b42332; }

.action-icon {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: #eaf1ff;
    color: var(--user-blue);
    font-size: 18px;
}

.action-icon.role-icon { background: #ede9fe; color: #6b21a8; }
.action-icon.edit-icon { background: #e8f1ff; color: var(--user-blue); }
.action-icon.view-icon { background: #e0f2fe; color: #0369a1; }
.action-icon.telegram-icon { background: #dbeafe; color: #0088cc; }
.action-icon.block-icon { background: #fef3c7; color: #b45309; }
.action-icon.unblock-icon { background: #d1f0e0; color: #147447; }
.action-icon.select-icon { background: #d1f0e0; color: #16794b; }
.action-icon.delete-icon { background: #fee; color: #b42332; }

.action-content { flex: 1; min-width: 0; }
.action-title { font-size: 14px; font-weight: 650; color: #172f4f; line-height: 1.3; margin-bottom: 2px; }
.action-desc { font-size: 12px; color: #718096; line-height: 1.3; }

.bottom-sheet-modal .modal-footer .btn-light {
    background: #f4f7fc;
    border: 1px solid #e0e9f5;
    color: #536781;
    font-weight: 600;
    padding: 12px;
    border-radius: 12px;
    transition: all 0.2s ease;
}
.bottom-sheet-modal .modal-footer .btn-light:hover { background: #e8f0fc; color: var(--user-blue); }

@media (max-width: 767.98px) {
    .bottom-sheet-modal .modal-content { max-height: 90vh; }
    .action-item { padding: 12px 14px; gap: 12px; }
    .action-icon { width: 40px; height: 40px; flex: 0 0 40px; font-size: 16px; }
    .action-title { font-size: 13px; }
    .action-desc { font-size: 11px; }
    .user-avatar-large { width: 42px; height: 42px; flex: 0 0 42px; font-size: 18px; }
}

@media (max-width: 575.98px) {
    .users-filter-panel { padding: .85rem; border-radius: 16px; }
    .users-grid { grid-template-columns: minmax(0, 1fr); gap: .75rem; }
    .user-card { padding: .9rem; border-radius: 15px; }
    .section-title { font-size: 1.05rem; }
    .detail-row { grid-template-columns: 18px 76px minmax(0, 1fr); font-size: .8rem; }
}

@media (prefers-reduced-motion: reduce) {
    .user-card { transition: none; }
    .user-card:hover { transform: none; }
    .action-item { transition: none; }
}
</style>
