<template>
    <main class="my-income-page container-fluid py-3 py-lg-4">
        <!-- Заголовок -->
        <header class="page-heading d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="heading-icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div>
                    <h1 class="h3 mb-1">Мой доход</h1>
                    <p class="text-muted mb-0">Ваши начисления от администраторов и поставщиков</p>
                </div>
            </div>

            <div class="heading-actions d-flex flex-wrap align-items-center gap-2">
                <button
                    class="btn btn-primary report-btn"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#incomeReportModal"
                >
                    <i class="fa-solid fa-file-circle-plus me-2"></i>
                    Сформировать отчёт
                </button>

                <button
                    class="btn btn-outline-primary refresh-btn"
                    type="button"
                    :disabled="loading"
                    @click="loadIncome"
                >
                    <i class="fa-solid fa-rotate-right me-2" :class="{ 'fa-spin': loading }"></i>
                    Обновить
                </button>
            </div>
        </header>

        <!-- Период -->
        <section class="period-card mb-4">
            <div class="period-title">
                <i class="fa-regular fa-calendar me-2"></i>
                Период расчёта
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-lg-4">
                    <label class="form-label" for="income-month">Месяц</label>
                    <input
                        id="income-month"
                        v-model="month"
                        type="month"
                        class="form-control"
                        @change="loadIncome"
                    >
                </div>

                <div class="col-12 col-sm-6 col-lg-4">
                    <button
                        class="btn btn-primary w-100"
                        type="button"
                        :disabled="loading || !month"
                        @click="loadIncome"
                    >
                        <i class="fa-solid fa-filter me-2"></i>
                        Применить
                    </button>
                </div>

                <div class="col-12 col-lg-4 period-presets">
                    <button type="button" class="btn btn-sm btn-light" @click="setThisMonth">
                        Текущий месяц
                    </button>
                    <button type="button" class="btn btn-sm btn-light" @click="setPreviousMonth">
                        Прошлый месяц
                    </button>
                </div>
            </div>
        </section>

        <!-- Ошибка -->
        <div v-if="error" class="alert alert-danger d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ error }}</span>
            <button class="btn btn-sm btn-outline-danger ms-auto" type="button" @click="loadIncome">
                Повторить
            </button>
        </div>

        <!-- Загрузка -->
        <div v-if="loading && !loaded" class="loading-state">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span>Загружаем данные о доходе...</span>
        </div>

        <template v-else-if="loaded">
            <!-- Профиль -->
            <section v-if="admin" class="agent-info-card mb-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="source-avatar">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>

                    <div class="flex-grow-1 min-width-0">
                        <strong>{{ admin.name }}</strong>
                        <div class="text-muted small">{{ admin.role_label }}</div>
                    </div>

                    <div class="text-end">
                        <small class="text-muted d-block">Личный процент</small>
                        <strong class="percent-pill">
                            {{ formatPercent(admin.personal_percent) }}%
                        </strong>
                    </div>
                </div>
            </section>

            <!-- Сводка -->
            <section class="summary-grid mb-4" aria-label="Итоги периода">
                <article class="summary-card summary-income">
                    <div class="summary-icon"><i class="fa-solid fa-wallet"></i></div>
                    <span class="summary-label">Итого к получению</span>
                    <strong>{{ money(summary.net_total) }}</strong>
                    <small>С учётом всех начислений и удержаний</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon"><i class="fa-solid fa-user-tie"></i></div>
                    <span class="summary-label">Доход с личных продаж</span>
                    <strong>{{ money(summary.own_commission) }}</strong>
                    <small>{{ formatPercent(admin?.personal_percent) }}% от собственного оборота</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon"><i class="fa-solid fa-arrow-down-wide-short"></i></div>
                    <span class="summary-label">Входящие начисления</span>
                    <strong>{{ money(summary.incoming_commission) }}</strong>
                    <small>От других администраторов и поставщиков</small>
                </article>

                <article class="summary-card">
                    <div class="summary-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <span class="summary-label">Собственный оборот</span>
                    <strong>{{ money(summary.own_turnover) }}</strong>
                    <small>{{ summary.sales_count }} завершённых сделок</small>
                </article>
            </section>

            <!-- Входящие начисления -->
            <section class="income-card mb-4">
                <div class="table-heading d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h2 class="h5 mb-1">От кого приходит доход</h2>
                        <p class="text-muted mb-0">
                            Начисления за завершённые сделки выбранного месяца
                        </p>
                    </div>
                    <span class="source-count">
                        {{ incoming.length }} {{ pluralSources(incoming.length) }}
                    </span>
                </div>

                <div v-if="incoming.length" class="table-responsive">
                    <table class="table align-middle income-table mb-0">
                        <thead>
                        <tr>
                            <th>Источник дохода</th>
                            <th class="text-center">Мой процент</th>
                            <th class="text-end">Оборот источника</th>
                            <th class="text-end">Начислено мне</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="source in incoming" :key="source.key">
                            <td>
                                <div class="source-cell">
                                        <span class="source-avatar" :class="source.owner_type">
                                            <i
                                                :class="source.owner_type === 'supplier'
                                                    ? 'fa-solid fa-boxes-stacked'
                                                    : 'fa-solid fa-user-tie'"
                                            ></i>
                                        </span>
                                    <span class="source-info">
                                            <strong>{{ source.owner_name }}</strong>
                                            <small>{{ source.owner_type_label }}</small>
                                        </span>
                                </div>
                            </td>
                            <td class="text-center">
                                    <span class="percent-pill">
                                        {{ formatPercent(source.percentage) }}%
                                    </span>
                            </td>
                            <td class="text-end numeric-cell">{{ money(source.turnover) }}</td>
                            <td class="text-end numeric-cell income-value">{{ money(source.amount) }}</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2"><strong>Итого входящих начислений</strong></td>
                            <td class="text-end numeric-cell">
                                <strong>{{ money(incomingTurnover) }}</strong>
                            </td>
                            <td class="text-end numeric-cell income-value">
                                <strong>{{ money(summary.incoming_commission) }}</strong>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <div v-else class="empty-state">
                    <div class="empty-icon"><i class="fa-solid fa-percent"></i></div>
                    <h3 class="h6">Начислений за период нет</h3>
                    <p class="text-muted mb-0">
                        За выбранный месяц пока нет входящих начислений от администраторов или поставщиков.
                    </p>
                </div>
            </section>

            <!-- Подробности сделок -->
            <section class="income-card">
                <div class="table-heading d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h2 class="h5 mb-1">Подробности сделок</h2>
                        <p class="text-muted mb-0">
                            Собственные продажи и сделки, с которых начисляется входящий процент
                        </p>
                    </div>
                    <span class="source-count">
                        {{ sales.length }} {{ pluralSales(sales.length) }}
                    </span>
                </div>

                <div v-if="sales.length" class="table-responsive">
                    <table class="table align-middle income-table mb-0">
                        <thead>
                        <tr>
                            <th>Дата доставки</th>
                            <th>Агент</th>
                            <th>Поставщик</th>
                            <th class="text-end">Сумма сделки</th>
                            <th class="text-end">Мой доход</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="sale in sales" :key="sale.id">
                            <td class="numeric-cell">{{ formatDate(sale.actual_delivery_date) }}</td>
                            <td>{{ sale.agent_name || '—' }}</td>
                            <td>{{ sale.supplier_name || '—' }}</td>
                            <td class="text-end numeric-cell">{{ money(sale.total_price) }}</td>
                            <td class="text-end numeric-cell income-value">
                                {{ money(sale.commission_total) }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="empty-state">
                    <div class="empty-icon"><i class="fa-solid fa-receipt"></i></div>
                    <h3 class="h6">Сделок за период нет</h3>
                    <p class="text-muted mb-0">
                        За выбранный месяц нет завершённых сделок, участвующих в расчёте.
                    </p>
                </div>
            </section>

            <p class="calculation-note mt-3 mb-0">
                <i class="fa-solid fa-circle-info me-1"></i>
                В расчёт входят завершённые сделки со статусом <code>completed</code>,
                у которых фактическая дата доставки попадает в выбранный месяц.
                Используются текущие настройки процентов.
            </p>
        </template>

        <!-- Генератор отчётов -->
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
                                <h2 id="incomeReportModalTitle" class="modal-title h5 mb-1">
                                    Генератор отчётов
                                </h2>
                                <p class="text-muted small mb-0">
                                    Формирование персональной отчётности
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Закрыть"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <ReportIndividualGenerator
                            v-if="agentId != null"
                            :agent-id="agentId"
                            @generate-report="handleReport"
                        />
                        <div v-else class="alert alert-warning mb-0">
                            Профиль агента не найден. Проверьте привязку агента к аккаунту.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import axios from 'axios'
import ReportIndividualGenerator from '@/Components/Admins/ReportIndividualGenerator.vue'
import { useUsersStore } from '@/stores/users'

const currentMonth = () => {
    const now = new Date()
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
}

export default {
    name: 'MyIncome',

    components: {
        ReportIndividualGenerator,
    },

    data() {
        return {
            userStore: useUsersStore(),
            month: currentMonth(),

            admin: null,

            summary: {
                own_turnover: 0,
                own_commission: 0,
                incoming_commission: 0,
                net_total: 0,
                sales_count: 0,
            },

            incoming: [],
            sales: [],

            loading: false,
            loaded: false,
            error: '',
            incomeRequestId: 0,
        }
    },

    computed: {
        agentId() {
            return this.userStore.self?.agent?.id ?? null
        },

        incomingTurnover() {
            return this.incoming.reduce(
                (total, item) => total + Number(item.turnover || 0),
                0
            )
        },
    },

    mounted() {
        this.loadIncome()
    },

    methods: {
        async loadIncome() {
            if (!this.month) return

            const requestId = ++this.incomeRequestId
            this.loading = true
            this.error = ''

            try {
                const { data } = await axios.get('/api/my-income', {
                    params: { month: this.month },
                })

                if (requestId !== this.incomeRequestId) return

                this.admin = data?.admin ?? null

                this.summary = {
                    own_turnover: Number(data?.summary?.own_turnover ?? 0),
                    own_commission: Number(data?.summary?.own_commission ?? 0),
                    incoming_commission: Number(data?.summary?.incoming_commission ?? 0),
                    net_total: Number(data?.summary?.net_total ?? 0),
                    sales_count: Number(data?.summary?.sales_count ?? 0),
                }

                this.incoming = Array.isArray(data?.distribution?.incoming)
                    ? data.distribution.incoming
                    : []

                this.sales = Array.isArray(data?.sales)
                    ? data.sales
                    : []

                this.loaded = true
            } catch (e) {
                if (requestId !== this.incomeRequestId) return

                this.error = e?.response?.data?.message
                    || 'Не удалось загрузить данные о доходе.'
            } finally {
                if (requestId === this.incomeRequestId) {
                    this.loading = false
                }
            }
        },

        setThisMonth() {
            this.month = currentMonth()
            this.loadIncome()
        },

        setPreviousMonth() {
            const now = new Date()
            const previous = new Date(now.getFullYear(), now.getMonth() - 1, 1)

            this.month = `${previous.getFullYear()}-${String(previous.getMonth() + 1).padStart(2, '0')}`
            this.loadIncome()
        },

        handleReport() {
            const element = document.getElementById('incomeReportModal')
            const modal = element && window.bootstrap?.Modal
                ? window.bootstrap.Modal.getInstance(element)
                : null

            modal?.hide()
        },

        money(value) {
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                maximumFractionDigits: 2,
            }).format(Number(value) || 0)
        },

        formatPercent(value) {
            const number = Number(value) || 0

            return Number.isInteger(number)
                ? String(number)
                : number.toLocaleString('ru-RU', { maximumFractionDigits: 2 })
        },

        formatDate(value) {
            if (!value) return '—'

            const date = new Date(`${value}`.slice(0, 10) + 'T00:00:00')

            return Number.isNaN(date.getTime())
                ? value
                : new Intl.DateTimeFormat('ru-RU').format(date)
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
.my-income-page {
    color: #18243a;
    max-width: 1600px;
    margin: 0 auto;
}

.heading-icon {
    width: 54px;
    height: 54px;
    flex: 0 0 54px;
    border-radius: 16px;
    display: grid;
    place-items: center;
    color: #fff;
    background: linear-gradient(135deg, #1769e0, #4289f5);
    box-shadow: 0 8px 20px #1769e02b;
    font-size: 21px;
}

.page-heading h1 {
    font-weight: 800;
    letter-spacing: -.035em;
}

.page-heading p {
    font-size: 14px;
}

.refresh-btn,
.report-btn {
    border-radius: 11px;
    font-weight: 650;
    padding: 10px 14px;
}

.period-card,
.income-card,
.agent-info-card {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 5px 22px #192b4808;
}

.agent-info-card {
    padding: 18px 22px;
}

.min-width-0 {
    min-width: 0;
}

.period-title {
    font-size: 14px;
    font-weight: 750;
    margin-bottom: 17px;
}

.period-card .form-label {
    font-size: 12px;
    font-weight: 700;
    color: #61718a;
}

.period-card .form-control {
    min-height: 43px;
    border-color: #dfe6f0;
    border-radius: 10px;
}

.period-presets {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.period-presets .btn {
    border: 1px solid #e4eaf3;
    border-radius: 8px;
    color: #53657f;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.summary-card {
    padding: 20px 22px;
    border: 1px solid #e8edf5;
    background: #fff;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 0;
}

.summary-income {
    background: linear-gradient(135deg, #f0f6ff, #fff);
    border-color: #d8e7ff;
}

.summary-icon {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border-radius: 10px;
    background: #edf3ff;
    color: #2868d6;
    margin-bottom: 5px;
}

.summary-label {
    color: #6d7c92;
    font-size: 12px;
    font-weight: 700;
}

.summary-card strong {
    color: #172641;
    font-size: clamp(19px, 1.8vw, 25px);
    letter-spacing: -.035em;
    overflow-wrap: anywhere;
}

.summary-income strong,
.income-value {
    color: #147b50 !important;
}

.summary-card small {
    color: #8b98aa;
    font-size: 11px;
}

.table-heading {
    padding-bottom: 20px;
}

.table-heading h2 {
    font-weight: 800;
}

.table-heading p {
    font-size: 12px;
}

.source-count {
    padding: 6px 10px;
    border-radius: 8px;
    background: #f1f5fb;
    color: #5d6e86;
    font-size: 12px;
    font-weight: 750;
    white-space: nowrap;
}

.income-table {
    min-width: 700px;
}

.income-table thead th {
    padding: 13px 16px;
    color: #7a879a;
    background: #f7f9fc;
    border-bottom: 1px solid #e9eef5;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .055em;
    white-space: nowrap;
}

.income-table tbody td,
.income-table tfoot td {
    padding: 15px 16px;
    border-color: #edf1f6;
}

.income-table tbody tr:hover {
    background: #fafcff;
}

.source-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 230px;
}

.source-avatar {
    width: 39px;
    height: 39px;
    flex: 0 0 39px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: #eaf1ff;
    color: #2369d5;
}

.source-avatar.supplier {
    color: #087c59;
    background: #e7f8f0;
}

.source-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.source-info strong {
    font-size: 13px;
    font-weight: 750;
    overflow-wrap: anywhere;
}

.source-info small {
    font-size: 11px;
    color: #8794a7;
}

.percent-pill {
    display: inline-flex;
    min-width: 62px;
    justify-content: center;
    padding: 7px 10px;
    border-radius: 8px;
    background: #eaf1ff;
    color: #2168d4;
    font-size: 12px;
    font-weight: 800;
}

.numeric-cell {
    white-space: nowrap;
    font-size: 13px;
    font-variant-numeric: tabular-nums;
}

.income-table tfoot td {
    background: #f8faff;
    border-top: 1px solid #e6ecf5;
    font-size: 13px;
}

.loading-state,
.empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 42px 16px;
    color: #6f7f95;
    font-size: 13px;
}

.empty-state {
    flex-direction: column;
    text-align: center;
}

.empty-icon {
    width: 50px;
    height: 50px;
    display: grid;
    place-items: center;
    border-radius: 15px;
    background: #f0f5fc;
    color: #7191c3;
    font-size: 20px;
    margin-bottom: 3px;
}

.empty-state h3 {
    font-weight: 800;
    color: #24334b;
}

.calculation-note {
    color: #8290a4;
    font-size: 11px;
    line-height: 1.6;
}

.report-modal {
    overflow: hidden;
    border: 1px solid #e8edf5;
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(23, 35, 59, .16);
}

.report-modal .modal-header {
    padding: 18px 22px;
    background: #f8faff;
    border-bottom: 1px solid #e8edf5;
}

.report-modal .modal-body {
    padding: 22px;
    background: #fff;
}

.report-modal-icon {
    width: 44px;
    height: 44px;
    display: grid;
    place-items: center;
    flex: 0 0 44px;
    border-radius: 13px;
    color: #2457d6;
    background: #eaf1ff;
    font-size: 18px;
}

@media (max-width: 1199.98px) {
    .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 767.98px) {
    .period-card,
    .income-card {
        padding: 16px;
        border-radius: 15px;
    }

    .agent-info-card {
        padding: 15px;
    }

    .summary-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .summary-card {
        padding: 16px;
    }

    .summary-card strong {
        font-size: 23px;
    }

    .page-heading {
        align-items: flex-start !important;
    }

    .heading-icon {
        width: 46px;
        height: 46px;
        flex-basis: 46px;
        border-radius: 13px;
    }

    .heading-actions {
        width: 100%;
    }

    .heading-actions .btn {
        flex: 1 1 auto;
        padding: 9px 10px;
        font-size: 12px;
    }

    .report-modal .modal-body {
        padding: 16px;
    }

    .report-modal .modal-header {
        padding: 15px 17px;
    }
}
</style>
