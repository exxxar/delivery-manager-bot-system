<template>
    <main class="container-fluid py-4 report-page">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <div class="report-eyebrow">ФИНАНСОВАЯ АНАЛИТИКА</div>
                <h1 class="report-page__title mb-1">Сводный отчёт</h1>
                <p class="report-muted mb-0">
                    Продажи активных админов и поставщиков за выбранный период
                </p>
            </div>

            <button
                type="button"
                class="btn btn-outline-primary"
                :disabled="store.loading || !store.report"
                @click="refreshReport"
            >
                <i class="fa-solid fa-rotate me-2" />
                Обновить
            </button>
        </div>

        <ReportFilters
            :filters="store.filters"
            :loading="store.loading"
            @update:filters="updateFilters"
            @submit="fetchReport"
            @reset="resetFilters"
        />

        <div v-if="store.error" class="alert alert-danger mt-4">
            <strong>Ошибка загрузки отчёта.</strong>
            {{ store.error }}
        </div>

        <div v-if="store.loading && !store.report" class="report-empty-state mt-4">
            <div class="spinner-border text-primary mb-3" />
            <div class="fw-semibold">Формируем отчёт...</div>
            <div class="report-muted small">Получаем продажи и рассчитываем показатели.</div>
        </div>

        <template v-else-if="store.report">
            <div class="report-period-line mt-4">
                <span><i class="fa-regular fa-calendar me-2" />Период:</span>
                <strong>{{ formatDate(store.report.period?.start) }} — {{ formatDate(store.report.period?.end) }}</strong>
                <span class="report-muted ms-auto">
          {{ store.report.counts?.agents ?? store.report.agents?.length ?? 0 }} админов
          <span class="mx-2">·</span>
          {{ store.report.counts?.suppliers ?? store.supplierItems.length }} поставщиков
          <span class="mx-2">·</span>
          {{ store.report.counts?.products ?? store.productItems.length }} товаров
        </span>
            </div>

            <section class="row g-3 mt-1">
                <div
                    v-for="card in summaryCards"
                    :key="card.key"
                    class="col-12 col-sm-6 col-xl-3"
                >
                    <article class="report-metric-card">
                        <div class="report-metric-card__top">
                            <span class="report-metric-card__label">{{ card.label }}</span>
                            <span class="report-metric-card__icon"><i :class="card.icon" /></span>
                        </div>
                        <template v-if="card.key === 'revenue_without_tax_total'">
                            <div class="report-metric-card__value">{{ money(summaryValue('revenue_after_tax')) }}</div>
                            <div class="report-metric-card__breakdown">
                                <div>
                                    <span>До налога</span>
                                    <strong>{{ money(summaryValue('revenue_total')) }}</strong>
                                </div>
                                <div>
                                    <span>Налог ({{ taxPercent }}%)</span>
                                    <strong class="report-metric-card__tax">− {{ money(summaryValue('revenue_tax_amount')) }}</strong>
                                </div>
                            </div>
                            <div class="report-metric-card__hint">Выручка после вычета налога</div>
                        </template>
                        <template v-else>
                            <div class="report-metric-card__value">{{ money(summaryValue(card.key)) }}</div>
                            <div class="report-metric-card__hint">{{ card.hint }}</div>
                        </template>
                    </article>
                </div>
            </section>

            <nav class="report-tabs mt-4" aria-label="Разделы отчёта">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    type="button"
                    class="report-tab"
                    :class="{ 'is-active': store.activeTab === tab.id }"
                    @click="store.activeTab = tab.id"
                >
                    <i :class="tab.icon" />
                    {{ tab.label }}
                    <span v-if="tab.count !== undefined" class="report-tab__count">{{ tab.count }}</span>
                </button>
            </nav>

            <section class="report-content mt-3">
                <div v-if="store.activeTab === 'summary'">
                    <div class="report-section-heading">
                        <div>
                            <h2>Итоги по админам</h2>
                            <p>Сводные показатели и выплаты по каждому админу.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table report-table align-middle">
                            <thead>
                            <tr>
                                <th>Админ</th>
                                <th class="text-end">Оборот</th>
                                <th class="text-end">Выручка после налога (6%)</th>
                                <th class="text-end">Вознаграждение до налога</th>
                                <th class="text-end">Налог с вознаграждения</th>
                                <th class="text-end">К выплате админу</th>
                                <th class="text-end">Зарплата</th>
                                <th class="text-end">Переводы</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="row in summaryRows" :key="row.agent_id">
                                <td class="fw-semibold">{{ row.agent_name || `Админ #${row.agent_id}` }}</td>
                                <td class="text-end">{{ money(row.total_sales) }}</td>
                                <td class="text-end">{{ money(row.revenue_after_tax) }}</td>
                                <td class="text-end">{{ money(row.agent_reward) }}</td>
                                <td class="text-end text-warning">{{ money(row.agent_reward_tax) }}</td>
                                <td class="text-end fw-semibold">{{ money(row.agent_reward_after_tax) }}</td>
                                <td class="text-end">{{ money(row.agent_salary) }}</td>
                                <td class="text-end">{{ money(row.transfer_from_after_tax) }}</td>
                            </tr>
                            <tr v-if="!summaryRows.length">
                                <td colspan="8" class="text-center report-muted py-4">Нет данных за выбранный период.</td>
                            </tr>
                            </tbody>
                            <tfoot v-if="summaryRows.length">
                            <tr>
                                <th>Итого</th>
                                <th class="text-end">{{ money(store.report.summary_sheet?.totals?.total_sales) }}</th>
                                <th class="text-end">{{ money(store.report.summary_sheet?.totals?.revenue_after_tax) }}</th>
                                <th class="text-end">{{ money(store.report.summary_sheet?.totals?.agent_reward) }}</th>
                                <th class="text-end">{{ money(store.report.summary_sheet?.totals?.agent_reward_tax) }}</th>
                                <th class="text-end">{{ money(store.report.summary_sheet?.totals?.agent_reward_after_tax) }}</th>
                                <th class="text-end">{{ money(store.report.summary_sheet?.totals?.agent_salary) }}</th>
                                <th class="text-end">{{ money(store.report.summary_sheet?.totals?.transfer_from_after_tax) }}</th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div v-if="adminColumns.length" class="mt-4">
                        <h3 class="report-subheading">Доходы старших администраторов</h3>
                        <div class="table-responsive">
                            <table class="table report-table align-middle">
                                <thead>
                                <tr>
                                    <th>Админ</th>
                                    <th v-for="admin in adminColumns" :key="admin.admin_id" class="text-end">
                                        {{ admin.admin_name }}
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="row in summaryRows" :key="`admin-${row.agent_id}`">
                                    <td>{{ row.agent_name }}</td>
                                    <td v-for="admin in adminColumns" :key="admin.admin_id" class="text-end">
                                        <div class="report-admin-income-cell">
                                            <strong>Начислено: {{ money(row.admin_income_details?.[admin.admin_id]?.gross || 0) }}</strong>
                                            <small>Налог: {{ money(row.admin_income_details?.[admin.admin_id]?.tax || 0) }}</small>
                                            <strong>К выплате: {{ money(row.admin_income_details?.[admin.admin_id]?.net || 0) }}</strong>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>Итого</th>
                                    <th v-for="admin in adminColumns" :key="admin.admin_id" class="text-end">
                                        <div class="report-admin-income-cell">
                                            <strong>Начислено: {{ money(store.report.summary_sheet?.totals?.admin_income_details?.[admin.admin_id]?.gross || 0) }}</strong>
                                            <small>Налог: {{ money(store.report.summary_sheet?.totals?.admin_income_details?.[admin.admin_id]?.tax || 0) }}</small>
                                            <strong>К выплате: {{ money(store.report.summary_sheet?.totals?.admin_income_details?.[admin.admin_id]?.net || 0) }}</strong>
                                        </div>
                                    </th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div v-else-if="store.activeTab === 'agents'">
                    <div class="report-section-heading">
                        <div>
                            <h2>Отчёты по админам</h2>
                            <p>Продажи и начисления в разрезе каждого админа.</p>
                        </div>
                    </div>

                    <article v-for="agentReport in store.agentReports" :key="agentReport.agent?.id" class="report-agent-card mb-3">
                        <div class="report-agent-card__header">
                            <div>
                                <h3>{{ agentReport.agent?.name || `Админ #${agentReport.agent?.id}` }}</h3>
                                <span class="report-muted small">
                  Процент админа: {{ agentReport.agent?.percent ?? '—' }}%
                </span>
                            </div>
                            <div class="text-end">
                                <div class="report-agent-card__amount">{{ money(agentReport.summary?.total_sales) }}</div>
                                <small class="report-muted">оборот</small>
                            </div>
                        </div>

                        <div class="row g-2 report-agent-stats">
                            <div class="col-6 col-lg-3">
                                <span>Выручка</span><strong>{{ money(agentReport.summary?.revenue_total) }}</strong>
                            </div>
                            <div class="col-6 col-lg-3">
                                <span>Налог с выручки ({{ taxPercent }}%)</span><strong class="text-warning">{{ money(revenueTax(agentReport)) }}</strong>
                            </div>
                            <div class="col-6 col-lg-3">
                                <span>После налога</span><strong>{{ money(revenueAfterTax(agentReport)) }}</strong>
                            </div>
                            <div class="col-6 col-lg-3">
                                <span>Вознаграждение до налога</span><strong>{{ money(agentReport.summary?.total_reward ?? agentReport.agent?.reward) }}</strong>
                            </div>
                            <div class="col-6 col-lg-3">
                                <span>Налог с вознаграждения ({{ taxPercent }}%)</span><strong class="text-warning">{{ money(rewardTax(agentReport)) }}</strong>
                            </div>
                            <div class="col-6 col-lg-3">
                                <span>К выплате админу</span><strong>{{ money(rewardAfterTax(agentReport)) }}</strong>
                            </div>
                            <div class="col-6 col-lg-3">
                                <span>Зарплата</span><strong>{{ money(agentReport.agent?.salary) }}</strong>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-sm report-table align-middle">
                                <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Поставщик / сделка</th>
                                    <th class="text-end">Продажа</th>
                                    <th class="text-end">Процент</th>
                                    <th class="text-end">Выручка</th>
                                    <th class="text-end">После налога</th>
                                    <th>Оплата</th>
                                    <th class="text-end">Вознаграждение</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="sale in agentReport.sales || []" :key="sale.id">
                                    <td>{{ formatDate(sale.date) }}</td>
                                    <td>{{ sale.supplier_name }} <span class="report-muted">#{{ sale.id }}</span></td>
                                    <td class="text-end">{{ money(sale.sale_amount) }}</td>
                                    <td class="text-end">{{ sale.percent }}%</td>
                                    <td class="text-end">{{ money(sale.revenue_total) }}</td>
                                    <td class="text-end">{{ money(sale.revenue_after_tax) }}</td>
                                    <td>{{ paymentType(sale.payment_type) }}</td>
                                    <td class="text-end">{{ money(sale.reward) }}</td>
                                </tr>
                                <tr v-if="!agentReport.sales?.length">
                                    <td colspan="8" class="text-center report-muted py-3">Продаж нет.</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="agentReport.admins?.length" class="mt-3">
                            <h4 class="report-subheading">Начисления старшим администраторам</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <div v-for="admin in agentReport.admins" :key="admin.admin_id" class="report-admin-chip">
                                    <span>{{ admin.admin_name }} · {{ admin.percent }}%</span>
                                    <strong>Начислено: {{ money(admin.income_total) }}</strong>
                                    <small>Налог: {{ money(Number(admin.income_total || 0) - Number(admin.income_after_tax || 0)) }}</small>
                                    <small>К выплате: {{ money(admin.income_after_tax) }}</small>
                                </div>
                            </div>
                        </div>
                    </article>
                    <div v-if="!store.agentReports.length" class="report-empty-inline">Нет данных по админам.</div>
                </div>

                <div v-else-if="store.activeTab === 'admins'">
                    <div class="report-section-heading">
                        <div>
                            <h2>Старшие администраторы</h2>
                            <p>Суммарные начисления по всем активным админам.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table report-table align-middle">
                            <thead>
                            <tr>
                                <th>Старший администратор</th>
                                <th class="text-end">Начислено</th>
                                <th class="text-end">Налог</th>
                                <th class="text-end">К выплате</th>
                                <th class="text-end">С наградами: начислено</th>
                                <th class="text-end">Налог с наградами</th>
                                <th class="text-end">К выплате с наградами</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="admin in adminColumns" :key="admin.admin_id">
                                <td class="fw-semibold">{{ admin.admin_name }}</td>
                                <td class="text-end">{{ money(admin.income_total) }}</td>
                                <td class="text-end text-warning">{{ money(admin.income_tax) }}</td>
                                <td class="text-end fw-semibold">{{ money(admin.income_after_tax) }}</td>
                                <td class="text-end">{{ money(admin.income_with_award_total) }}</td>
                                <td class="text-end text-warning">{{ money(admin.income_with_award_tax) }}</td>
                                <td class="text-end fw-semibold">{{ money(admin.income_with_award_after_tax) }}</td>
                            </tr>
                            <tr v-if="!adminColumns.length">
                                <td colspan="7" class="text-center report-muted py-4">Нет начислений старшим администраторам.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else-if="store.activeTab === 'suppliers'">
                    <div class="report-section-heading">
                        <div>
                            <h2>Поставщики</h2>
                            <p>Поставщики с завершёнными продажами за выбранный период.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table report-table align-middle">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Поставщик</th>
                                <th>Дата рождения</th>
                                <th>Телефон</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="supplier in store.supplierItems" :key="supplier.id">
                                <td>{{ supplier.id }}</td>
                                <td class="fw-semibold">{{ supplier.name }}</td>
                                <td>{{ formatDate(supplier.birthday) }}</td>
                                <td>{{ supplier.phone || '—' }}</td>
                            </tr>
                            <tr v-if="!store.supplierItems.length">
                                <td colspan="4" class="text-center report-muted py-4">Поставщики не найдены.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div v-else-if="store.activeTab === 'products'">
                    <div class="report-section-heading">
                        <div>
                            <h2>Товары</h2>
                            <p>Товары активных поставщиков за выбранный период.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table report-table align-middle">
                            <thead>
                            <tr>
                                <th>Товар</th>
                                <th>Категория</th>
                                <th>Поставщик</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="product in store.productItems" :key="product.id">
                                <td class="fw-semibold">{{ product.name }}</td>
                                <td>{{ product.category?.name || '—' }}</td>
                                <td>{{ product.supplier?.name || '—' }}</td>
                            </tr>
                            <tr v-if="!store.productItems.length">
                                <td colspan="3" class="text-center report-muted py-4">Товары не найдены.</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </template>

        <div v-else-if="!store.loading" class="report-empty-state mt-4">
            <i class="fa-solid fa-chart-column report-empty-state__icon" />
            <div class="fw-semibold">Отчёт ещё не сформирован</div>
            <div class="report-muted small">Выберите период и нажмите «Сформировать отчёт».</div>
        </div>
    </main>
