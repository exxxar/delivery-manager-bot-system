
<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";

import AdminJobForm from "@/Components/Users/Forms/AdminJobForm.vue";
import AgentJobForm from "@/Components/Users/Forms/AgentJobForm.vue";
import ClientJobForm from "@/Components/Users/Forms/ClientJobForm.vue";
import SupplierJobForm from "@/Components/Users/Forms/SupplierJobForm.vue";

import {
    startTimer,
    checkTimer,
} from "../utilites/commonMethods.js";

import { useUsersStore } from "@/stores/users";
import { useAlertStore } from "@/stores/utillites/useAlertStore";

const userStore = useUsersStore();
const alertStore = useAlertStore();

const spent_time = ref(0);
const requesting = ref(false);

function onSpentTimer(event) {
    spent_time.value = Number(event.detail) || 0;
}

onMounted(() => {
    checkTimer();
    window.addEventListener("trigger-spent-timer", onSpentTimer);
});

onBeforeUnmount(() => {
    window.removeEventListener("trigger-spent-timer", onSpentTimer);
});

const jobOptions = [
    {
        id: "adminJobModal",
        title: "Старший администратор",
        description: "Получение расширенных административных прав",
        icon: "fa-crown",
        color: "red",
        badge: "Администрирование",
    },
    {
        id: "agentJobModal",
        title: "Администратор",
        description: "Работа с системой и операционными задачами",
        icon: "fa-user-shield",
        color: "blue",
        badge: "Работа в системе",
    },
    {
        id: "supplierJobModal",
        title: "Поставщик",
        description: "Сотрудничество по поставкам товаров",
        icon: "fa-truck-fast",
        color: "red",
        badge: "Поставки",
    },
    {
        id: "clientJobModal",
        title: "Клиент",
        description: "Сотрудничество и работа с компанией",
        icon: "fa-handshake",
        color: "blue",
        badge: "Сотрудничество",
    },
];

function openModal(modalId) {
    const element = document.getElementById(modalId);

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(element).show();
    }
}

function hideModal(modalId) {
    const element = document.getElementById(modalId);

    if (element && window.bootstrap?.Modal) {
        window.bootstrap.Modal.getInstance(element)?.hide();
    }
}

async function requestInvite() {
    if (spent_time.value > 0 || requesting.value) {
        return;
    }

    requesting.value = true;
    startTimer(10);

    try {
        await userStore.selfRoleRequest();
        alertStore.show("Запрос успешно отправлен", "success");
    } catch (error) {
        console.error("Ошибка отправки запроса сотрудника:", error);
        alertStore.show("Ошибка отправки запроса", "error");
    } finally {
        requesting.value = false;
    }
}
</script>

