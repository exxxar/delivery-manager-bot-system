<script setup>
import SaleList from "@/Components/Sales/SaleList.vue";
import NotVerifiedSaleList from "@/Components/Sales/NotVerifiedSaleList.vue";
import BackBtn from "@/Components/BackBtn.vue";
import SaleForm from "@/Components/Sales/SaleForm.vue";
import AgentInfo from "@/Components/Agents/AgentInfo.vue";
</script>

<template>
    <main class="sales-page">
        <div class="container-fluid sales-page__container">
            <div class="sales-page__topbar">
                <BackBtn />

                <div class="sales-page__eyebrow">
                    <span class="sales-page__eyebrow-icon">
                        <i class="fa-solid fa-truck-fast"></i>
                    </span>
                    <span>Рабочее пространство</span>
                </div>
            </div>

            <header class="sales-page__header">
                <div class="sales-page__heading">
                    <p class="sales-page__overline">Управление доставками</p>
                    <h1 class="sales-page__title">Список доставок</h1>
                    <p class="sales-page__subtitle">
                        Просматривайте заявки и управляйте текущими доставками в одном месте.
                    </p>
                </div>

                <template v-if="user?.role >= 3">
                    <button
                        type="button"
                        class="sales-page__verify-btn"
                        @click="openVerifyModal"
                    >
                        <span class="sales-page__verify-icon">
                            <i class="fa-solid fa-list-check"></i>
                        </span>
                        <span>Заявки на проверку</span>
                        <i class="fa-solid fa-arrow-up-right-from-square sales-page__verify-arrow"></i>
                    </button>
                </template>
            </header>

            <section class="sales-page__content" aria-label="Список доставок">
                <div class="sales-page__section-heading">
                    <div>
                        <h2>Все доставки</h2>
                        <p>Список заявок и информация по ним</p>
                    </div>
                    <span class="sales-page__section-mark">
                        <i class="fa-solid fa-layer-group"></i>
                    </span>
                </div>

                <SaleList v-if="!loading" />
                <div v-else class="sales-page__loading">
                    <span class="sales-page__spinner"></span>
                    <span>Обновляем данные…</span>
                </div>
            </section>
        </div>

        <div class="sales-page__bottom-bar">
            <div class="sales-page__bottom-inner">
                <div class="sales-page__bottom-hint">
                    <span class="sales-page__bottom-hint-icon">
                        <i class="fa-solid fa-circle-plus"></i>
                    </span>
                    <span>
                        <strong>Новая доставка</strong>
                        <small>Создайте заявку за пару шагов</small>
                    </span>
                </div>

                <button
                    type="button"
                    class="sales-page__add-btn"
                    @click="addSale"
                >
                    <i class="fa-solid fa-plus"></i>
                    <span>Добавить доставку</span>
                </button>
            </div>
        </div>

        <!-- Модальное окно проверки заявок -->
        <div class="modal fade sales-modal" id="verifiedSales" tabindex="-1" aria-labelledby="verifiedSalesTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md modal-fullscreen-md-down">
                <div class="modal-content">
                    <div class="modal-header sales-modal__header">
                        <div class="sales-modal__title-wrap">
                            <span class="sales-modal__icon sales-modal__icon--blue">
                                <i class="fa-solid fa-shield-halved"></i>
                            </span>
                            <div>
                                <h5 class="modal-title" id="verifiedSalesTitle">Проверка заявок</h5>
                                <p>Заявки, ожидающие подтверждения</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>

                    <div class="modal-body sales-modal__body">
                        <template v-if="user?.role >= 3">
                            <NotVerifiedSaleList
                                v-on:agent-info="openAgentInfoModal"
                                v-if="!loading"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно создания доставки -->
        <div class="modal fade sales-modal" id="newSaleModal" tabindex="-1" aria-labelledby="newSaleModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down modal-md">
                <div class="modal-content">
                    <div class="modal-header sales-modal__header">
                        <div class="sales-modal__title-wrap">
                            <span class="sales-modal__icon sales-modal__icon--red">
                                <i class="fa-solid fa-truck-fast"></i>
                            </span>
                            <div>
                                <h5 class="modal-title" id="newSaleModalTitle">Создание доставки</h5>
                                <p>Заполните данные новой заявки</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>

                    <div class="modal-body sales-modal__body">
                        <SaleForm
                            v-if="!loading"
                            @saved="fetchData"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно информации об агенте -->
        <div class="modal fade sales-modal" id="agentInfoModal" tabindex="-1" aria-labelledby="agentInfoModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header sales-modal__header">
                        <div class="sales-modal__title-wrap">
                            <span class="sales-modal__icon sales-modal__icon--blue">
                                <i class="fa-solid fa-user-shield"></i>
                            </span>
                            <div>
                                <h5 class="modal-title" id="agentInfoModalTitle">Информация об администраторе</h5>
                                <p>Данные выбранного пользователя</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>

                    <div class="modal-body sales-modal__body">
                        <AgentInfo
                            :edit="false"
                            :agent="selectedAgent"
                            v-if="selectedAgent"
                        />
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import { useUsersStore } from "@/stores/users.ts";

