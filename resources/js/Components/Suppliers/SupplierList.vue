<template>
    <div class="form-check form-switch mb-2">
        <input
            class="form-check-input"
            type="checkbox"
            v-model="showSimpleSupplierForm"
            :id="`show-simple-supplier-form`"
        />
        <label class="form-check-label" :for="`show-simple-supplier-form`">
            Добавить и выбрать нового поставщика
        </label>
    </div>

    <!-- 🔹 Табы режимов -->
    <ul class="nav nav-pills nav-fill mb-3">
        <li class="nav-item">
            <button
                type="button"
                class="nav-link"
                :class="{ active: viewMode === 'all' }"
                @click="switchMode('all')"
            >
                <i class="fa-solid fa-list me-1"></i>
                Все
            </button>
        </li>
        <li class="nav-item">
            <button
                type="button"
                class="nav-link"
                :class="{ active: viewMode === 'active' }"
                @click="switchMode('active')"
            >
                <i class="fa-solid fa-bolt text-success me-1"></i>
                Активные
            </button>
        </li>
        <li class="nav-item">
            <button
                type="button"
                class="nav-link"
                :class="{ active: viewMode === 'inactive' }"
                @click="switchMode('inactive')"
            >
                <i class="fa-solid fa-moon text-secondary me-1"></i>
                Неактивные
            </button>
        </li>
    </ul>

    <!-- 🔹 Селектор месяца (только для активных/неактивных) -->
    <div v-if="viewMode !== 'all'" class="form-floating mb-3">
        <select
            class="form-select"
            v-model="selectedMonth"
            @change="loadByMode"
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

    <!-- 🔹 Статистика (для активных/неактивных) -->
    <div v-if="viewMode !== 'all' && suppliersStore.stats" class="alert alert-info mb-3 py-2">
        <div class="row text-center small">
            <div class="col-6">
                <div class="text-muted">Поставщиков</div>
                <div class="fw-bold fs-5">{{ suppliersStore.stats.total_suppliers }}</div>
            </div>
            <div class="col-6" v-if="suppliersStore.stats.total_turnover !== undefined">
                <div class="text-muted">Товарооборот</div>
                <div class="fw-bold fs-5 text-success">{{ formatMoney(suppliersStore.stats.total_turnover) }}</div>
            </div>
        </div>
    </div>

    <template v-if="showSimpleSupplierForm">
        <SupplierForm
            v-on:saved="addNewSupplier"
            class="mb-2"
        ></SupplierForm>
    </template>

    <template v-if="!showSimpleSupplierForm">
        <div class="input-group mb-2">
            <div class="form-floating ">
                <input class="form-control"
                       type="search"
                       v-model="search"
                       id="supplierInput" placeholder="Поставщик"/>
                <label for="supplierInput">Поставщик</label>
            </div>

            <button
                @click="findSupplier"
                type="button"
                class="btn btn-outline-light text-primary find-btn"
            >
                Найти
            </button>
        </div>

        <template v-if="!forSelect">
            <SupplierFilter v-on:apply-filters="applyFilters"></SupplierFilter>

            <div class="d-flex" v-if="user && (user.role || 0) >= 3">
                <a href="javascript:void(0)"
                   @click="selectAll"
                   class="small">Выделить все</a>
                <template v-if="selection.length>0">
                    <a href="javascript:void(0)"
                       @click="removeAll"
                       class="small text-danger mx-2">Удалить выделенное</a>
                </template>
            </div>
        </template>

        <ul class="list-group">
            <li
                v-for="supplier in suppliersStore.items"
                :key="supplier.id"
                class="list-group-item d-flex justify-content-between align-items-start"
                v-bind:class="{'border-primary': selection.indexOf(supplier.id)!==-1}"
            >
                <!-- Левая часть -->
                <div class="flex-grow-1 me-3 text-break" @click="selectSupplier(supplier)">
                    <div class="fw-bold" @click="toggleSelection(supplier)">
                        <span class="badge bg-primary" v-if="field_visible?.id||false">#{{ supplier.id }}</span>
                        {{ supplier.name }}
                        <span
                            v-if="field_visible?.percent||false"
                            class="badge bg-primary rounded-pill">{{ supplier.percent }}%</span>
                    </div>

                    <p class="mb-0" v-if="field_visible?.description||true">{{ supplier.description || '-' }}</p>
                    <p class="mb-0 fw-bold small" v-if="field_visible?.phone||true">{{ supplier.phone }}</p>
                    <p class="mb-2" v-if="field_visible?.email||false">{{ supplier.email }}</p>
                    <p class="mb-2" v-if="field_visible?.address||false">{{ supplier.address }}</p>
                    <p class="mb-2" v-if="field_visible?.birthday||false">{{ supplier.birthday }}</p>

                    <!-- 🔹 Статистика для активных (чистая, без пометок избранного) -->
                    <template v-if="viewMode === 'active' && supplier.month_sales_count">
                        <div class="d-flex gap-2 mt-1">
            <span
                class="badge bg-success clickable-badge"
                @click.stop="openSupplierSales(supplier)"
                :title="`Посмотреть сделки поставщика за ${selectedMonth}`"
            >
                <i class="fa-solid fa-receipt me-1"></i>
                {{ supplier.month_sales_count }} сделок
                <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size: 10px;"></i>
            </span>
                            <span class="badge bg-info text-dark">
                <i class="fa-solid fa-money-bill-trend-up me-1"></i>
                {{ formatMoney(supplier.month_turnover) }}
            </span>
                        </div>
                    </template>

                    <!-- 🔹 Для неактивных (чистая, без пометок избранного) -->
                    <template v-if="viewMode === 'inactive'">
                        <div class="mt-1">
            <span class="badge bg-secondary">
                <i class="fa-solid fa-pause me-1"></i>
                Нет сделок за выбранный месяц
            </span>
                        </div>
                    </template>
                </div>

                <!-- Правая часть (меню) -->
                <div class="d-flex justify-content-between">
                    <button
                        type="button"
                        class="btn btn-sm"
                        @click="toggleFavorites(supplier.id)"
                        :disabled="!user"
                    >
                        <span v-if="!favorites.includes(supplier.id)">
                            <i class="fa-regular fa-star text-danger"></i>
                        </span>
                        <span v-else>
                            <i class="fa-solid fa-star text-danger"></i>
                        </span>
                    </button>
                    <div class="dropdown flex-shrink-0">
                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bars"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <template v-if="forSelect">
                                <li>
                                    <a class="dropdown-item" href="#" @click.prevent="selectSupplier(supplier)">Выбрать</a>
                                </li>
                            </template>
                            <template v-else>
                                <li>
                                    <a class="dropdown-item" href="#" @click.prevent="openEditModal(supplier)">Редактировать</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" @click.prevent="openPercentages(supplier)">
                                        <i class="fa-solid fa-percent me-2"></i>Проценты
                                    </a>
                                </li>
                                <li v-if="(user?.role || 0) >= 3">
                                    <a class="dropdown-item text-danger" href="#" @click.prevent="openDeleteModal(supplier)">Удалить</a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </li>
        </ul>

        <template v-if="suppliersStore.pagination?.total>0">
            <div class="form-floating my-2">
                <select
                    id="itemsPerPage"
                    class="form-select"
                    v-model="size"
                >
                    <option value="10">10</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                    <option value="5000">5000</option>
                </select>
                <label for="itemsPerPage">Элементов на странице</label>
            </div>
            <!-- Пагинация -->
            <Pagination
                :pagination="suppliersStore.pagination"
                @page-changed="fetchDataByUrl"
            />
        </template>

        <!-- Сообщение если список пуст -->
        <div v-if="suppliersStore.items?.length === 0" class="alert alert-info mt-3">
            Поставщиков пока нет.
        </div>
    </template>

    <!-- Модалка редактирования -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование поставщика</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SupplierForm
                        v-if="selectedSupplier"
                        :initial-data="selectedSupplier" @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка со сделками поставщика -->
    <div class="modal fade" id="supplierSalesModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-truck text-primary me-2"></i>
                        Сделки: <span class="text-primary">{{ selectedSupplier?.name }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Загрузка -->
                    <div v-if="suppliersStore.loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                    </div>

                    <template v-else>
                        <!-- Список сделок -->
                        <div v-if="suppliersStore.supplierSales.length > 0">
                            <SaleCard
                                v-for="sale in suppliersStore.supplierSales"
                                :key="sale.id"
                                :sale="sale"
                                :field_visible="field_visible"
                                :saleStatuses="saleStatuses"
                                class="shadow-sm mb-2 rounded-2 p-2"
                            />

                            <!-- Пагинация -->
                            <Pagination
                                v-if="suppliersStore.supplierSalesPagination?.total > 0"
                                :pagination="suppliersStore.supplierSalesPagination"
                                @page-changed="fetchSupplierSalesPage"
                            />
                        </div>

                        <!-- Пусто -->
                        <div v-else class="text-center text-muted py-5">
                            <i class="fa-solid fa-inbox fs-1 mb-3 d-block"></i>
                            <p class="mb-0">За выбранный период сделок не найдено</p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔹 Модалка управления процентами -->
    <div class="modal fade" id="percentagesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-percent text-success me-2"></i>
                        Управление процентами поставщика
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <PercentageManager
                        v-if="selectedSupplier"
                        owner-type="supplier"
                        :owner-id="selectedSupplier.id"
                        :owner-name="selectedSupplier.name"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Pagination from "@/Components/Pagination.vue";
import SupplierFilter from "@/Components/Suppliers/SupplierFilter.vue";
import SupplierForm from "@/Components/Suppliers/SupplierForm.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
import PercentageManager from "@/Components/Percentage/PercentageManager.vue";
import {useSuppliersStore} from "@/stores/suppliers";
import {useModalStore} from "@/stores/utillites/useConfitmModalStore";
import {useUsersStore} from "@/stores/users";
import {useAlertStore} from "@/stores/utillites/useAlertStore";

export default {
    name: 'SupplierListGroup',
    components: {
        Pagination,
        SupplierFilter,
        SupplierForm,
        SaleCard,
        PercentageManager
    },
    props: ["forSelect"],
    data() {
        return {
            field_visible: null,
            modalStore: useModalStore(),
            userStore: useUsersStore(),
            alertStore: useAlertStore(),
            selection: [],
            saleStatuses: {
                pending: "В ожидании",
                assigned: "Назначено",
                completed: "Завершено",
                rejected: "Отклонено",
                delivered: "Доставляется"
            },
            search: null,
            size: 30,
            showSimpleSupplierForm: false,
            selectedSupplier: null,
            suppliersStore: useSuppliersStore(),
            viewMode: 'all',
            selectedMonth: this.getCurrentMonth(),
        }
    },
    watch: {
        search: function () {
            this.findSupplier()
        },
        size: function () {
            this.loadByMode()
        }
    },
    computed: {
        favorites() {
            return this.user?.agent?.favorite_suppliers || []
        },
        user() {
            return this.userStore?.self || null
        }
    },
    created() {
        if (!this.userStore.self) {
            this.userStore.fetchSelf()
        }
        this.loadByMode()
    },
    methods: {
        async openSupplierSales(supplier) {
            this.selectedSupplier = supplier
            try {
                await this.suppliersStore.fetchSupplierSales(supplier.id, this.selectedMonth)
                new bootstrap.Modal(document.getElementById('supplierSalesModal')).show()
            } catch (e) {
                console.error('Ошибка загрузки сделок поставщика:', e)
                this.alertStore.show('Не удалось загрузить сделки поставщика', 'error')
            }
        },

        async fetchSupplierSalesPage(url) {
            const match = url.match(/page=(\d+)/)
            const page = match ? parseInt(match[1]) : 1
            if (this.selectedSupplier) {
                await this.suppliersStore.fetchSupplierSales(
                    this.selectedSupplier.id,
                    this.selectedMonth,
                    page
                )
            }
        },

        findSupplier() {
            this.suppliersStore.setFilters({
                name: this.search || ''
            })
            this.suppliersStore.setSort('id', 'desc')
            this.suppliersStore.fetchFiltered(0, this.size)
        },

        addNewSupplier(supplier) {
            this.$emit("select", supplier)
        },

        applyFilters(filters) {
            this.field_visible = filters.field_visible
            let size = filters.size || this.size || 30
            let page = filters.page || 0
            delete filters.field_visible
            this.suppliersStore.setFilters(filters.filters)
            this.suppliersStore.setSort(filters.sort.field, filters.sort.direction)
            this.suppliersStore.fetchFiltered(page, size)
        },

        toggleFavorites(id) {
            if (!this.user) {
                this.alertStore.show('Необходимо авторизоваться', 'warning')
                return
            }

            this.suppliersStore.toggleFavorites(id).then(() => {
                this.alertStore.show(
                    this.favorites.includes(id)
                        ? "Убираем из избранного"
                        : "Добавляем в избранное",
                    "success"
                )
                this.userStore.fetchSelf()
            })
        },

        removeAll() {
            this.modalStore.open(
                `Вы уверены, что хотите удалить все выбранные элементы?`,
                () => {
                    this.suppliersStore.removeAll(this.selection)
                    this.selection = []
                },
                () => {
                    this.modalStore.close()
                    this.selection = []
                }
            )
        },

        openEditModal(supplier) {
            this.selectedSupplier = null
            this.$nextTick(() => {
                this.selectedSupplier = supplier
                new bootstrap.Modal(document.getElementById("editSupplierModal")).show()
            })
        },

        openPercentages(supplier) {
            this.selectedSupplier = null
            this.$nextTick(() => {
                this.selectedSupplier = {...supplier}
                this.$nextTick(() => {
                    const modal = new bootstrap.Modal(document.getElementById('percentagesModal'))
                    modal.show()
                })
            })
        },

        toggleSelection(supplier) {
            if (this.forSelect) {
                this.selectSupplier(supplier)
                return
            }

            let id = supplier.id
            let index = this.selection.findIndex(i => i === id)
            if (index === -1)
                this.selection.push(id)
            else
                this.selection.splice(index, 1)

            this.$emit("select", this.selection)
        },

        selectAll() {
            if (this.selection.length === 0)
                this.suppliersStore.items.forEach(i => {
                    if (this.selection.indexOf(i.id) === -1)
                        this.selection.push(i.id)
                })
            else
                this.selection = []

            this.$emit("select", this.selection)
        },

        async fetchData(page = 0) {
            await this.suppliersStore.fetchAllByPage(page, this.size)
        },

        async fetchDataByUrl(url) {
            await this.suppliersStore.fetchByUrl(url)
        },

        selectSupplier(supplier) {
            if (!this.forSelect)
                return
            this.$emit("select", supplier)
        },

        openDeleteModal(supplier) {
            this.selectedSupplier = supplier
            this.modalStore.open(
                `Вы уверены, что хотите удалить ${this.selectedSupplier.name}?`,
                () => {
                    this.suppliersStore.remove(this.selectedSupplier.id)
                    this.selection = []
                    this.fetchData()
                },
                () => {
                    this.modalStore.close()
                    this.selection = []
                })
        },

        getCurrentMonth() {
            const now = new Date()
            const year = now.getFullYear()
            const month = String(now.getMonth() + 1).padStart(2, '0')
            return `${year}-${month}`
        },

        getMonthList() {
            const months = []
            const now = new Date()

            for (let i = 0; i < 12; i++) {
                const date = new Date(now.getFullYear(), now.getMonth() - i, 1)
                const year = date.getFullYear()
                const month = String(date.getMonth() + 1).padStart(2, '0')
                const key = `${year}-${month}`
                months.push({key, label: key})
            }

            return months
        },

        switchMode(mode) {
            this.viewMode = mode
            this.loadByMode()
        },

        async loadByMode(page = 1) {
            if (this.viewMode === 'active') {
                await this.suppliersStore.fetchActive(this.selectedMonth, page, this.size)
            } else if (this.viewMode === 'inactive') {
                await this.suppliersStore.fetchInactive(this.selectedMonth, page, this.size)
            } else {
                await this.fetchData(page)
            }
        },

        formatMoney(value) {
            return new Intl.NumberFormat('ru-RU').format(value || 0) + ' ₽'
        },
    }
}
</script>

<style scoped>
/* Общая визуальная система */
:deep(.form-check.form-switch) {
    display: flex;
    align-items: center;
    gap: .65rem;
    padding: .9rem 1rem .9rem 3.1rem;
    margin: 0 0 1rem;
    border: 1px solid #dbe7f5;
    border-radius: 16px;
    background: linear-gradient(135deg, #f7faff, #fff);
}
:deep(.form-check-input) {
    cursor: pointer;
    width: 2.5rem;
    height: 1.35rem;
    margin-left: -2.1rem;
    box-shadow: none;
}
:deep(.form-check-input:checked) {
    background-color: #1769d2;
    border-color: #1769d2;
}
:deep(.form-check-label) { color: #20334f; font-weight: 600; }

:deep(.nav.nav-pills) {
    display: flex;
    gap: .5rem;
    padding: .4rem;
    border: 1px solid #e0e9f5;
    border-radius: 16px;
    background: #f4f7fc;
}
:deep(.nav.nav-pills .nav-link) {
    border: 0;
    border-radius: 11px;
    color: #536781;
    font-weight: 650;
    padding: .75rem .85rem;
    transition: background .18s ease, color .18s ease, box-shadow .18s ease;
}
:deep(.nav.nav-pills .nav-link:hover) { background: #e8f0fc; color: #145fc1; }
:deep(.nav.nav-pills .nav-link.active) {
    color: #fff;
    background: linear-gradient(135deg, #1769d2, #124da5);
    box-shadow: 0 5px 14px rgba(23, 105, 210, .22);
}
:deep(.nav.nav-pills .nav-link .text-success),
:deep(.nav.nav-pills .nav-link .text-secondary) { color: inherit !important; }

:deep(.form-floating .form-select),
:deep(.form-floating .form-control) {
    min-height: 56px;
    border: 1px solid #d8e3f1;
    border-radius: 13px;
    color: #20334f;
    background-color: #fff;
    box-shadow: none;
}
:deep(.form-floating .form-select:focus),
:deep(.form-floating .form-control:focus) {
    border-color: #4a91e8;
    box-shadow: 0 0 0 3px rgba(23, 105, 210, .11);
}
:deep(.input-group) {
    padding: .35rem;
    border: 1px solid #dce6f2;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 4px 16px rgba(24, 53, 91, .04);
}
:deep(.input-group .form-floating) { flex: 1; min-width: 0; }
:deep(.input-group .form-control) { border: 0; border-radius: 10px; box-shadow: none; }
:deep(.find-btn) {
    align-self: stretch;
    margin-left: .35rem !important;
    padding: 0 1.2rem;
    border: 0;
    border-radius: 11px !important;
    color: #fff !important;
    background: #1769d2;
    font-weight: 700;
}
:deep(.find-btn:hover) { background: #1255b0; }

:deep(.alert-info) {
    border: 1px solid #d4e7ff;
    border-radius: 16px;
    color: #234366;
    background: linear-gradient(135deg, #f0f7ff, #fff);
}
:deep(.alert-info .text-muted) { color: #647994 !important; }
:deep(.alert-info .fs-5) { color: #173e70; font-weight: 800; }

:deep(.list-group) { display: grid; gap: .7rem; }
:deep(.list-group-item) {
    gap: .8rem;
    padding: 1rem;
    border: 1px solid #e0e8f2 !important;
    border-radius: 16px !important;
    background: #fff;
    color: #20334f;
    box-shadow: 0 3px 12px rgba(27, 52, 84, .035);
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}
:deep(.list-group-item:hover) {
    border-color: #b8d3f5 !important;
    box-shadow: 0 8px 22px rgba(27, 72, 128, .08);
}
:deep(.list-group-item.border-primary) {
    border-color: #4a91e8 !important;
    background: #f5f9ff;
    box-shadow: 0 0 0 2px rgba(23, 105, 210, .08);
}
:deep(.list-group-item .fw-bold) { color: #172f4f; line-height: 1.45; }
:deep(.list-group-item p) { color: #62748c; font-size: .92rem; overflow-wrap: anywhere; }
:deep(.list-group-item .badge) {
    display: inline-flex;
    align-items: center;
    gap: .15rem;
    padding: .42rem .62rem;
    border-radius: 8px;
    font-size: .75rem;
    font-weight: 700;
}
:deep(.list-group-item .bg-primary) { background: #e8f1ff !important; color: #1459ad !important; }
:deep(.list-group-item .bg-success) { background: #e1f7ed !important; color: #16794b !important; }
:deep(.list-group-item .bg-info) { background: #e4f5fb !important; color: #12627b !important; }
:deep(.list-group-item .bg-secondary) { background: #edf1f5 !important; color: #586a80 !important; }
:deep(.list-group-item .btn-sm) {
    display: inline-grid;
    place-items: center;
    width: 36px;
    height: 36px;
    border: 1px solid #e3eaf3;
    border-radius: 10px;
    color: #526780;
    background: #fff;
}
:deep(.list-group-item .btn-sm:hover) { border-color: #b7d1f2; background: #f2f7ff; }
:deep(.list-group-item .dropdown-menu) {
    padding: .4rem;
    border: 1px solid #e1e9f3;
    border-radius: 12px;
    box-shadow: 0 12px 30px rgba(22, 44, 75, .14);
}
:deep(.list-group-item .dropdown-item) { padding: .65rem .8rem; border-radius: 8px; }
:deep(.list-group-item .dropdown-item:hover) { background: #edf4ff; color: #1459ad; }
:deep(.small a), :deep(a.small) { font-weight: 600; text-decoration: none; }

:deep(#itemsPerPage) { border-radius: 12px; }
:deep(.pagination) { gap: .25rem; flex-wrap: wrap; }
:deep(.modal-content) { overflow: hidden; border: 1px solid #dfe8f3; border-radius: 18px; box-shadow: 0 20px 60px rgba(18, 38, 66, .2); }
:deep(.modal-header) { padding: 1.1rem 1.25rem; border-bottom: 1px solid #e6edf5; background: #f7faff; }
:deep(.modal-title) { color: #173455; font-weight: 750; }
:deep(.modal-body) { padding: 1.25rem; }
:deep(.clickable-badge) {
    cursor: pointer;
    transition: all .2s ease;
    display: inline-flex;
    align-items: center;
    position: relative;
    overflow: hidden;
}
:deep(.clickable-badge:hover) {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(25, 135, 84, .22);
    filter: brightness(1.03);
}
:deep(.clickable-badge:active) { transform: translateY(0); }

@media (max-width: 575.98px) {
    :deep(.nav.nav-pills) { gap: .2rem; padding: .3rem; }
    :deep(.nav.nav-pills .nav-link) { padding: .65rem .3rem; font-size: .84rem; }
    :deep(.list-group-item) { padding: .8rem; gap: .35rem; }
    :deep(.list-group-item .btn-sm) { width: 32px; height: 32px; }
    :deep(.input-group) { align-items: stretch; }
    :deep(.find-btn) { padding: 0 .8rem; font-size: .9rem; }
    :deep(.modal-body) { padding: .9rem; }
}
</style>
