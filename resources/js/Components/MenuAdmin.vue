
<script setup>
import { useRouter } from "vue-router";

import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";
import SaleForm from "@/Components/Sales/SaleForm.vue";

const router = useRouter();

const menuItems = [
    {
        title: "Список доставок",
        description: "Просмотр и контроль всех доставок",
        icon: "fa-truck-fast",
        color: "blue",
        action: () => goTo("SalePage"),
    },
    {
        title: "Внести доставку",
        description: "Создание нового задания на доставку",
        icon: "fa-circle-plus",
        color: "red",
        action: () => openModal("newSaleModal"),
    },
    {
        title: "Сформировать отчет",
        description: "Генерация отчетности по операциям",
        icon: "fa-file-circle-plus",
        color: "blue",
        action: () => openModal("reportModal"),
    },
    {
        title: "Мои отчеты",
        description: "Просмотр сформированных документов",
        icon: "fa-file-lines",
        color: "red",
        action: () => goTo("ReportsPage"),
    },
    {
        title: "Работа с товаром",
        description: "Просмотр и управление товарами",
        icon: "fa-box-open",
        color: "blue",
        action: () => goTo("ProductPage"),
    },
    {
        title: "Список поставщиков",
        description: "Информация о поставщиках",
        icon: "fa-truck",
        color: "red",
        action: () => goTo("SupplierPage"),
    },
    {
        title: "История действий",
        description: "Журнал операций в системе",
        icon: "fa-clock-rotate-left",
        color: "blue",
        action: () => goTo("UserLogsPage"),
    },
];

function goTo(name) {
    router.push({ name });
}

function openModal(id) {
    const element = document.getElementById(id);

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(element).show();
    }
}

function closeModal(id) {
    const element = document.getElementById(id);

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getInstance(element)?.hide();
    }
}

function handleReport(payload) {
    closeModal("reportModal");
    console.log("Отчёт сформирован:", payload);
    // Здесь можно вызвать API или Pinia store.
}

async function fetchData() {
    closeModal("newSaleModal");
}
</script>

<template>
    <div class="agent-dashboard">
        <!-- Заголовок меню -->
        <header class="dashboard-header">
            <div class="dashboard-heading">
                <div class="dashboard-heading__icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>

                <div>
                    <h2 class="dashboard-title">
                        Рабочий кабинет
                    </h2>
                    <p class="dashboard-subtitle">
                        Доставки, товары, отчеты и история операций
                    </p>
                </div>
            </div>

            <div class="dashboard-counter">
                <i class="fa-solid fa-grid-2"></i>
                <span>{{ menuItems.length }} разделов</span>
            </div>
        </header>

        <!-- Сетка разделов -->
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

    <!-- Создание доставки -->
    <div
        id="newSaleModal"
        class="modal fade"
        tabindex="-1"
        aria-labelledby="newSaleModalTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content custom-modal">
                <div class="modal-header custom-modal__header">
                    <div class="custom-modal__heading">
                        <div class="custom-modal__icon custom-modal__icon--red">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>

                        <div>
                            <h2
                                id="newSaleModalTitle"
                                class="modal-title"
                            >
                                Создание задания
                            </h2>
                            <p class="custom-modal__subtitle mb-0">
                                Заполните данные новой доставки
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
                    <SaleForm @saved="fetchData" />
                </div>
            </div>
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
                        <div class="custom-modal__icon custom-modal__icon--blue">
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
                        @generate-report="handleReport"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.agent-dashboard {
    --dashboard-blue: #2457d6;
    --dashboard-blue-dark: #173b91;
    --dashboard-red: #df3548;
    --dashboard-text: #17233b;
    --dashboard-muted: #778399;
    --dashboard-border: #e5eaf2;

    width: 100%;
    color: var(--dashboard-text);
}

/* Заголовок */

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
    letter-spacing: -0.6px;
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

/* Карточки */

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
    min-height: 230px;
    padding: 22px;
    overflow: hidden;
    border: 1px solid var(--dashboard-border);
    border-radius: 18px;
    background: #fff;
    color: var(--dashboard-text);
    text-align: left;
    cursor: pointer;
    animation: card-appear .35s ease both;
    animation-delay: calc(var(--card-index, 0) * 35ms);
    transition:
        transform .22s ease,
        border-color .22s ease,
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
    opacity: .9;
}

.menu-card--blue {
    --card-accent: var(--dashboard-blue);
    --card-icon-bg: #edf3ff;
}

.menu-card--red {
    --card-accent: var(--dashboard-red);
    --card-icon-bg: #fff0f1;
}

.menu-card:hover {
    transform: translateY(-4px);
    border-color: color-mix(in srgb, var(--card-accent) 35%, white);
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
    transition: color .2s, background .2s;
}

.menu-card:hover .menu-card__arrow {
    background: var(--card-icon-bg);
    color: var(--card-accent);
}

.menu-card__content {
    flex: 1;
}

.menu-card__title {
    margin: 0 0 9px;
    font-size: 17px;
    font-weight: 750;
    line-height: 1.4;
    letter-spacing: -.2px;
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

/* Модальные окна */

.custom-modal {
    border: 0;
    border-radius: 18px;
    overflow: hidden;
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
    font-size: 20px;
}

.custom-modal__icon--red {
    color: var(--dashboard-red);
    background: #fff0f1;
}

.custom-modal__icon--blue {
    color: var(--dashboard-blue);
    background: #edf3ff;
}

.custom-modal .modal-title {
    font-size: 19px;
    font-weight: 750;
    color: var(--dashboard-text);
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

/* Адаптивность */

@media (max-width: 1199.98px) {
    .dashboard-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .menu-card {
        min-height: 220px;
        padding: 20px;
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

    .custom-modal__icon {
        width: 40px;
        height: 40px;
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
    .menu-card__arrow,
    .menu-card__footer i {
        transition: none;
    }
}
</style>
