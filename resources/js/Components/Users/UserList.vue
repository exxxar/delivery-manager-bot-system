<script setup>
import UserForm from "@/Components/Users/UserForm.vue";
import UserCard from "@/Components/Users/UserCard.vue";
import Pagination from "@/Components/Pagination.vue";
import UserFilter from "@/Components/Users/UserFilter.vue";
</script>

<template>

        <h4 class="mb-3">Список пользователей</h4>

        <UserFilter v-on:apply-filters="applyFilter"></UserFilter>

        <ul class="list-group">
            <li v-for="user in usersStore.items" :key="user.id" class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">{{ user.name }}</div>
                    <small class="text-muted">{{ user.email }}</small>
                </div>

                <!-- Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        Действия
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" @click.prevent="$emit('select', user)">Выбрать</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="openEdit(user)">Редактировать</a></li>
                        <li><a class="dropdown-item text-danger" href="#" @click.prevent="confirmDelete(user)">Удалить</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="openView(user)">Просмотреть</a></li>
                    </ul>
                </div>
            </li>
        </ul>

        <!-- Пагинация -->
        <Pagination
            :pagination="usersStore.pagination"
            @page-changed="fetchUsersByUrl"
        />
        <!-- Сообщение если список пуст -->
        <div v-if="usersStore.length === 0" class="alert alert-info mt-3">
            Пользователей пока нет.
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
                        <UserForm v-if="selectedUser" :initialData="selectedUser" @saved="fetchUsers" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка удаления -->
        <div class="modal fade" id="deleteUserModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Удаление пользователя</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Вы уверены, что хотите удалить <strong>{{ selectedUser?.name }}</strong>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-danger" @click="deleteUser">Удалить</button>
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
                        <UserCard v-if="selectedUser" :user="selectedUser" @edit="openEdit" />
                    </div>
                </div>
            </div>
        </div>




</template>

<script>
import { useUsersStore } from '@/stores/users'

export default {
    name: 'UserList',

    data() {
        return {

            usersStore: useUsersStore(),
            selectedUser: null
        }
    },

    created() {
        this.fetchUsers()
    },

    methods: {

        async fetchUsers(page = 1) {
            await this.usersStore.fetchAllByPage(page)

        },
        async fetchUsersByUrl(url) {
            await this.usersStore.fetchByUrl(url)
        },
        async applyFilter(payload){
            this.usersStore.setFilters(payload.filters)
            this.usersStore.setSort(payload.sort.field, payload.sort.direction)
            this.usersStore.fetchFiltered()
        },

        openEdit(user) {
            this.selectedUser = user
            new bootstrap.Modal(document.getElementById('editUserModal')).show()
        },

        confirmDelete(user) {
            this.selectedUser = user
            new bootstrap.Modal(document.getElementById('deleteUserModal')).show()
        },

        async deleteUser() {
            try {
                await this.usersStore.remove(this.selectedUser.id)
                bootstrap.Modal.getInstance(document.getElementById('deleteUserModal')).hide()
            } catch (error) {
                console.error('Ошибка удаления:', error)
            }
        },

        openView(user) {
            this.selectedUser = user
            new bootstrap.Modal(document.getElementById('viewUserModal')).show()
        },

        // 🔹 Дополнительные методы для ролей и блокировки
        async changeRole(user, role) {
            try {
                await this.usersStore.updateRole(user.id, role)
            } catch (error) {
                console.error('Ошибка изменения роли:', error)
            }
        },

        async blockUser(user, message = '') {
            try {
                await this.usersStore.block(user.id, message)
            } catch (error) {
                console.error('Ошибка блокировки:', error)
            }
        },

        async unblockUser(user) {
            try {
                await this.usersStore.unblock(user.id)
            } catch (error) {
                console.error('Ошибка разблокировки:', error)
            }
        }
    }
}
</script>
<style scoped>
.full-width-btn {
    width: 100%;
    background: transparent;
    border: 1px solid #007bff; /* рамка синяя */
    color: #007bff;            /* текст синий */
    padding: 1rem;
}

</style>