</template>

<script>
import { useReportsStore } from '@/stores/reports/reports'
import ReportFilters from '@/components/Reports/ReportFilters.vue'

export default {
    name: 'SummaryReport',

    components: {
        ReportFilters,
    },

    data() {
        return {
            store: useReportsStore(),
        }
    },

    computed: {
        summaryRows() {
            return this.store.report?.summary_sheet?.rows || []
        },

        adminColumns() {
            return this.store.report?.summary_sheet?.admins || []
        },

        taxPercent() {
            return Number(this.store.report?.summary?.tax_percent ?? 6)
        },

        summaryCards() {
            return [
                { key: 'total_sales', label: 'Оборот', hint: 'Общая сумма продаж', icon: 'fa-solid fa-wallet' },
                { key: 'revenue_without_tax_total', label: 'Выручка после налога', hint: 'Доход по поставщикам после налога', icon: 'fa-solid fa-chart-line' },
                { key: 'total_reward', label: 'Вознаграждение админов', hint: 'Начислено админам', icon: 'fa-solid fa-percent' },
                { key: 'agent_salary', label: 'Зарплата админов', hint: 'По расчёту отчёта', icon: 'fa-solid fa-money-bill-wave' },
            ]
        },

        tabs() {
            return [
                { id: 'summary', label: 'Итого', icon: 'fa-solid fa-chart-pie' },
                { id: 'agents', label: 'Админы', icon: 'fa-solid fa-users', count: this.store.agentReports.length },
                { id: 'admins', label: 'Старшие администраторы', icon: 'fa-solid fa-user-tie', count: this.adminColumns.length },
                { id: 'suppliers', label: 'Поставщики', icon: 'fa-solid fa-store', count: this.store.supplierItems.length },
                { id: 'products', label: 'Товары', icon: 'fa-solid fa-box', count: this.store.productItems.length },
            ]
        },
    },

    async mounted() {
        await this.fetchReport()
    },

    methods: {
        updateFilters(filters) {
            this.store.filters = { ...filters }
        },

        async fetchReport() {
            try {
                await this.store.fetchReport()
            } catch (error) {
                // Ошибка уже сохранена в Pinia и отображается в шаблоне.
            }
        },

        async refreshReport() {
            await this.fetchReport()
        },

        async resetFilters() {
            this.store.resetFilters()
            await this.fetchReport()
        },

        summaryValue(key) {
            return this.store.report?.summary?.[key] ?? 0
        },

        revenueTax(agentReport) {
            const gross = Number(agentReport?.summary?.revenue_total ?? 0)
            return Math.round((gross * this.taxPercent / 100) * 100) / 100
        },

        revenueAfterTax(agentReport) {
            const gross = Number(agentReport?.summary?.revenue_total ?? 0)
            return gross - this.revenueTax(agentReport)
        },

        rewardTax(agentReport) {
            const gross = Number(agentReport?.summary?.total_reward ?? agentReport?.agent?.reward ?? 0)
            return Math.round((gross * this.taxPercent / 100) * 100) / 100
        },

        rewardAfterTax(agentReport) {
            const gross = Number(agentReport?.summary?.total_reward ?? agentReport?.agent?.reward ?? 0)
            return gross - this.rewardTax(agentReport)
        },

        money(value) {
            return new Intl.NumberFormat('ru-RU', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2,
            }).format(Number(value) || 0) + ' ₽'
        },

        formatDate(value) {
            if (!value) return '—'
            // Дата вида YYYY-MM-DD разбирается локально, без сдвига часового пояса.
            const dateOnly = String(value).slice(0, 10)
            const parts = dateOnly.split('-').map(Number)
            if (parts.length === 3 && parts.every(Number.isFinite)) {
                return new Intl.DateTimeFormat('ru-RU').format(new Date(parts[0], parts[1] - 1, parts[2]))
            }
            const date = new Date(value)
            if (Number.isNaN(date.getTime())) return value
            return new Intl.DateTimeFormat('ru-RU').format(date)
        },

        paymentType(value) {
            if (Number(value) === 0) return 'Наличный'
            if (Number(value) === 1) return 'Безналичный'
            return '—'
        },
    },
}
</script>

