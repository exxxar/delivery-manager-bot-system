<template>
    <section class="reports-page">
        <header class="reports-header">
            <div class="reports-heading">
                <div class="reports-heading-icon">
                    <i class="fa-solid fa-file-excel"></i>
                </div>
                <div class="reports-heading-text">
                    <h2>Отчёты</h2>
                    <p>Просматривайте, скачивайте и отправляйте сформированные отчёты</p>
                </div>
            </div>

            <div class="reports-count" v-if="reportsStore.items.length > 0">
                <i class="fa-solid fa-folder-open"></i>
                <span>{{ reportsStore.items.length }} в списке</span>
            </div>
        </header>

        <div v-if="reportsStore.error" class="reports-alert reports-alert-error" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ reportsStore.error }}</span>
        </div>

        <div v-if="reportsStore.loading" class="reports-loading" role="status">
            <span class="spinner-border" aria-hidden="true"></span>
            <span>Загружаем отчёты...</span>
        </div>

        <div v-else-if="reportsStore.items.length > 0" class="reports-grid">
            <article
                v-for="report in reportsStore.items"
                :key="report.id"
                class="report-card"
            >
                <div class="report-card-top">
                    <div class="report-file-icon">
                        <i class="fa-solid fa-file-excel"></i>
                    </div>

                    <div class="report-actions dropdown">
                        <button
                            class="report-actions-button"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            :aria-label="`Действия с отчётом ${report.title}`"
                        >
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end report-actions-menu">
                            <li>
                                <button class="dropdown-item" type="button" @click="downloadReport(report)">
                                    <i class="fa-solid fa-download"></i>
                                    Скачать файл
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item" type="button" @click="sendToTelegram(report)">
                                    <i class="fa-brands fa-telegram"></i>
                                    Отправить в Telegram
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item danger-item" type="button" @click="confirmDelete(report)">
                                    <i class="fa-solid fa-trash"></i>
                                    Удалить отчёт
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="report-title-row">
                    <span class="report-type">
                        {{ reportsStore.getReportTypeLabel(report.report_type) }}
                    </span>
                    <h3>{{ report.title }}</h3>
                </div>

                <div class="report-file-name">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>{{ report.file_name }}</span>
                </div>

                <div class="report-details">
                    <div class="report-detail">
                        <span class="detail-icon blue">
                            <i class="fa-regular fa-calendar"></i>
                        </span>
                        <span class="detail-copy">
                            <small>Дата создания</small>
                            <strong>{{ reportsStore.formatDate(report.created_at) }}</strong>
                        </span>
                    </div>

                    <div class="report-detail">
                        <span class="detail-icon gray">
                            <i class="fa-solid fa-hard-drive"></i>
                        </span>
                        <span class="detail-copy">
                            <small>Размер файла</small>
                            <strong>{{ reportsStore.formatFileSize(report.file_size) }}</strong>
                        </span>
                    </div>

                    <div v-if="report.start_date && report.end_date" class="report-detail report-period">
                        <span class="detail-icon red">
                            <i class="fa-solid fa-calendar-days"></i>
                        </span>
                        <span class="detail-copy">
                            <small>Отчётный период</small>
                            <strong>{{ formatDate(report.start_date) }} — {{ formatDate(report.end_date) }}</strong>
                        </span>
                    </div>
                </div>

                <div class="report-link-block">
                    <div class="report-link-heading">
                        <i class="fa-solid fa-link"></i>
                        <span>Ссылка на файл</span>
                    </div>

                    <div class="report-link-controls">
                        <a
                            :href="getFileUrl(report)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="report-file-link"
                            :title="getFileUrl(report)"
                        >
                            {{ getFileUrl(report) }}
                        </a>

                        <button
                            type="button"
                            class="copy-link-button"
                            :class="{ copied: copiedId === report.id }"
                            @click="copyFileUrl(report)"
                            :title="copiedId === report.id ? 'Скопировано!' : 'Скопировать ссылку'"
                        >
                            <i :class="copiedId === report.id ? 'fa-solid fa-check' : 'fa-solid fa-copy'"></i>
                            <span>{{ copiedId === report.id ? 'Готово' : 'Копировать' }}</span>
                        </button>
                    </div>
                </div>
            </article>
        </div>

        <div v-else class="reports-empty">
            <div class="empty-icon">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <h3>Отчётов пока нет</h3>
            <p>Когда отчёты будут сформированы, они появятся здесь.</p>
        </div>

        <div v-if="!reportsStore.loading" class="reports-pagination">
            <Pagination
                :pagination="reportsStore.pagination"
                @page-changed="fetchDataByUrl"
            />
        </div>
    </section>
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
.reports-page {
    --reports-blue: #2457d6;
    --reports-blue-dark: #173b91;
    --reports-red: #df3548;
    --reports-text: #17233b;
    --reports-muted: #778399;
    --reports-border: #e5eaf2;
    color: var(--reports-text);
    width: 100%;
}

