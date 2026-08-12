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
                    <p class="text-muted small mb-1">
                        <i class="fa-solid fa-weight-hanging text-secondary"></i>
                        Размер: {{ reportsStore.formatFileSize(report.file_size) }}
                    </p>
                    <p class="text-muted small mb-2" v-if="report.start_date && report.end_date">
                        <i class="fa-solid fa-calendar-days text-info"></i>
                        Период: {{ formatDate(report.start_date) }} - {{ formatDate(report.end_date) }}
                    </p>

                    <!-- 🔹 Блок со ссылкой на файл -->
                    <div class="file-link-block mt-2 p-2 bg-light rounded border">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-link text-primary"></i>
                            <a
                                :href="getFileUrl(report)"
                                target="_blank"
                                class="file-link small text-truncate flex-grow-1"
                                :title="getFileUrl(report)"
                            >
                               Ссылка
                            </a>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary flex-shrink-0 copy-btn"
                                @click="copyFileUrl(report)"
                                :title="copiedId === report.id ? 'Скопировано!' : 'Скопировать ссылку'"
                            >
                                <i :class="copiedId === report.id ? 'fa-solid fa-check text-success' : 'fa-solid fa-copy'"></i>
                                <span class="d-none d-sm-inline ms-1">
                                    {{ copiedId === report.id ? 'Готово' : 'Копировать' }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Dropdown -->
                <div class="dropdown ms-2">
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
                            <a class="dropdown-item" href="#" @click.prevent="sendToTelegram(report)">
                                <i class="fa-solid fa-paper-plane text-primary me-2"></i>
                                Отправить в телеграм
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
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
import { useAlertStore } from "@/stores/utillites/useAlertStore";
import Pagination from "@/Components/Pagination.vue";

export default {
    name: 'ReportList',
    components: { Pagination },
    data() {
        return {
            reportsStore: useReportsStore(),
            modalStore: useModalStore(),
            alertStore: useAlertStore(),
            copiedId: null, // ID отчёта, ссылку на который только что скопировали
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

        /**
         * Формирует полный URL к файлу отчёта
         * Пример: https://domain.com/storage/app/exports/file.xlsx
         */
        getFileUrl(report) {
            if (!report.file_path) return '#'

            // Кодируем путь, чтобы кириллица и пробелы отображались корректно
            const basePath = '/storage/app/'
            const encodedPath = encodeURI(report.file_path)

            return window.location.origin + basePath + encodedPath
        },

        /**
         * Копирует ссылку в буфер обмена
         */
        async copyFileUrl(report) {
            const url = this.getFileUrl(report)

            try {
                // Современный способ
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(url)
                } else {
                    // Fallback для старых браузеров / http
                    this.fallbackCopy(url)
                }

                // Показываем индикатор "Скопировано"
                this.copiedId = report.id
                this.alertStore.show('Ссылка скопирована в буфер обмена', 'success')

                // Сбрасываем через 2 секунды
                setTimeout(() => {
                    if (this.copiedId === report.id) {
                        this.copiedId = null
                    }
                }, 2000)

            } catch (e) {
                console.error('Ошибка копирования:', e)
                this.alertStore.show('Не удалось скопировать ссылку', 'error')
            }
        },

        /**
         * Fallback для копирования через временный input
         */
        fallbackCopy(text) {
            const textarea = document.createElement('textarea')
            textarea.value = text
            textarea.style.position = 'fixed'
            textarea.style.opacity = '0'
            document.body.appendChild(textarea)
            textarea.select()

            try {
                document.execCommand('copy')
            } finally {
                document.body.removeChild(textarea)
            }
        },

        async sendToTelegram(report) {
            try {
                await this.reportsStore.sendToTelegram(report.id)
                this.alertStore.show('Отчёт отправлен в Telegram', 'success')
            } catch (e) {
                console.error('Ошибка отправки:', e)
                this.alertStore.show('Не удалось отправить отчёт', 'error')
            }
        },

        async downloadReport(report) {
            try {
                await this.reportsStore.download(report.id)
            } catch (e) {
                console.error('Ошибка при скачивании:', e)
                this.alertStore.show('Не удалось скачать отчёт', 'error')
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

<style scoped>
.file-link-block {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.file-link {
    color: #0d6efd;
    text-decoration: none;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    min-width: 0; /* важно для text-truncate внутри flex */
}

.file-link:hover {
    text-decoration: underline;
    color: #0a58ca;
}

.copy-btn {
    font-size: 12px;
    padding: 4px 10px;
    transition: all 0.2s ease;
}

.copy-btn:hover {
    transform: scale(1.05);
}

.copy-btn:active {
    transform: scale(0.95);
}
</style>
