<template>
    <main class="my-income-page container-fluid py-3 py-lg-4">
        <header class="page-heading d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="heading-icon"><i class="fa-solid fa-wallet"></i></div>
                <div>
                    <h1 class="h3 mb-1">Мой доход</h1>
                    <p class="text-muted mb-0">Кто начисляет вам процент с продаж</p>
                </div>
            </div>
            <div class="heading-actions d-flex flex-wrap align-items-center gap-2">
                <button
                    class="btn btn-primary report-btn"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#incomeReportModal"
                >
                    <i class="fa-solid fa-file-circle-plus me-2"></i>Сформировать отчёт
                </button>
                <button class="btn btn-outline-primary refresh-btn" type="button" :disabled="loading" @click="loadIncome">
                    <i class="fa-solid fa-rotate-right me-2" :class="{ 'fa-spin': loading }"></i>Обновить
                </button>
            </div>
        </header>

        <section class="period-card mb-4">
            <div class="period-title"><i class="fa-regular fa-calendar me-2"></i>Календарный период</div>
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-5 col-lg-3">
                    <label class="form-label" for="income-from">С даты</label>
                    <input id="income-from" v-model="from" type="date" class="form-control" :max="to || undefined" @change="loadIncome">
                </div>
                <div class="col-12 col-sm-5 col-lg-3">
                    <label class="form-label" for="income-to">По дату</label>
                    <input id="income-to" v-model="to" type="date" class="form-control" :min="from || undefined" @change="loadIncome">
                </div>
                <div class="col-12 col-sm-2 col-lg-3">
                    <button class="btn btn-primary w-100" type="button" :disabled="loading || !from || !to || from > to" @click="loadIncome">
                        <i class="fa-solid fa-filter me-2"></i>Применить
                    </button>
                </div>
                <div class="col-12 col-lg-3 period-presets">
                    <button type="button" class="btn btn-sm btn-light" @click="setThisMonth">Текущий месяц</button>
                    <button type="button" class="btn btn-sm btn-light" @click="setPreviousMonth">Прошлый месяц</button>
                </div>
            </div>
        </section>

        <div v-if="error" class="alert alert-danger d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i><span>{{ error }}</span>
            <button class="btn btn-sm btn-outline-danger ms-auto" type="button" @click="loadIncome">Повторить</button>
        </div>

        <section class="summary-grid mb-4" aria-label="Итоги периода">
            <article class="summary-card summary-income">
                <span class="summary-label">Начислено за период</span>
                <strong>{{ money(summary.total_income) }}</strong>
                <small>По активным процентам</small>
            </article>
            <article class="summary-card">
                <span class="summary-label">Источников дохода</span>
                <strong>{{ summary.sources_count || 0 }}</strong>
                <small>Администраторы и поставщики</small>
            </article>
            <article class="summary-card">
                <span class="summary-label">Оборот источников</span>
                <strong>{{ money(summary.total_turnover) }}</strong>
                <small>{{ summary.sales_count || 0 }} завершённых сделок</small>
            </article>
        </section>

        <section class="income-card">
            <div class="table-heading d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h2 class="h5 mb-1">Мои проценты</h2>
                    <p class="text-muted mb-0">Сумма рассчитывается от оборота завершённых сделок за выбранные даты</p>
                </div>
                <span class="source-count">{{ sources.length }} {{ pluralSources(sources.length) }}</span>
            </div>

            <div v-if="loading" class="loading-state">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span>Загружаем доход…</span>
            </div>
            <div v-else-if="sources.length" class="table-responsive">
                <table class="table align-middle income-table mb-0">
                    <thead>
                    <tr>
                        <th>От кого приходит доход</th>
                        <th class="text-center">Мой процент</th>
                        <th class="text-end">Оборот за период</th>
                        <th class="text-end">Начислено</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="source in sources" :key="source.id">
                        <td>
                            <div class="source-cell">
                                <span class="source-avatar" :class="source.owner_type">
                                    <i :class="source.owner_type === 'supplier' ? 'fa-solid fa-boxes-stacked' : 'fa-solid fa-user-tie'"></i>
                                </span>
                                <span class="source-info">
                                    <strong>{{ source.owner_name }}</strong>
                                    <small>{{ source.owner_type_label }} · {{ source.sales_count }} {{ pluralSales(source.sales_count) }}</small>
                                </span>
                            </div>
                        </td>
                        <td class="text-center"><span class="percent-pill">{{ formatPercent(source.percentage) }}%</span></td>
                        <td class="text-end numeric-cell">{{ money(source.turnover) }}</td>
                        <td class="text-end numeric-cell income-value">{{ money(source.income) }}</td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="2"><strong>Итого за период</strong></td>
                        <td class="text-end numeric-cell"><strong>{{ money(summary.total_turnover) }}</strong></td>
                        <td class="text-end numeric-cell income-value"><strong>{{ money(summary.total_income) }}</strong></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <div v-else-if="!error" class="empty-state">
                <div class="empty-icon"><i class="fa-solid fa-percent"></i></div>
                <h3 class="h6">Начислений за период нет</h3>
                <p class="text-muted mb-0">Для выбранных дат не найдено активных процентов или завершённых сделок.</p>
            </div>
        </section>

        <p class="calculation-note mt-3 mb-0">
            <i class="fa-solid fa-circle-info me-1"></i>
            В расчёт входят только активные проценты и завершённые сделки со статусом <code>completed</code>, у которых фактическая дата доставки попадает в выбранный период. Используются текущие настройки процентов.
        </p>

        <!-- Модальное окно индивидуального генератора отчётов -->
        <div
            id="incomeReportModal"
            class="modal fade"
            tabindex="-1"
            aria-labelledby="incomeReportModalTitle"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content report-modal">
                    <div class="modal-header">
                        <div class="d-flex align-items-center gap-3">
                            <div class="report-modal-icon">
                                <i class="fa-solid fa-file-lines"></i>
                            </div>
                            <div>
                                <h2 id="incomeReportModalTitle" class="modal-title h5 mb-1">Генератор отчётов</h2>
                                <p class="text-muted small mb-0">Формирование персональной отчётности</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <ReportIndividualGenerator
                            v-if="agentId != null"
                            :agent-id="agentId"
                            @generate-report="handleReport"
                        />
                        <div v-else class="alert alert-warning d-flex align-items-start gap-2 mb-0">
                            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                            <span>Профиль агента не найден. Проверьте привязку агента к вашему аккаунту.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import axios from 'axios'
