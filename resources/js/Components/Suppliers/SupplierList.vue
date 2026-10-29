<template>
    <div class="supplier-list-redesign">
        <div class="supplier-toolbar">
            <div class="supplier-toolbar-title">
                <span class="supplier-toolbar-icon"><i class="fa-solid fa-truck-field"></i></span>
                <div>
                    <strong>Поставщики</strong>
                    <small>Каталог, активность и распределение доходов</small>
                </div>
            </div>
            <button
                v-if="!forSelect"
                type="button"
                class="supplier-create-btn"
                @click="showSimpleSupplierForm = !showSimpleSupplierForm"
            >
                <i :class="showSimpleSupplierForm ? 'fa-solid fa-list' : 'fa-solid fa-plus'"></i>
                <span>{{ showSimpleSupplierForm ? 'К списку' : 'Добавить' }}</span>
            </button>
        </div>
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
                    class="list-group-item"
                    v-bind:class="{'border-primary': selection.indexOf(supplier.id)!==-1}"
                >
                    <!-- Левая часть -->
                    <div class="flex-grow-1 text-break" @click="selectSupplier(supplier)">
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
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
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
                        <!-- Нижняя часть: кнопки (прижаты к низу) -->
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2">


                            <!-- 🔹 Кнопка открытия нижней модалки -->
                            <button
                                type="button"
                                class="btn btn-sm actions-btn"
                                @click.stop="openActionsModal(supplier)"
                                title="Действия"
                            >
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
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
    </div>

    <!-- 🔹 Нижняя модалка с действиями -->
    <div class="modal fade bottom-sheet-modal" id="supplierActionsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-bottom">
            <div class="modal-content">
                <!-- Ручка для перетаскивания (визуальный элемент) -->
                <div class="bottom-sheet-handle">
                    <div class="handle-bar"></div>
                </div>

                <!-- Заголовок с информацией о поставщике -->
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3 w-100">
                        <div class="supplier-avatar">
                            <i class="fa-solid fa-truck"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <h6 class="modal-title mb-0 text-truncate">
                                {{ selectedSupplier?.name }}
                            </h6>
                            <small class="text-muted" v-if="selectedSupplier?.phone">
                                <i class="fa-solid fa-phone me-1"></i>
                                {{ selectedSupplier.phone }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>

                <!-- Пункты меню -->
                <div class="modal-body pt-3">
                    <div class="actions-list">
                        <!-- Доход за месяц -->
                        <button
                            v-if="!forSelect"
                            class="action-item action-item-highlight"
                            @click="handleAction('salary')"
                        >
                        <span class="action-icon salary-icon">
                            <i class="fa-solid fa-wallet"></i>
                        </span>
                            <div class="action-content">
                                <div class="action-title">Доход за месяц</div>
                                <div class="action-desc">Начисления, проценты и сделки</div>
                            </div>
                            <i class="fa-solid fa-chevron-right action-arrow"></i>
                        </button>

                        <!-- Избранное -->
                        <button
                            class="action-item"
                            @click="handleAction('favorite')"
                            :disabled="!user"
                        >
                        <span class="action-icon favorite-icon">
                            <i :class="favorites.includes(selectedSupplier?.id)
                                ? 'fa-solid fa-star'
                                : 'fa-regular fa-star'"></i>
                        </span>
                            <div class="action-content">
                                <div class="action-title">
                                    {{ favorites.includes(selectedSupplier?.id)
                                    ? 'Убрать из избранного'
                                    : 'Добавить в избранное' }}
                                </div>
                                <div class="action-desc">Быстрый доступ к поставщику</div>
                            </div>
                        </button>

                        <!-- Редактировать -->
                        <button
                            v-if="!forSelect"
                            class="action-item"
                            @click="handleAction('edit')"
                        >
                        <span class="action-icon edit-icon">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </span>
                            <div class="action-content">
                                <div class="action-title">Редактировать</div>
                                <div class="action-desc">Изменить данные поставщика</div>
                            </div>
                        </button>

                        <!-- Проценты -->
                        <button
                            v-if="!forSelect"
                            class="action-item"
                            @click="handleAction('percentages')"
                        >
                        <span class="action-icon percent-icon">
                            <i class="fa-solid fa-percent"></i>
                        </span>
                            <div class="action-content">
                                <div class="action-title">Управление процентами</div>
                                <div class="action-desc">Настройка распределения доходов</div>
                            </div>
                        </button>

                        <!-- Выбрать (режим forSelect) -->
                        <button
                            v-if="forSelect"
                            class="action-item action-item-primary"
                            @click="handleAction('select')"
                        >
                        <span class="action-icon select-icon">
                            <i class="fa-solid fa-check"></i>
                        </span>
                            <div class="action-content">
                                <div class="action-title">Выбрать поставщика</div>
                                <div class="action-desc">Использовать этого поставщика</div>
                            </div>
                        </button>

                        <!-- Удалить (только для админов) -->
                        <button
                            v-if="!forSelect && (user?.role || 0) >= 3"
                            class="action-item action-item-danger"
                            @click="handleAction('delete')"
                        >
                        <span class="action-icon delete-icon">
                            <i class="fa-solid fa-trash"></i>
                        </span>
                            <div class="action-content">
                                <div class="action-title">Удалить</div>
                                <div class="action-desc">Удалить поставщика из системы</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Кнопка закрытия -->
                <div class="modal-footer border-0 pt-0">
                    <button
                        type="button"
                        class="btn btn-light w-100"
                        data-bs-dismiss="modal"
                    >
                        Отмена
                    </button>
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

        openActionsModal(supplier) {
            this.selectedSupplier = {...supplier}
            this.$nextTick(() => {
                const modal = new bootstrap.Modal(document.getElementById('supplierActionsModal'))
                modal.show()
            })
        },
        openSupplierSalary(supplier) {
            if (!supplier?.id) return

            this.$router.push({
                name: 'supplier-salary',
                params: { supplierId: supplier.id }
            })
        },

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
        handleAction(action) {
            // Закрываем модалку
            const modalEl = document.getElementById('supplierActionsModal')
            const modal = bootstrap.Modal.getInstance(modalEl)
            modal?.hide()

            // Выполняем действие после закрытия
            setTimeout(() => {
                switch (action) {
                    case 'salary':
                        this.openSupplierSalary(this.selectedSupplier)
                        break
                    case 'favorite':
                        this.toggleFavorites(this.selectedSupplier.id)
                        break
                    case 'edit':
                        this.openEditModal(this.selectedSupplier)
                        break
                    case 'percentages':
                        this.openPercentages(this.selectedSupplier)
                        break
                    case 'select':
                        this.selectSupplier(this.selectedSupplier)
                        break
                    case 'delete':
                        this.openDeleteModal(this.selectedSupplier)
                        break
                }
            }, 300)
        },
    }
}
</script>

