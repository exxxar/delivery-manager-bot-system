<template>
    <div>
        <h5 class="mb-3">
            <i class="fa-solid fa-truck text-success me-2"></i>
            Мои доставки
        </h5>

        <!-- 🔹 Табы -->
        <div class="custom-tabs mb-3">
            <button
                type="button"
                class="custom-tab"
                :class="{ active: currentStatus === 'all' }"
                @click="switchStatus('all')"
            >
                <i class="fa-solid fa-list"></i>
                <span>Все</span>
            </button>

            <button
                type="button"
                class="custom-tab"
                :class="{ active: currentStatus === 'completed' }"
                @click="switchStatus('completed')"
            >
                <i class="fa-solid fa-circle-check text-success"></i>
                <span>Завершённые</span>
            </button>

            <button
                type="button"
                class="custom-tab"
                :class="{ active: currentStatus === 'pending' }"
                @click="switchStatus('pending')"
            >
                <i class="fa-solid fa-clock text-warning"></i>
                <span>В работе</span>
                <span v-if="salesStore.myDeliveriesStats?.pending_count > 0" class="tab-badge">
                    {{ salesStore.myDeliveriesStats.pending_count }}
                </span>
            </button>
        </div>

        <!-- 🔹 Селектор месяца -->
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <div class="form-floating">
                    <select
                        class="form-select"
                        v-model="selectedMonth"
                        @change="loadData(1)"
                        id="monthSelect"
                    >
                        <option v-for="m in getMonthList()" :key="m.key" :value="m.key">
                            {{ m.label }}
                        </option>
                    </select>
                    <label for="monthSelect">
                        <i class="fa-solid fa-calendar-days me-1"></i>
                        Месяц
                    </label>
                </div>
            </div>
        </div>

        <!-- 🔹 Статистика -->
        <div v-if="salesStore.myDeliveriesStats" class="alert alert-success mb-3 py-2">
            <div class="row text-center small">
                <div class="col-4">
                    <div class="text-muted">Доставок</div>
                    <div class="fw-bold fs-5">{{ salesStore.myDeliveriesStats.total_deliveries }}</div>
                </div>
                <div class="col-4">
                    <div class="text-muted">Оборот</div>
                    <div class="fw-bold fs-5 text-success">
                        {{ formatMoney(salesStore.myDeliveriesStats.total_sum) }}
                    </div>
                </div>
                <div class="col-4">
                    <div class="text-muted">В работе</div>
                    <div class="fw-bold fs-5 text-warning">
                        {{ salesStore.myDeliveriesStats.pending_count }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 🔹 Загрузка -->
        <div v-if="salesStore.myDeliveriesLoading" class="text-center my-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
        </div>

        <template v-else>
            <!-- 🔹 Список доставок -->
            <div v-if="salesStore.myDeliveries.length > 0" class="container-fluid px-0">
                <div class="row g-2">
                    <div
                        v-for="sale in salesStore.myDeliveries"
                        :key="sale.id"
                        class="col-12 col-md-6"
                    >
                        <div
                            class="card shadow-sm h-100 sale-card position-relative"
                            :class="{
                                'bg-danger-subtle': sale.status === 'completed' && !sale.actual_delivery_date
                            }"
                        >
                            <!-- Dropdown -->
                            <div class="dropdown position-absolute top-0 end-0 m-2 z-3">
                                <button
                                    class="btn btn-sm btn-light border"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                >
                                    <i class="fas fa-bars text-primary"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a
                                            class="dropdown-item"
                                            href="javascript:void(0)"
                                            @click.prevent="openView(sale)"
                                        >
                                            <i class="fa-solid fa-eye me-2"></i>
                                            Просмотреть
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <div class="card-body">
                                <SaleCard
                                    :sale="sale"
                                    :field_visible="field_visible"
                                    :saleStatuses="saleStatuses"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔹 Пагинация -->
            <Pagination
                v-if="salesStore.myDeliveriesPagination?.total > 0"
                :pagination="salesStore.myDeliveriesPagination"
                @page-changed="fetchPage"
            />

            <!-- 🔹 Пусто -->
            <div v-if="salesStore.myDeliveries.length === 0" class="alert alert-info mt-3 text-center">
                <i class="fa-solid fa-inbox fs-3 mb-2 d-block"></i>
                <p class="mb-0">За выбранный период доставок не найдено</p>
            </div>
        </template>

        <!-- 🔹 Модалка просмотра -->
        <div class="modal fade" id="viewDeliveryModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Детали доставки</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <TaskCard
                            v-if="selectedSale"
                            :task="selectedSale"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Pagination from "@/Components/Pagination.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