import ReportIndividualGenerator from "@/Components/Admins/ReportIndividualGenerator.vue"
import { useUsersStore } from "@/stores/users"

const dateString = date => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

export default {
    name: 'MyIncome',
    data() {
        const now = new Date()
        return {
            userStore: useUsersStore(),
            from: dateString(new Date(now.getFullYear(), now.getMonth(), 1)),
            to: dateString(new Date(now.getFullYear(), now.getMonth() + 1, 0)),
            sources: [],
            summary: { sources_count: 0, total_income: 0, total_turnover: 0, sales_count: 0 },
            loading: false,
            error: '',
            incomeRequestId: 0,
        }
    },
    components: {
        ReportIndividualGenerator,
    },
    computed: {
        agentId() {
            return this.userStore.self?.agent?.id ?? null
        },
    },
    mounted() {
        this.loadIncome()
    },
    methods: {
        handleReport() {
            const element = document.getElementById('incomeReportModal')
            const modal = element && window.bootstrap?.Modal
                ? window.bootstrap.Modal.getInstance(element)
                : null
            modal?.hide()
        },
        async loadIncome() {
            if (!this.from || !this.to || this.from > this.to) return

            // Не позволяем более медленному старому запросу перезаписать
            // результат нового периода.
            const requestId = ++this.incomeRequestId
            this.loading = true
            this.error = ''

            try {
                const params = { from: this.from, to: this.to }
                const { data } = await axios.get('/api/my-income', { params })

                if (requestId !== this.incomeRequestId) return

                this.sources = Array.isArray(data?.sources) ? data.sources : []
                this.summary = {
                    sources_count: Number(data?.summary?.sources_count ?? this.sources.length),
                    total_income: Number(data?.summary?.total_income ?? 0),
                    total_turnover: Number(data?.summary?.total_turnover ?? 0),
                    sales_count: Number(data?.summary?.sales_count ?? 0),
                }
            } catch (e) {
                if (requestId !== this.incomeRequestId) return

                // При ошибке не подменяем реальные данные нулями.
                this.error = e?.response?.data?.message || 'Не удалось загрузить данные о доходе.'
            } finally {
                if (requestId === this.incomeRequestId) {
                    this.loading = false
                }
            }
        },
        setThisMonth() {
            const now = new Date()
            this.from = dateString(new Date(now.getFullYear(), now.getMonth(), 1))
            this.to = dateString(new Date(now.getFullYear(), now.getMonth() + 1, 0))
            this.loadIncome()
        },
        setPreviousMonth() {
            const now = new Date()
            this.from = dateString(new Date(now.getFullYear(), now.getMonth() - 1, 1))
            this.to = dateString(new Date(now.getFullYear(), now.getMonth(), 0))
            this.loadIncome()
        },
        money(value) {
            return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 2 }).format(Number(value) || 0)
        },
        formatPercent(value) {
            const number = Number(value) || 0
            return Number.isInteger(number) ? String(number) : number.toLocaleString('ru-RU', { maximumFractionDigits: 2 })
        },
        pluralSources(count) {
            const n = Math.abs(count) % 100
            const last = n % 10
            if (n > 10 && n < 20) return 'источников'
            if (last === 1) return 'источник'
            if (last >= 2 && last <= 4) return 'источника'
            return 'источников'
        },
        pluralSales(count) {
            const n = Math.abs(count) % 100
            const last = n % 10
            if (n > 10 && n < 20) return 'сделок'
            if (last === 1) return 'сделка'
            if (last >= 2 && last <= 4) return 'сделки'
            return 'сделок'
        },
    },
}
</script>