.reports-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
}

.reports-heading {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 0;
}

.reports-heading-icon {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    color: #fff;
    background: linear-gradient(135deg, var(--reports-blue), var(--reports-blue-dark));
    border-radius: 16px;
    box-shadow: 0 7px 18px rgba(36, 87, 214, .17);
    font-size: 24px;
}

.reports-heading-text h2 {
    margin: 0 0 5px;
    font-size: clamp(21px, 2.5vw, 27px);
    font-weight: 750;
    letter-spacing: -.45px;
}

.reports-heading-text p {
    margin: 0;
    color: var(--reports-muted);
    font-size: 14px;
    line-height: 1.5;
}

.reports-count {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 13px;
    color: var(--reports-blue);
    background: #edf3ff;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.reports-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.report-card {
    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    padding: 21px;
    background: #fff;
    border: 1px solid var(--reports-border);
    border-radius: 17px;
    box-shadow: 0 3px 12px rgba(23, 35, 59, .035);
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

.report-card::before {
    position: absolute;
    top: 0;
    left: 18px;
    right: 18px;
    height: 3px;
    content: "";
    background: linear-gradient(90deg, var(--reports-blue), #6e92ed);
    border-radius: 0 0 4px 4px;
}

.report-card:hover {
    border-color: #cbd8f4;
    box-shadow: 0 10px 26px rgba(23, 35, 59, .075);
    transform: translateY(-2px);
}

.report-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 17px;
}

.report-file-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    color: #17845b;
    background: #e8f8f0;
    border-radius: 14px;
    font-size: 22px;
}

.report-actions-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    color: #65738a;
    background: #f6f8fc;
    border: 1px solid transparent;
    border-radius: 10px;
    font-size: 18px;
    transition: color .2s ease, background .2s ease, border-color .2s ease;
}

.report-actions-button:hover,
.report-actions-button[aria-expanded="true"] {
    color: var(--reports-blue);
    background: #edf3ff;
    border-color: #d9e4fb;
}

.report-actions-menu {
    min-width: 205px;
    padding: 7px;
    border: 1px solid var(--reports-border);
    border-radius: 12px;
    box-shadow: 0 12px 30px rgba(23, 35, 59, .13);
}

.report-actions-menu .dropdown-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    color: var(--reports-text);
    border-radius: 8px;
    font-size: 13px;
}

.report-actions-menu .dropdown-item i {
    width: 17px;
    color: var(--reports-blue);
    text-align: center;
}

.report-actions-menu .dropdown-item:hover {
    background: #f3f6fc;
}

.report-actions-menu .dropdown-item.danger-item,
.report-actions-menu .dropdown-item.danger-item i {
    color: var(--reports-red);
}

.report-actions-menu .dropdown-item.danger-item:hover {
    background: #fff0f1;
}

.report-title-row {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 9px;
    margin-bottom: 13px;
}

.report-type {
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    padding: 5px 9px;
    color: var(--reports-blue);
    background: #edf3ff;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 750;
    line-height: 1.35;
}

.report-title-row h3 {
    margin: 0;
    overflow-wrap: anywhere;
    font-size: 18px;
    font-weight: 750;
    line-height: 1.4;
}

.report-file-name {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin-bottom: 18px;
    color: var(--reports-muted);
    font-size: 13px;
    overflow-wrap: anywhere;
}

.report-file-name i {
    flex-shrink: 0;
    margin-top: 3px;
    color: #198754;
}