<template>
    <main class="job-dashboard">
        <!-- Заголовок -->
        <header class="job-header">
            <div class="job-header__main">
                <div class="job-header__icon">
                    <i class="fa-solid fa-briefcase"></i>
                </div>

                <div>
                    <h1 class="job-header__title">
                        Сотрудничество
                    </h1>

                    <p class="job-header__subtitle">
                        Подтвердите статус сотрудника или выберите направление работы
                    </p>
                </div>
            </div>

            <div class="job-header__counter">
                <i class="fa-solid fa-layer-group"></i>
                <span>4 направления</span>
            </div>
        </header>

        <!-- Подтверждение сотрудника -->
        <section class="employee-card">
            <div class="employee-card__main">
                <div class="employee-card__icon">
                    <i class="fa-solid fa-user-check"></i>
                </div>

                <div class="employee-card__content">
                    <span class="employee-card__eyebrow">
                        Уже работаете с нами?
                    </span>

                    <h2 class="employee-card__title">
                        Подтверждение сотрудника
                    </h2>

                    <p class="employee-card__description">
                        Отправьте запрос, чтобы подтвердить свой статус
                        и получить соответствующий доступ.
                    </p>
                </div>
            </div>

            <button
                type="button"
                class="employee-card__button"
                :disabled="spent_time > 0 || requesting"
                @click="requestInvite"
            >
                <template v-if="spent_time > 0">
                    <i class="fa-solid fa-clock"></i>
                    Повторно через {{ spent_time }} сек.
                </template>

                <template v-else-if="requesting">
                    <span
                        class="spinner-border spinner-border-sm"
                        aria-hidden="true"
                    ></span>
                    Отправка запроса...
                </template>

                <template v-else>
                    <i class="fa-solid fa-check"></i>
                    Я уже являюсь сотрудником
                    <i class="fa-solid fa-arrow-right employee-card__arrow"></i>
                </template>
            </button>
        </section>

        <!-- Выбор направления -->
        <div class="job-section-heading">
            <div>
                <h2 class="job-section-heading__title">
                    Выберите направление
                </h2>

                <p class="job-section-heading__subtitle">
                    Выберите подходящую категорию и заполните заявку
                </p>
            </div>
        </div>

        <div class="job-grid">
            <button
                v-for="(job, index) in jobOptions"
                :key="job.id"
                type="button"
                class="job-card"
                :class="`job-card--${job.color}`"
                :style="{ '--card-index': index }"
                @click="openModal(job.id)"
            >
                <div class="job-card__top">
                    <div class="job-card__icon">
                        <i class="fa-solid" :class="job.icon"></i>
                    </div>

                    <span class="job-card__badge">
                        {{ job.badge }}
                    </span>
                </div>

                <div class="job-card__content">
                    <h3 class="job-card__title">
                        {{ job.title }}
                    </h3>

                    <p class="job-card__description">
                        {{ job.description }}
                    </p>
                </div>

                <div class="job-card__footer">
                    <span>Подать заявку</span>
                    <span class="job-card__arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </span>
                </div>
            </button>
        </div>

        <!-- Заявка старшего администратора -->
        <div
            id="adminJobModal"
            class="modal fade"
            tabindex="-1"
            aria-labelledby="adminJobModalTitle"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content job-modal">
                    <div class="modal-header job-modal__header">
                        <div class="job-modal__heading">
                            <div class="job-modal__icon job-modal__icon--red">
                                <i class="fa-solid fa-crown"></i>
                            </div>

                            <div>
                                <h2
                                    id="adminJobModalTitle"
                                    class="modal-title"
                                >
                                    Заявка на старшего администратора
                                </h2>

                                <p class="job-modal__subtitle mb-0">
                                    Заполните данные для рассмотрения заявки
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

                    <div class="modal-body job-modal__body">
                        <AdminJobForm
                            @submit-application="hideModal('adminJobModal')"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Заявка администратора -->
        <div
            id="agentJobModal"
            class="modal fade"
            tabindex="-1"
            aria-labelledby="agentJobModalTitle"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content job-modal">
                    <div class="modal-header job-modal__header">
                        <div class="job-modal__heading">
                            <div class="job-modal__icon job-modal__icon--blue">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>

                            <div>
                                <h2
                                    id="agentJobModalTitle"
                                    class="modal-title"
                                >
                                    Заявка на администратора
                                </h2>

                                <p class="job-modal__subtitle mb-0">
                                    Заполните данные для рассмотрения заявки
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

                    <div class="modal-body job-modal__body">
                        <AgentJobForm
                            @submit-application="hideModal('agentJobModal')"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Заявка поставщика -->
        <div
            id="supplierJobModal"
            class="modal fade"
            tabindex="-1"
            aria-labelledby="supplierJobModalTitle"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content job-modal">
                    <div class="modal-header job-modal__header">
                        <div class="job-modal__heading">
                            <div class="job-modal__icon job-modal__icon--red">
                                <i class="fa-solid fa-truck-fast"></i>
                            </div>

                            <div>
                                <h2
                                    id="supplierJobModalTitle"
                                    class="modal-title"
                                >
                                    Заявка поставщика
                                </h2>

                                <p class="job-modal__subtitle mb-0">
                                    Заявка на сотрудничество по поставкам
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

                    <div class="modal-body job-modal__body">
                        <SupplierJobForm
                            @submit-application="hideModal('supplierJobModal')"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Заявка клиента -->
        <div
            id="clientJobModal"
            class="modal fade"
            tabindex="-1"
            aria-labelledby="clientJobModalTitle"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content job-modal">
                    <div class="modal-header job-modal__header">
                        <div class="job-modal__heading">
                            <div class="job-modal__icon job-modal__icon--blue">
                                <i class="fa-solid fa-handshake"></i>
                            </div>

                            <div>
                                <h2
                                    id="clientJobModalTitle"
                                    class="modal-title"
                                >
                                    Заявка клиента
                                </h2>

                                <p class="job-modal__subtitle mb-0">
                                    Заявка на сотрудничество с компанией
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

                    <div class="modal-body job-modal__body">
                        <ClientJobForm
                            @submit-application="hideModal('clientJobModal')"
                        />
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<style scoped>
.job-dashboard {
    --job-blue: #2457d6;
    --job-blue-dark: #173b91;
    --job-red: #df3548;
    --job-text: #17233b;
    --job-muted: #778399;
    --job-border: #e5eaf2;

    width: 100%;
    color: var(--job-text);
}

