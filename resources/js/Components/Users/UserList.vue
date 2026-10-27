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
                            <span class="user-id" v-if="field_visible?.id">ID: {{ user.id }}</span>
                        </span>
                    </button>
                    <div class="dropdown user-actions">
                        <button class="action-menu-btn" type="button" data-bs-toggle="dropdown" aria-label="Действия с пользователем">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <template v-if="forSelect">
                                <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="$emit('select', user)"><i class="fa-solid fa-check me-2"></i>Выбрать</a></li>
                                <li><hr class="dropdown-divider"></li>
                            </template>
                            <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="openRoleSwitcher(user)"><i class="fa-solid fa-user-shield me-2"></i>Сменить роль</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="openEdit(user)"><i class="fa-solid fa-pen-to-square me-2"></i>Редактировать</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="openView(user)"><i class="fa-regular fa-eye me-2"></i>Просмотреть</a></li>
                            <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="getTelegramLink(user)"><i class="fa-brands fa-telegram me-2"></i>Ссылка на Telegram</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)" @click.prevent="confirmDelete(user)"><i class="fa-solid fa-trash me-2"></i>Удалить</a></li>
                            <li v-if="!user.blocked_at"><a class="dropdown-item text-danger" href="javascript:void(0)" @click.prevent="confirmBlocked(user)"><i class="fa-solid fa-ban me-2"></i>Заблокировать</a></li>
                            <li v-else><a class="dropdown-item text-success" href="javascript:void(0)" @click.prevent="confirmUnBlocked(user)"><i class="fa-solid fa-unlock me-2"></i>Разблокировать</a></li>
                        </ul>
                    </div>
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
                        <span class="detail-value">{{ user.phone || 'Не указан' }}</span>
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
                    <div v-if="field_visible?.created_at" class="detail-row">
                        <i class="fa-regular fa-calendar detail-icon"></i>
                        <span class="detail-label">Создан</span>
                        <span class="detail-value">{{ user.created_at || '—' }}</span>
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



        <!-- Модалка редактирования -->
        <div class="modal fade" id="roleSwitcherUserModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Редактирование роли</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form
                            v-on:submit.prevent="changeRole"
                            v-if="selectedUser">
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
                        <UserCard v-if="selectedUser" :user="selectedUser"
                                  @edit="openEdit"/>
                    </div>
                </div>
            </div>
        </div>



    </section>
</template>

<script>
import {useUsersStore} from '@/stores/users'
import {useModalStore} from '@/stores/utillites/useConfitmModalStore'

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
            console.log("test", this.field_visible)
            this.usersStore.setFilters(payload.filters)
            this.usersStore.setSort(payload.sort.field, payload.sort.direction)
            this.usersStore.fetchFiltered()
        },
        openRoleSwitcher(user) {
            this.selectedUser = user
            new bootstrap.Modal(document.getElementById('roleSwitcherUserModal')).show()

        },
        openEdit(user) {
            this.selectedUser = null
            this.$nextTick(()=>{
                this.selectedUser = user
                const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewUserModal'))
                if (viewModal)
                    viewModal.hide()
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

        // 🔹 Дополнительные методы для ролей и блокировки
        async changeRole() {
            await this.usersStore.updateRole(this.selectedUser.id, this.selectedUser.role)

            const viewModal = bootstrap.Modal.getInstance(document.getElementById('roleSwitcherUserModal'))
            if (viewModal)
                viewModal.hide()
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
.user-identity { display: flex; align-items: center; gap: .75rem; min-width: 0; padding: 0; border: 0; background: transparent; color: inherit; text-align: left; }
.user-avatar { flex: 0 0 46px; width: 46px; height: 46px; display: grid; place-items: center; border-radius: 15px; background: linear-gradient(145deg, #eaf1ff, #f4f7ff); color: var(--user-blue); font-size: 1.15rem; }
.user-title-wrap { display: flex; flex-direction: column; min-width: 0; gap: .15rem; }
.user-name { font-size: 1rem; font-weight: 800; line-height: 1.25; overflow-wrap: anywhere; }
.user-full-name, .user-id { color: #718096; font-size: .78rem; overflow-wrap: anywhere; }
.action-menu-btn { display: grid; place-items: center; width: 36px; height: 36px; border: 1px solid #e6ebf3; border-radius: 11px; color: #596579; background: #fff; }
.action-menu-btn:hover, .action-menu-btn:focus { color: var(--user-blue); border-color: #bfd1f8; background: #f4f7ff; }
.user-actions .dropdown-menu { border: 1px solid #e7ebf2; border-radius: 13px; padding: .4rem; box-shadow: 0 12px 30px rgba(20, 33, 61, .14); }
.user-actions .dropdown-item { border-radius: 8px; padding: .55rem .7rem; font-size: .9rem; }
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
:deep(.modal-content) { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 20px 60px rgba(20, 33, 61, .2); }
:deep(.modal-header) { background: #f7f9fd; border-bottom-color: #e8edf5; padding: 1rem 1.25rem; }
:deep(.modal-title) { color: var(--user-navy); font-weight: 800; }
:deep(.modal-footer) { border-top-color: #e8edf5; }
@media (max-width: 575.98px) { .users-filter-panel { padding: .85rem; border-radius: 16px; } .users-grid { grid-template-columns: minmax(0, 1fr); gap: .75rem; } .user-card { padding: .9rem; border-radius: 15px; } .section-title { font-size: 1.05rem; } .detail-row { grid-template-columns: 18px 76px minmax(0, 1fr); font-size: .8rem; } }
@media (prefers-reduced-motion: reduce) { .user-card { transition: none; } .user-card:hover { transform: none; } }
</style>