<style scoped>
.report-page {
    color: #182338;
    min-height: 100%;
}
.report-page__title {
    font-size: clamp(1.6rem, 2.5vw, 2.15rem);
    font-weight: 750;
    letter-spacing: -0.04em;
}
.report-eyebrow {
    color: #64748b;
    font-size: .7rem;
    font-weight: 800;
    letter-spacing: .14em;
    margin-bottom: .35rem;
}
.report-muted { color: #77849a; }
.report-panel {
    background: #fff;
    border: 1px solid #e5eaf1;
    border-radius: 16px;
    padding: 1.25rem;
    box-shadow: 0 8px 28px rgba(22, 34, 54, .045);
}
.report-panel__heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.15rem;
}
.report-panel__title { font-size: 1rem; font-weight: 750; margin: 0 0 .25rem; }
.report-loading-label { color: #64748b; font-size: .8rem; }
.report-filters .form-label { color: #46546a; font-size: .8rem; font-weight: 700; }
.report-filters .form-control,
.report-filters .form-select { border-color: #dce3ec; border-radius: 9px; min-height: 42px; }
.report-multi-select { min-height: 94px !important; }
.report-filters small { display: block; margin-top: .35rem; font-size: .72rem; }
.report-period-line {
    display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
    color: #64748b; font-size: .88rem;
}
.report-period-line strong { color: #24324a; }
.report-metric-card {
    height: 100%; background: #fff; border: 1px solid #e6ebf2;
    border-radius: 15px; padding: 1.1rem 1.15rem;
    box-shadow: 0 7px 22px rgba(22, 34, 54, .035);
}
.report-metric-card__top { display: flex; align-items: center; justify-content: space-between; gap: .5rem; }
.report-metric-card__label { color: #65738a; font-size: .8rem; font-weight: 700; }
.report-metric-card__icon {
    display: grid; place-items: center; width: 34px; height: 34px;
    color: #3159a6; background: #edf3ff; border-radius: 10px;
}
.report-metric-card__value { margin-top: .8rem; font-size: clamp(1.25rem, 2vw, 1.75rem); font-weight: 800; letter-spacing: -.04em; overflow-wrap: anywhere; }
.report-metric-card__hint { color: #91a0b4; font-size: .72rem; margin-top: .35rem; }
.report-metric-card__breakdown { display: grid; gap: .28rem; margin-top: .65rem; padding-top: .55rem; border-top: 1px solid #edf1f7; }
.report-metric-card__breakdown > div { display: flex; align-items: baseline; justify-content: space-between; gap: .6rem; }
.report-metric-card__breakdown span { color: #8290a5; font-size: .72rem; }
.report-metric-card__breakdown strong { color: #344258; font-size: .78rem; font-weight: 750; text-align: right; }
.report-metric-card__breakdown .report-metric-card__tax { color: #b7791f; }
.report-tabs {
    display: flex; gap: .35rem; overflow-x: auto; padding: .35rem;
    background: #eaf0f7; border-radius: 12px;
}
.report-tab {
    display: inline-flex; align-items: center; gap: .55rem; white-space: nowrap;
    border: 0; background: transparent; color: #66758b;
    border-radius: 9px; padding: .7rem .95rem; font-size: .84rem; font-weight: 700;
}
.report-tab.is-active { color: #244b91; background: #fff; box-shadow: 0 2px 8px rgba(20, 35, 60, .08); }
.report-tab__count { background: #edf1f6; border-radius: 20px; padding: .1rem .45rem; font-size: .7rem; }
.report-content { background: #fff; border: 1px solid #e6ebf2; border-radius: 15px; padding: 1.25rem; }
.report-section-heading { display: flex; justify-content: space-between; margin-bottom: 1rem; }
.report-section-heading h2 { font-size: 1.1rem; font-weight: 800; margin: 0 0 .25rem; }
.report-section-heading p { color: #8793a6; font-size: .8rem; margin: 0; }
.report-subheading { font-size: .92rem; font-weight: 800; margin-bottom: .8rem; }
.report-table { --bs-table-bg: transparent; margin-bottom: 0; font-size: .82rem; }
.report-table thead th {
    color: #77849a; background: #f7f9fc; border-bottom: 1px solid #e7edf4;
    font-size: .72rem; font-weight: 800; white-space: nowrap; padding: .8rem .75rem;
}
.report-table tbody td, .report-table tbody th { border-color: #edf1f5; padding: .75rem; }
.report-table tfoot th { background: #f7f9fc; border-top: 1px solid #dfe6ef; padding: .85rem .75rem; }
.report-agent-card { border: 1px solid #e5eaf1; border-radius: 13px; padding: 1rem; }
.report-agent-card__header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
.report-agent-card__header h3 { font-size: 1rem; font-weight: 800; margin: 0 0 .25rem; }
.report-agent-card__amount { font-size: 1.2rem; font-weight: 800; }
.report-agent-stats { margin-top: .8rem; padding: .8rem; background: #f7f9fc; border-radius: 10px; }
.report-agent-stats span { display: block; color: #8793a6; font-size: .7rem; }
.report-agent-stats strong { display: block; margin-top: .2rem; font-size: .9rem; }
.report-admin-chip {
    display: flex; flex-direction: column; gap: .25rem; padding: .65rem .8rem;
    border: 1px solid #e6ebf2; border-radius: 9px; min-width: 160px;
    color: #66758b; font-size: .75rem;
}
.report-admin-chip strong { color: #263750; font-size: .9rem; }
.report-admin-chip small { color: #8290a5; font-size: .72rem; }
.report-admin-income-cell { display: grid; gap: .18rem; min-width: 155px; }
.report-admin-income-cell strong { color: #344258; font-size: .75rem; white-space: nowrap; }
.report-admin-income-cell small { color: #b7791f; font-size: .7rem; white-space: nowrap; }
.report-empty-state {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 220px; background: #fff; border: 1px dashed #d7e0eb;
    border-radius: 15px; color: #34445c;
}
.report-empty-state__icon { color: #9aabc2; font-size: 2rem; margin-bottom: 1rem; }
.report-empty-inline { padding: 2rem; text-align: center; color: #8793a6; }
@media (max-width: 767.98px) {
    .report-panel, .report-content { padding: .9rem; }
    .report-tab { padding: .65rem .75rem; }
    .report-agent-card { padding: .8rem; }
}
</style>