/* Заголовок */

.job-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--job-border);
}

.job-header__main {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.job-header__icon {
    display: grid;
    place-items: center;
    width: 52px;
    height: 52px;
    flex-shrink: 0;
    border-radius: 16px;
    background: #edf3ff;
    color: var(--job-blue);
    font-size: 23px;
}

.job-header__title {
    margin: 0 0 5px;
    font-size: clamp(21px, 2.5vw, 28px);
    font-weight: 750;
    letter-spacing: -.6px;
}

.job-header__subtitle {
    margin: 0;
    color: var(--job-muted);
    font-size: 13px;
    line-height: 1.6;
}

.job-header__counter {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    padding: 10px 13px;
    border: 1px solid var(--job-border);
    border-radius: 12px;
    background: #fff;
    color: #5e6c82;
    font-size: 13px;
    font-weight: 600;
}

/* Подтверждение сотрудника */

.employee-card {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 32px;
    padding: 26px;
    overflow: hidden;
    border: 1px solid #d8e4ff;
    border-radius: 20px;
    background: linear-gradient(115deg, #f1f5ff 0%, #fff 80%);
}

.employee-card::before {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 4px;
    background: var(--job-blue);
    content: "";
}

.employee-card__main {
    display: flex;
    align-items: center;
    gap: 18px;
    min-width: 0;
}

.employee-card__icon {
    display: grid;
    place-items: center;
    width: 64px;
    height: 64px;
    flex-shrink: 0;
    border-radius: 19px;
    background: var(--job-blue);
    color: #fff;
    font-size: 27px;
}

.employee-card__content {
    min-width: 0;
}

.employee-card__eyebrow {
    display: block;
    margin-bottom: 5px;
    color: var(--job-blue);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .8px;
    text-transform: uppercase;
}

.employee-card__title {
    margin: 0 0 7px;
    font-size: 20px;
    font-weight: 750;
    line-height: 1.4;
}

.employee-card__description {
    max-width: 600px;
    margin: 0;
    color: var(--job-muted);
    font-size: 13px;
    line-height: 1.7;
}

.employee-card__button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 48px;
    padding: 13px 18px;
    flex-shrink: 0;
    border: 0;
    border-radius: 12px;
    background: var(--job-blue);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    transition: background .2s, transform .2s;
}

.employee-card__button:hover:not(:disabled) {
    transform: translateY(-1px);
    background: var(--job-blue-dark);
}

.employee-card__button:disabled {
    cursor: not-allowed;
    opacity: .7;
}

.employee-card__arrow {
    margin-left: 4px;
}

/* Заголовок секции */

.job-section-heading {
    margin-bottom: 18px;
}

.job-section-heading__title {
    margin: 0 0 5px;
    font-size: 19px;
    font-weight: 750;
}

.job-section-heading__subtitle {
    margin: 0;
    color: var(--job-muted);
    font-size: 13px;
    line-height: 1.6;
}

/* Карточки направлений */

.job-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.job-card {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: stretch;
    min-width: 0;
    min-height: 225px;
    padding: 22px;
    overflow: hidden;
    border: 1px solid var(--job-border);
    border-radius: 18px;
    background: #fff;
    color: var(--job-text);
    text-align: left;
    cursor: pointer;
    animation: job-card-appear .35s ease both;
    animation-delay: calc(var(--card-index, 0) * 45ms);
    transition: transform .22s, border-color .22s, box-shadow .22s;
}

.job-card::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: var(--card-accent);
    content: "";
}

.job-card--blue {
    --card-accent: var(--job-blue);
    --card-icon-bg: #edf3ff;
}

.job-card--red {
    --card-accent: var(--job-red);
    --card-icon-bg: #fff0f1;
}

.job-card:hover {
    transform: translateY(-4px);
    border-color: var(--card-accent);
    box-shadow: 0 12px 32px rgb(23 35 59 / 8%);
}

.job-card:focus-visible {
    outline: 3px solid rgb(36 87 214 / 35%);
    outline-offset: 3px;
}

.job-card__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 24px;
}

.job-card__icon {
    display: grid;
    place-items: center;
    width: 56px;
    height: 56px;
    flex-shrink: 0;
    border-radius: 17px;
    background: var(--card-icon-bg);
    color: var(--card-accent);
    font-size: 25px;
    transition: transform .22s;
}

