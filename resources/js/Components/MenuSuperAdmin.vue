
<script setup>
import { ref, nextTick } from "vue";
import { useRouter } from "vue-router";

import { useBaseExports } from "@/stores/baseExports";
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";

const router = useRouter();
const reportStore = useBaseExports();

const load = ref(true);

const menuItems = [
    {
        title: "Мой доход",
        description: "Просмотр и контроль своих источников дохода",
        icon: "fa-wallet",
        color: "green",
        action: () => goTo("admin-my-income"),
    },
    {
        title: "Сформировать отчет",
        description: "Генерация отчетов по системе",
        icon: "fa-file-circle-plus",
        color: "red",
        action: () => openReportModal(),
    },
    {
        title: "Мои отчеты",
        description: "Просмотр сформированных документов",
        icon: "fa-file-lines",
        color: "blue",
        action: () => goTo("ReportsPage"),
    },
    {
        title: "Список продаж",
        description: "Управление продажами",
        icon: "fa-cart-shopping",
        color: "red",
        action: () => goTo("SalePage"),
    },
    {
        title: "Пользователи",
        description: "Управление учетными записями",
        icon: "fa-users",
        color: "blue",
        action: () => goTo("UserPage"),
    },
    {
        title: "Поставщики",
        description: "Управление поставщиками",
        icon: "fa-truck",
        color: "red",
        action: () => goTo("SupplierPage"),
    },
    {
        title: "Администраторы",
        description: "Управление администраторами",
        icon: "fa-user-shield",
        color: "blue",
        action: () => goTo("AgentPage"),
    },
    {
        title: "Старшие администраторы",
        description: "Управление старшими администраторами",
        icon: "fa-user-tie",
        color: "red",
        action: () => goTo("AdminPage"),
    },
    {
        title: "Работа с товаром",
        description: "Управление товарами системы",
        icon: "fa-box-open",
        color: "blue",
        action: () => goTo("ProductPage"),
    },
    {
        title: "Выгрузка в Excel",
        description: "Экспорт данных системы",
        icon: "fa-file-excel",
        color: "red",
        action: () => goTo("ExcelExportPage"),
    },
    {
        title: "Дни рождения",
        description: "Ближайшие дни рождения пользователей",
        icon: "fa-cake-candles",
        color: "blue",
        action: () => goTo("BirthdayPage"),
    },
    {
        title: "История действий",
        description: "Журнал действий в системе",
        icon: "fa-clock-rotate-left",
        color: "red",
        action: () => goTo("UserLogsPage"),
    },
];

function goTo(name) {
    router.push({ name });
}

async function openReportModal() {
    load.value = false;
    await nextTick();
    load.value = true;
    await nextTick();

    const element = document.getElementById("reportModal");

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(element).show();
    }
}

async function handleReport(payload) {
    const element = document.getElementById("reportModal");

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getInstance(element)?.hide();
    }

    await reportStore.exportSalary(payload);
}
</script>

<template>
    <div class="admin-dashboard">
        <header class="dashboard-header">
            <div class="dashboard-heading">
                <div class="dashboard-heading__icon">
                    <i class="fa-solid fa-gauge-high"></i>
                </div>

                <div>
                    <h2 class="dashboard-title">
                        Панель управления
                    </h2>
                    <p class="dashboard-subtitle">
                        Управление системой, пользователями и отчетностью
                    </p>
                </div>
            </div>

            <div class="dashboard-counter">
                <i class="fa-solid fa-grip"></i>
                <span>{{ menuItems.length }} разделов</span>
            </div>
        </header>

        <div class="dashboard-grid">
            <button
                v-for="(item, index) in menuItems"
                :key="item.title"
                type="button"
                class="menu-card"
                :class="`menu-card--${item.color}`"
                :style="{ '--card-index': index }"
                @click="item.action()"
            >
                <div class="menu-card__top">
                    <div class="menu-card__icon">
                        <i class="fa-solid" :class="item.icon"></i>
                    </div>

                    <span class="menu-card__arrow">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </span>
                </div>

                <div class="menu-card__content">
                    <h3 class="menu-card__title">
                        {{ item.title }}
                    </h3>

                    <p class="menu-card__description">
                        {{ item.description }}
                    </p>
                </div>

                <div class="menu-card__footer">
                    <span>Открыть раздел</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </button>
        </div>
    </div>

    <!-- Генератор отчетов -->
    <div
        id="reportModal"
        class="modal fade"
        tabindex="-1"
        aria-labelledby="reportModalTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content custom-modal">
                <div class="modal-header custom-modal__header">
                    <div class="custom-modal__heading">
                        <div class="custom-modal__icon">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>

                        <div>
                            <h2
                                id="reportModalTitle"
                                class="modal-title"
                            >
                                Генератор отчетов
                            </h2>
                            <p class="custom-modal__subtitle mb-0">
                                Настройка и формирование отчетности
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

                <div class="modal-body custom-modal__body">
                    <ReportGenerator
                        v-if="load"
                        @generate-report="handleReport"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.admin-dashboard {
    --dashboard-blue: #2457d6;
    --dashboard-red: #df3548;
    --dashboard-text: #17233b;
    --dashboard-green: #01a401;
    --dashboard-muted: #778399;
    --dashboard-border: #e5eaf2;

    width: 100%;
    color: var(--dashboard-text);
}

.dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--dashboard-border);
}