<style scoped>
.supplier-list-redesign {
    --supplier-blue: #2056b4;
    --supplier-ink: #172d50;
    --supplier-muted: #7b8aa1;
    color: var(--supplier-ink);
    padding: 8px 0 24px;
}
.supplier-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}
.supplier-toolbar-title {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}
.supplier-toolbar-icon {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: #eaf1ff;
    color: var(--supplier-blue);
    font-size: 20px;
}
.supplier-toolbar-title strong {
    display: block;
    font-size: 19px;
    letter-spacing: -.3px;
}
.supplier-toolbar-title small {
    display: block;
    margin-top: 2px;
    color: var(--supplier-muted);
    font-size: 12px;
}
.supplier-create-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 42px;
    padding: 0 14px;
    border: 0;
    border-radius: 12px;
    background: var(--supplier-blue);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
    box-shadow: 0 5px 14px #2056b426;
    transition: transform .18s ease, background .18s ease;
}
.supplier-create-btn:hover { background: #174693; transform: translateY(-1px); }

@media (max-width: 575.98px) {
    .supplier-toolbar-title strong { font-size: 17px; }
    .supplier-toolbar-title small { max-width: 200px; }
    .supplier-create-btn { min-height: 38px; padding: 0 11px; }
    .supplier-create-btn span { font-size: 12px; }
}
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

:deep(.list-group) {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    list-style: none;
    padding: 0;
    margin: 0;
}


/* Планшет: 2 колонки */
@media (max-width: 1199.98px) {
    :deep(.list-group) {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Мобильные: 1 колонка */
@media (max-width: 767.98px) {
    :deep(.list-group) {
        grid-template-columns: 1fr;
    }
}

/* Карточки одинаковой высоты */
:deep(.list-group-item) {
    display: flex;
    flex-direction: column;
    height: 100%;
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
    transform: translateY(-2px);
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

/* 🔹 Кнопка действий (заменяет dropdown) */
.actions-btn {
    width: 36px;
    height: 36px;
    border: 1px solid #e3eaf3;
    border-radius: 10px;
    background: #fff;
    color: #526780;
    transition: all 0.2s ease;
}

.actions-btn:hover {
    border-color: #b7d1f2;
    background: #f2f7ff;
    color: #1769d2;
}

/* 🔹 Нижняя модалка (bottom sheet) */
.bottom-sheet-modal {
    --bs-modal-bg: #fff;
}

.bottom-sheet-modal .modal-dialog {
    margin: 0;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    max-width: 100%;
    height: auto;
    transform: translateY(100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.bottom-sheet-modal.show .modal-dialog {
    transform: translateY(0);
}

.bottom-sheet-modal .modal-content {
    border-radius: 24px 24px 0 0 !important;
    border: none;
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
    max-height: 85vh;
    overflow-y: auto;
}

/* На десктопе — модалка по центру, но с закруглёнными углами */
@media (min-width: 768px) {
    .bottom-sheet-modal .modal-dialog {
        position: fixed;
        bottom: 20px;
        left: 50%;
        right: auto;
        transform: translateX(-50%) translateY(100%);
        max-width: 500px;
        width: 100%;
    }

    .bottom-sheet-modal.show .modal-dialog {
        transform: translateX(-50%) translateY(0);
    }

    .bottom-sheet-modal .modal-content {
        border-radius: 24px !important;
        max-height: 80vh;
    }
}

/* 🔹 Ручка для перетаскивания */
.bottom-sheet-handle {
    display: flex;
    justify-content: center;
    padding: 12px 0 4px;
    cursor: grab;
}

.handle-bar {
    width: 40px;
    height: 4px;
    background: #d1d9e6;
    border-radius: 2px;
    transition: background 0.2s ease;
}

.bottom-sheet-handle:hover .handle-bar {
    background: #a8b5c9;
}

/* 🔹 Аватар поставщика в модалке */
.supplier-avatar {
    width: 48px;
    height: 48px;
    flex: 0 0 48px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: linear-gradient(135deg, #eaf1ff, #dceaff);
    color: #2056b4;
    font-size: 20px;
}

.min-width-0 {
    min-width: 0;
}

/* 🔹 Список действий */
.actions-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.action-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border: none;
    border-radius: 14px;
    background: #f7faff;
    color: #20334f;
    text-align: left;
    width: 100%;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.action-item:hover {
    background: #edf4ff;
    transform: translateX(2px);
}

.action-item:active {
    transform: scale(0.98);
}

.action-item:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Подсветка главного действия (доход за месяц) */
.action-item-highlight {
    background: linear-gradient(135deg, #e8f1ff, #f0f7ff);
    border: 1px solid #d4e7ff;
}

.action-item-highlight:hover {
    background: linear-gradient(135deg, #dceaff, #e8f1ff);
}

/* Основное действие (выбрать) */
.action-item-primary {
    background: linear-gradient(135deg, #e1f7ed, #f0fff5);
    border: 1px solid #b8e6cc;
}

.action-item-primary:hover {
    background: linear-gradient(135deg, #d1f0e0, #e1f7ed);
}

/* Опасное действие (удалить) */
.action-item-danger {
    background: #fff5f5;
    border: 1px solid #ffd4d4;
}

.action-item-danger:hover {
    background: #ffe8e8;
}

.action-item-danger .action-title {
    color: #c53030;
}

.action-item-danger .action-icon {
    background: #fee;
    color: #c53030;
}

/* 🔹 Иконки действий */
.action-icon {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: #e8f1ff;
    color: #2056b4;
    font-size: 18px;
}

.action-icon.salary-icon {
    background: linear-gradient(135deg, #dceaff, #e8f1ff);
    color: #1769d2;
}

.action-icon.favorite-icon {
    background: #fff0f0;
    color: #e53e3e;
}

.action-icon.edit-icon {
    background: #e8f1ff;
    color: #2056b4;
}

.action-icon.percent-icon {
    background: #e8f5e9;
    color: #2e7d32;
}

.action-icon.select-icon {
    background: #d1f0e0;
    color: #16794b;
}

.action-icon.delete-icon {
    background: #fee;
    color: #c53030;
}

/* 🔹 Контент действия */
.action-content {
    flex: 1;
    min-width: 0;
}

.action-title {
    font-size: 14px;
    font-weight: 650;
    color: #172f4f;
    line-height: 1.3;
    margin-bottom: 2px;
}

.action-desc {
    font-size: 12px;
    color: #647994;
    line-height: 1.3;
}

/* Стрелка справа */
.action-arrow {
    color: #a8b5c9;
    font-size: 12px;
    margin-left: auto;
}

.action-item:hover .action-arrow {
    color: #2056b4;
    transform: translateX(2px);
    transition: transform 0.2s ease;
}

/* 🔹 Кнопка отмены */
.bottom-sheet-modal .modal-footer .btn-light {
    background: #f4f7fc;
    border: 1px solid #e0e9f5;
    color: #536781;
    font-weight: 600;
    padding: 12px;
    border-radius: 12px;
    transition: all 0.2s ease;
}

.bottom-sheet-modal .modal-footer .btn-light:hover {
    background: #e8f0fc;
    color: #145fc1;
}

/* 🔹 Адаптив для мобильных */
@media (max-width: 767.98px) {
    .bottom-sheet-modal .modal-content {
        max-height: 90vh;
    }

    .action-item {
        padding: 12px 14px;
        gap: 12px;
    }

    .action-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        font-size: 16px;
    }

    .action-title {
        font-size: 13px;
    }

    .action-desc {
        font-size: 11px;
    }

    .supplier-avatar {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        font-size: 18px;
    }
}
</style>
