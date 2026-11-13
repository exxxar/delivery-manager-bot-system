<script setup>
import axios from 'axios';
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
import BackBtn from "@/Components/BackBtn.vue";
import SupplierForm from "@/Components/Suppliers/SupplierForm.vue";
</script>

<template>
    <div class="suppliers-page container-fluid ">
        <div class="suppliers-topbar">
            <BackBtn/>
            <span class="page-kicker">СПРАВОЧНИК</span>
        </div>

        <header class="page-header">
            <div class="header-icon" aria-hidden="true">
                <i class="fa-solid fa-truck-field"></i>
            </div>
            <div class="header-copy">
                <h1>Поставщики</h1>
                <p>Управление поставщиками и контактами</p>
            </div>
            <div class="header-badge">
                <i class="fa-solid fa-address-book"></i>
                <span>Справочник</span>
            </div>
        </header>

        <section class="without-percent-card">
            <div class="without-percent-info">
                <div class="without-percent-icon">
                    <i class="fa-solid fa-percent"></i>
                </div>

                <div>
                    <strong>Поставщики без процента</strong>
                    <p>Поставщики, для которых не настроен размер отчислений</p>
                </div>
            </div>

            <button
                type="button"
                class="without-percent-btn"
                @click="openWithoutPercentModal"
            >
        <span v-if="loadingWithoutPercent">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </span>
                <template v-else>
                    {{ withoutPercentCount }}
                    <i class="fa-solid fa-arrow-right"></i>
                </template>
            </button>
        </section>

        <section class="suppliers-content" aria-label="Список поставщиков">
            <div class="section-heading">
                <div>
                    <h2>Список поставщиков</h2>
                    <p>Просматривайте и редактируйте данные контрагентов</p>
                </div>
                <span class="section-symbol"><i class="fa-solid fa-users"></i></span>
            </div>

            <SupplierList ref="supplierList"/>
        </section>

        <nav class="bottom-action" aria-label="Действия с поставщиками">
            <div class="bottom-action-inner">
                <div class="bottom-action-note">
                    <span class="note-icon"><i class="fa-solid fa-plus"></i></span>
                    <span>
            <strong>Новый поставщик</strong>
            <small>Добавить запись в справочник</small>
          </span>
                </div>
                <button @click="addSupplier" type="button" class="add-supplier-btn">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Добавить поставщика</span>
                </button>
            </div>
        </nav>
    </div>

    <!-- Модальное окно добавления поставщика -->
    <div class="modal fade supplier-modal" id="newSupplierModal" tabindex="-1" aria-labelledby="newSupplierModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-title-wrap">
                        <span class="modal-title-icon"><i class="fa-solid fa-truck-field"></i></span>
                        <div>
                            <h5 class="modal-title" id="newSupplierModalLabel">Добавление поставщика</h5>
                            <p>Заполните информацию о новом контрагенте</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>
                <div class="modal-body">
                    <SupplierForm @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Список поставщиков без процента -->
    <div
        class="modal fade"
        id="withoutPercentModal"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">
                            <i class="fa-solid fa-percent text-primary me-2"></i>
                            Поставщики без процента
                        </h5>
                        <small class="text-muted">
                            Найдено: {{ withoutPercentCount }}
                        </small>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body">
                    <div v-if="loadingWithoutPercent" class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                    </div>

                    <div
                        v-else-if="withoutPercentSuppliers.length === 0"
                        class="text-center text-muted py-4"
                    >
                        <i class="fa-solid fa-circle-check fs-1 text-success mb-3"></i>
                        <p class="mb-0">Все поставщики имеют настроенный процент.</p>
                    </div>

                    <div v-else class="list-group">
                        <div
                            v-for="supplier in withoutPercentSuppliers"
                            :key="supplier.id"
                            class="list-group-item d-flex align-items-center justify-content-between gap-3"
                        >
                            <div class="min-width-0">
                                <div class="fw-bold">{{ supplier.name }}</div>
                                <small class="text-muted">
                                    {{ supplier.phone || supplier.email || 'Контакты не указаны' }}
                                </small>
                            </div>

                            <button
                                type="button"
                                class="btn btn-primary btn-sm flex-shrink-0"
                                @click="openSupplierPercentEditor(supplier)"
                            >
                                <i class="fa-solid fa-pen me-1"></i>
                                Настроить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Редактирование процента поставщика -->
    <div
        class="modal fade"
        id="editSupplierPercentModal"
        tabindex="-1"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Настройка процента поставщика</h5>
                        <small class="text-muted">
                            {{ selectedSupplier?.name }}
                        </small>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body">
                    <SupplierForm
                        v-if="selectedSupplier"
                        :initial-data="selectedSupplier"
                        @saved="onSupplierPercentSaved"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            withoutPercentCount: 0,
            withoutPercentSuppliers: [],
            loadingWithoutPercent: false,
            selectedSupplier: null,
        };
    },

    mounted() {
        this.loadWithoutPercent();
    },

    methods: {
        addSupplier() {
            const modalElement = document.getElementById("newSupplierModal");

            if (modalElement) {
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            }
        },

        async fetchData() {
            const modalElement = document.getElementById("newSupplierModal");

            if (modalElement) {
                bootstrap.Modal.getInstance(modalElement)?.hide();
            }

            await this.refreshSupplierData();
        },

        async refreshSupplierData() {
            await Promise.all([
                this.loadWithoutPercent(),
                this.$refs.supplierList?.fetchData?.(),
            ]);
        },

        async loadWithoutPercent() {
            this.loadingWithoutPercent = true;

            try {
                const response = await axios.get('/api/suppliers/without-percent');

                this.withoutPercentSuppliers = response.data.data || [];
                this.withoutPercentCount = Number(response.data.count || 0);
            } catch (error) {
                console.error(
                    'Ошибка загрузки поставщиков без процента:',
                    error
                );
            } finally {
                this.loadingWithoutPercent = false;
            }
        },

        async openWithoutPercentModal() {
            await this.loadWithoutPercent();

            this.$nextTick(() => {
                const element = document.getElementById('withoutPercentModal');

                if (element) {
                    bootstrap.Modal.getOrCreateInstance(element).show();
                }
            });
        },

        openSupplierPercentEditor(supplier) {
            if (!supplier?.id) return;

            this.selectedSupplier = { ...supplier };

            this.$nextTick(() => {
                const listModal = document.getElementById('withoutPercentModal');
                const editModal = document.getElementById('editSupplierPercentModal');

                const showEditor = () => {
                    if (editModal) {
                        bootstrap.Modal.getOrCreateInstance(editModal).show();
                    }
                };

                if (listModal?.classList.contains('show')) {
                    listModal.addEventListener(
                        'hidden.bs.modal',
                        showEditor,
                        { once: true }
                    );

                    bootstrap.Modal.getInstance(listModal)?.hide();
                } else {
                    showEditor();
                }
            });
        },

        async onSupplierPercentSaved() {
            const editModal = document.getElementById('editSupplierPercentModal');

            if (editModal) {
                bootstrap.Modal.getInstance(editModal)?.hide();
            }

            await this.refreshSupplierData();

            this.selectedSupplier = null;
        },
    },
};
</script>