export default {
    data() {
        return {
            userStore: useUsersStore(),
            loading: false,
            selectedAgent: null,
        };
    },
    computed: {
        user() {
            return this.userStore.self || null;
        },
    },
    methods: {
        openAgentInfoModal(agent) {
            this.selectedAgent = null;
            this.$nextTick(() => {
                this.selectedAgent = agent;
                const modalElement = document.getElementById("agentInfoModal");
                if (modalElement && window.bootstrap?.Modal) {
                    window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
                }
            });
        },
        openVerifyModal() {
            this.loading = true;
            this.$nextTick(() => {
                this.loading = false;
                this.$nextTick(() => {
                    const modalElement = document.getElementById("verifiedSales");
                    if (modalElement && window.bootstrap?.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
                    }
                });
            });
        },
        addSale() {
            this.loading = true;
            this.$nextTick(() => {
                this.loading = false;
                this.$nextTick(() => {
                    const modalElement = document.getElementById("newSaleModal");
                    if (modalElement && window.bootstrap?.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
                    }
                });
            });
        },
        async fetchData() {
            this.loading = true;
            this.$nextTick(() => {
                this.loading = false;
                this.$nextTick(() => {
                    const modalElement = document.getElementById("newSaleModal");
                    if (modalElement && window.bootstrap?.Modal) {
                        window.bootstrap.Modal.getInstance(modalElement)?.hide();
                    }
                });
            });
        },
    },
};
</script>

<style>
.sales-page {
    --sales-ink: #17243b;
    --sales-muted: #718096;
    --sales-blue: #2563eb;
    --sales-blue-dark: #1d4ed8;
    --sales-red: #e5484d;
    --sales-border: #e7ecf3;
    --sales-surface: #ffffff;
    --sales-bg: #f5f7fb;
    min-height: 100vh;
    padding-bottom: 112px;
    color: var(--sales-ink);
    background:
        radial-gradient(ellipse at 85% 0%, rgba(37, 99, 235, .07), transparent 34rem),
        var(--sales-bg);
}

.sales-page *,
.sales-page *::before,
.sales-page *::after {
    box-sizing: border-box;
}

.sales-page__container {
    width: min(100%, 1600px);
    margin-inline: auto;
    padding: 18px clamp(16px, 3vw, 42px) 36px;
}

.sales-page__topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 26px;
}

.sales-page__eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    color: #65748b;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
}

.sales-page__eyebrow-icon {
    display: grid;
    width: 32px;
    height: 32px;
    place-items: center;
    color: var(--sales-blue);
    background: #e8efff;
    border-radius: 10px;
    font-size: .95rem;
}

.sales-page__header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    margin-bottom: 28px;
    padding: clamp(22px, 3vw, 36px);
    overflow: hidden;
    position: relative;
    background: var(--sales-surface);
    border: 1px solid rgba(226, 232, 240, .9);
    border-radius: 24px;
    box-shadow: 0 12px 36px rgba(24, 39, 75, .055);
}

.sales-page__header::after {
    position: absolute;
    top: -68px;
    right: -32px;
    width: 210px;
    height: 210px;
    content: "";
    pointer-events: none;
    border: 28px solid rgba(37, 99, 235, .045);
    border-radius: 50%;
}

