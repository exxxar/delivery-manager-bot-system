<template>
    <main class="salary-page">
        <header class="page-header">
            <div class="header-copy">
                <div class="header-icon"><i class="fa-solid fa-wallet"></i></div>
                <div>
                    <p class="eyebrow">ФИНАНСЫ / АДМИНИСТРАТОРЫ</p>
                    <h1>Зарплата администратора</h1>
                    <p class="subtitle">Начисления по завершённым сделкам за календарный месяц</p>
                </div>
            </div>
            <div class="month-picker">
                <label for="salary-month">Расчётный месяц</label>
                <input id="salary-month" v-model="month" type="month" class="form-control" @change="loadReport">
            </div>
        </header>

        <div v-if="loading" class="state-card">
            <span class="spinner-border spinner-border-sm" role="status"></span>
            <span>Рассчитываем зарплату…</span>
        </div>
        <div v-else-if="error" class="state-card state-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ error }}</span>
            <button class="btn-retry" type="button" @click="loadReport">Повторить</button>
        </div>

        <template v-else-if="report">
            <section class="admin-card">
                <div class="admin-avatar"><i class="fa-solid fa-user-tie"></i></div>
                <div class="admin-main">
                    <div class="admin-name-row">
                        <h2>{{ report.admin.name || 'Администратор' }}</h2>
                        <span class="role-badge">{{ report.admin.role_label || 'Администратор' }}</span>
                    </div>
                    <div class="admin-meta">
                        <span v-if="report.admin.email"><i class="fa-regular fa-envelope"></i>{{ report.admin.email }}</span>
                        <span v-if="report.admin.phone"><i class="fa-solid fa-phone"></i>{{ report.admin.phone }}</span>
                        <span v-if="report.admin.region"><i class="fa-solid fa-location-dot"></i>{{ report.admin.region }}</span>
                    </div>
                    <p class="period-line">
                        <i class="fa-regular fa-calendar"></i>
                        Период: <strong>{{ formatDate(report.period.from) }} — {{ formatDate(report.period.to) }}</strong>
                        <span class="period-note">· календарный месяц</span>
                    </p>
                </div>
            </section>

            <section class="summary-grid">
                <article class="metric-card metric-primary">
                    <div class="metric-top"><span>Итого к выплате</span><span class="metric-icon"><i class="fa-solid fa-wallet"></i></span></div>
                    <strong class="metric-value">{{ money(report.summary.net_total) }}</strong>
                    <small>Начисления + доход от других − доли другим</small>
                </article>
                <article class="metric-card">
                    <div class="metric-top"><span>Собственные начисления</span><span class="metric-icon blue"><i class="fa-solid fa-basket-shopping"></i></span></div>
                    <strong class="metric-value">{{ money(report.summary.own_commission) }}</strong>
                    <small>Оборот собственных продаж: {{ money(report.summary.own_turnover) }}</small>
                </article>
                <article class="metric-card">
                    <div class="metric-top"><span>Заработано на других</span><span class="metric-icon green"><i class="fa-solid fa-arrow-trend-up"></i></span></div>
                    <strong class="metric-value">{{ money(report.summary.incoming_commission) }}</strong>
                    <small>По назначенным активным процентам</small>
                </article>
                <article class="metric-card">
                    <div class="metric-top"><span>Доли другим администраторам</span><span class="metric-icon red"><i class="fa-solid fa-share-nodes"></i></span></div>
                    <strong class="metric-value">{{ money(report.summary.outgoing_commission) }}</strong>
                    <small>Распределено с собственных продаж</small>
                </article>
            </section>

            <section class="distribution-grid">
                <article class="content-card">
                    <div class="section-heading">
                        <div class="section-icon outgoing"><i class="fa-solid fa-arrow-up-right-from-square"></i></div>
                        <div><h3>Кто зарабатывает на нём</h3><p>Доли других администраторов с его продаж</p></div>
                    </div>
                    <div v-if="report.distribution.outgoing.length" class="distribution-list">
                        <div v-for="item in report.distribution.outgoing" :key="item.id" class="distribution-row">
                            <div class="person-avatar"><i class="fa-solid fa-user"></i></div>
                            <div class="person-copy"><strong>{{ item.user_name }}</strong><small>{{ formatPercent(item.percentage) }}% от оборота · {{ item.is_active ? 'Активен' : 'Неактивен' }}</small></div>
                            <strong class="distribution-amount">{{ money(item.amount) }}</strong>
                        </div>
                    </div>
                    <div v-else class="empty-inline">Нет назначенных долей на этот месяц.</div>
                </article>

                <article class="content-card">
                    <div class="section-heading">
                        <div class="section-icon incoming"><i class="fa-solid fa-arrow-down-left"></i></div>
                        <div><h3>На ком он зарабатывает</h3><p>Доход с продаж других администраторов и поставщиков</p></div>
                    </div>
                    <div v-if="report.distribution.incoming.length" class="distribution-list">
                        <div v-for="item in report.distribution.incoming" :key="item.key" class="distribution-row">
                            <div class="person-avatar green"><i :class="item.owner_type === 'supplier' ? 'fa-solid fa-boxes-stacked' : 'fa-solid fa-user-tie'"></i></div>
                            <div class="person-copy"><strong>{{ item.owner_name }}</strong><small>{{ item.owner_type_label }} · {{ formatPercent(item.percentage) }}%</small></div>
                            <strong class="distribution-amount positive">{{ money(item.amount) }}</strong>
                        </div>
                    </div>
                    <div v-else class="empty-inline">Нет начислений с других владельцев.</div>
                </article>
            </section>

            <section class="content-card sales-card">
                <div class="sales-heading">
                    <div class="section-heading">
                        <div class="section-icon sales"><i class="fa-solid fa-receipt"></i></div>
                        <div><h3>Продажи, участвующие в расчёте</h3><p>Только завершённые сделки по фактической дате доставки</p></div>
                    </div>
                    <span class="count-badge">{{ report.sales.length }} сделок</span>
                </div>
                <div v-if="report.sales.length" class="sales-list">
                    <article v-for="sale in report.sales" :key="sale.id" class="sale-row">
                        <div class="sale-id">#{{ sale.id }}</div>
                        <div class="sale-main">
                            <strong>{{ sale.product_name || sale.title || 'Товар не указан' }}</strong>
                            <span>{{ sale.supplier_name || 'Поставщик не указан' }}<template v-if="sale.agent_name"> · {{ sale.agent_name }}</template></span>
                            <small><i class="fa-regular fa-calendar"></i> Доставка: {{ formatDate(sale.actual_delivery_date) }}</small>
                        </div>
                        <div class="sale-finance">
                            <strong>{{ money(sale.total_price) }}</strong>
                            <small v-if="sale.own_commission > 0">Свои: {{ money(sale.own_commission) }}</small>
                            <small v-if="sale.incoming_commission > 0" class="positive">Доля: +{{ money(sale.incoming_commission) }}</small>
                        </div>
                    </article>
                </div>
                <div v-else class="empty-sales">
                    <i class="fa-regular fa-folder-open"></i>
                    <strong>За этот месяц продаж нет</strong>
                    <span>Проверьте выбранный месяц или статус сделок.</span>
                </div>
                <footer class="sales-footer">
                    <span>Количество сделок в расчёте</span>
                    <strong>{{ report.summary.sales_count }}</strong>
                    <span class="footer-spacer"></span>
                    <span>Оборот сделок</span>
                    <strong>{{ money(report.summary.involved_turnover) }}</strong>
                </footer>
            </section>
            <p class="calculation-note"><i class="fa-solid fa-circle-info"></i> В расчёт включены сделки со статусом «Завершено» (`completed`) и датой `actual_delivery_date` внутри выбранного календарного месяца. Суммы рассчитаны по текущим активным настройкам процентов.</p>
        </template>
    </main>
