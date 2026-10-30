<script setup>
import SaleForm from "@/Components/Sales/SaleForm.vue";
import Pagination from "@/Components/Pagination.vue";
import SaleFilterForm from '@/components/Sales/SaleFilterForm.vue'
import TaskCard from "@/Components/Sales/TaskCard.vue";
import DealForm from "@/Components/Sales/Forms/DealForm.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
</script>
<template>
    <div class="sales-page-content">

        <div class="sales-page">
            <header class="sales-page-header">
                <div class="sales-heading-icon">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <div class="sales-heading-copy">
                    <div class="sales-eyebrow">МОЙПРОЦЕНТ · УПРАВЛЕНИЕ</div>
                    <h1>Сделки и заявки</h1>
                    <p>Контроль продаж, доставки и незавершённых операций</p>
                </div>
                <div v-if="salesStore.pagination" class="sales-total-chip">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>{{ salesStore.pagination.total }} записей</span>
                </div>
            </header>

            <!-- 🔹 Красивые табы -->
            <div class="custom-tabs mb-3">
                <button
                    type="button"
                    class="custom-tab"
                    :class="{ active: currentTab === 'list' }"
                    @click="switchTab('list')"
                >
                    <i class="fa-solid fa-list"></i>
                    <span>Все сделки</span>
                </button>

                <button
                    type="button"
                    class="custom-tab"
                    :class="{ active: currentTab === 'timeline' }"
                    @click="switchTab('timeline')"
                >
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>По месяцам</span>
                </button>

                <button
                    type="button"
                    class="custom-tab"
                    :class="{ active: currentTab === 'incomplete' }"
                    @click="switchTab('incomplete')"
                >
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>Незавершённые</span>
                    <span v-if="salesStore.incompletePagination?.total > 0" class="tab-badge">
            {{ salesStore.incompletePagination.total }}
        </span>
                </button>
            </div>

            <div class="d-flex justify-content-between my-2">
                <div>
                    <a href="javascript:void(0)"
                       @click="selectAll"
                       style="font-size:12px;"
                       class="small fw-bold">Выделить все</a>
                    <template v-if="selection.length>0">
                        <a href="javascript:void(0)"
                           @click="acceptAll"
                           style="font-size:12px;"
                           class="small text-danger mx-2 fw-bold">Подтвердить заявки ({{ selection.length }})</a>

                    </template>
                </div>

                <span
                    class="fw-bold small "
                    v-if="salesStore.pagination">Всего {{ salesStore.pagination.total }} ед.</span>
            </div>

            <template v-if="filteredBadSales.length > 0">
                <h6 class="fw-bold my-3">
                    <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                    Заявки на проверку
                </h6>

                <!-- 🔹 Сетка: 1 колонка (моб.), 2 колонки (планшет), 3 колонки (десктоп) -->
                <div class="row g-2">
                    <div
                        v-for="sale in filteredBadSales"
                        :key="sale.id"
                        class="col-12 col-md-6 col-lg-4"
                    >
                        <!-- Обёртка для применения классов выделения и стилизации -->
                        <div
                            class="bad-sale-card-wrapper h-100"
                            :class="{
                    'border-primary border-3': selection.indexOf(sale.id) !== -1,
                    'bg-danger-subtle': sale.status === 'completed' && (!sale.sale_date || !sale.actual_delivery_date)
                }"
                        >
                            <SaleCard
                                :sale="sale"
                                :field_visible="field_visible"
                                :saleStatuses="saleStatuses"
                                @toggle-selection="toggleSelection"
                            >
                                <template #actions>
                                    <div class="sale-card__dropdown">
                                        <button
                                            class="sale-card__menu-button"
                                            type="button"
                                            aria-label="Действия со сделкой"
                                            @click.stop="openSaleActions(sale)"
                                        >
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                    </div>
                                </template>
                            </SaleCard>
                        </div>
                    </div>
                </div>

                <h6 class="fw-bold my-3">
                    <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                    Все заявки
                </h6>
            </template>

            <!-- 🔹 РЕЖИМ: СПИСОК (старый) -->
            <template v-if="currentTab === 'list'">
                <!-- Быстрый поиск -->
                <input v-model="search" type="text" class="form-control mb-2" placeholder="Поиск по названию...">

                <SaleFilterForm v-on:apply-filters="applyFilters"></SaleFilterForm>

                <div class="container-fluid px-0">
                    <div class="row g-2">

                        <div
                            v-for="sale in filteredSales"
                            :key="sale.id"
                            class="col-12 col-md-6 col-xxl-4"
                        >

                            <div
                                class="card shadow-sm h-100 sale-card position-relative"

                                :class="{
                    'border-primary border-3': selection.indexOf(sale.id)!==-1,
                    'bg-danger-subtle':
                        sale.status === 'completed' &&
                        (!sale.actual_delivery_date)
                }"
                            >


                                <!-- Контент карточки -->
                                <div class="card-body p-0">

                                    <SaleCard
                                        :sale="sale"
                                        :field_visible="field_visible"
                                        :saleStatuses="saleStatuses"
                                        @toggle-selection="toggleSelection">
                                        <template #actions>
                                            <div class="sale-card__dropdown">
                                                <button
                                                    class="sale-card__menu-button"
                                                    type="button"
                                                    aria-label="Действия со сделкой"
                                                    @click.stop="openSaleActions(sale)"
                                                >
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </SaleCard>

                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <Pagination
                    v-if="salesStore.items.length > 0"
                    :pagination="salesStore.pagination"
                    @page-changed="fetchDataByUrl"
                />

                <!-- Сообщение если список пуст -->
                <div v-if="salesStore.items.length === 0" class="alert alert-info mt-3">
                    На текущий момент у вас нет продаж.
                </div>

            </template>

            <!-- 🔹 РЕЖИМ: ТАЙМЛАЙН (новый) -->
            <template v-if="currentTab === 'timeline'">
                <!-- Селектор месяца -->
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-6">
                        <div class="form-floating">
                            <select
                                class="form-select"
                                v-model="selectedMonth"
                                @change="changeMonth"
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
                    <div class="col-12 col-md-6">
                        <div class="form-floating">
                            <select
                                class="form-select"
                                v-model="daysPerPage"
                                @change="loadMonthData(1)"
                                id="daysPerPageSelect"
                            >
                                <option :value="5">5 дней</option>
                                <option :value="7">7 дней</option>
                                <option :value="10">10 дней</option>
                                <option :value="15">15 дней</option>
                                <option :value="30">Весь месяц</option>
                            </select>
                            <label for="daysPerPageSelect">
                                <i class="fa-solid fa-calendar-week me-1"></i>
                                Дней на странице
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Статистика месяца -->
                <div v-if="salesStore.groupedMonth" class="alert alert-info month-stats mb-3">
                    <div class="month-stats__grid">

                        <div class="month-stats__item">
                            <div class="month-stats__label">Всего сделок</div>
                            <div class="month-stats__value">
                                {{ salesStore.groupedMonth.month_stats.total_sales }}
                            </div>
                        </div>

                        <div class="month-stats__item">
                            <div class="month-stats__label">Общая сумма</div>
                            <div class="month-stats__value month-stats__value--success">
                                {{ formatMoney(salesStore.groupedMonth.month_stats.total_sum) }}
                            </div>
                        </div>

                        <div class="month-stats__item">
                            <div class="month-stats__label">Средний чек</div>
                            <div class="month-stats__value">
                                {{ formatMoney(salesStore.groupedMonth.month_stats.avg_sum) }}
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Загрузка -->
                <div v-if="salesStore.loading" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>

                <!-- Дни -->
                <div v-else-if="salesStore.groupedMonth && salesStore.groupedMonth.days.length > 0">
                    <div v-for="day in salesStore.groupedMonth.days" :key="day.date" class="day-group mb-4">

                        <!-- Заголовок дня -->
                        <div
                            class="day-header d-flex justify-content-between align-items-center mb-2 px-3 py-2 bg-light rounded border-start border-primary border-3">
                            <div>
                                <i class="fa-regular fa-calendar me-2 text-primary"></i>
                                <strong>{{ day.date }}</strong>
                                <span class="text-muted small ms-2">
            {{ day.count }} сделок
        </span>
                            </div>
                            <div class="fw-bold text-success">
                                {{ formatMoney(day.total) }}
                            </div>
                        </div>

                        <!-- Карточки сделок -->
                        <div class="container-fluid px-0">
                            <div class="row g-2">
                                <div
                                    v-for="sale in day.items"
                                    :key="sale.id"
                                    class="col-12 col-md-6 col-xxl-4"
                                >
                                    <div
                                        class="card shadow-sm h-100 sale-card position-relative"
                                        :class="{
                                'border-primary border-3': selection.indexOf(sale.id) !== -1,
                                'bg-danger-subtle': sale.status === 'completed' && !sale.actual_delivery_date
                            }"
                                    >

                                        <div class="card-body p-0">
                                            <SaleCard
                                                :sale="sale"
                                                :field_visible="field_visible"
                                                :saleStatuses="saleStatuses"
                                                @toggle-selection="toggleSelection">
                                                <template #actions>
                                                    <div class="sale-card__dropdown">
                                                        <button
                                                            class="sale-card__menu-button"
                                                            type="button"
                                                            aria-label="Действия со сделкой"
                                                            @click.stop="openSaleActions(sale)"
                                                        >
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                            </SaleCard>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Пагинация по дням -->
                    <div v-if="salesStore.pagination.last_page > 1" class="mt-4">
                        <nav aria-label="Пагинация по дням">
                            <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ disabled: salesStore.pagination.current_page === 1 }">
                                    <a class="page-link" href="javascript:void(0)"
                                       @click="changePage(salesStore.pagination.current_page - 1)">
                                        &laquo;
                                    </a>
                                </li>
                                <li
                                    v-for="page in salesStore.pagination.last_page"
                                    :key="page"
                                    class="page-item"
                                    :class="{ active: page === salesStore.pagination.current_page }"
                                >
                                    <a class="page-link" href="javascript:void(0)" @click="changePage(page)">
                                        {{ page }}
                                    </a>
                                </li>
                                <li class="page-item"
                                    :class="{ disabled: salesStore.pagination.current_page === salesStore.pagination.last_page }">
                                    <a class="page-link" href="javascript:void(0)"
                                       @click="changePage(salesStore.pagination.current_page + 1)">
                                        &raquo;
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small">
                            Показаны дни {{ salesStore.pagination.from }}-{{ salesStore.pagination.to }} из
                            {{ salesStore.pagination.total }}
                        </div>
                    </div>
                </div>

                <!-- Пусто -->
                <div v-else class="alert alert-info mt-3">
                    За выбранный месяц сделок не найдено.
                </div>
            </template>

            <!-- 🔹 РЕЖИМ: НЕЗАВЕРШЁННЫЕ СДЕЛКИ -->
            <template v-if="currentTab === 'incomplete'">

                <!-- 🔹 Панель фильтров -->

                <!-- 🔹 Фильтр по датам -->
                <div class="date-range-filters card mb-3 shadow-sm">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">
                                <i class="fa-solid fa-calendar-days text-primary me-2"></i>
                                Период создания сделок
                                <small class="text-muted fw-normal ms-2">(по дате создания)</small>
                            </h6>

                            <button
                                v-if="incompleteFilters.date_from || incompleteFilters.date_to"
                                type="button"
                                class="btn btn-sm btn-outline-secondary"
                                @click="clearDateFilters"
                            >
                                <i class="fa-solid fa-xmark me-1"></i>
                                Сбросить даты
                            </button>
                        </div>

                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="incompleteDateFrom"
                                        v-model="incompleteFilters.date_from"
                                        @change="onIncompleteFilterChange"
                                        :max="incompleteFilters.date_to || todayDate"
                                    />
                                    <label for="incompleteDateFrom">
                                        <i class="fa-solid fa-calendar-day me-1"></i>
                                        Созданы с
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="incompleteDateTo"
                                        v-model="incompleteFilters.date_to"
                                        @change="onIncompleteFilterChange"
                                        :min="incompleteFilters.date_from"
                                        :max="todayDate"
                                    />
                                    <label for="incompleteDateTo">
                                        <i class="fa-solid fa-calendar-check me-1"></i>
                                        Созданы по
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Индикатор активного периода -->
                        <div
                            v-if="incompleteFilters.date_from || incompleteFilters.date_to"
                            class="mt-2 small text-primary"
                        >
                            <i class="fa-solid fa-filter me-1"></i>
                            Показаны сделки за период:
                            <strong>
                                {{ incompleteFilters.date_from ? formatDateShort(incompleteFilters.date_from) : 'начала' }}
                            </strong>
                            —
                            <strong>
                                {{ incompleteFilters.date_to ? formatDateShort(incompleteFilters.date_to) : 'сейчас' }}
                            </strong>
                        </div>
                    </div>
                </div>

                <div class="incomplete-filters card mb-3 shadow-sm">
                    <div class="card-body p-0">
                        <h6 class="mb-3">
                            <i class="fa-solid fa-filter text-primary me-2"></i>
                            Критерии незавершённости
                            <small class="text-muted fw-normal ms-2">(выберите, что считать проблемой)</small>
                        </h6>

                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <div
                                    class="filter-toggle"
                                    :class="{ active: incompleteFilters.include_missing_date }"
                                    @click="toggleIncompleteFilter('include_missing_date')"
                                >
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                    <div class="filter-info">
                                        <div class="filter-title">Нет даты доставки</div>
                                        <div class="filter-desc">actual_delivery_date не заполнен</div>
                                    </div>
                                    <i class="fa-solid fa-check filter-check"></i>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div
                                    class="filter-toggle"
                                    :class="{ active: incompleteFilters.include_status }"
                                    @click="toggleIncompleteFilter('include_status')"
                                >
                                    <i class="fa-solid fa-truck-clock"></i>
                                    <div class="filter-info">
                                        <div class="filter-title">Статус не "доставлено"</div>
                                        <div class="filter-desc">status ≠ delivered</div>
                                    </div>
                                    <i class="fa-solid fa-check filter-check"></i>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <div
                                    class="filter-toggle"
                                    :class="{ active: incompleteFilters.include_missing_price }"
                                    @click="toggleIncompleteFilter('include_missing_price')"
                                >
                                    <i class="fa-solid fa-money-bill-circle"></i>
                                    <div class="filter-info">
                                        <div class="filter-title">Нет суммы сделки</div>
                                        <div class="filter-desc">total_price = 0 или не указан</div>
                                    </div>
                                    <i class="fa-solid fa-check filter-check"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Предупреждение, если все фильтры выключены -->
                        <div
                            v-if="!incompleteFilters.include_missing_date && !incompleteFilters.include_status && !incompleteFilters.include_missing_price"
                            class="alert alert-warning mt-3 mb-0 small"
                        >
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>
                            Включите хотя бы один фильтр, чтобы увидеть список сделок
                        </div>
                    </div>
                </div>

                <!-- 🔹 Панель массовых действий (только для админов) -->
                <div v-if="(user?.role || 0) >= 3" class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex gap-3 align-items-center">
                        <a href="javascript:void(0)"
                           @click="toggleSelectAllIncomplete"
                           class="small fw-bold text-decoration-none">
                            <i class="fa-solid fa-check-double me-1"></i>
                            {{
                                incompleteSelection.length === salesStore.incompleteItems.length && salesStore.incompleteItems.length > 0
                                    ? 'Снять выделение'
                                    : 'Выделить все'
                            }}
                        </a>

                        <span v-if="incompleteSelection.length > 0" class="small text-muted">
                Выбрано: <strong class="text-primary">{{ incompleteSelection.length }}</strong>
            </span>
                    </div>

                    <button
                        v-if="incompleteSelection.length > 0"
                        type="button"
                        class="btn btn-sm btn-danger"
                        @click="confirmBulkDeleteIncomplete"
                    >
                        <i class="fa-solid fa-trash me-1"></i>
                        Удалить выбранные ({{ incompleteSelection.length }})
                    </button>
                </div>

                <!-- Загрузка -->
                <div v-if="salesStore.incompleteLoading" class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>

                <template v-else>
                    <!-- Статистика -->
                    <div v-if="salesStore.incompleteItems.length > 0" class="alert alert-warning mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fa-solid fa-hourglass-half fs-4 me-3"></i>
                            <div>
                                <strong>{{ salesStore.incompletePagination?.total || 0 }}</strong>
                                сделок ожидают завершения
                            </div>
                        </div>
                    </div>

                    <!-- Список -->
                    <div class="container-fluid px-0">
                        <div class="row g-2">
                            <div
                                v-for="sale in salesStore.incompleteItems"
                                :key="sale.id"
                                class="col-12 col-md-6 col-xxl-4"
                            >
                                <div
                                    class="card shadow-sm h-100 sale-card position-relative"
                                    :class="{ 'border-danger border-3': incompleteSelection.includes(sale.id) }"
                                >

                                    <!-- 🔹 Чекбокс (только для админов) -->
                                    <div
                                        v-if="(user?.role || 0) >= 3"
                                        class="incomplete-checkbox position-absolute top-0 start-0 m-2 z-3"
                                        @click.stop="toggleIncompleteSelection(sale.id)"
                                    >
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            :checked="incompleteSelection.includes(sale.id)"
                                            @click.stop="toggleIncompleteSelection(sale.id)"
                                        />
                                    </div>


                                    <!-- Индикатор статуса -->
                                    <div class="card-status-indicator" :class="'status-' + sale.status"></div>

                                    <!-- 🔹 Бейджи причин "незавершённости" -->
                                    <div class="incomplete-reasons position-absolute z-3 d-flex flex-wrap gap-1"
                                         :class="(user?.role || 0) >= 3 ? 'top-0 start-0 mt-5 ms-2' : 'top-0 start-0 m-2'">
                            <span v-if="incompleteFilters.include_missing_date && !sale.actual_delivery_date"
                                  class="badge bg-warning text-dark"
                                  title="Отсутствует дата доставки">
                                <i class="fa-solid fa-calendar-xmark"></i>
                            </span>
                                        <span v-if="incompleteFilters.include_status && sale.status !== 'delivered'"
                                              class="badge bg-info text-dark"
                                              :title="`Статус: ${saleStatuses[sale.status] || sale.status}`">
                                <i class="fa-solid fa-truck"></i>
                            </span>
                                        <span
                                            v-if="incompleteFilters.include_missing_price && (!sale.total_price || sale.total_price == 0)"
                                            class="badge bg-danger"
                                            title="Не указана сумма сделки">
                                <i class="fa-solid fa-money-bill"></i>
                            </span>
                                    </div>

                                    <div class="card-body p-0">
                                        <SaleCard
                                            :sale="sale"
                                            :field_visible="field_visible"
                                            :saleStatuses="saleStatuses"
                                            @toggle-selection="toggleSelection">
                                            <template #actions>
                                                <div class="sale-card__dropdown">
                                                    <button
                                                        class="sale-card__menu-button"
                                                        type="button"
                                                        aria-label="Действия со сделкой"
                                                        @click.stop="openSaleActions(sale, 'incomplete')"
                                                    >
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </SaleCard>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Пагинация -->
                    <Pagination
                        v-if="salesStore.incompleteItems.length > 0"
                        :pagination="salesStore.incompletePagination"
                        @page-changed="fetchIncompleteByUrl"
                    />

                    <!-- Пусто -->
                    <div v-if="salesStore.incompleteItems.length === 0" class="alert alert-success mt-3 text-center">
                        <i class="fa-solid fa-check-circle fs-3 mb-2 d-block"></i>
                        По выбранным критериям незавершённых сделок не найдено
                    </div>
                </template>
            </template>

            <!-- Модалка редактирования -->
            <div class="modal fade" id="editSaleModal" tabindex="-1">
                <div class="modal-dialog modal-fullscreen">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Редактирование задания</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <SaleForm v-if="selectedSale" :initialData="selectedSale" @saved="fetchData"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Модалка просмотра -->
            <div class="modal fade" id="viewSaleModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Карточка задания</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <TaskCard
                                v-if="selectedSale"
                                :task="selectedSale"></TaskCard>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Модалка подтверждения сделки -->
            <div class="modal fade" id="confirmDealModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Подтверждение сделки</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <DealForm
                                v-if="selectedSale"
                                v-model="dealForm" @callback="confirmDeal"/>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- Нижняя модалка действий со сделкой -->
    <Transition name="sale-actions-fade" @after-leave="onSaleActionsClosed">
        <div v-if="showSaleActions" class="sale-actions-backdrop" @click.self="closeSaleActions" @keydown.esc="closeSaleActions">
            <section class="sale-actions-sheet" role="dialog" aria-modal="true" aria-labelledby="saleActionsTitle">
                <div class="sale-actions-handle"></div>
                <header class="sale-actions-header">
                    <div class="sale-actions-heading-icon"><i class="fa-solid fa-receipt"></i></div>
                    <div class="sale-actions-heading-copy">
                        <h5 id="saleActionsTitle">Действия со сделкой</h5>
                        <p>{{ selectedActionSale?.title || `Сделка #${selectedActionSale?.id || ''}` }}</p>
                    </div>
                    <button type="button" class="sale-actions-close" aria-label="Закрыть" @click="closeSaleActions"><i class="fa-solid fa-xmark"></i></button>
                </header>
                <div class="sale-actions-list">
                    <template v-if="forSelect">
                        <button type="button" class="sale-action-item" @click="runSaleAction('select')">
                            <span class="sale-action-icon"><i class="fa-solid fa-check"></i></span><span><strong>Выбрать</strong><small>Выбрать эту сделку</small></span><i class="fa-solid fa-chevron-right sale-action-chevron"></i>
                        </button>
                    </template>
                    <template v-else>
                        <button type="button" class="sale-action-item" @click="runSaleAction('view')">
                            <span class="sale-action-icon"><i class="fa-regular fa-eye"></i></span><span><strong>Просмотреть</strong><small>Открыть карточку сделки</small></span><i class="fa-solid fa-chevron-right sale-action-chevron"></i>
                        </button>
                        <button type="button" class="sale-action-item" @click="runSaleAction('edit')">
                            <span class="sale-action-icon"><i class="fa-solid fa-pen-to-square"></i></span><span><strong>Редактировать</strong><small>Изменить данные сделки</small></span><i class="fa-solid fa-chevron-right sale-action-chevron"></i>
                        </button>
                        <button v-if="selectedActionSale && selectedActionSale.status !== 'completed'" type="button" class="sale-action-item is-success" @click="runSaleAction('confirm')">
                            <span class="sale-action-icon"><i class="fa-solid fa-truck-fast"></i></span><span><strong>Подтвердить оплату и доставку</strong><small>Завершить обработку сделки</small></span><i class="fa-solid fa-chevron-right sale-action-chevron"></i>
                        </button>
                        <button v-if="selectedActionContext !== 'incomplete' && selectedActionSale?.payment_document_name" type="button" class="sale-action-item is-success" @click="runSaleAction('document')">
                            <span class="sale-action-icon"><i class="fa-brands fa-telegram"></i></span><span><strong>Отправить документ в чат</strong><small>Отправить платёжный документ</small></span><i class="fa-solid fa-chevron-right sale-action-chevron"></i>
                        </button>
                        <div class="sale-actions-divider"></div>
                        <button type="button" class="sale-action-item is-danger" @click="runSaleAction('cancel')">
                            <span class="sale-action-icon"><i class="fa-solid fa-ban"></i></span><span><strong>Отменить сделку</strong><small>Отменить текущую сделку</small></span><i class="fa-solid fa-chevron-right sale-action-chevron"></i>
                        </button>
                        <button v-if="selectedActionContext !== 'incomplete'" type="button" class="sale-action-item is-danger" @click="runSaleAction('delete')">
                            <span class="sale-action-icon"><i class="fa-solid fa-trash"></i></span><span><strong>Удалить</strong><small>Удалить сделку без возможности восстановления</small></span><i class="fa-solid fa-chevron-right sale-action-chevron"></i>
                        </button>
                    </template>
                </div>
                <footer class="sale-actions-footer"><button type="button" class="btn btn-light w-100" @click="closeSaleActions">Закрыть</button></footer>
            </section>
        </div>
    </Transition>