.sales-page__heading {
    position: relative;
    z-index: 1;
    max-width: 680px;
}

.sales-page__overline {
    margin: 0 0 8px;
    color: var(--sales-blue);
    font-size: .76rem;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.sales-page__title {
    margin: 0;
    color: var(--sales-ink);
    font-size: clamp(1.8rem, 3vw, 2.55rem);
    font-weight: 800;
    letter-spacing: -.045em;
    line-height: 1.1;
}

.sales-page__subtitle {
    max-width: 560px;
    margin: 12px 0 0;
    color: var(--sales-muted);
    font-size: .98rem;
    line-height: 1.65;
}

.sales-page__verify-btn {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 12px;
    flex: 0 0 auto;
    min-height: 54px;
    padding: 10px 16px 10px 11px;
    color: #fff;
    background: linear-gradient(135deg, #ef4444, #d92d3a);
    border: 0;
    border-radius: 15px;
    box-shadow: 0 8px 18px rgba(229, 72, 77, .2);
    font-size: .9rem;
    font-weight: 750;
    transition: transform .18s ease, box-shadow .18s ease;
}

.sales-page__verify-btn:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(229, 72, 77, .27);
}

.sales-page__verify-icon {
    display: grid;
    width: 34px;
    height: 34px;
    place-items: center;
    background: rgba(255, 255, 255, .18);
    border-radius: 10px;
    font-size: 1rem;
}

.sales-page__verify-arrow {
    margin-left: 3px;
    opacity: .72;
    font-size: .75rem;
}

.sales-page__content {
    min-width: 0;
    padding: clamp(16px, 2vw, 26px);
    background: var(--sales-surface);
    border: 1px solid var(--sales-border);
    border-radius: 22px;
    box-shadow: 0 10px 30px rgba(24, 39, 75, .045);
}

.sales-page__section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 18px;
    border-bottom: 1px solid #edf0f5;
}

.sales-page__section-heading h2 {
    margin: 0;
    color: var(--sales-ink);
    font-size: 1.12rem;
    font-weight: 800;
    letter-spacing: -.02em;
}

.sales-page__section-heading p {
    margin: 5px 0 0;
    color: var(--sales-muted);
    font-size: .86rem;
}

.sales-page__section-mark {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    color: var(--sales-blue);
    background: #edf3ff;
    border-radius: 13px;
    font-size: 1.05rem;
}

.sales-page__loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    min-height: 180px;
    color: var(--sales-muted);
    font-size: .92rem;
    font-weight: 600;
}

.sales-page__spinner {
    width: 22px;
    height: 22px;
    border: 2px solid #dbe6ff;
    border-top-color: var(--sales-blue);
    border-radius: 50%;
    animation: sales-spin .7s linear infinite;
}

@keyframes sales-spin {
    to { transform: rotate(360deg); }
}

.sales-page__bottom-bar {
    position: fixed;
    z-index: 100;
    right: 0;
    bottom: 0;
    left: 0;
    padding: 12px clamp(12px, 3vw, 36px) calc(12px + env(safe-area-inset-bottom));
    background: rgba(255, 255, 255, .9);
    border-top: 1px solid rgba(226, 232, 240, .9);
    box-shadow: 0 -8px 28px rgba(24, 39, 75, .07);
    backdrop-filter: blur(16px);
}

.sales-page__bottom-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    width: min(100%, 1500px);
    margin-inline: auto;
}

.sales-page__bottom-hint {
    display: flex;
    align-items: center;
    gap: 11px;
    color: var(--sales-ink);
}

.sales-page__bottom-hint-icon {
    display: grid;
    width: 42px;
    height: 42px;
    place-items: center;
    color: var(--sales-blue);
    background: #edf3ff;
    border-radius: 13px;
    font-size: 1.1rem;
}

.sales-page__bottom-hint strong,
.sales-page__bottom-hint small {
    display: block;
}

.sales-page__bottom-hint strong {
    font-size: .87rem;
    font-weight: 800;
}

.sales-page__bottom-hint small {
    margin-top: 3px;
    color: var(--sales-muted);
    font-size: .76rem;
}

