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

            <!-- Поиск дублирующихся сделок -->
            <section class="duplicates-panel">
                <div class="duplicates-panel__info">
                    <div class="duplicates-panel__icon">
                        <i class="fa-solid fa-copy"></i>
                    </div>

                    <div>
                        <h3>Проверка дублирующихся сделок</h3>
                        <p>Поиск совпадений по сумме, агенту и поставщику</p>
                    </div>
                </div>

                <div class="duplicates-panel__controls">
                    <div class="duplicates-panel__date">
                        <label for="duplicatesDateFrom">С даты доставки</label>
                        <input
                            id="duplicatesDateFrom"
                            type="date"
                            class="form-control"
                            v-model="duplicateFilters.date_from"
                            :max="duplicateFilters.date_to || undefined"
                        />
                    </div>

                    <div class="duplicates-panel__date">
                        <label for="duplicatesDateTo">По дату доставки</label>
                        <input
                            id="duplicatesDateTo"
                            type="date"
                            class="form-control"
                            v-model="duplicateFilters.date_to"
                            :min="duplicateFilters.date_from || undefined"
                        />
                    </div>

                    <button
                        type="button"
                        class="duplicates-panel__button"
                        :disabled="duplicatesLoading || !duplicateFilters.date_from || !duplicateFilters.date_to"
                        @click="findDuplicates"
                    >
                        <i
                            class="fa-solid"
                            :class="duplicatesLoading ? 'fa-spinner fa-spin' : 'fa-magnifying-glass'"
                        ></i>
                        <span>{{ duplicatesLoading ? 'Поиск...' : 'Поиск дублей' }}</span>
                    </button>
                </div>
            </section>

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
import axios from 'axios';

export default {
    data() {
        return {
            userStore: useUsersStore(),
            loading: false,
            selectedAgent: null,
            duplicateFilters: {
                date_from: new Date(new Date().getFullYear(), new Date().getMonth(), 1)
                    .toLocaleDateString('en-CA'),
                date_to: new Date().toLocaleDateString('en-CA'),
            },

            duplicateResults: {
                groups_count: 0,
                duplicates_count: 0,
                extra_duplicates_count: 0,
                agents: [],
            },

            duplicatesLoading: false,
            deletingDuplicateId: null,
            selectedDuplicate: null,
        };
    },
    computed: {
        user() {
            return this.userStore.self || null;
        },
    },
    methods: {
        async findDuplicates() {
            if (!this.duplicateFilters.date_from || !this.duplicateFilters.date_to) {
                return;
            }

            if (this.duplicateFilters.date_from > this.duplicateFilters.date_to) {
                alert('Начальная дата не может быть больше конечной');
                return;
            }

            this.duplicatesLoading = true;

            try {
                const response = await axios.get('/api/sales/duplicates', {
                    params: {
                        date_from: this.duplicateFilters.date_from,
                        date_to: this.duplicateFilters.date_to,
                    },
                });

                this.duplicateResults = response.data;

                this.$nextTick(() => {
                    const modalElement = document.getElementById('duplicatesModal');

                    if (modalElement && window.bootstrap?.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(modalElement).show();
                    }
                });
            } catch (error) {
                console.error('Ошибка поиска дублей:', error);

                alert(
                    error.response?.data?.message ||
                    'Не удалось выполнить поиск дублирующихся сделок'
                );
            } finally {
                this.duplicatesLoading = false;
            }
        },

        formatDuplicateMoney(value) {
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                maximumFractionDigits: 2,
            }).format(Number(value || 0));
        },

        formatDuplicateDate(value) {
            if (!value) return '—';

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) return value;

            return date.toLocaleDateString('ru-RU');
        },

        duplicateStatusLabel(status) {
            const labels = {
                pending: 'Ожидание',
                assigned: 'В работе',
                delivered: 'Доставляется',
                completed: 'Завершено',
                rejected: 'Отклонено',
            };

            return labels[status] || 'Неизвестно';
        },

        confirmDeleteDuplicate(sale) {
            this.selectedDuplicate = sale;

            this.$nextTick(() => {
                const resultsModal = document.getElementById('duplicatesModal');
                const confirmModal = document.getElementById('confirmDuplicateDeleteModal');

                const showConfirmation = () => {
                    if (confirmModal && window.bootstrap?.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(confirmModal).show();
                    }
                };

                if (resultsModal?.classList.contains('show')) {
                    resultsModal.addEventListener(
                        'hidden.bs.modal',
                        showConfirmation,
                        { once: true }
                    );

                    window.bootstrap.Modal.getInstance(resultsModal)?.hide();
                } else {
                    showConfirmation();
                }
            });
        },

        async deleteDuplicate() {
            if (!this.selectedDuplicate?.id || this.deletingDuplicateId !== null) {
                return;
            }

            const saleId = this.selectedDuplicate.id;

            this.deletingDuplicateId = saleId;

            try {
                await axios.delete(`/api/sales/duplicates/${saleId}`);

                const confirmModal = document.getElementById(
                    'confirmDuplicateDeleteModal'
                );

                if (confirmModal) {
                    const instance = bootstrap.Modal.getInstance(confirmModal);

                    if (instance) {
                        await new Promise(resolve => {
                            confirmModal.addEventListener(
                                'hidden.bs.modal',
                                resolve,
                                { once: true }
                            );

                            instance.hide();
                        });
                    }
                }

                this.selectedDuplicate = null;

                await this.findDuplicates();

            } catch (error) {
                console.error('Ошибка удаления дубля:', error);

                alert(
                    error.response?.data?.message ||
                    'Не удалось удалить сделку'
                );
            } finally {
                this.deletingDuplicateId = null;
            }
        },
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
   /* background:
        radial-gradient(ellipse at 85% 0%, rgba(37, 99, 235, .07), transparent 34rem),
        var(--sales-bg);*/
    background: transparent;
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