.job-card:hover .job-card__icon {
    transform: scale(1.06);
}

.job-card__badge {
    padding: 7px 10px;
    border-radius: 8px;
    background: var(--card-icon-bg);
    color: var(--card-accent);
    font-size: 10px;
    font-weight: 750;
}

.job-card__content {
    flex: 1;
}

.job-card__title {
    margin: 0 0 9px;
    font-size: 18px;
    font-weight: 750;
    line-height: 1.4;
}

.job-card__description {
    margin: 0;
    color: var(--job-muted);
    font-size: 13px;
    line-height: 1.7;
}

.job-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 22px;
    padding-top: 15px;
    border-top: 1px solid #edf0f5;
    color: var(--card-accent);
    font-size: 12px;
    font-weight: 750;
}

.job-card__arrow {
    display: grid;
    place-items: center;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    background: var(--card-icon-bg);
    transition: transform .2s;
}

.job-card:hover .job-card__arrow {
    transform: translateX(3px);
}

/* Модальные окна */

.job-modal {
    overflow: hidden;
    border: 0;
}

.job-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 22px 26px;
    border-bottom: 1px solid var(--job-border);
    background: #fff;
}

.job-modal__heading {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.job-modal__icon {
    display: grid;
    place-items: center;
    width: 46px;
    height: 46px;
    flex-shrink: 0;
    border-radius: 14px;
    font-size: 20px;
}

.job-modal__icon--blue {
    background: #edf3ff;
    color: var(--job-blue);
}

.job-modal__icon--red {
    background: #fff0f1;
    color: var(--job-red);
}

.job-modal .modal-title {
    color: var(--job-text);
    font-size: 19px;
    font-weight: 750;
}

.job-modal__subtitle {
    margin-top: 5px;
    color: var(--job-muted);
    font-size: 13px;
    line-height: 1.6;
}

.job-modal__body {
    padding: 26px;
    background: #f8faff;
}

/* Адаптивность */

@media (max-width: 991.98px) {
    .employee-card {
        align-items: flex-start;
        flex-direction: column;
    }

    .employee-card__button {
        width: 100%;
    }

    .job-grid {
        gap: 14px;
    }
}

@media (max-width: 575.98px) {
    .job-header {
        align-items: flex-start;
        margin-bottom: 18px;
        padding-bottom: 16px;
    }

    .job-header__main {
        align-items: flex-start;
        gap: 11px;
    }

    .job-header__icon {
        width: 42px;
        height: 42px;
        border-radius: 13px;
        font-size: 19px;
    }

    .job-header__title {
        font-size: 20px;
    }

    .job-header__subtitle {
        font-size: 12px;
    }

    .job-header__counter {
        padding: 8px;
        font-size: 11px;
    }

    .job-header__counter i {
        display: none;
    }

    .employee-card {
        gap: 18px;
        margin-bottom: 26px;
        padding: 18px;
        border-radius: 16px;
    }

    .employee-card__main {
        align-items: flex-start;
        gap: 13px;
    }

    .employee-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        font-size: 21px;
    }

    .employee-card__title {
        font-size: 17px;
    }

    .employee-card__description {
        font-size: 12px;
    }

    .employee-card__button {
        min-height: 46px;
        padding: 12px;
        font-size: 12px;
    }

    .job-section-heading__title {
        font-size: 17px;
    }

    .job-section-heading__subtitle {
        font-size: 12px;
    }

    .job-grid {
        grid-template-columns: minmax(0, 1fr);
        gap: 12px;
    }

    .job-card {
        min-height: 0;
        padding: 18px;
        border-radius: 16px;
    }

    .job-card__top {
        margin-bottom: 16px;
    }

    .job-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        font-size: 22px;
    }

    .job-card__title {
        font-size: 16px;
    }

    .job-card__footer {
        margin-top: 16px;
        padding-top: 12px;
    }

    .job-modal__header {
        padding: 17px;
    }

    .job-modal__heading {
        gap: 10px;
    }

    .job-modal__icon {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }

    .job-modal .modal-title {
        font-size: 16px;
    }

    .job-modal__subtitle {
        font-size: 11px;
    }

    .job-modal__body {
        padding: 16px;
    }
}

@keyframes job-card-appear {
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
    .job-card {
        animation: none;
        transition: none;
    }

    .job-card__icon,
    .job-card__arrow {
        transition: none;
    }
}
</style>