import TaskCard from "@/Components/Sales/TaskCard.vue";
import { useSalesStore } from "@/stores/sales";

export default {
    name: 'SupplierMyDeliveries',
    components: {
        Pagination,
        SaleCard,
        TaskCard,
    },
    data() {
        return {
            salesStore: useSalesStore(),
            currentStatus: 'all',
            selectedMonth: this.getCurrentMonth(),
            selectedSale: null,
            field_visible: null,
            saleStatuses: {
                pending: "В ожидании",
                assigned: "Назначено",
                completed: "Завершено",
                rejected: "Отклонено",
                delivered: "Доставляется"
            },
        }
    },
    created() {
        this.loadData(1)
    },
    methods: {
        switchStatus(status) {
            if (this.currentStatus === status) return
            this.currentStatus = status
            this.loadData(1)
        },

        async loadData(page = 1) {
            await this.salesStore.fetchMyDeliveries(
                page,
                this.selectedMonth,
                this.currentStatus
            )
        },

        fetchPage(urlOrPage) {
            let page = 1
            if (typeof urlOrPage === 'number') {
                page = urlOrPage
            } else if (typeof urlOrPage === 'string') {
                const match = urlOrPage.match(/page=(\d+)/)
                page = match ? parseInt(match[1]) : 1
            }
            this.loadData(page)
        },

        getCurrentMonth() {
            const now = new Date()
            return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
        },

        getMonthList() {
            const months = []
            const now = new Date()
            for (let i = 0; i < 12; i++) {
                const date = new Date(now.getFullYear(), now.getMonth() - i, 1)
                const year = date.getFullYear()
                const month = String(date.getMonth() + 1).padStart(2, '0')
                const label = date.toLocaleDateString('ru-RU', { month: 'long', year: 'numeric' })
                months.push({ key: `${year}-${month}`, label })
            }
            return months
        },

        formatMoney(value) {
            return new Intl.NumberFormat('ru-RU').format(value || 0) + ' ₽'
        },

        openView(sale) {
            this.selectedSale = null
            this.$nextTick(() => {
                this.selectedSale = sale
                new bootstrap.Modal(document.getElementById('viewDeliveryModal')).show()
            })
        },
    },
}
</script>

<style scoped>
.sale-card {
    transition: transform 0.2s ease;
}

.sale-card:hover {
    transform: translateY(-2px);
}

.custom-tabs {
    display: flex;
    gap: 6px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 6px;
    border-radius: 14px;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
}

.custom-tab {
    flex: 1;
    padding: 12px 16px;
    border: none;
    background: transparent;
    border-radius: 10px;
    font-weight: 500;
    font-size: 14px;
    color: #6c757d;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    cursor: pointer;
}

.custom-tab.active {
    background: #ffffff;
    color: #198754;
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.15);
    font-weight: 600;
}

.custom-tab:hover:not(.active) {
    color: #198754;
    background: rgba(25, 135, 84, 0.06);
}

.tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: white;
    border-radius: 11px;
    font-size: 11px;
    font-weight: 700;
    margin-left: 4px;
}

@media (max-width: 576px) {
    .custom-tabs {
        gap: 4px;
        padding: 4px;
    }

    .custom-tab {
        padding: 10px 8px;
        font-size: 12px;
    }

    .custom-tab span {
        display: none;
    }
}
</style>
