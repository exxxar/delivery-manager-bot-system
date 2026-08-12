<script setup>
import SaleForm from "@/Components/Sales/SaleForm.vue";
import Pagination from "@/Components/Pagination.vue";
import SaleFilterForm from '@/components/Sales/SaleFilterForm.vue'
import TaskCard from "@/Components/Sales/TaskCard.vue";
import DealForm from "@/Components/Sales/Forms/DealForm.vue";
import SaleCard from "@/Components/Sales/Forms/SaleCard.vue";
</script>
<template>





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


    <template v-if="filteredBadSales.length>0">
        <h6 class="fw-bold my-3"><i class="fa-solid fa-triangle-exclamation text-danger"></i>
            Заявки на проверку</h6>
        <ul class="list-group">
            <li

                v-bind:class="{'border-primary': selection.indexOf(sale.id)!==-1, 'bg-danger-subtle':sale.status === 'completed'&&(!sale.sale_date||!sale.actual_delivery_date)}"

                v-for="sale in filteredBadSales" :key="sale.id"
                class="list-group-item d-flex justify-content-between align-items-start ">
                <SaleCard
                    :sale="sale"
                    :field_visible="field_visible"
                    :saleStatuses="saleStatuses"
                    @toggle-selection="toggleSelection"
                />

                <!-- Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light text-primary" type="button"
                            data-bs-toggle="dropdown">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <template v-if="forSelect">
                            <li><a class="dropdown-item" href="#" @click.prevent="$emit('select', sale)">Выбрать</a>
                            </li>
                        </template>
                        <template v-if="!forSelect">
                            <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="openView(sale)">Просмотреть</a>
                            </li>
                            <li><a class="dropdown-item" href="javascript:void(0)" @click.prevent="openEdit(sale)">Редактировать</a>
                            </li>
                            <li v-if="sale.status!=='completed'"><a class="dropdown-item text-success"
                                                                    href="javascript:void(0)"
                                                                    @click.prevent="openConfirmDeal(sale)">Подтвердить
                                оплату и доставка</a></li>
                            <!--                        <li v-if="sale.payment_type===0&&!sale.sale_date"><a class="dropdown-item text-success"
                                                                                                         href="javascript:void(0)"

                                                                                                         @click.prevent="openConfirmPayment(sale)">Подтвердить
                                                        оплату</a></li>-->

                            <template v-if="sale.payment_document_name">
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-success"
                                       href="javascript:void(0)"
                                       @click.prevent="sendPaymentDocumentToTg(sale.id)">Отправить документ в чат</a>
                                </li>
                            </template>

                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)"
                                   @click.prevent="confirmDelete(sale)">Удалить</a></li>
                            <li><a class="dropdown-item text-danger" href="javascript:void(0)"
                                   @click.prevent="confirmCancelDeal(sale)">Отменить
                                сделку</a></li>
                        </template>
                    </ul>
                </div>
            </li>
        </ul>
        <h6 class="fw-bold my-3"><i class="fa-solid fa-triangle-exclamation text-danger"></i> Все заявки</h6>
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

                                <template v-if="forSelect">

                                    <li>
                                        <a
                                            class="dropdown-item"
                                            href="#"
                                            @click.prevent="$emit('select', sale)"
                                        >
                                            Выбрать
                                        </a>
                                    </li>

                                </template>

                                <template v-if="!forSelect">

                                    <li>
                                        <a
                                            class="dropdown-item"
                                            href="javascript:void(0)"
                                            @click.prevent="openView(sale)"
                                        >
                                            Просмотреть
                                        </a>
                                    </li>

                                    <li>
                                        <a
                                            class="dropdown-item"
                                            href="javascript:void(0)"
                                            @click.prevent="openEdit(sale)"
                                        >
                                            Редактировать
                                        </a>
                                    </li>

                                    <li v-if="sale.status !== 'completed'">
                                        <a
                                            class="dropdown-item text-success"
                                            href="javascript:void(0)"
                                            @click.prevent="openConfirmDeal(sale)"
                                        >
                                            Подтвердить оплату и доставку
                                        </a>
                                    </li>

                                    <template v-if="sale.payment_document_name">

                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>

                                        <li>
                                            <a
                                                class="dropdown-item text-success"
                                                href="javascript:void(0)"
                                                @click.prevent="sendPaymentDocumentToTg(sale.id)"
                                            >
                                                Отправить документ в чат
                                            </a>
                                        </li>

                                    </template>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <li>
                                        <a
                                            class="dropdown-item text-danger"
                                            href="javascript:void(0)"
                                            @click.prevent="confirmDelete(sale)"
                                        >
                                            Удалить
                                        </a>
                                    </li>

                                    <li>
                                        <a
                                            class="dropdown-item text-danger"
                                            href="javascript:void(0)"
                                            @click.prevent="confirmCancelDeal(sale)"
                                        >
                                            Отменить сделку
                                        </a>
                                    </li>

                                </template>

                            </ul>
                        </div>

                        <!-- Контент карточки -->
                        <div class="card-body">

                            <SaleCard
                                :sale="sale"
                                :field_visible="field_visible"
                                :saleStatuses="saleStatuses"
                                @toggle-selection="toggleSelection"
                            />

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
        <div v-if="salesStore.groupedMonth" class="alert alert-info mb-3">
            <div class="row text-center">
                <div class="col-4">
                    <div class="small text-muted">Всего сделок</div>
                    <div class="fw-bold fs-5">{{ salesStore.groupedMonth.month_stats.total_sales }}</div>
                </div>
                <div class="col-4">
                    <div class="small text-muted">Общая сумма</div>
                    <div class="fw-bold fs-5 text-success">{{ formatMoney(salesStore.groupedMonth.month_stats.total_sum) }}</div>
                </div>
                <div class="col-4">
                    <div class="small text-muted">Средний чек</div>
                    <div class="fw-bold fs-5">{{ formatMoney(salesStore.groupedMonth.month_stats.avg_sum) }}</div>
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
                <div class="day-header d-flex justify-content-between align-items-center mb-2 px-3 py-2 bg-light rounded border-start border-primary border-3">
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
                                <!-- Dropdown -->
                                <div class="dropdown position-absolute top-0 end-0 m-2 z-3">
                                    <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-bars text-primary"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <template v-if="forSelect">
                                            <li>
                                                <a class="dropdown-item" href="#" @click.prevent="$emit('select', sale)">
                                                    Выбрать
                                                </a>
                                            </li>
                                        </template>
                                        <template v-else>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" @click.prevent="openView(sale)">
                                                    Просмотреть
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" @click.prevent="openEdit(sale)">
                                                    Редактировать
                                                </a>
                                            </li>
                                            <li v-if="sale.status !== 'completed'">
                                                <a class="dropdown-item text-success" href="javascript:void(0)" @click.prevent="openConfirmDeal(sale)">
                                                    Подтвердить оплату и доставку
                                                </a>
                                            </li>
                                            <template v-if="sale.payment_document_name">
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-success" href="javascript:void(0)" @click.prevent="sendPaymentDocumentToTg(sale.id)">
                                                        Отправить документ в чат
                                                    </a>
                                                </li>
                                            </template>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" @click.prevent="confirmDelete(sale)">
                                                    Удалить
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" @click.prevent="confirmCancelDeal(sale)">
                                                    Отменить сделку
                                                </a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>

                                <div class="card-body">
                                    <SaleCard
                                        :sale="sale"
                                        :field_visible="field_visible"
                                        :saleStatuses="saleStatuses"
                                        @toggle-selection="toggleSelection"
                                    />
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
                            <a class="page-link" href="javascript:void(0)" @click="changePage(salesStore.pagination.current_page - 1)">
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
                        <li class="page-item" :class="{ disabled: salesStore.pagination.current_page === salesStore.pagination.last_page }">
                            <a class="page-link" href="javascript:void(0)" @click="changePage(salesStore.pagination.current_page + 1)">
                                &raquo;
                            </a>
                        </li>
                    </ul>
                </nav>
                <div class="text-center text-muted small">
                    Показаны дни {{ salesStore.pagination.from }}-{{ salesStore.pagination.to }} из {{ salesStore.pagination.total }}
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
        <div class="incomplete-filters card mb-3 shadow-sm">
            <div class="card-body p-3">
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
                    {{ incompleteSelection.length === salesStore.incompleteItems.length && salesStore.incompleteItems.length > 0
                    ? 'Снять выделение'
                    : 'Выделить все' }}
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

                            <!-- Dropdown -->
                            <div class="dropdown position-absolute top-0 end-0 m-2 z-3">
                                <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-bars text-primary"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" @click.prevent="openView(sale)">
                                            Просмотреть
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" @click.prevent="openEdit(sale)">
                                            Редактировать
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-success" href="javascript:void(0)" @click.prevent="openConfirmDeal(sale)">
                                            Подтвердить оплату и доставку
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0)" @click.prevent="confirmCancelDeal(sale)">
                                            Отменить сделку
                                        </a>
                                    </li>
                                </ul>
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
                                <span v-if="incompleteFilters.include_missing_price && (!sale.total_price || sale.total_price == 0)"
                                      class="badge bg-danger"
                                      title="Не указана сумма сделки">
                                <i class="fa-solid fa-money-bill"></i>
                            </span>
                            </div>

                            <div class="card-body">
                                <SaleCard
                                    :sale="sale"
                                    :field_visible="field_visible"
                                    :saleStatuses="saleStatuses"
                                    @toggle-selection="toggleSelection"
                                />
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
            return this.salesStore.items.filter(s => s.title.toLowerCase().includes(this.search.toLowerCase()))
        },
        filteredBadSales() {
            return this.salesStore.bad_items ?? []
        }
    },
    created() {
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
        toggleIncompleteFilter(key) {
            // Инвертируем значение
            this.incompleteFilters[key] = !this.incompleteFilters[key];
            // Перезагружаем список
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

        fetchIncompleteByUrl(url) {
            const match = url.match(/page=(\d+)/);
            const page = match ? parseInt(match[1]) : 1;
            // 🔹 Передаём текущие фильтры
            this.salesStore.fetchIncomplete(page, 20, this.incompleteFilters);
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
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.08); }
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
</style>
