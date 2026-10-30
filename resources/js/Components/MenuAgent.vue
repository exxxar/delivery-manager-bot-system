<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";

import { useBaseExports } from "@/stores/baseExports";
import { useUsersStore } from "@/stores/users";

import ReportIndividualGenerator from "@/Components/Admins/ReportIndividualGenerator.vue";
import AgentMagicLinkManager from "@/Components/Agents/AgentMagicLinkManager.vue";

const router = useRouter();
const userStore = useUsersStore();
const reportStore = useBaseExports();

const agentId = computed(() => userStore.self?.agent?.id ?? null);

const menuItems = [
    {
        title: "Мой доход",
        description: "Просмотр и контроль своих источников дохода",
        icon: "fa-wallet",
        color: "green",
        action: () => goTo("admin-my-income"),
    },
    {
        title: "Мои доставки",
        description: "Просмотр и контроль своих доставок",
        icon: "fa-truck-fast",
        color: "blue",
        action: () => goTo("SalePage"),
    },
    {
        title: "Сформировать отчет",
        description: "Создание персонального отчета",
        icon: "fa-file-circle-plus",
        color: "red",
        action: () => openReportModal(),
    },
    {
        title: "Поставщики",
        description: "Информация и работа с поставщиками",
        icon: "fa-truck",
        color: "blue",
        action: () => goTo("SupplierPage"),
    },
    {
        title: "Мои отчеты",
        description: "Список сформированных документов",
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
        title: "История действий",
        description: "Журнал операций в системе",
        icon: "fa-clock-rotate-left",
        color: "red",
        action: () => goTo("UserLogsPage"),
    },
];

function goTo(name) {
    router.push({ name });
}

function openReportModal() {
    const element = document.getElementById("reportModal");

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(element).show();
    }
}

function openLoginLinkModal() {
    const element = document.getElementById("loginLinkModal");

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(element).show();
    }
}

function handleReport(payload) {
    const element = document.getElementById("reportModal");

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getInstance(element)?.hide();
    }

    // При необходимости здесь можно обработать payload
    // или обновить reportStore после генерации отчета.
}
</script>

<template>
    <div class="agent-dashboard">
        <header class="dashboard-header">
            <div class="dashboard-heading">
                <div class="dashboard-heading__icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>

                <div>
                    <h2 class="dashboard-title">Рабочий кабинет</h2>
                    <p class="dashboard-subtitle">
                        Доставки, товары и персональная отчетность
                    </p>
                </div>
            </div>

            <div class="dashboard-header-actions">
                <button
                    type="button"
                    class="login-link-trigger"
                    @click="openLoginLinkModal"
                    :disabled="agentId == null"
                    title="Создать персональную ссылку для входа"
                >
                    <i class="fa-solid fa-link"></i>
                    <span>Ссылка для входа</span>
                </button>

                <div class="dashboard-counter">
                    <i class="fa-solid fa-grip"></i>
                    <span>{{ menuItems.length }} разделов</span>
                </div>
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

    <!-- Индивидуальный генератор отчетов -->
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
                                Формирование персональной отчетности
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
                    <ReportIndividualGenerator
                        v-if="agentId != null"
                        :agent-id="agentId"
                        @generate-report="handleReport"
                    />

                    <div
                        v-else
                        class="agent-warning"
                    >
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>
                            Данные агента не найдены.
                            Проверьте профиль пользователя.
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Персональная ссылка для входа текущего администратора -->
    <div
        id="loginLinkModal"
        class="modal fade"
        tabindex="-1"
        aria-labelledby="loginLinkModalTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <div class="modal-header custom-modal__header">
                    <div class="custom-modal__heading">
                        <div class="custom-modal__icon login-link-modal-icon">
                            <i class="fa-solid fa-link"></i>
                        </div>
                        <div>
                            <h2 id="loginLinkModalTitle" class="modal-title">
                                Персональная ссылка
                            </h2>
                            <p class="custom-modal__subtitle mb-0">
                                Быстрый вход в ваш аккаунт на другом устройстве
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
                    <AgentMagicLinkManager
                        v-if="agentId != null"
                        :agent="{ id: agentId, name: userStore.self?.name || 'Мой аккаунт' }"
                    />

                    <div v-else class="agent-warning">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Данные агента не найдены. Проверьте привязку агента к вашему профилю.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.agent-dashboard {
    --dashboard-blue: #2457d6;
    --dashboard-green: #01a401;
    --dashboard-red: #df3548;
    --dashboard-text: #17233b;
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
    animation-delay: calc(var(--card-index, 0) * 35ms);
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

.menu-card--green {
    --card-accent: var(--dashboard-green);
    --card-icon-bg: #edf3ff;
}

.menu-card--red {
    --card-accent: var(--dashboard-red);
    --card-icon-bg: #fff0f1;
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
    background: #edf3ff;
    color: var(--dashboard-blue);
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

.agent-warning {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border: 1px solid #f4d49b;
    border-radius: 12px;
    background: #fff8e8;
    color: #805b14;
    line-height: 1.6;
}

.agent-warning i {
    margin-top: 3px;
}

.dashboard-header-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
}

.login-link-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 42px;
    padding: 10px 15px;
    border: 1px solid #cbd9f4;
    border-radius: 12px;
    background: #edf3ff;
    color: #2457d6;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: .2s ease;
}

.login-link-trigger:hover:not(:disabled) {
    border-color: #2457d6;
    background: #e2ecff;
    transform: translateY(-1px);
}

.login-link-trigger:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.login-link-modal-icon {
    background: #eaf1ff;
    color: #2457d6;
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
        flex-wrap: wrap;
        margin-bottom: 18px;
        padding-bottom: 16px;
    }

    .dashboard-header-actions {
        width: 100%;
        justify-content: space-between;
    }

    .login-link-trigger {
        min-height: 38px;
        padding: 8px 11px;
        font-size: 12px;
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