<style scoped>
.my-income-page { color: #18243a; max-width: 1600px; margin: 0 auto; }
.heading-icon { width: 54px; height: 54px; border-radius: 16px; display: grid; place-items: center; color: #fff; background: linear-gradient(135deg, #1769e0, #4289f5); box-shadow: 0 8px 20px #1769e02b; font-size: 21px; }
.page-heading h1 { font-weight: 800; letter-spacing: -.035em; }
.page-heading p { font-size: 14px; }
.refresh-btn, .report-btn { border-radius: 11px; font-weight: 650; padding: 10px 14px; }
.report-modal { overflow: hidden; border: 1px solid #e8edf5; border-radius: 18px; box-shadow: 0 20px 60px rgba(23, 35, 59, .16); }
.report-modal .modal-header { padding: 18px 22px; background: #f8faff; border-bottom: 1px solid #e8edf5; }
.report-modal .modal-body { padding: 22px; background: #fff; }
.report-modal-icon { width: 44px; height: 44px; display: grid; place-items: center; flex: 0 0 44px; border-radius: 13px; color: #2457d6; background: #eaf1ff; font-size: 18px; }
.period-card, .income-card { background: #fff; border: 1px solid #e8edf5; border-radius: 18px; padding: 22px; box-shadow: 0 5px 22px #192b4808; }
.period-title { font-size: 14px; font-weight: 750; margin-bottom: 17px; }
.period-card .form-label { font-size: 12px; font-weight: 700; color: #61718a; }
.period-card .form-control { min-height: 43px; border-color: #dfe6f0; border-radius: 10px; }
.period-presets { display: flex; flex-wrap: wrap; gap: 7px; }
.period-presets .btn { border: 1px solid #e4eaf3; border-radius: 8px; color: #53657f; }
.summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
.summary-card { padding: 20px 22px; border: 1px solid #e8edf5; background: #fff; border-radius: 16px; display: flex; flex-direction: column; gap: 7px; min-width: 0; }
.summary-income { background: linear-gradient(135deg, #f0f6ff, #fff); border-color: #d8e7ff; }
.summary-label { color: #6d7c92; font-size: 12px; font-weight: 700; }
.summary-card strong { color: #172641; font-size: clamp(20px, 2vw, 28px); letter-spacing: -.035em; overflow-wrap: anywhere; }
.summary-income strong, .income-value { color: #147b50 !important; }
.summary-card small { color: #8b98aa; font-size: 11px; }
.table-heading { padding-bottom: 20px; }
.table-heading h2 { font-weight: 800; }
.table-heading p { font-size: 12px; }
.source-count { padding: 6px 10px; border-radius: 8px; background: #f1f5fb; color: #5d6e86; font-size: 12px; font-weight: 750; white-space: nowrap; }
.income-table { min-width: 700px; }
.income-table thead th { padding: 13px 16px; color: #7a879a; background: #f7f9fc; border-bottom: 1px solid #e9eef5; font-size: 10px; text-transform: uppercase; letter-spacing: .055em; white-space: nowrap; }
.income-table tbody td, .income-table tfoot td { padding: 15px 16px; border-color: #edf1f6; }
.income-table tbody tr:hover { background: #fafcff; }
.source-cell { display: flex; align-items: center; gap: 12px; min-width: 230px; }
.source-avatar { width: 39px; height: 39px; flex: 0 0 39px; display: grid; place-items: center; border-radius: 12px; background: #eaf1ff; color: #2369d5; }
.source-avatar.supplier { color: #087c59; background: #e7f8f0; }
.source-info { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.source-info strong { font-size: 13px; font-weight: 750; overflow-wrap: anywhere; }
.source-info small { font-size: 11px; color: #8794a7; }
.percent-pill { display: inline-flex; min-width: 62px; justify-content: center; padding: 7px 10px; border-radius: 8px; background: #eaf1ff; color: #2168d4; font-size: 12px; font-weight: 800; }
.numeric-cell { white-space: nowrap; font-size: 13px; font-variant-numeric: tabular-nums; }
.income-table tfoot td { background: #f8faff; border-top: 1px solid #e6ecf5; font-size: 13px; }
.loading-state, .empty-state { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 42px 16px; color: #6f7f95; font-size: 13px; }
.empty-state { flex-direction: column; text-align: center; }
.empty-icon { width: 50px; height: 50px; display: grid; place-items: center; border-radius: 15px; background: #f0f5fc; color: #7191c3; font-size: 20px; margin-bottom: 3px; }
.empty-state h3 { font-weight: 800; color: #24334b; }
.calculation-note { color: #8290a4; font-size: 11px; line-height: 1.6; }
@media (max-width: 767.98px) {
    .period-card, .income-card { padding: 16px; border-radius: 15px; }
    .summary-grid { grid-template-columns: 1fr; gap: 10px; }
    .summary-card { padding: 16px; }
    .summary-card strong { font-size: 23px; }
    .page-heading { align-items: flex-start !important; }
    .heading-icon { width: 46px; height: 46px; border-radius: 13px; }
    .heading-actions { width: 100%; }
    .heading-actions .btn { flex: 1 1 auto; padding: 9px 10px; font-size: 12px; }
    .report-modal .modal-body { padding: 16px; }
    .report-modal .modal-header { padding: 15px 17px; }
}
</style>