.sales-page__add-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 50px;
    padding: 12px 22px;
    color: #fff;
    background: linear-gradient(135deg, #2f6df6, #1d4ed8);
    border: 0;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(37, 99, 235, .23);
    font-size: .94rem;
    font-weight: 800;
    white-space: nowrap;
    transition: transform .18s ease, box-shadow .18s ease;
}

.sales-page__add-btn:hover {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 12px 26px rgba(37, 99, 235, .3);
}

.sales-page__add-btn:active,
.sales-page__verify-btn:active {
    transform: translateY(0);
}

.sales-modal .modal-dialog {
    padding: 12px;
}

.sales-modal .modal-content {
    overflow: hidden;
    background: #fff;
    border: 1px solid rgba(226, 232, 240, .85);
    border-radius: 22px;
    box-shadow: 0 24px 80px rgba(15, 23, 42, .2);
}

.sales-modal__header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 22px 24px;
    background: linear-gradient(180deg, #fff, #fbfcff);
    border-bottom: 1px solid #edf0f5;
}

.sales-modal__title-wrap {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.sales-modal__icon {
    display: grid;
    width: 46px;
    height: 46px;
    flex: 0 0 auto;
    place-items: center;
    border-radius: 14px;
    font-size: 1.15rem;
}

.sales-modal__icon--blue {
    color: var(--sales-blue);
    background: #edf3ff;
}

.sales-modal__icon--red {
    color: var(--sales-red);
    background: #fff0f0;
}

.sales-modal__header .modal-title {
    margin: 0;
    color: var(--sales-ink);
    font-size: 1.05rem;
    font-weight: 800;
}

.sales-modal__header p {
    margin: 4px 0 0;
    color: var(--sales-muted);
    font-size: .8rem;
}

.sales-modal__header .btn-close {
    margin-left: auto;
    flex: 0 0 auto;
}

.sales-modal__body {
    padding: 24px;
}

@media (max-width: 767.98px) {
    .sales-page {
        padding-bottom: 94px;
    }

    .sales-page__container {
        padding: 12px 12px 24px;
    }

    .sales-page__topbar {
        align-items: flex-start;
        margin-bottom: 18px;
    }

    .sales-page__eyebrow {
        font-size: .65rem;
    }

    .sales-page__eyebrow-icon {
        width: 29px;
        height: 29px;
    }

    .sales-page__header {
        align-items: stretch;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 16px;
        padding: 22px 18px;
        border-radius: 19px;
    }

    .sales-page__title {
        font-size: 1.8rem;
    }

    .sales-page__subtitle {
        margin-top: 9px;
        font-size: .9rem;
    }

    .sales-page__verify-btn {
        width: 100%;
        justify-content: flex-start;
    }

    .sales-page__verify-arrow {
        margin-left: auto;
    }

    .sales-page__content {
        padding: 16px 12px;
        border-radius: 18px;
    }

    .sales-page__section-heading {
        margin-bottom: 14px;
        padding: 0 3px 14px;
    }

    .sales-page__section-mark {
        width: 38px;
        height: 38px;
    }

    .sales-page__bottom-bar {
        padding: 9px 12px calc(9px + env(safe-area-inset-bottom));
    }

    .sales-page__bottom-inner {
        gap: 10px;
    }

    .sales-page__bottom-hint {
        display: none;
    }

    .sales-page__add-btn {
        width: 100%;
        min-height: 49px;
        border-radius: 14px;
    }

    .sales-modal .modal-dialog {
        margin: 0;
        padding: 0;
    }

    .sales-modal .modal-content {
        min-height: 100%;
        border: 0;
        border-radius: 0;
    }

    .sales-modal__header {
        padding: 18px 16px;
    }

    .sales-modal__body {
        padding: 18px 14px;
    }
}

@media (min-width: 768px) and (max-width: 991.98px) {
    .sales-page__header {
        align-items: flex-start;
        flex-direction: column;
    }

    .sales-page__verify-btn {
        align-self: flex-start;
    }
}

@media (prefers-reduced-motion: reduce) {
    .sales-page *,
    .sales-page *::before,
    .sales-page *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}
</style>