.report-details {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}

.report-detail {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    min-width: 0;
    padding: 11px;
    background: #f8faff;
    border: 1px solid #edf1f8;
    border-radius: 11px;
}

.report-detail.report-period {
    grid-column: 1 / -1;
}

.detail-icon {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 9px;
    font-size: 13px;
}

.detail-icon.blue {
    color: var(--reports-blue);
    background: #e8efff;
}

.detail-icon.gray {
    color: #65738a;
    background: #e9edf3;
}

.detail-icon.red {
    color: var(--reports-red);
    background: #fff0f1;
}

.detail-copy {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.detail-copy small {
    color: var(--reports-muted);
    font-size: 11px;
}

.detail-copy strong {
    color: var(--reports-text);
    font-size: 12px;
    font-weight: 700;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.report-link-block {
    margin-top: auto;
    padding: 13px;
    background: #f8faff;
    border: 1px solid var(--reports-border);
    border-radius: 12px;
}

.report-link-heading {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 9px;
    color: var(--reports-muted);
    font-size: 12px;
    font-weight: 700;
}

.report-link-heading i {
    color: var(--reports-blue);
}

.report-link-controls {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 0;
}

.report-file-link {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    color: var(--reports-blue);
    font-family: "Courier New", monospace;
    font-size: 11px;
    text-decoration: none;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.report-file-link:hover {
    color: var(--reports-blue-dark);
    text-decoration: underline;
}

.copy-link-button {
    display: inline-flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 35px;
    padding: 7px 10px;
    color: var(--reports-blue);
    background: #fff;
    border: 1px solid #d6e1f8;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 700;
    transition: background .2s ease, border-color .2s ease, color .2s ease;
}

.copy-link-button:hover {
    background: #edf3ff;
    border-color: #b9cdf6;
}

.copy-link-button.copied {
    color: #168253;
    background: #e8f8f0;
    border-color: #bce8d2;
}

.reports-empty,
.reports-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 52px 20px;
    color: var(--reports-muted);
    text-align: center;
    background: #fff;
    border: 1px dashed #dbe3ef;
    border-radius: 17px;
}

.empty-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 68px;
    height: 68px;
    margin-bottom: 17px;
    color: var(--reports-blue);
    background: #edf3ff;
    border-radius: 20px;
    font-size: 29px;
}

.reports-empty h3 {
    margin: 0 0 7px;
    color: var(--reports-text);
    font-size: 18px;
    font-weight: 750;
}

.reports-empty p {
    margin: 0;
    font-size: 13px;
}

.reports-loading {
    flex-direction: row;
    gap: 12px;
    border-style: solid;
    font-size: 14px;
}

.reports-loading .spinner-border {
    width: 22px;
    height: 22px;
    color: var(--reports-blue);
    border-width: 2px;
}

.reports-alert {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 16px;
    padding: 13px 15px;
    border-radius: 12px;
    font-size: 13px;
}

.reports-alert i {
    margin-top: 2px;
}

.reports-alert-error {
    color: #a92335;
    background: #fff0f1;
    border: 1px solid #f5cbd1;
}

.reports-pagination {
    margin-top: 22px;
}

@media (max-width: 991.98px) {
    .reports-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 575.98px) {
    .reports-header {
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .reports-heading {
        gap: 12px;
    }

    .reports-heading-icon {
        width: 47px;
        height: 47px;
        border-radius: 13px;
        font-size: 20px;
    }

    .reports-heading-text {
        flex: 1;
    }

    .reports-heading-text p {
        font-size: 12px;
    }

    .reports-count {
        margin-left: 59px;
    }

    .report-card {
        padding: 17px;
    }

    .report-details {
        grid-template-columns: 1fr;
    }

    .report-detail.report-period {
        grid-column: auto;
    }

    .report-link-controls {
        align-items: stretch;
        flex-direction: column;
    }

    .report-file-link {
        padding: 5px 0;
    }

    .copy-link-button {
        align-self: flex-start;
    }
}

@media (prefers-reduced-motion: reduce) {
    .report-card,
    .report-actions-button,
    .copy-link-button {
        transition: none;
    }
}
</style>
