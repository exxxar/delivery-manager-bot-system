<script setup>
import UserForm from "@/Components/Users/UserForm.vue";
import UserCard from "@/Components/Users/UserCard.vue";
import Pagination from "@/Components/Pagination.vue";
import UserFilter from "@/Components/Users/UserFilter.vue";
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";
</script>

<template>

        <h4 class="mb-3">Список администраторов</h4>

        <ul class="list-group">
            <li v-for="user in adminsStore.items" :key="user.id" class="list-group-item d-flex justify-content-between align-items-center">
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
                        <li>
                            <button
                                type="button"
                                @click="selectedAdmin = user"
                                class="dropdown-item" data-bs-toggle="modal" :data-bs-target="'#reportModal'">
                                Сформировать отчет
                            </button>
                        </li>
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



</template>

<script>

import {useAdminsStore} from "@/stores/admins";

export default {
    name: 'AdminList',

    data() {
        return {
            adminsStore: useAdminsStore(),
            selectedAdmin:null,
        }
    },

    created() {
        this.fetchData()
    },

    methods: {
        async handleReport(form){
            form.user_id = this.selectedAdmin.id
            await this.adminsStore.downloadPersonalAdminReport(form)
        },
        async fetchData(page = 1) {
            await this.adminsStore.fetchAdminsByPage(page)

        },
        async fetchDataByUrl(url) {
            await this.adminsStore.fetchByUrl(url)
        },
        async downloadReport(id){
            await this.adminsStore.downloadReport(id)
        }
    }
}
</script>
<style scoped>


</style>