</template>

<script>
import { makeAxiosFactory } from '@/stores/utillites/makeAxiosFactory'

export default {
    name: 'AdminMonthlySalary',
    props: {
        agentId: { type: [Number, String], required: true },
    },
    data() {
        return {
            month: new Date().toISOString().slice(0, 7),
            report: null,
            loading: false,
            error: '',
        }
    },
    mounted() {
        this.loadReport()
    },
    methods: {
        async loadReport() {
            if (!this.agentId || !this.month) return
            this.loading = true
            this.error = ''
            try {
                const { data } = await makeAxiosFactory(
                    `/agents/${this.agentId}/salary?month=${encodeURIComponent(this.month)}`,
                    'GET'
                )
                this.report = data
            } catch (e) {
                this.error = e?.response?.data?.message || 'Не удалось загрузить расчёт зарплаты.'
            } finally {
                this.loading = false
            }
        },
        money(value) {
            return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 2 }).format(Number(value) || 0)
        },
        formatPercent(value) {
            const n = Number(value) || 0
            return Number.isInteger(n) ? String(n) : n.toFixed(2).replace(/\.?0+$/, '')
        },
        formatDate(value) {
            if (!value) return '—'
            const date = new Date(`${String(value).slice(0, 10)}T00:00:00`)
            return Number.isNaN(date.getTime()) ? String(value) : new Intl.DateTimeFormat('ru-RU').format(date)
        },
    },
}
</script>