<style scoped>
.suppliers-page {
    --supplier-blue: #2458d3;
    --supplier-blue-dark: #173f9e;
    --supplier-red: #e34b55;
    --supplier-ink: #18243b;
    --supplier-muted: #758198;
    --supplier-border: #e8edf5;
    min-height: 100%;
    padding-bottom: 118px !important;
    color: var(--supplier-ink);
    background: #f5f7fb;
}

.suppliers-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    min-height: 42px;
    margin-bottom: 18px;
}

.page-kicker {
    color: #8b96aa;
    font-size: 0.7rem;
    font-weight: 800;
    letter-spacing: 0.14em;
}

.page-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px;
    margin-bottom: 22px;
    overflow: hidden;
    position: relative;
    border: 1px solid #e7ecf6;
    border-radius: 22px;
    background: linear-gradient(120deg, #ffffff 0%, #f0f5ff 100%);
    box-shadow: 0 8px 28px rgba(28, 53, 102, 0.055);
}

.page-header::after {
    content: "";
    position: absolute;
    width: 170px;
    height: 170px;
    right: -50px;
    top: -85px;
    border-radius: 50%;
    background: rgba(36, 88, 211, 0.055);
    pointer-events: none;
}

.header-icon,
.section-symbol,
.note-icon,
.modal-title-icon {
    display: grid;
    flex: 0 0 auto;
    place-items: center;
}

.header-icon {
    width: 62px;
    height: 62px;
    border-radius: 19px;
    color: #fff;
    background: linear-gradient(145deg, var(--supplier-blue), #5484f1);
    box-shadow: 0 9px 20px rgba(36, 88, 211, 0.22);
    font-size: 1.55rem;
}

.header-copy {
    min-width: 0;
    position: relative;
    z-index: 1;
}

.header-copy h1 {
    margin: 0 0 5px;
    color: var(--supplier-ink);
    font-size: clamp(1.45rem, 3vw, 2rem);
    font-weight: 800;
    letter-spacing: -0.035em;
}

.header-copy p,
.section-heading p,
.modal-title-wrap p {
    margin: 0;
    color: var(--supplier-muted);
    font-size: 0.9rem;
}

.header-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    padding: 10px 13px;
    border: 1px solid #dce6fb;
    border-radius: 12px;
    color: var(--supplier-blue-dark);
    background: rgba(255, 255, 255, 0.8);
    font-size: 0.78rem;
    font-weight: 700;
    white-space: nowrap;
    position: relative;
    z-index: 1;
}

