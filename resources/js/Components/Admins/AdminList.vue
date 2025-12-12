<script setup>
import UserForm from "@/Components/Users/UserForm.vue";
import UserCard from "@/Components/Users/UserCard.vue";
import Pagination from "@/Components/Pagination.vue";
import UserFilter from "@/Components/Users/UserFilter.vue";
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";
</script>

<template>
    <ul class="list-group">
        <li v-for="user in adminsStore.items" :key="user.id"
            class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-bold"><span class="badge bg-primary">{{ user.percent }}%</span> {{ user.name }}</div>
                <small class="text-muted mb-2">{{ user.email }}</small>
                <div
                    class="form-check form-switch ">
                    <input
                        @change="changeAdminWorkStatus(user)"
                        class="form-check-input"
                        type="checkbox"
                        v-model="user.is_work"
                        :id="`is-work-${user.id}`"
                    />
                    <label class="form-check-label" :for="`is-work-${user.id}`">
                        {{ user.is_work ? 'работает' : 'не работает' }}
                    </label>
                </div>
            </div>

            <!-- Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm" type="button"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <button
                            type="button"
                            @click="selectedAdmin = user"
                            class="dropdown-item" data-bs-toggle="modal" :data-bs-target="'#reportModal'">
                            Сформировать отчет
                        </button>
                    </li>
                    <li><a class="dropdown-item" href="#" @click.prevent="openRoleSwitcher(user)">Сменить роль</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="openEdit(user)">Редактировать</a></li>
                </ul>
            </div>
        </li>
    </ul>

    <!-- Пагинация -->
    <Pagination
        v-if="adminsStore.items.length > 0"
        :pagination="adminsStore.pagination"
        @page-changed="fetchDataByUrl"
    />
    <!-- Сообщение если список пуст -->
    <div v-if="adminsStore.items.length === 0" class="alert alert-info mt-3">
        Администраторов пока нет.
    </div>



    <!-- Модалка -->
    <div class="modal fade" :id="'reportModal'" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Отчет по администратору</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ReportGenerator
                        v-if="selectedAdmin"
                        :type="'admins'"
                        :is-simple="true"
                        :object-id="selectedAdmin.id"
                        @generate-report="handleReport"></ReportGenerator>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка редактирования -->
    <div class="modal fade" id="roleSwitcherUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование пользователя</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form
                        v-on:submit.prevent="changeRole"
                        v-if="selectedAdmin">
                        <div class="form-floating mb-2">
                            <select v-model="selectedAdmin.role" class="form-select" id="role" required>
                                <option :value="0">Пользователь</option>
                                <option :value="1">Младший администратор</option>
                                <option :value="2">Поставщик</option>
                                <option :value="3">Администратор</option>
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


</template>

<script>

import {useAdminsStore} from "@/stores/admins";
import {useUsersStore} from "@/stores/users";

export default {
    name: 'AdminList',

    data() {
        return {
            adminsStore: useAdminsStore(),
            userStore: useUsersStore(),
            selectedAdmin: null,
        }
    },

    created() {
        this.fetchData()
    },

    methods: {

        openRoleSwitcher(user) {
            this.selectedAdmin = user
            new bootstrap.Modal(document.getElementById('roleSwitcherUserModal')).show()

        },
        openEdit(user) {
            this.selectedAdmin = user
            new bootstrap.Modal(document.getElementById('editUserModal')).show()

        },
        async changeAdminWorkStatus(user) {
            await this.userStore.updateWorkStatus(user.id, user.is_work)
        },
        async handleReport(form) {
            form.user_id = this.selectedAdmin.id
            await this.adminsStore.downloadPersonalAdminReport(form)
        },
        async fetchData(page = 1) {
            await this.adminsStore.fetchAdminsByPage(page).then(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'))
                if (modal)
                    modal.hide()
            })

        },
        async fetchDataByUrl(url) {
            await this.adminsStore.fetchByUrl(url)
        },
        async downloadReport(id) {
            await this.adminsStore.downloadReport(id)
        },
        async changeRole() {
            await this.userStore.updateRole(this.selectedAdmin.id, this.selectedAdmin.role).then(() => {
                this.fetchData()
                bootstrap.Modal.getInstance(document.getElementById('roleSwitcherUserModal')).hide()
            })
        },
    }
}
</script>
<style scoped>


</style>