<style scoped>
.salary-page{--ink:#17243b;--muted:#758198;--line:#e5eaf2;--blue:#2457d6;--green:#16804a;--red:#c53b4b;color:var(--ink);display:flex;flex-direction:column;gap:20px;padding:clamp(14px,2vw,28px);background:#f6f8fc;min-height:100%}
.page-header,.header-copy,.admin-card,.admin-name-row,.admin-meta,.metric-top,.section-heading,.distribution-row,.sale-row,.sales-heading,.sales-footer{display:flex;align-items:center}
.page-header{justify-content:space-between;gap:18px;flex-wrap:wrap}
.header-copy{gap:15px}.header-icon{width:54px;height:54px;display:grid;place-items:center;border-radius:17px;background:#e8efff;color:var(--blue);font-size:22px}
.eyebrow{margin:0 0 4px;color:var(--blue);font-size:10px;font-weight:800;letter-spacing:.1em}.page-header h1{margin:0;font-size:clamp(22px,3vw,30px);font-weight:800;letter-spacing:-.04em}.subtitle{margin:5px 0 0;color:var(--muted);font-size:13px}
.month-picker{display:flex;flex-direction:column;gap:6px;min-width:190px}.month-picker label{font-size:11px;font-weight:750;color:#64718a}.month-picker input{min-height:43px;border:1px solid #dce3ef;border-radius:11px;background:#fff;box-shadow:none}
.admin-card,.content-card,.metric-card,.state-card{background:#fff;border:1px solid var(--line);border-radius:17px;box-shadow:0 5px 20px rgba(22,43,82,.045)}
.admin-card{padding:20px;gap:16px;background:linear-gradient(120deg,#fff 45%,#f1f5ff)}.admin-avatar{flex:0 0 58px;width:58px;height:58px;display:grid;place-items:center;border-radius:17px;background:#eaf0ff;color:var(--blue);font-size:24px}.admin-main{min-width:0;flex:1}.admin-name-row{gap:10px;flex-wrap:wrap}.admin-name-row h2{margin:0;font-size:19px;font-weight:800}.role-badge,.count-badge{padding:5px 9px;border-radius:99px;background:#edf3ff;color:var(--blue);font-size:10px;font-weight:800}.admin-meta{gap:15px;flex-wrap:wrap;margin-top:9px;color:#64718a;font-size:12px}.admin-meta span{display:inline-flex;align-items:center;gap:6px}.period-line{margin:12px 0 0;color:#64718a;font-size:12px}.period-line i{margin-right:6px;color:var(--blue)}.period-line strong{color:var(--ink)}.period-note{color:#8a95a8}
.summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}.metric-card{padding:17px;min-width:0}.metric-primary{background:linear-gradient(135deg,#2457d6,#3978f6);border-color:#2457d6;color:#fff}.metric-top{justify-content:space-between;gap:8px;color:#77839a;font-size:11px;font-weight:700}.metric-primary .metric-top{color:#e4ebff}.metric-icon{width:34px;height:34px;display:grid;place-items:center;border-radius:11px;background:#f0f4ff;color:var(--blue)}.metric-icon.green{background:#e5f8ed;color:var(--green)}.metric-icon.red{background:#fff0f1;color:var(--red)}.metric-value{display:block;margin-top:13px;font-size:clamp(18px,2vw,25px);font-weight:850;letter-spacing:-.04em;overflow-wrap:anywhere}.metric-card small{display:block;margin-top:7px;color:#8490a4;font-size:10px;line-height:1.5}.metric-primary small{color:#e1e9ff}
.distribution-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}.content-card{padding:19px;min-width:0}.section-heading{gap:11px;min-width:0}.section-icon{flex:0 0 40px;width:40px;height:40px;display:grid;place-items:center;border-radius:13px;background:#edf3ff;color:var(--blue);font-size:16px}.section-icon.outgoing{background:#fff0f1;color:var(--red)}.section-icon.incoming{background:#e8f8ef;color:var(--green)}.section-icon.sales{background:#edf3ff;color:var(--blue)}.section-heading h3{margin:0;font-size:14px;font-weight:800}.section-heading p{margin:4px 0 0;color:var(--muted);font-size:11px}.distribution-list{display:flex;flex-direction:column;margin-top:16px}.distribution-row{gap:10px;padding:11px 0;border-bottom:1px solid #eef1f6}.distribution-row:last-child{border-bottom:0;padding-bottom:0}.person-avatar{flex:0 0 34px;width:34px;height:34px;display:grid;place-items:center;border-radius:11px;background:#fff0f1;color:var(--red)}.person-avatar.green{background:#e8f8ef;color:var(--green)}.person-copy{display:flex;flex:1;flex-direction:column;gap:4px;min-width:0}.person-copy strong{font-size:12px;overflow-wrap:anywhere}.person-copy small{font-size:10px;color:var(--muted)}.distribution-amount{font-size:12px;white-space:nowrap}.positive{color:var(--green)!important}.empty-inline{padding:24px 0 8px;color:var(--muted);font-size:12px;text-align:center}
.sales-card{padding:0;overflow:hidden}.sales-heading{justify-content:space-between;gap:12px;padding:19px;border-bottom:1px solid #edf0f5}.sales-list{padding:0 19px}.sale-row{gap:13px;padding:14px 0;border-bottom:1px solid #edf0f5}.sale-row:last-child{border-bottom:0}.sale-id{flex:0 0 42px;width:42px;height:42px;display:grid;place-items:center;border-radius:12px;background:#f1f5fb;color:#64718a;font-size:11px;font-weight:800}.sale-main{display:flex;flex:1;flex-direction:column;gap:4px;min-width:0}.sale-main strong{font-size:12px;overflow-wrap:anywhere}.sale-main span{font-size:11px;color:#64718a;overflow-wrap:anywhere}.sale-main small,.sale-finance small{font-size:10px;color:#8a95a8}.sale-main small i{margin-right:4px}.sale-finance{display:flex;flex-direction:column;align-items:flex-end;gap:4px;white-space:nowrap}.sale-finance strong{font-size:13px}.empty-sales{display:flex;flex-direction:column;align-items:center;gap:8px;padding:35px 15px;color:var(--muted);font-size:12px}.empty-sales i{font-size:25px;color:#a5b2c6}.empty-sales strong{color:var(--ink);font-size:13px}.sales-footer{gap:9px;flex-wrap:wrap;padding:14px 19px;background:#f8faff;color:#748198;font-size:11px}.sales-footer strong{color:var(--ink);font-size:12px}.footer-spacer{flex:1}.calculation-note{margin:0;color:#7b879a;font-size:10px;line-height:1.6}.calculation-note i{margin-right:5px;color:var(--blue)}.state-card{display:flex;align-items:center;justify-content:center;gap:10px;padding:28px;color:var(--muted);font-size:13px}.state-error{color:var(--red);flex-wrap:wrap}.btn-retry{padding:7px 11px;border:0;border-radius:9px;background:#edf3ff;color:var(--blue);font-weight:700}
@media(max-width:1050px){.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:720px){.salary-page{gap:14px;padding:12px}.page-header{align-items:stretch;flex-direction:column}.month-picker{width:100%}.admin-card{align-items:flex-start;padding:15px}.admin-meta{align-items:flex-start;flex-direction:column;gap:7px}.distribution-grid{grid-template-columns:1fr}.content-card{padding:15px}.sales-heading{align-items:flex-start}.sales-list{padding:0 13px}.sale-row{align-items:flex-start;gap:9px}.sale-id{flex-basis:34px;width:34px;height:34px}.sale-finance strong{font-size:12px}.sales-footer{align-items:flex-start}.footer-spacer{flex-basis:100%;height:0}}
@media(max-width:420px){.summary-grid{grid-template-columns:1fr 1fr;gap:9px}.metric-card{padding:12px}.metric-top{align-items:flex-start;font-size:10px}.metric-icon{flex:0 0 29px;width:29px;height:29px}.metric-value{font-size:18px}.metric-card small{font-size:9px}.admin-avatar{flex-basis:44px;width:44px;height:44px}.admin-name-row h2{font-size:16px}.sale-row{flex-wrap:wrap}.sale-main{min-width:calc(100% - 55px)}.sale-finance{margin-left:43px;align-items:flex-start;flex-direction:row;flex-wrap:wrap;white-space:normal}}
</style>