</template>

<script>
import axios from 'axios'

import {useSalesStore} from '@/stores/sales'
import {useAgentsStore} from "@/stores/agents";
import {useUsersStore} from "@/stores/users";
import {useModalStore} from "@/stores/utillites/useConfitmModalStore";
import {useAlertStore} from "@/stores/utillites/useAlertStore";

export default {
    name: 'SaleList',
    props: ["forSelect", "adminId", "agentId", "productId", "customerId", "supplierId"],
    data() {
        return {
            incompleteSelection: [],
            currentTab: 'timeline', // 'list' или 'timeline'
            selectedMonth: this.getCurrentMonth(), // текущий месяц
            daysPerPage: 7,

            selection: [],
            field_visible: null,
            sales: [],
            search: '',
            alertStore: useAlertStore(),
            userStore: useUsersStore(),
            salesStore: useSalesStore(),
            agentStore: useAgentsStore(),
            modalStore: useModalStore(),
            selectedSale: null,
            showSaleActions: false,
            selectedActionSale: null,
            selectedActionContext: 'standard',
            pendingSaleAction: null,
            saleStatuses: {
                pending: "В ожидании",
                assigned: "Назначено",
                completed: "Завершено",
                rejected: "Отклонено",
                delivered: "Доставляется"
            },
            incompleteFilters: {
                include_missing_date: true,
                include_status: true,
                include_missing_price: true,
                date_from: '',  // 🔹 формат YYYY-MM-DD
                date_to: '',    // 🔹 формат YYYY-MM-DD
            },
            dealForm: {
                sale_date: null,
                actual_delivery_date: null,
                quantity: 0,
                total_price: 0,
                files: [],
                additional_comment: null,
                payment_document_name: null,
                payment_type: '0',
                receipt_is_lost: false,
                same_sale_delivery_date: true,
                need_additional_comment: false,
            }
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
        filteredSales() {
            const query = this.search.trim().toLowerCase();

            return this.salesStore.items.filter(s =>
                String(s.title ?? '').toLowerCase().includes(query)
            );
        },
        filteredBadSales() {
            return this.salesStore.bad_items ?? []
        },
        todayDate() {
            const now = new Date();
            return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        },
    },
    created() {

        const saved = localStorage.getItem('incomplete_filters');
        if (saved) {
            try {
                const parsed = JSON.parse(saved);
                this.incompleteFilters = {...this.incompleteFilters, ...parsed};
            } catch (e) {
            }
        }

        this.salesStore.setFilters({
            created_by_id: this.adminId || null,
            customer_id: this.customerId || null,
            product_id: this.productId || null,
            supplier_id: this.supplierId || null,
        })

        // Загружаем данные для текущей вкладки
        this.loadTabData(this.currentTab);
    },
    methods: {
        openSaleActions(sale, context = 'standard') {
            this.selectedActionSale = sale
            this.selectedActionContext = context
            this.showSaleActions = true
            document.body.classList.add('sale-actions-open')
        },
        closeSaleActions() {
            this.showSaleActions = false
            document.body.classList.remove('sale-actions-open')
        },
        runSaleAction(action) {
            const sale = this.selectedActionSale
            if (!sale) return

            // Сначала полностью убираем нашу модалку и её затемнение,
            // и только после завершения leave-перехода открываем следующую.
            this.pendingSaleAction = { action, sale }
            this.closeSaleActions()
        },
        onSaleActionsClosed() {
            document.body.classList.remove('sale-actions-open')
            const pending = this.pendingSaleAction
            if (!pending) return
            this.pendingSaleAction = null
            const { action, sale } = pending
            switch (action) {
                case 'select':
                    this.$emit('select', sale)
                    break
                case 'view':
                    this.openView(sale)
                    break
                case 'edit':
                    this.openEdit(sale)
                    break
                case 'confirm':
                    this.openConfirmDeal(sale)
                    break
                case 'document':
                    this.sendPaymentDocumentToTg(sale.id)
                    break
                case 'delete':
                    this.confirmDelete(sale)
                    break
                case 'cancel':
                    this.confirmCancelDeal(sale)
                    break
            }
        },
        toggleIncompleteFilter(key) {
            this.incompleteFilters[key] = !this.incompleteFilters[key];
            localStorage.setItem('incomplete_filters', JSON.stringify(this.incompleteFilters));
            this.onIncompleteFilterChange();
        },
        switchTab(tab) {
            if (this.currentTab === tab) return;
            this.currentTab = tab;
            this.loadTabData(tab);
        },

        loadTabData(tab) {
            switch (tab) {
                case 'list':
                    this.salesStore.fetchFiltered();
                    this.salesStore.fetchBadData();
                    break;
                case 'incomplete':
                    // 🔹 Всегда перезагружаем с текущими фильтрами
                    this.salesStore.fetchIncomplete(1, 20, this.incompleteFilters);
                    break;
                case 'timeline':
                    this.loadMonthData();
                    break;
            }
        },

        fetchIncompleteByUrl(urlOrPage) {
            let page = 1

            if (typeof urlOrPage === 'number') {
                // Получили просто номер страницы
                page = urlOrPage
            } else if (typeof urlOrPage === 'string') {
                // Получили URL — извлекаем номер страницы
                const match = urlOrPage.match(/page=(\d+)/)
                page = match ? parseInt(match[1]) : 1
            }

            this.salesStore.fetchIncomplete(page, 20, this.incompleteFilters)
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

            // Генерируем список месяцев за последний год
            for (let i = 0; i < 12; i++) {
                const date = new Date(now.getFullYear(), now.getMonth() - i, 1)
                const year = date.getFullYear()
                const month = String(date.getMonth() + 1).padStart(2, '0')
                const key = `${year}-${month}`
                const label = date.toLocaleDateString('ru-RU', {month: 'long', year: 'numeric'})

                months.push({key, label})
            }

            return months
        },

        async loadMonthData(page = 1) {
            await this.salesStore.fetchMonthData(
                this.selectedMonth,
                page,
                this.daysPerPage
            )
        },

        async changeMonth() {
            await this.loadMonthData(1)
        },

        async changePage(page) {
            await this.loadMonthData(page)
        },

        formatMoney(value) {
            return new Intl.NumberFormat('ru-RU').format(value || 0) + ' ₽'
        },
        onIncompleteFilterChange() {
            // 🔹 Сохраняем в localStorage
            localStorage.setItem('incomplete_filters', JSON.stringify(this.incompleteFilters));
            // Сбрасываем на первую страницу и перезагружаем
            this.salesStore.fetchIncomplete(1, 20, this.incompleteFilters);
        },

        async sendPaymentDocumentToTg(id) {
            await this.salesStore.sendPaymentDocumentToTg(id).then(() => {
                this.alertStore.show("Чек отправлен вам в телеграм бот!");
            })

        },

        selectAll() {
            if (this.selection.length === 0)
                this.salesStore.items.forEach(i => {
                    if (this.selection.indexOf(i.id) === -1)
                        this.selection.push(i.id)
                })
            else
                this.selection = []
        },
        toggleSelection(id) {
            let index = this.selection.findIndex(i => i === id)
            if (index === -1)
                this.selection.push(id)
            else
                this.selection.splice(index, 1)
        },

        async fetchData(page = 1) {
            await this.salesStore.fetchAllByPage(page)

            const editModal = bootstrap.Modal.getInstance(document.getElementById('editSaleModal'))
            if (editModal)
                editModal.hide()

            const confirmDealModal = bootstrap.Modal.getInstance(document.getElementById('confirmDealModal'))
            if (confirmDealModal)
                confirmDealModal.hide()
        },
        async fetchDataByUrl(url) {
            await this.salesStore.fetchByUrl(url)
        },

        openEdit(sale) {
            this.selectedSale = null
            this.$nextTick(() => {
                this.selectedSale = sale
                new bootstrap.Modal(document.getElementById('editSaleModal')).show()
            })

        },
        confirmDelete(sale) {
            this.selectedSale = sale
            this.modalStore.open(
                `Вы уверены, что хотите удалить ${this.selectedSale?.title}?`,
                () => this.salesStore.deleteSale(this.selectedSale.id),
                () => this.modalStore.close()
            )
        },
        confirmCancelDeal(sale) {
            this.selectedSale = sale
            this.modalStore.open(
                `Вы уверены, что хотите отменить сделку ${this.selectedSale?.title}?`,
                () => this.salesStore.cancelDeal(sale),
                () => this.modalStore.close()
            )
        },
        async deleteSale() {
            try {
                await this.salesStore.deleteSale()
                bootstrap.Modal.getInstance(document.getElementById('deleteSaleModal')).hide()
            } catch (error) {
                console.error('Ошибка удаления продажи:', error)
            }
        },
        openView(sale) {
            this.selectedSale = null
            this.$nextTick(() => {
                this.selectedSale = sale
                new bootstrap.Modal(document.getElementById('viewSaleModal')).show()
            })
        },
        openConfirmPayment(sale) {
            this.selectedSale = null
            this.$nextTick(() => {
                this.selectedSale = sale
                this.paymentConfirmForm.payment_type = sale.payment_type
                new bootstrap.Modal(document.getElementById('paymentConfirmForm')).show()
            })
        },
        acceptAll() {
            this.modalStore.open(
                `Вы уверены, что хотите подтвердить все выбранные заявки? Будет подтвержден факт доставки и факт оплаты`,
                () => {
                    this.salesStore.acceptAll(this.selection).then(() => {
                        this.salesStore.fetchFiltered()
                    })
                    this.selection = []
                },
                () => {
                    this.modalStore.close()
                    this.selection = []
                }
            )

        },

        clearDateFilters() {
            this.incompleteFilters.date_from = '';
            this.incompleteFilters.date_to = '';
            this.onIncompleteFilterChange();
        },

        formatDateShort(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('ru-RU', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                });
            } catch {
                return dateString;
            }
        },
        openConfirmDeal(sale) {
            this.selectedSale = null
            this.salesStore.progress = 0
            this.$nextTick(() => {
                this.selectedSale = sale
                this.dealForm.quantity = sale.quantity
                this.dealForm.id = sale.id
                this.dealForm.payment_type = '' + sale.payment_type
                this.dealForm.total_price = sale.total_price
                this.dealForm.payment_document_name = sale.payment_document_name
                new bootstrap.Modal(document.getElementById('confirmDealModal')).show()
            })

        },
        /* async confirmPayment() {
             this.paymentConfirmForm.id = this.selectedSale.id
             await this.salesStore.confirmPayment(this.paymentConfirmForm)
             bootstrap.Modal.getInstance(document.getElementById('paymentConfirmForm')).hide()

         },*/
        async confirmDeal() {
            this.dealForm.id = this.selectedSale.id
            await this.salesStore.confirmDealAndPayment(this.dealForm).then(() => {
                this.dealForm.files = []
                this.dealForm.actual_delivery_date = ''
                this.dealForm.quantity = 1
                this.dealForm.total_price = 0
                this.dealForm.same_sale_delivery_date = true
                this.dealForm.need_additional_comment = false
                this.dealForm.additional_comment = ''
            })
            bootstrap.Modal.getInstance(document.getElementById('confirmDealModal')).hide()

        },
        async cancelDeal(sale) {
            try {
                await this.salesStore.cancelDeal(sale)
            } catch (error) {
                console.error('Ошибка отмены сделки:', error)
            }
        },

        applyFilters(filters) {
            this.field_visible = filters.field_visible
            let size = filters.size || 30
            let page = filters.page || 1
            const only_my_tasks = filters.only_my_tasks || (this.user?.role || 0) < 3
            delete filters.field_visible
            delete filters.only_my_tasks
            this.salesStore.setFilters(filters)
            if (only_my_tasks)
                this.salesStore.selfSalesFiltered(page, size)
            else
                this.salesStore.fetchFiltered(page, size)
        },

        toggleIncompleteSelection(id) {
            const index = this.incompleteSelection.indexOf(id);
            if (index === -1) {
                this.incompleteSelection.push(id);
            } else {
                this.incompleteSelection.splice(index, 1);
            }
        },

        toggleSelectAllIncomplete() {
            if (this.incompleteSelection.length === this.salesStore.incompleteItems.length
                && this.salesStore.incompleteItems.length > 0) {
                // Снять все
                this.incompleteSelection = [];
            } else {
                // Выделить все видимые на текущей странице
                this.incompleteSelection = this.salesStore.incompleteItems.map(sale => sale.id);
            }
        },

        confirmBulkDeleteIncomplete() {
            const count = this.incompleteSelection.length;
            this.modalStore.open(
                `Вы уверены, что хотите удалить <b>${count}</b> ${this.pluralize(count, ['сделку', 'сделки', 'сделок'])}?<br>Это действие необратимо.`,
                async () => {
                    try {
                        await this.salesStore.bulkDelete([...this.incompleteSelection]);
                        this.incompleteSelection = [];
                        // Перезагружаем список с текущими фильтрами
                        this.salesStore.fetchIncomplete(1, 20, this.incompleteFilters);
                    } catch (e) {
                        console.error('Ошибка массового удаления:', e);
                    }
                    this.modalStore.close();
                },
                () => this.modalStore.close()
            );
        },

        pluralize(n, forms) {
            const abs = Math.abs(n) % 100;
            const n1 = abs % 10;
            if (abs > 10 && abs < 20) return forms[2];
            if (n1 > 1 && n1 < 5) return forms[1];
            if (n1 === 1) return forms[0];
            return forms[2];
        },

    }
}
</script>
<style scoped>
p {
    overflow-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
}