.dashboard-heading {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.dashboard-heading__icon {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 52px;
    height: 52px;
    border-radius: 16px;
    background: #edf3ff;
    color: var(--dashboard-blue);
    font-size: 23px;
}

.dashboard-title {
    margin: 0 0 5px;
    font-size: clamp(20px, 2.5vw, 27px);
    font-weight: 750;
    letter-spacing: -.6px;
}

.dashboard-subtitle {
    margin: 0;
    color: var(--dashboard-muted);
    font-size: 13px;
    line-height: 1.6;
}

.dashboard-counter {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    padding: 10px 13px;
    border: 1px solid var(--dashboard-border);
    border-radius: 12px;
    background: #fff;
    color: #5e6c82;
    font-size: 13px;
    font-weight: 600;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
}

.menu-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    min-width: 0;
    min-height: 225px;
    padding: 22px;
    overflow: hidden;
    border: 1px solid var(--dashboard-border);
    border-radius: 18px;
    background: #fff;
    color: var(--dashboard-text);
    text-align: left;
    cursor: pointer;
    animation: card-appear .35s ease both;
    animation-delay: calc(var(--card-index, 0) * 25ms);
    transition: transform .22s ease, border-color .22s ease,
    box-shadow .22s ease;
}

.menu-card::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--card-accent);
    content: "";
}

.menu-card--blue {
    --card-accent: var(--dashboard-blue);
    --card-icon-bg: #edf3ff;
}

.menu-card--red {
    --card-accent: var(--dashboard-red);
    --card-icon-bg: #fff0f1;
}

.menu-card--green {
    --card-accent: var(--dashboard-green);
    --card-icon-bg: #edf3ff;
}

.menu-card:hover {
    transform: translateY(-4px);
    border-color: var(--card-accent);
    box-shadow: 0 12px 32px rgb(23 35 59 / 8%);
}

.menu-card:focus-visible {
    outline: 3px solid rgb(36 87 214 / 35%);
    outline-offset: 3px;
}

.menu-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
}

.menu-card__icon {
    display: grid;
    place-items: center;
    width: 56px;
    height: 56px;
    flex-shrink: 0;
    border-radius: 17px;
    background: var(--card-icon-bg);
    color: var(--card-accent);
    font-size: 25px;
    transition: transform .22s ease;
}

.menu-card:hover .menu-card__icon {
    transform: scale(1.06);
}

.menu-card__arrow {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #f6f8fb;
    color: #98a3b5;
    font-size: 13px;
}

.menu-card__content {
    flex: 1;
}

.menu-card__title {
    margin: 0 0 9px;
    font-size: 17px;
    font-weight: 750;
    line-height: 1.4;
}

.menu-card__description {
    margin: 0;
    color: var(--dashboard-muted);
    font-size: 13px;
    line-height: 1.65;
}

.menu-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 22px;
    padding-top: 15px;
    border-top: 1px solid #edf0f5;
    color: var(--card-accent);
    font-size: 12px;
    font-weight: 700;
}

.menu-card__footer i {
    transition: transform .2s ease;
}

.menu-card:hover .menu-card__footer i {
    transform: translateX(4px);
}

/* Модальное окно */

.custom-modal {
    overflow: hidden;
    border: 0;
    border-radius: 18px;
}

.custom-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 22px 26px;
    border-bottom: 1px solid var(--dashboard-border);
    background: #fff;
}

.custom-modal__heading {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.custom-modal__icon {
    display: grid;
    place-items: center;
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 14px;
    background: #fff0f1;
    color: var(--dashboard-red);
    font-size: 20px;
}

.custom-modal .modal-title {
    color: var(--dashboard-text);
    font-size: 19px;
    font-weight: 750;
}

.custom-modal__subtitle {
    margin-top: 5px;
    color: var(--dashboard-muted);
    font-size: 13px;
}

.custom-modal__body {
    padding: 26px;
    background: #f8faff;
}

@media (max-width: 1199.98px) {
    .dashboard-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
}

@media (max-width: 575.98px) {
    .dashboard-header {
        align-items: flex-start;
        margin-bottom: 18px;
        padding-bottom: 16px;
    }

    .dashboard-heading {
        align-items: flex-start;
        gap: 11px;
    }

    .dashboard-heading__icon {
        width: 42px;
        height: 42px;
        border-radius: 13px;
        font-size: 19px;
    }

    .dashboard-title {
        font-size: 20px;
    }

    .dashboard-subtitle {
        font-size: 12px;
    }

    .dashboard-counter {
        padding: 9px;
        font-size: 12px;
    }

    .dashboard-counter i {
        display: none;
    }

    .dashboard-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 12px;
    }

    .menu-card {
        min-height: 0;
        padding: 18px;
        border-radius: 16px;
    }

    .menu-card__top {
        margin-bottom: 15px;
    }

    .menu-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        font-size: 22px;
    }

    .menu-card__title {
        font-size: 16px;
    }

    .menu-card__footer {
        margin-top: 16px;
        padding-top: 12px;
    }

    .custom-modal__header {
        padding: 17px;
    }

    .custom-modal__body {
        padding: 16px;
    }

    .custom-modal .modal-title {
        font-size: 16px;
    }
}

@keyframes card-appear {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .menu-card {
        animation: none;
        transition: none;
    }

    .menu-card__icon,
    .menu-card__footer i {
        transition: none;
    }
}
</style>
