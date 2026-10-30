<template>
    <div class="supplier-dashboard">
        <div class="dashboard-heading">
            <div class="heading-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <div>
                <p class="eyebrow">ЛИЧНЫЙ КАБИНЕТ</p>
                <h2>Рабочее пространство</h2>
                <p class="heading-description">Быстрый доступ к доставкам, товарам и отчётам</p>
            </div>
        </div>

        <div class="dashboard-grid">
            <button type="button" class="dashboard-card card-deliveries" @click="goTo('MyDeliveries')">
                <span class="card-icon"><i class="fa-solid fa-truck-fast"></i></span>
                <span class="card-content">
                    <span class="card-title">Мои доставки</span>
                    <span class="card-description">Просмотр доставок, назначенных вам</span>
                </span>
                <span class="card-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
            </button>

            <button type="button" class="dashboard-card card-generate" data-bs-toggle="modal" data-bs-target="#reportModal">
                <span class="card-icon"><i class="fa-solid fa-file-circle-plus"></i></span>
                <span class="card-content">
                    <span class="card-title">Сформировать отчёт</span>
                    <span class="card-description">Создание отчётности за выбранный период</span>
                </span>
                <span class="card-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
            </button>

            <button type="button" class="dashboard-card card-reports" @click="goTo('ReportsPage')">
                <span class="card-icon"><i class="fa-solid fa-folder-open"></i></span>
                <span class="card-content">
                    <span class="card-title">Мои отчёты</span>
                    <span class="card-description">История сформированных отчётов</span>
                </span>
                <span class="card-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
            </button>

            <button type="button" class="dashboard-card card-products" @click="goTo('ProductPage')">
                <span class="card-icon"><i class="fa-solid fa-box-open"></i></span>
                <span class="card-content">
                    <span class="card-title">Работа с товарами</span>
                    <span class="card-description">Каталог и управление товарами</span>
                </span>
                <span class="card-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
            </button>

            <button type="button" class="dashboard-card card-history" @click="goTo('UserLogsPage')">
                <span class="card-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                <span class="card-content">
                    <span class="card-title">История действий</span>
                    <span class="card-description">Журнал активности в системе</span>
                </span>
                <span class="card-arrow"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
            </button>
        </div>

        <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content report-modal-content">
                    <div class="modal-header report-modal-header">
                        <div class="modal-heading-icon"><i class="fa-solid fa-file-lines"></i></div>
                        <div class="modal-heading-text">
                            <h5 class="modal-title" id="reportModalTitle">Генератор отчётов</h5>
                            <p class="mb-0">Выберите период и сформируйте отчёт</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body p-4">
                        <ReportIndividualGenerator
                            v-if="userStore.self?.supplier"
                            :agent-id="userStore.self.supplier.id"
                            @generate-report="handleReport"
                        />
                        <div v-else class="supplier-warning">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <div>
                                <strong>Профиль поставщика не найден</strong>
                                <p class="mb-0">Для формирования отчёта необходимо связать пользователя с профилем поставщика.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import ReportIndividualGenerator from "@/Components/Admins/ReportIndividualGenerator.vue";
</script>

<script>
import { useBaseExports } from "@/stores/baseExports";
import { useUsersStore } from "@/stores/users";

export default {
    name: 'AgentDashboardMenu',
    data() {
        return {
            userStore: useUsersStore(),
            reportStore: useBaseExports()
        }
    },
    methods: {
        goTo(name) {
            this.$router.push({ name })
        },
        handleReport(payload) {
            const modalElement = document.getElementById('reportModal')
            const modal = modalElement ? bootstrap.Modal.getInstance(modalElement) : null
            modal?.hide()
        }
    }
}
</script>

<style scoped>
.supplier-dashboard {
    --navy: #172b4d;
    --muted: #748198;
    --blue: #2864dc;
    --red: #d94352;
    color: var(--navy);
}