.suppliers-content {
    padding: 22px;
    border: 1px solid var(--supplier-border);
    border-radius: 22px;
    background: #fff;
    box-shadow: 0 8px 28px rgba(28, 53, 102, 0.045);
}

.section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}

.section-heading h2 {
    margin: 0 0 5px;
    font-size: 1.15rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.section-symbol {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    color: var(--supplier-blue);
    background: #edf3ff;
    font-size: 1.1rem;
}

.bottom-action {
    position: fixed;
    z-index: 1020;
    right: 0;
    bottom: 0;
    left: 0;
    padding: 14px 16px calc(14px + env(safe-area-inset-bottom));
    border-top: 1px solid rgba(226, 232, 243, 0.9);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 -8px 28px rgba(28, 43, 75, 0.07);
    backdrop-filter: blur(14px);
}

.bottom-action-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    width: 100%;
    max-width: 1440px;
    margin: 0 auto;
}

.bottom-action-note {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.note-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    color: var(--supplier-red);
    background: #fff0f1;
}

.bottom-action-note strong,
.bottom-action-note small {
    display: block;
}

.bottom-action-note strong {
    font-size: 0.87rem;
    font-weight: 800;
}

.bottom-action-note small {
    margin-top: 2px;
    color: var(--supplier-muted);
    font-size: 0.75rem;
}

.add-supplier-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 48px;
    padding: 12px 22px;
    border: 0;
    border-radius: 14px;
    color: #fff;
    background: linear-gradient(120deg, var(--supplier-blue), #3e73e7);
    box-shadow: 0 7px 17px rgba(36, 88, 211, 0.22);
    font-size: 0.9rem;
    font-weight: 750;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.add-supplier-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(36, 88, 211, 0.28);
}

.add-supplier-btn:focus-visible {
    outline: 3px solid rgba(36, 88, 211, 0.25);
    outline-offset: 3px;
}

.supplier-modal .modal-content {
    border: 0;
    background: #f5f7fb;
}

.supplier-modal .modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 22px clamp(18px, 4vw, 42px);
    border-bottom: 1px solid var(--supplier-border);
    background: #fff;
}

.modal-title-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
}

.modal-title-icon {
    width: 48px;
    height: 48px;
    border-radius: 15px;
    color: var(--supplier-blue);
    background: #edf3ff;
    font-size: 1.2rem;
}

.supplier-modal .modal-title {
    margin-bottom: 4px;
    color: var(--supplier-ink);
    font-size: 1.15rem;
    font-weight: 800;
}

.supplier-modal .modal-body {
    padding: clamp(18px, 4vw, 36px);
}

@media (max-width: 600px) {
    .suppliers-page {
        padding-right: 12px !important;
        padding-left: 12px !important;
    }

    .page-header {
        align-items: flex-start;
        gap: 12px;
        padding: 18px;
        border-radius: 18px;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        border-radius: 15px;
        font-size: 1.2rem;
    }

    .header-copy p {
        font-size: 0.82rem;
    }

    .header-badge {
        display: none;
    }

    .suppliers-content {
        padding: 16px 13px;
        border-radius: 18px;
    }

    .bottom-action {
        padding-right: 12px;
        padding-left: 12px;
    }

    .bottom-action-note {
        display: none;
    }

    .add-supplier-btn {
        width: 100%;
        min-height: 50px;
    }

    .modal-title-wrap p {
        font-size: 0.78rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .add-supplier-btn {
        transition: none;
    }
}

.without-percent-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 17px 20px;
    margin-bottom: 22px;
    border: 1px solid #f2dfe1;
    border-radius: 18px;
    background: linear-gradient(120deg, #fff 0%, #fff5f5 100%);
    box-shadow: 0 6px 20px rgba(28, 53, 102, 0.04);
}

.without-percent-info {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.without-percent-icon {
    display: grid;
    place-items: center;
    flex: 0 0 46px;
    width: 46px;
    height: 46px;
    border-radius: 14px;
    color: #d94752;
    background: #ffeaec;
    font-size: 1.2rem;
}

.without-percent-info strong {
    display: block;
    color: #18243b;
    font-size: 0.95rem;
    font-weight: 800;
}

.without-percent-info p {
    margin: 3px 0 0;
    color: #758198;
    font-size: 0.8rem;
}

.without-percent-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    min-width: 70px;
    min-height: 42px;
    padding: 9px 15px;
    border: 0;
    border-radius: 12px;
    color: #fff;
    background: linear-gradient(120deg, #df4652, #f06b73);
    font-weight: 800;
    cursor: pointer;
    transition: transform 0.18s ease;
}

.without-percent-btn:hover {
    transform: translateY(-2px);
}

@media (max-width: 600px) {
    .without-percent-card {
        padding: 14px;
    }

    .without-percent-info p {
        font-size: 0.72rem;
    }
}
</style>
