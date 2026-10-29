<template>
    <section v-if="isSupportedOwner" class="percentage-manager">
        <header class="manager-header">
            <div class="heading-content">
                <div class="heading-icon"><i class="fa-solid fa-percent"></i></div>
                <div class="heading-copy">
                    <h6>Распределение процентов</h6>
                    <p v-if="ownerName" class="owner-name">Получатель: <strong>{{ ownerName }}</strong></p>
                    <p v-else class="owner-name">Настройка долей распределения</p>
                </div>
            </div>
            <button class="btn-add" type="button" @click="showAddForm = !showAddForm">
                <i :class="showAddForm ? 'fa-solid fa-xmark' : 'fa-solid fa-plus'"></i>
                <span>{{ showAddForm ? 'Закрыть' : 'Добавить процент' }}</span>
            </button>
        </header>

        <Transition name="fade">
            <div v-if="showAddForm" class="add-panel">
                <div class="panel-heading">
                    <div class="panel-icon"><i class="fa-solid fa-user-plus"></i></div>
                    <div>
                        <h6>Новый процент</h6>
                        <p>Выберите пользователя и укажите его долю</p>
                    </div>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="field-label" for="percentage-user">Получатель процента</label>
                        <select id="percentage-user" v-model="newPercentage.user_id" class="form-select modern-control">
                            <option :value="null" disabled>Выберите пользователя</option>
                            <option v-for="u in adminUsers" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="field-label" for="percentage-value">Размер процента</label>
                        <div class="percentage-input-wrap">
                            <input
                                id="percentage-value"
                                v-model.number="newPercentage.percentage"
                                type="number"
                                class="form-control modern-control"
                                min="0"
                                max="100"
                                step="0.1"
                                placeholder="0,0"
                            />
                            <span class="input-suffix">%</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="form-actions">
                            <button class="btn-save" type="button" @click="addPercentage">
                                <i class="fa-solid fa-check"></i> Добавить
                            </button>
                            <button class="btn-cancel" type="button" @click="showAddForm = false">Отмена</button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <div v-if="loading" class="loading-state">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span>Загружаем распределение…</span>
        </div>

        <div v-else-if="percentages.length" class="percentage-content">
            <div class="summary-strip">
                <div class="summary-copy">
                    <span class="summary-label">Сумма активных процентов</span>
                    <strong>{{ formatPercent(totalPercent) }}%</strong>
                </div>
                <div class="summary-progress" role="progressbar" :aria-valuenow="Math.min(totalPercent, 100)" aria-valuemin="0" aria-valuemax="100">
                    <div class="summary-progress-fill" :class="{ 'is-overflow': totalPercent > 100 }" :style="{ width: `${Math.min(Math.max(totalPercent, 0), 100)}%` }"></div>
                </div>
                <span class="summary-caption" :class="totalPercent > 100 ? 'caption-warning' : 'caption-muted'">
                    {{ totalPercent > 100 ? 'Сумма превышает 100%' : `Из 100% распределено ${formatPercent(totalPercent)}%` }}
                </span>
            </div>

            <div class="table-responsive percentage-table-wrap">
                <table class="table percentage-table align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Получатель</th>
                        <th class="text-center">Процент</th>
                        <th class="text-center">Статус</th>
                        <th class="text-end">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="p in percentages" :key="p.id">
                        <td>
                            <div class="recipient-cell">
                                <span class="recipient-avatar"><i class="fa-solid fa-user"></i></span>
                                <span class="recipient-name">{{ p.user?.name || 'Неизвестно' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="percentage-pill">{{ formatPercent(p.percentage) }}%</span>
                        </td>
                        <td class="text-center">
                            <button
                                type="button"
                                class="status-toggle"
                                :class="p.is_active ? 'status-active' : 'status-inactive'"
                                @click="toggleActive(p)"
                                :title="p.is_active ? 'Активен (кликните, чтобы деактивировать)' : 'Неактивен (кликните, чтобы активировать)'"
                                :aria-label="p.is_active ? 'Деактивировать процент' : 'Активировать процент'"
                            >
                                <i :class="p.is_active ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark'"></i>
                                <span>{{ p.is_active ? 'Активен' : 'Неактивен' }}</span>
                            </button>
                        </td>
                        <td class="text-end">
                            <button type="button" class="btn-delete" @click="confirmDelete(p)" title="Удалить" aria-label="Удалить процент">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td><strong>Итого по активным</strong></td>
                        <td class="text-center"><span class="total-pill">{{ formatPercent(totalPercent) }}%</span></td>
                        <td colspan="2"></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div v-else class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-percent"></i></div>
            <h6>Проценты ещё не назначены</h6>
            <p>Добавьте получателя, чтобы настроить распределение долей.</p>
            <button class="btn-add empty-action" type="button" @click="showAddForm = true">
                <i class="fa-solid fa-plus"></i> Добавить первый процент
            </button>
        </div>
    </section>
</template>

<script>
import { makeAxiosFactory } from '@/stores/utillites/makeAxiosFactory'
import { useAlertStore } from '@/stores/utillites/useAlertStore'
import { useModalStore } from '@/stores/utillites/useConfitmModalStore'
import { useUsersStore } from '@/stores/users'

export default {
    name: 'PercentageManager',
    props: {
        ownerType: { type: String, required: true }, // 'agent' или 'supplier'
        ownerId: { type: Number, required: true },
        ownerName: { type: String, default: '' },
    },
    data() {
        return {
            percentages: [],
            users: [],
            loading: false,
            showAddForm: false,
            newPercentage: {
                user_id: null,
                percentage: 0,
                is_active: true,
            },
            alertStore: useAlertStore(),
            modalStore: useModalStore(),
        }
    },
    computed: {
        // Доли распределяются только между администраторами.
        // role: 1 — администратор, 3 — старший администратор, 4 — суперадмин.
        adminUsers() {
            return this.users.filter(user => [1, 3, 4].includes(Number(user.role)))
        },
        // Компонент предназначен для владельцев типа agent или supplier.
        isSupportedOwner() {
            return ['agent', 'supplier'].includes(this.ownerType)
        },
        totalPercent() {
            return this.percentages
                .filter(p => p.is_active)
                .reduce((sum, p) => sum + Number(p.percentage), 0)
        },
    },
    mounted() {
        this.loadPercentages()
        this.loadUsers()
    },
    methods: {
        formatPercent(value) {
            const number = Number(value) || 0
            return Number.isInteger(number) ? String(number) : number.toFixed(1).replace(/\.0$/, '')
        },
        async loadPercentages() {
            this.loading = true
            try {
                const { data } = await makeAxiosFactory(
                    `/percentages?type=${this.ownerType}&id=${this.ownerId}`,
                    'GET'
                )
                this.percentages = data.percentages || []
            } catch (e) {
                console.error('Ошибка загрузки процентов:', e)
            } finally {
                this.loading = false
            }
        },

        async loadUsers() {
            try {
                const usersStore = useUsersStore()
                if (usersStore.items.length === 0) {
                    await usersStore.fetchAll()
                }
                this.users = usersStore.items
            } catch (e) {
                console.error('Ошибка загрузки пользователей:', e)
            }
        },

        async addPercentage() {
            if (!this.newPercentage.user_id) {
                this.alertStore.show('Выберите получателя', 'warning')
                return
            }
            if (!this.newPercentage.percentage || this.newPercentage.percentage <= 0) {
                this.alertStore.show('Укажите процент больше 0', 'warning')
                return
            }
            if (this.newPercentage.percentage > 100) {
                this.alertStore.show('Процент не может превышать 100', 'warning')
                return
            }

            try {
                const { data } = await makeAxiosFactory('/percentages', 'POST', {
                    type: this.ownerType,
                    owner_id: this.ownerId,
                    ...this.newPercentage,
                })
                this.percentages.push(data)
                this.newPercentage = { user_id: null, percentage: 0, is_active: true }
                this.showAddForm = false
                this.alertStore.show('Процент добавлен', 'success')
            } catch (e) {
                console.error('Ошибка добавления:', e)
            }
        },

        async toggleActive(p) {
            try {
                const { data } = await makeAxiosFactory(
                    `/percentages/${p.id}`,
                    'PUT',
                    { is_active: !p.is_active }
                )
                const idx = this.percentages.findIndex(x => x.id === p.id)
                if (idx !== -1) this.percentages[idx] = data
            } catch (e) {
                console.error('Ошибка обновления:', e)
            }
        },

        confirmDelete(p) {
            this.modalStore.open(
                `Удалить процент <b>${p.percentage}%</b> для <b>${p.user?.name || 'Неизвестно'}</b>?`,
                async () => {
                    try {
                        await makeAxiosFactory(`/percentages/${p.id}`, 'DELETE')
                        this.percentages = this.percentages.filter(x => x.id !== p.id)
                        this.alertStore.show('Процент удалён', 'success')
                    } catch (e) {
                        console.error('Ошибка удаления:', e)
                    }
                    this.modalStore.close()
                },
                () => this.modalStore.close()
            )
        },
    },
}
</script>

<style scoped>
.percentage-manager {
    --pm-blue: #2457d6;
    --pm-blue-dark: #173eaa;
    --pm-text: #17243b;
    --pm-muted: #758198;
    --pm-border: #e5eaf2;
    color: var(--pm-text);
    background: #fff;
    border: 1px solid var(--pm-border);
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 8px 28px rgba(27, 47, 87, .06);
}
.manager-header,
.heading-content,
.panel-heading,
.recipient-cell,
.form-actions,
.summary-copy {
    display: flex;
    align-items: center;
}
.manager-header {
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
}
.heading-content { gap: 13px; min-width: 0; }
.heading-icon,
.panel-icon,
.recipient-avatar,
.empty-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.heading-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    color: var(--pm-blue);
    background: #edf3ff;
    font-size: 20px;
}
.heading-copy h6 { margin: 0 0 4px; font-size: 16px; font-weight: 750; letter-spacing: -.02em; }
.owner-name { margin: 0; color: var(--pm-muted); font-size: 13px; }
.owner-name strong { color: var(--pm-blue); font-weight: 700; }
.btn-add,
.btn-save,
.btn-cancel,
.btn-delete,
.status-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: 0;
    cursor: pointer;
    transition: background .18s ease, color .18s ease, box-shadow .18s ease, transform .18s ease;
}
.btn-add {
    padding: 10px 15px;
    border-radius: 11px;
    background: var(--pm-blue);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
    box-shadow: 0 5px 12px rgba(36, 87, 214, .18);
}
.btn-add:hover, .btn-save:hover { background: var(--pm-blue-dark); transform: translateY(-1px); }
.add-panel {
    margin-bottom: 22px;
    padding: 20px;
    border: 1px solid #dbe6ff;
    border-radius: 15px;
    background: linear-gradient(135deg, #f8faff, #f1f5ff);
}
.panel-heading { gap: 11px; margin-bottom: 18px; }
.panel-icon { width: 38px; height: 38px; border-radius: 12px; background: #e2ebff; color: var(--pm-blue); }
.panel-heading h6 { margin: 0 0 3px; font-size: 14px; font-weight: 750; }
.panel-heading p { margin: 0; color: var(--pm-muted); font-size: 12px; }
.field-label { display: block; margin-bottom: 7px; color: #4b5870; font-size: 12px; font-weight: 700; }
.modern-control {
    min-height: 42px;
    border: 1px solid #dce3ef;
    border-radius: 10px;
    background-color: #fff;
    color: var(--pm-text);
    font-size: 13px;
    box-shadow: none;
}
.modern-control:focus { border-color: #7da2ff; box-shadow: 0 0 0 3px rgba(36, 87, 214, .1); }
.percentage-input-wrap { position: relative; }
.percentage-input-wrap .modern-control { padding-right: 38px; }
.input-suffix { position: absolute; top: 50%; right: 13px; transform: translateY(-50%); color: var(--pm-muted); font-weight: 700; pointer-events: none; }
.form-actions { gap: 8px; min-height: 42px; }
.btn-save { min-height: 40px; padding: 0 14px; border-radius: 10px; background: var(--pm-blue); color: #fff; font-size: 12px; font-weight: 700; }
.btn-cancel { min-height: 40px; padding: 0 13px; border: 1px solid #dce3ef; border-radius: 10px; background: #fff; color: #59667c; font-size: 12px; font-weight: 650; }
.btn-cancel:hover { background: #f2f5fa; }
.summary-strip { display: grid; grid-template-columns: auto minmax(80px, 1fr) auto; align-items: center; gap: 18px; margin-bottom: 18px; padding: 15px 17px; border: 1px solid #e7edfa; border-radius: 13px; background: #f8faff; }
.summary-copy { flex-direction: column; align-items: flex-start; gap: 3px; }
.summary-label { color: var(--pm-muted); font-size: 11px; font-weight: 650; }
.summary-copy strong { color: var(--pm-blue); font-size: 22px; line-height: 1.1; letter-spacing: -.03em; }
.summary-progress { height: 8px; overflow: hidden; border-radius: 99px; background: #e3eaf7; }
.summary-progress-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #3977f6, #2457d6); transition: width .25s ease; }
.summary-progress-fill.is-overflow { background: linear-gradient(90deg, #f5a623, #dc3545); }
.summary-caption { font-size: 11px; font-weight: 650; white-space: nowrap; }
.caption-muted { color: var(--pm-muted); }
.caption-warning { color: #c24135; }
.percentage-table-wrap { border: 1px solid var(--pm-border); border-radius: 13px; overflow: hidden; }
.percentage-table { min-width: 550px; }
.percentage-table thead th { padding: 13px 16px; background: #f7f9fc; color: #7b879a; border-bottom: 1px solid var(--pm-border); font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; white-space: nowrap; }
.percentage-table tbody td, .percentage-table tfoot td { padding: 13px 16px; border-color: #edf0f5; font-size: 13px; }
.percentage-table tbody tr:last-child td { border-bottom: 0; }
.percentage-table tbody tr:hover { background: #fafcff; }
.recipient-cell { gap: 10px; }
.recipient-avatar { width: 34px; height: 34px; border-radius: 11px; background: #edf3ff; color: var(--pm-blue); }
.recipient-name { font-weight: 650; }
.percentage-pill, .total-pill { display: inline-flex; align-items: center; justify-content: center; min-width: 60px; padding: 6px 10px; border-radius: 8px; background: #eaf0ff; color: var(--pm-blue); font-size: 12px; font-weight: 800; }
.status-toggle { gap: 6px; padding: 6px 9px; border-radius: 8px; background: transparent; font-size: 11px; font-weight: 700; }
.status-active { background: #eaf8f0; color: #16804a; }
.status-active:hover { background: #d8f2e4; }
.status-inactive { background: #f0f2f5; color: #7c8798; }
.status-inactive:hover { background: #e3e7ed; }
.btn-delete { width: 34px; height: 34px; border: 1px solid #f2d9dc; border-radius: 9px; background: #fff7f7; color: #d3424f; }
.btn-delete:hover { background: #d3424f; color: #fff; border-color: #d3424f; }
.percentage-table tfoot td { background: #f8faff; border-top: 1px solid var(--pm-border); }
.percentage-table tfoot strong { font-size: 12px; }
.total-pill { background: #e4f7ec; color: #16804a; font-size: 13px; }
.empty-state { display: flex; flex-direction: column; align-items: center; padding: 36px 20px 28px; text-align: center; border: 1px dashed #d9e2f0; border-radius: 14px; background: #fbfcff; }
.empty-icon { width: 54px; height: 54px; margin-bottom: 14px; border-radius: 17px; background: #edf3ff; color: var(--pm-blue); font-size: 22px; }
.empty-state h6 { margin-bottom: 6px; font-size: 15px; font-weight: 750; }
.empty-state p { max-width: 320px; margin-bottom: 17px; color: var(--pm-muted); font-size: 12px; }
.empty-action { font-size: 12px; }
.loading-state { display: flex; justify-content: center; align-items: center; gap: 10px; padding: 28px; color: var(--pm-muted); font-size: 13px; }
.fade-enter-active, .fade-leave-active { transition: opacity .22s ease, transform .22s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-8px); }
@media (max-width: 767.98px) {
    .percentage-manager { padding: 16px; border-radius: 15px; }
    .manager-header { align-items: flex-start; flex-direction: column; }
    .btn-add { width: 100%; }
    .add-panel { padding: 15px; }
    .form-actions { align-items: stretch; }
    .btn-save, .btn-cancel { flex: 1; }
    .summary-strip { grid-template-columns: 1fr; gap: 10px; }
    .summary-caption { white-space: normal; }
}
</style>