.day-header {
    transition: all 0.2s ease;
}

.day-header:hover {
    background-color: #e9ecef;
}

.sale-card {
    transition: transform 0.2s ease;
}

.sale-card:hover {
    transform: translateY(-2px);
}

/* 🔹 Красивые табы */
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
    position: relative;
    overflow: hidden;
}

.custom-tab i {
    font-size: 15px;
    transition: transform 0.25s ease;
}

.custom-tab:hover {
    color: #0d6efd;
    background: rgba(13, 110, 253, 0.06);
}

.custom-tab:hover i {
    transform: translateY(-1px);
}

.custom-tab.active {
    background: #ffffff;
    color: #0d6efd;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15),
    0 1px 3px rgba(0, 0, 0, 0.06);
    font-weight: 600;
}

.custom-tab.active i {
    color: #0d6efd;
}

.tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border-radius: 11px;
    font-size: 11px;
    font-weight: 700;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    margin-left: 4px;
    animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.08);
    }
}

/* Индикатор статуса на карточке */
.card-status-indicator {
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    border-radius: 4px 0 0 4px;
}

.card-status-indicator.status-pending {
    background: linear-gradient(180deg, #ffc107 0%, #ff9800 100%);
}

.card-status-indicator.status-assigned {
    background: linear-gradient(180deg, #0dcaf0 0%, #0d6efd 100%);
}

.card-status-indicator.status-delivered {
    background: linear-gradient(180deg, #198754 0%, #0f5132 100%);
}

/* Адаптив для маленьких экранов */
@media (max-width: 576px) {
    .custom-tabs {
        gap: 4px;
        padding: 4px;
    }

    .custom-tab {
        padding: 10px 8px;
        font-size: 12px;
        gap: 4px;
    }

    .custom-tab span {
        display: none;
    }

    .custom-tab i {
        font-size: 18px;
    }

    .tab-badge {
        position: absolute;
        top: 4px;
        right: 4px;
        min-width: 18px;
        height: 18px;
        font-size: 10px;
    }
}

/* 🔹 Панель фильтров для незавершённых сделок */
.incomplete-filters {
    border: 1px solid #e9ecef;
    border-radius: 12px;
}

.filter-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #ffffff;
    user-select: none;
    position: relative;
}

.filter-toggle:hover {
    border-color: #0d6efd;
    background: rgba(13, 110, 253, 0.03);
}

.filter-toggle.active {
    border-color: #0d6efd;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.12);
}

.filter-toggle > i:first-child {
    font-size: 18px;
    color: #6c757d;
    transition: color 0.2s ease;
}

.filter-toggle.active > i:first-child {
    color: #0d6efd;
}

.filter-info {
    flex: 1;
    min-width: 0;
}

.filter-title {
    font-weight: 600;
    font-size: 13px;
    color: #212529;
    line-height: 1.2;
}

.filter-desc {
    font-size: 11px;
    color: #6c757d;
    margin-top: 2px;
}

.filter-check {
    font-size: 14px;
    color: transparent;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.filter-toggle.active .filter-check {
    color: #0d6efd;
    transform: scale(1.1);
}

/* Бейджи причин незавершённости на карточке */
.incomplete-reasons .badge {
    font-size: 11px;
    padding: 4px 7px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

/* Адаптив */
@media (max-width: 768px) {
    .filter-toggle {
        padding: 10px 12px;
    }

    .filter-title {
        font-size: 12px;
    }

    .filter-desc {
        font-size: 10px;
    }
}

/* Чекбокс для массового выделения в режиме incomplete */
.incomplete-checkbox {
    cursor: pointer;
}

.incomplete-checkbox .form-check-input {
    cursor: pointer;
    width: 1.3em;
    height: 1.3em;
    margin: 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.incomplete-checkbox .form-check-input:checked {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Карточка с выделением */
.sale-card.border-danger {
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2) !important;
}

/* 🔹 Панель фильтра по датам */
.date-range-filters {
    border: 1px solid #e9ecef;
    border-radius: 12px;
}

.date-range-filters .form-floating .form-control {
    padding-top: 1.625rem;
    padding-bottom: 0.625rem;
}

.date-range-filters .form-floating label {
    font-size: 13px;
}

.date-range-filters .form-floating label i {
    color: #0d6efd;
}

/* =========================================================
   МОЙПРОЦЕНТ — обновлённая визуальная система SaleList
   ========================================================= */
.sales-page {
    --sales-blue: #2457d6;
    --sales-blue-dark: #173b91;
    --sales-red: #df3548;
    --sales-ink: #17233b;
    --sales-muted: #778399;
    --sales-border: #e5eaf2;
    --sales-surface: #ffffff;
    --sales-soft-blue: #edf3ff;
    color: var(--sales-ink);
    width: 100%;
    min-width: 0;
}

.sales-page * {
    box-sizing: border-box;
}

.sales-page-content {
    min-width: 0;
}

.sales-page-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin: 0 0 24px;
    padding: 22px;
    background: #fff;
    border: 1px solid var(--sales-border);
    border-radius: 18px;
    box-shadow: 0 4px 16px rgba(23, 35, 59, .04);
}

.sales-heading-icon {
    display: flex;
    flex: 0 0 58px;
    align-items: center;
    justify-content: center;
    width: 58px;
    height: 58px;
    color: #fff;
    background: linear-gradient(135deg, var(--sales-blue), var(--sales-blue-dark));
    border-radius: 17px;
    box-shadow: 0 7px 18px rgba(36, 87, 214, .18);
    font-size: 25px;
}

.sales-heading-copy {
    flex: 1;
    min-width: 0;
}

.sales-eyebrow {
    margin-bottom: 4px;
    color: var(--sales-blue);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .12em;
}

.sales-heading-copy h1 {
    margin: 0 0 5px;
    color: var(--sales-ink);
    font-size: clamp(21px, 2.4vw, 28px);
    font-weight: 800;
    letter-spacing: -.6px;
}

.sales-heading-copy p {
    margin: 0;
    color: var(--sales-muted);
    font-size: 13px;
    line-height: 1.5;
}

.sales-total-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    padding: 10px 13px;
    color: var(--sales-blue);
    background: var(--sales-soft-blue);
    border: 1px solid #dce7ff;
    border-radius: 11px;
    font-size: 12px;
    font-weight: 750;
    white-space: nowrap;
}

/* Табы */
.sales-page .custom-tabs {
    display: flex;
    gap: 6px;
    margin-bottom: 22px !important;
    padding: 6px;
    background: #f3f6fb;
    border: 1px solid var(--sales-border);
    border-radius: 15px;
    box-shadow: none;
}

.sales-page .custom-tab {
    min-width: 0;
    padding: 12px 16px;
    color: var(--sales-muted);
    border: 1px solid transparent;
    border-radius: 11px;
    font-size: 13px;
    font-weight: 650;
}

.sales-page .custom-tab:hover {
    color: var(--sales-blue);
    background: rgba(36, 87, 214, .05);
}

.sales-page .custom-tab.active {
    color: var(--sales-blue);
    background: #fff;
    border-color: #e4ebf8;
    box-shadow: 0 3px 10px rgba(23, 35, 59, .07);
}

.sales-page .custom-tab.active i {
    color: var(--sales-blue);
}

.sales-page .tab-badge {
    min-width: 22px;
    height: 22px;
    color: #fff;
    background: var(--sales-red);
    border-radius: 7px;
    box-shadow: none;
    animation: none;
}

/* Панель массовых действий */
.sales-page .sales-page-content > .d-flex.justify-content-between.my-2 {
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: #fff;
    border: 1px solid var(--sales-border);
    border-radius: 12px;
}

.sales-page a.small.fw-bold {
    color: var(--sales-blue);
    text-decoration: none;
}

.sales-page a.small.fw-bold:hover {
    text-decoration: underline;
}

.sales-page .text-danger.mx-2.fw-bold {
    color: var(--sales-red) !important;
}

.sales-page .fw-bold.small {
    color: var(--sales-muted);
}

/* Карточки сделок */
.sales-page .sale-card {
    height: 100%;
    overflow: visible;
    border-radius: 16px;
    padding:10px;
    /*  background: var(--sales-surface);


      box-shadow: 0 3px 12px rgba(23, 35, 59, .045);*/
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}

.sales-page .sale-card:hover {
    border-color: #d1dcf0;
    transform: translateY(-3px);
}

.sales-page .sale-card .card-body {
    padding: 19px;
}

.sales-page .sale-card.border-primary,
.sales-page .sale-card.border-danger {
    border-width: 2px !important;
    border-color: var(--sales-blue) !important;
    box-shadow: 0 0 0 3px rgba(36, 87, 214, .10) !important;
}

.sales-page .sale-card.bg-danger-subtle {
    background: linear-gradient(180deg, #fff 0%, #fff7f7 100%) !important;
    border-color: #f4cbd0 !important;
}

/* Dropdown menus replaced with the bottom action sheet. */

/* Поиск и фильтры */
.sales-page .form-control,
.sales-page .form-select {
    min-height: 44px;
    color: var(--sales-ink);
    border-color: #dfe6f0;
    border-radius: 11px;
    box-shadow: none;
}

.sales-page .form-control:focus,
.sales-page .form-select:focus {
    border-color: #8eabef;
    box-shadow: 0 0 0 3px rgba(36, 87, 214, .10);
}

.sales-page input.form-control[placeholder] {
    padding: 12px 15px;
}

.sales-page .date-range-filters,
.sales-page .incomplete-filters {
    overflow: hidden;
    background: #fff;
    border: 1px solid var(--sales-border);
    border-radius: 16px;
    box-shadow: 0 3px 12px rgba(23, 35, 59, .035) !important;
}

.sales-page .date-range-filters .card-body,
.sales-page .incomplete-filters .card-body {
    padding: 20px !important;
}

.sales-page .filter-toggle {
    height: 100%;
    padding: 14px;
    background: #fff;
    border: 1px solid var(--sales-border);
    border-radius: 12px;
}

.sales-page .filter-toggle:hover {
    background: #f8faff;
    border-color: #b9cbf3;
}

.sales-page .filter-toggle.active {
    background: #f0f5ff;
    border-color: var(--sales-blue);
    box-shadow: inset 0 0 0 1px rgba(36, 87, 214, .08);
}

.sales-page .filter-toggle.active > i:first-child,
.sales-page .filter-toggle.active .filter-check {
    color: var(--sales-blue);
}

.sales-page .filter-title {
    color: var(--sales-ink);
    font-size: 13px;
}

.sales-page .filter-desc {
    color: var(--sales-muted);
    font-size: 11px;
}

/* Заголовки дней и статистика */
.sales-page .day-header {
    gap: 12px;
    padding: 13px 16px !important;
    background: #fff !important;
    border: 1px solid var(--sales-border) !important;
    border-left: 4px solid var(--sales-blue) !important;
    border-radius: 12px !important;
}

.sales-page .day-header:hover {
    background: #f8faff !important;
}

.sales-page .alert {
    border-radius: 13px;
}

.sales-page .alert-info {
    color: #234a9b;
    background: #f0f5ff;
    border: 1px solid #dce7ff;
}

.sales-page .alert-warning {
    color: #79550a;
    background: #fff8e8;
    border: 1px solid #f4e3b7;
}

.sales-page .alert-success {
    color: #176b50;
    background: #eefaf5;
    border: 1px solid #ccefe0;
}

.sales-page .alert-danger {
    border: 1px solid #f4cbd0;
}

/* Индикатор статуса */
.sales-page .card-status-indicator {
    width: 4px;
    border-radius: 16px 0 0 16px;
}

.sales-page .card-status-indicator.status-pending {
    background: #e5a323;
}

.sales-page .card-status-indicator.status-assigned {
    background: var(--sales-blue);
}

.sales-page .card-status-indicator.status-delivered,
.sales-page .card-status-indicator.status-completed {
    background: #1b936b;
}

.sales-page .incomplete-reasons .badge {
    border-radius: 7px;
    box-shadow: none;
}

/* Пагинация */
.sales-page .pagination {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin: 18px 0 8px;
}

.sales-page .pagination .page-item {
    margin: 0;
}

.sales-page .pagination .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    min-height: 38px;
    padding: 8px 12px;
    color: var(--sales-blue);
    background: #fff;
    border: 1px solid var(--sales-border);
    border-radius: 10px !important;
    font-size: 13px;
    font-weight: 650;
    text-decoration: none;
    box-shadow: none;
}

.sales-page .pagination .page-link:hover {
    background: var(--sales-soft-blue);
    border-color: #cbd9f7;
}

.sales-page .pagination .page-item.active .page-link {
    color: #fff;
    background: var(--sales-blue);
    border-color: var(--sales-blue);
}

.sales-page .pagination .page-item.disabled .page-link {
    color: #aab3c1;
    background: #f7f8fa;
}

/* Модальные окна */
.sales-page .modal-content {
    overflow: hidden;
    border: 1px solid var(--sales-border);
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(23, 35, 59, .18);
}

.sales-page .modal-header {
    gap: 12px;
    padding: 18px 22px;
    background: #fff;
    border-bottom: 1px solid var(--sales-border);
}

.sales-page .modal-title {
    color: var(--sales-ink);
    font-size: 17px;
    font-weight: 750;
}

.sales-page .modal-body {
    padding: 22px;
}

.sales-page .btn-primary {
    background: var(--sales-blue);
    border-color: var(--sales-blue);
}

.sales-page .btn-primary:hover {
    background: var(--sales-blue-dark);
    border-color: var(--sales-blue-dark);
}

.sales-page .btn-danger {
    background: var(--sales-red);
    border-color: var(--sales-red);
}

/* Адаптивность */
@media (max-width: 767.98px) {
    .sales-page-header {
        flex-wrap: wrap;
        gap: 12px;
        padding: 17px;
        margin-bottom: 17px;
        border-radius: 15px;
    }

    .sales-heading-icon {
        flex-basis: 48px;
        width: 48px;
        height: 48px;
        border-radius: 14px;
        font-size: 21px;
    }

    .sales-heading-copy {
        flex-basis: calc(100% - 64px);
    }

    .sales-heading-copy p {
        font-size: 12px;
    }

    .sales-total-chip {
        margin-left: 60px;
        padding: 8px 10px;
    }

    .sales-page .custom-tabs {
        gap: 4px;
        padding: 4px;
        border-radius: 12px;
    }

    .sales-page .custom-tab {
        min-height: 46px;
        padding: 9px 7px;
        gap: 5px;
        font-size: 11px;
    }

    .sales-page .custom-tab span:not(.tab-badge) {
        display: inline;
    }

    .sales-page .sale-card .card-body {
        padding: 15px;
    }

    .sales-page .date-range-filters .card-body,
    .sales-page .incomplete-filters .card-body {
        padding: 15px !important;
    }

    .sales-page .day-header {
        align-items: flex-start !important;
        flex-direction: column;
        gap: 5px;
    }

    .sales-page .sales-page-content > .d-flex.justify-content-between.my-2 {
        align-items: flex-start;
        flex-direction: column;
    }
}

@media (max-width: 420px) {
    .sales-page .custom-tab {
        flex-direction: column;
        justify-content: center;
        min-height: 58px;
    }

    .sales-page .custom-tab span:not(.tab-badge) {
        display: inline;
        font-size: 10px;
    }

    .sales-page .custom-tab i {
        font-size: 17px;
    }

    .sales-page .tab-badge {
        position: absolute;
        top: 3px;
        right: 3px;
        min-width: 17px;
        height: 17px;
        padding: 0 4px;
        font-size: 9px;
    }

    .sales-page .sales-total-chip {
        margin-left: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .sales-page .sale-card,
    .sales-page .custom-tab,
    .sales-page .filter-toggle {
        transition: none;
    }
}



/* Нижняя модалка действий со сделкой */
.sales-page .sale-card__dropdown { display:flex; justify-content:flex-end; }
.sales-page .sale-card__menu-button {
    display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px;
    border:1px solid var(--sales-border); border-radius:12px; background:#fff; color:var(--sales-blue);
    box-shadow:0 3px 10px rgba(23,35,59,.06); transition:background .2s,transform .2s,border-color .2s;
}
.sales-page .sale-card__menu-button:hover { background:var(--sales-soft-blue); border-color:#cbd9f7; transform:translateY(-1px); }
.sale-actions-backdrop {
    position:fixed; inset:0; z-index:1090; display:flex; align-items:flex-end; justify-content:center;
    padding:0; background:rgba(14,25,45,.48); backdrop-filter:blur(4px); -webkit-backdrop-filter:blur(4px);
}
.sale-actions-sheet {
    width:100%; max-width:640px; max-height:min(82vh,760px); overflow:auto; overscroll-behavior:contain;
    background:#fff !important; color:#17233b; border:1px solid rgba(255,255,255,.8); border-bottom:0;
    border-radius:24px 24px 0 0; box-shadow:0 -18px 60px rgba(13,28,55,.24); padding:10px 20px calc(18px + env(safe-area-inset-bottom));
}
.sale-actions-handle { width:42px; height:5px; border-radius:99px; background:#d8dfeb; margin:0 auto 17px; }
.sale-actions-header { display:flex; align-items:center; gap:12px; padding:2px 0 17px; border-bottom:1px solid #edf0f5; }
.sale-actions-heading-icon { flex:0 0 44px; width:44px; height:44px; display:grid; place-items:center; border-radius:14px; background:#edf3ff; color:#2457d6; font-size:18px; }
.sale-actions-heading-copy { min-width:0; flex:1; }
.sale-actions-heading-copy h5 { margin:0; font-size:17px; font-weight:750; letter-spacing:-.2px; }
.sale-actions-heading-copy p { margin:4px 0 0; color:#7a879b; font-size:12px; overflow-wrap:anywhere; }
.sale-actions-close { flex:0 0 36px; width:36px; height:36px; border:0; border-radius:11px; background:#f2f5fa; color:#68768b; font-size:17px; }
.sale-actions-list { display:flex; flex-direction:column; gap:5px; padding:14px 0; }
.sale-action-item { width:100%; display:flex; align-items:center; gap:12px; padding:12px; text-align:left; border:1px solid transparent; border-radius:14px; background:#fff; color:#17233b; transition:background .18s,border-color .18s; }
.sale-action-item:hover { background:#f5f8fd; border-color:#e4eaf4; }
.sale-action-item > span:nth-child(2) { display:flex; flex-direction:column; gap:3px; flex:1; min-width:0; }
.sale-action-item strong { font-size:14px; font-weight:700; }
.sale-action-item small { color:#8390a4; font-size:12px; line-height:1.35; }
.sale-action-icon { flex:0 0 40px; width:40px; height:40px; display:grid; place-items:center; border-radius:12px; background:#edf3ff; color:#2457d6; font-size:16px; }
.sale-action-chevron { color:#a5afbf; font-size:12px; }
.sale-action-item.is-success .sale-action-icon { background:#e8f8f0; color:#16805d; }
.sale-action-item.is-success strong { color:#16805d; }
.sale-action-item.is-danger .sale-action-icon { background:#fff0f2; color:#df3548; }
.sale-action-item.is-danger strong { color:#cf3043; }
.sale-actions-divider { height:1px; background:#edf0f5; margin:5px 2px; }
.sale-actions-footer { border-top:1px solid #edf0f5; padding-top:13px; }
.sale-actions-footer .btn { min-height:44px; border:1px solid #e3e9f2; border-radius:12px; color:#53627a; font-weight:650; background:#f7f9fc; }
.sale-actions-fade-enter-active,.sale-actions-fade-leave-active { transition:opacity .22s ease; }
.sale-actions-fade-enter-active .sale-actions-sheet,.sale-actions-fade-leave-active .sale-actions-sheet { transition:transform .25s ease; }
.sale-actions-fade-enter-from,.sale-actions-fade-leave-to { opacity:0; }
.sale-actions-fade-enter-from .sale-actions-sheet,.sale-actions-fade-leave-to .sale-actions-sheet { transform:translateY(35px); }
@media (min-width:700px) {
    .sale-actions-backdrop { align-items:center; padding:24px; }
    .sale-actions-sheet { border:1px solid #e7edf6; border-radius:24px; padding:12px 22px 20px; }
}
@media (max-width:480px) {
    .sale-actions-sheet { padding-left:15px; padding-right:15px; max-height:88vh; }
    .sale-action-item { padding:10px 8px; gap:10px; }
    .sale-action-icon { width:36px; height:36px; flex-basis:36px; }
}

/* 🔹 Обёртка карточки "Заявки на проверку" */
.bad-sale-card-wrapper {
    border-radius: 16px;
    border: 1px solid #e5ebf4;
    background: #fff;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    overflow: hidden; /* Чтобы border-radius корректно обрезал содержимое */
    position: relative; /* Для позиционирования выпадающего меню внутри */
}

.bad-sale-card-wrapper:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(20, 33, 61, 0.08);
}

/* Единая позиция кнопки действий во всех режимах карточек */
.sales-page .sale-card {
    position: relative;
}

.sales-page .bad-sale-card-wrapper {
    position: relative;
    height: 100%;
}

.sales-page .sale-card__dropdown {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 30;
    display: block;
    width: 40px;
    height: 40px;
}

.sales-page .sale-card__menu-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    border: 1px solid var(--sales-border, #e5eaf2);
    border-radius: 12px;
    background: rgba(255, 255, 255, .96);
    color: var(--sales-blue, #2457d6);
    box-shadow: 0 3px 10px rgba(23, 35, 59, .08);
    cursor: pointer;
    transition: background .2s ease, border-color .2s ease, transform .2s ease;
}

.sales-page .sale-card__menu-button:hover {
    background: var(--sales-soft-blue, #edf3ff);
    border-color: #cbd9f7;
    transform: translateY(-1px);
}

/* 🔹 Адаптив: на очень маленьких экранах убираем лишние отступы */
@media (max-width: 575.98px) {
    .bad-sale-card-wrapper {
        border-radius: 12px;
    }
}

/* Статистика месяца */
.sales-page .month-stats {
    padding: 14px;
    border-radius: 16px;
    overflow: hidden;
}

.sales-page .month-stats__grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0;
    width: 100%;
}

.sales-page .month-stats__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 0;
    padding: 8px 6px;
    text-align: center;
}

.sales-page .month-stats__item + .month-stats__item {
    border-left: 1px solid rgba(36, 87, 214, .15);
}

.sales-page .month-stats__label {
    margin-bottom: 6px;
    color: #64748b;
    font-size: 13px;
    line-height: 1.35;
}

.sales-page .month-stats__value {
    color: #17233b;
    font-size: clamp(16px, 2vw, 22px);
    font-weight: 750;
    line-height: 1.3;
    white-space: nowrap;
    overflow-wrap: normal;
}

.sales-page .month-stats__value--success {
    color: #168252;
}

/* Планшет */
@media (max-width: 768px) {
    .sales-page .month-stats__value {
        font-size: 16px;
    }
}

/* Телефон */
@media (max-width: 576px) {
    .sales-page .month-stats {
        padding: 8px 14px;
    }

    .sales-page .month-stats__grid {
        grid-template-columns: 1fr;
    }

    .sales-page .month-stats__item {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 4px;
        text-align: left;
    }

    .sales-page .month-stats__item + .month-stats__item {
        border-left: none;
        border-top: 1px solid rgba(36, 87, 214, .15);
    }

    .sales-page .month-stats__label {
        margin: 0;
        font-size: 13px;
        white-space: normal;
    }

    .sales-page .month-stats__value {
        flex-shrink: 0;
        font-size: 17px;
        white-space: nowrap;
    }
}
</style>
