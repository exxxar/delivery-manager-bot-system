<template>
    <div>
        <ul class="list-group" v-if="reportsStore.items.length > 0">
            <li v-for="report in reportsStore.items" :key="report.id"
                class="list-group-item d-flex justify-content-between align-items-center">
                <div class="flex-grow-1">
                    <div class="fw-bold">
                        <span class="badge bg-primary me-2">
                            {{ reportsStore.getReportTypeLabel(report.report_type) }}
                        </span>
                        {{ report.title }}
                    </div>
                    <p class="text-muted small mb-1">
                        <i class="fa-solid fa-file-excel text-success"></i>
                        {{ report.file_name }}
                    </p>
                    <p class="text-muted small mb-1">
                        <i class="fa-solid fa-calendar text-primary"></i>
                        Создан: {{ reportsStore.formatDate(report.created_at) }}
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="fa-solid fa-weight-hanging text-secondary"></i>
                        Размер: {{ reportsStore.formatFileSize(report.file_size) }}
                    </p>
                    <p class="text-muted small mb-0" v-if="report.start_date && report.end_date">
                        <i class="fa-solid fa-calendar-days text-info"></i>
                        Период: {{ formatDate(report.start_date) }} - {{ formatDate(report.end_date) }}
                    </p>
                </div>

                <!-- Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm" type="button"
                            data-bs-toggle="dropdown">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#" @click.prevent="downloadReport(report)">
                                <i class="fa-solid fa-download text-success me-2"></i>
                                Скачать
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="#"
                               @click.prevent="confirmDelete(report)">
                                <i class="fa-solid fa-trash me-2"></i>
                                Удалить
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>

        <Pagination
            :pagination="reportsStore.pagination"
            @page-changed="fetchDataByUrl"
        />

        <!-- Сообщение если список пуст -->
        <div v-if="reportsStore.items.length === 0 && !reportsStore.loading" class="alert alert-info mt-3">
            Отчетов пока нет.
        </div>

        <!-- Индикатор загрузки -->
        <div v-if="reportsStore.loading" class="text-center my-3">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
        </div>

        <!-- Сообщение об ошибке -->
        <div v-if="reportsStore.error" class="alert alert-danger mt-3">
            {{ reportsStore.error }}
        </div>
    </div>
</template>

<script>
import { useReportsStore } from "@/stores/reports";
import { useModalStore } from "@/stores/utillites/useConfitmModalStore";
import Pagination from "@/Components/Pagination.vue";

export default {
    name: 'ReportList',
    components: { Pagination },
    data() {
        return {
            reportsStore: useReportsStore(),
            modalStore: useModalStore(),
        }
    },
    created() {
        this.fetchData()
    },
    methods: {
        async fetchData(page = 1) {
            await this.reportsStore.fetchAll(page)
        },
        async fetchDataByUrl(url) {
            await this.reportsStore.fetchByUrl(url)
        },
        async downloadReport(report) {
            try {
                await this.reportsStore.download(report.id)
            } catch (e) {
                console.error('Ошибка при скачивании:', e)
            }
        },
        confirmDelete(report) {
            this.modalStore.open(
                `Вы уверены, что хотите удалить отчет <b>${report.title}</b>?`,
                async () => {
                    try {
                        await this.reportsStore.remove(report.id)
                        this.modalStore.close()
                    } catch (e) {
                        console.error('Ошибка при удалении:', e)
                    }
                },
                () => this.modalStore.close()
            )
        },
        formatDate(dateString) {
            if (!dateString) return '-'
            const date = new Date(dateString)
            return date.toLocaleDateString('ru-RU')
        }
    }
}
</script>
