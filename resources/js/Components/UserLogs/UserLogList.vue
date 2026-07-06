<template>
    <div>
        <ul class="list-group" v-if="logsStore.items.length > 0">
            <li v-for="log in logsStore.items" :key="log.id"
                class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <p class="mb-1" v-html="log.message"></p>
                        <small class="text-muted">
                            <i class="fa-solid fa-clock me-1"></i>
                            {{ logsStore.formatDate(log.created_at) }}
                        </small>
                    </div>
                </div>
            </li>
        </ul>

        <Pagination
            :pagination="logsStore.pagination"
            @page-changed="fetchDataByUrl"
        />

        <!-- Сообщение если список пуст -->
        <div v-if="logsStore.items.length === 0 && !logsStore.loading" class="alert alert-info mt-3">
            Логи пока отсутствуют.
        </div>

        <!-- Индикатор загрузки -->
        <div v-if="logsStore.loading" class="text-center my-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
        </div>

        <!-- Сообщение об ошибке -->
        <div v-if="logsStore.error" class="alert alert-danger mt-3">
            {{ logsStore.error }}
        </div>
    </div>
</template>

<script>
import { useUserLogsStore } from "@/stores/userLogs";
import Pagination from "@/Components/Pagination.vue";

export default {
    name: 'UserLogList',
    components: { Pagination },
    data() {
        return {
            logsStore: useUserLogsStore(),
        }
    },
    created() {
        this.fetchData()
    },
    methods: {
        async fetchData(page = 1) {
            await this.logsStore.fetchAll(page)
        },
        async fetchDataByUrl(url) {
            await this.logsStore.fetchByUrl(url)
        }
    }
}
</script>