/* Панель поиска дублей */
.duplicates-panel {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
    padding: 20px;
    background: #fff;
    border: 1px solid #e7ecf3;
    border-radius: 20px;
    box-shadow: 0 8px 26px rgba(24, 39, 75, .045);
}

.duplicates-panel__info {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 230px;
}

.duplicates-panel__icon {
    display: grid;
    place-items: center;
    flex: 0 0 46px;
    width: 46px;
    height: 46px;
    color: #d94854;
    background: #fff0f1;
    border-radius: 14px;
    font-size: 19px;
}

.duplicates-panel__info h3 {
    margin: 0;
    color: #17243b;
    font-size: 15px;
    font-weight: 800;
}

.duplicates-panel__info p {
    margin: 5px 0 0;
    color: #718096;
    font-size: 12px;
}

.duplicates-panel__controls {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 10px;
}

.duplicates-panel__date {
    min-width: 145px;
}

.duplicates-panel__date label {
    display: block;
    margin-bottom: 5px;
    color: #718096;
    font-size: 11px;
    font-weight: 700;
}

.duplicates-panel__date input {
    min-height: 43px;
    border-color: #e1e7f0;
    border-radius: 11px;
    font-size: 13px;
}

.duplicates-panel__button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 43px;
    padding: 10px 17px;
    color: #fff;
    background: linear-gradient(135deg, #e5484d, #cf303c);
    border: 0;
    border-radius: 11px;
    font-size: 13px;
    font-weight: 750;
    transition: .2s ease;
}

.duplicates-panel__button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 7px 16px rgba(229, 72, 77, .2);
}

.duplicates-panel__button:disabled {
    cursor: not-allowed;
    opacity: .55;
}

/* Статистика */
.duplicates-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 22px;
}

.duplicates-stat {
    padding: 16px;
    background: #f4f7fc;
    border: 1px solid #e9eef6;
    border-radius: 14px;
}

.duplicates-stat span,
.duplicates-stat strong {
    display: block;
}

.duplicates-stat span {
    color: #718096;
    font-size: 12px;
}

.duplicates-stat strong {
    margin-top: 5px;
    color: #17243b;
    font-size: 25px;
    font-weight: 850;
}

.duplicates-stat--danger {
    background: #fff3f3;
    border-color: #ffe0e0;
}

.duplicates-stat--danger strong {
    color: #dc3545;
}

/* Группы */
.duplicate-agent {
    margin-bottom: 18px;
    overflow: hidden;
    border: 1px solid #e6ebf2;
    border-radius: 15px;
}

.duplicate-agent__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 17px;
    background: #f4f7fc;
}

.duplicate-agent__header h6 {
    margin: 0;
    color: #17243b;
    font-weight: 800;
}

.duplicate-agent__header small {
    display: block;
    margin-top: 4px;
    color: #718096;
    font-size: 12px;
}

.duplicate-agent__badge {
    display: grid;
    place-items: center;
    min-width: 34px;
    height: 34px;
    padding: 0 8px;
    color: #fff;
    background: #dc3545;
    border-radius: 10px;
    font-weight: 800;
}

.duplicate-group {
    padding: 14px 16px;
    border-top: 1px solid #edf0f5;
}

.duplicate-group__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.duplicate-group__header strong,
.duplicate-group__header span {
    display: block;
}

.duplicate-group__header strong {
    color: #17243b;
    font-size: 15px;
    font-weight: 850;
}

.duplicate-group__header div > span {
    margin-top: 3px;
    color: #718096;
    font-size: 12px;
}

.duplicate-sales {
    display: grid;
    gap: 8px;
}

.duplicate-sale {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px;
    background: #fafbfd;
    border: 1px solid #edf0f5;
    border-radius: 11px;
}

.duplicate-sale__main {
    display: grid;
    gap: 3px;
    min-width: 0;
}

.duplicate-sale__main strong {
    color: #2563eb;
    font-size: 13px;
}

.duplicate-sale__main span {
    overflow: hidden;
    color: #17243b;
    font-size: 13px;
    font-weight: 650;
    text-overflow: ellipsis;
}

.duplicate-sale__main small {
    color: #718096;
    font-size: 11px;
}

.duplicate-sale__actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.duplicates-empty {
    padding: 45px 15px;
    text-align: center;
}

.duplicates-empty > i {
    color: #16a36a;
    font-size: 42px;
}

.duplicates-empty h5 {
    margin-top: 15px;
    color: #17243b;
    font-weight: 800;
}

.duplicates-empty p {
    color: #718096;
    font-size: 13px;
}

@media (max-width: 900px) {
    .duplicates-panel {
        align-items: stretch;
        flex-direction: column;
    }

    .duplicates-panel__controls {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .duplicates-panel__button {
        grid-column: 1 / -1;
    }
}

@media (max-width: 575px) {
    .duplicates-panel {
        padding: 15px;
        border-radius: 16px;
    }

    .duplicates-panel__controls {
        grid-template-columns: 1fr;
    }

    .duplicates-panel__button {
        grid-column: auto;
        width: 100%;
    }

    .duplicates-stats {
        gap: 6px;
    }

    .duplicates-stat {
        padding: 10px;
    }

    .duplicates-stat span {
        font-size: 10px;
    }

    .duplicates-stat strong {
        font-size: 20px;
    }

    .duplicate-sale {
        align-items: flex-start;
        flex-direction: column;
    }

    .duplicate-sale__actions {
        width: 100%;
        justify-content: flex-end;
    }

    .duplicate-group__header {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