.dashboard-heading {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
    padding: 1.25rem 1.4rem;
    border: 1px solid #e8edf5;
    border-radius: 20px;
    background: linear-gradient(120deg, #fff 0%, #f5f8ff 100%);
}

.heading-icon,
.card-icon,
.modal-heading-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.heading-icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    color: #fff;
    background: linear-gradient(145deg, #3478f6, #2452bb);
    box-shadow: 0 8px 18px rgba(40, 100, 220, .2);
    font-size: 1.35rem;
}

.eyebrow {
    margin: 0 0 .25rem;
    color: var(--blue);
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .12em;
}

.dashboard-heading h2 {
    margin: 0;
    font-size: clamp(1.2rem, 2vw, 1.55rem);
    font-weight: 750;
    letter-spacing: -.025em;
}

.heading-description {
    margin: .3rem 0 0;
    color: var(--muted);
    font-size: .9rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}

.dashboard-card {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    width: 100%;
    min-height: 150px;
    padding: 1.35rem;
    overflow: hidden;
    text-align: left;
    color: var(--navy);
    background: #fff;
    border: 1px solid #e7ecf4;
    border-radius: 18px;
    box-shadow: 0 4px 14px rgba(22, 42, 77, .045);
    cursor: pointer;
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}

.dashboard-card::after {
    position: absolute;
    right: -28px;
    bottom: -48px;
    width: 115px;
    height: 115px;
    border-radius: 50%;
    background: var(--tint, rgba(40, 100, 220, .06));
    content: "";
    pointer-events: none;
}

.dashboard-card:hover {
    transform: translateY(-4px);
    border-color: var(--accent, #cbd9f7);
    box-shadow: 0 12px 28px rgba(22, 42, 77, .11);
}

.dashboard-card:focus-visible {
    outline: 3px solid rgba(40, 100, 220, .28);
    outline-offset: 3px;
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 15px;
    color: var(--accent, var(--blue));
    background: var(--tint, #eef4ff);
    font-size: 1.35rem;
}

.card-content {
    z-index: 1;
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: .45rem;
    min-width: 0;
    padding-top: .1rem;
}

.card-title {
    font-size: 1rem;
    line-height: 1.35;
    font-weight: 750;
}

.card-description {
    color: var(--muted);
    font-size: .83rem;
    line-height: 1.55;
}

.card-arrow {
    z-index: 1;
    color: #a0abc0;
    font-size: .85rem;
    transition: color .2s ease, transform .2s ease;
}

.dashboard-card:hover .card-arrow {
    color: var(--accent, var(--blue));
    transform: translate(2px, -2px);
}

.card-deliveries { --accent: #2864dc; --tint: #edf3ff; }
.card-generate { --accent: #d94352; --tint: #fff0f1; }
.card-reports { --accent: #5069c9; --tint: #eff1ff; }
.card-products { --accent: #138a78; --tint: #e8f8f4; }
.card-history { --accent: #64748b; --tint: #f0f3f7; }

.report-modal-content {
    overflow: hidden;
    border: 1px solid #e8edf5;
    border-radius: 20px;
    box-shadow: 0 24px 70px rgba(17, 35, 67, .2);
}

.report-modal-header {
    display: flex;
    align-items: center;
    gap: .9rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #edf0f6;
    background: #f8faff;
}

.modal-heading-icon {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    color: var(--blue);
    background: #eaf1ff;
    font-size: 1.15rem;
}

.modal-heading-text { flex: 1; }

.modal-title {
    color: var(--navy);
    font-weight: 750;
}

.modal-heading-text p {
    margin-top: .25rem;
    color: var(--muted);
    font-size: .82rem;
}

.supplier-warning {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    padding: 1rem;
    border: 1px solid #f5d7a1;
    border-radius: 14px;
    color: #795315;
    background: #fff9eb;
}

.supplier-warning > i {
    margin-top: .15rem;
    color: #d99a20;
    font-size: 1.2rem;
}

.supplier-warning p {
    margin-top: .3rem;
    color: #8c7449;
    font-size: .85rem;
    line-height: 1.5;
}

@media (max-width: 991.98px) {
    .dashboard-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 575.98px) {
    .dashboard-heading { padding: 1rem; border-radius: 16px; }
    .heading-icon { width: 46px; height: 46px; border-radius: 13px; }
    .heading-description { font-size: .82rem; }
    .dashboard-grid { grid-template-columns: minmax(0, 1fr); gap: .75rem; }
    .dashboard-card { min-height: 118px; padding: 1.05rem; border-radius: 16px; }
    .report-modal-header { padding: 1rem; }
    .modal-body.p-4 { padding: 1rem !important; }
}

@media (prefers-reduced-motion: reduce) {
    .dashboard-card,
    .card-arrow { transition: none; }
}
</style>
