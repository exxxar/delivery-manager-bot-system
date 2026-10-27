<script setup>
import SaleFilterForm from "@/Components/Sales/SaleFilterForm.vue";
</script>

<template>
    <section class="sale-filter-toolbar">
        <div class="toolbar-heading">
            <div class="toolbar-icon"><i class="fa-solid fa-sliders"></i></div>
            <div class="toolbar-copy">
                <h2>Фильтрация и сортировка</h2>
                <p>Настройте отображение сделок под свои задачи</p>
            </div>
        </div>

        <div class="toolbar-actions">
            <button
                class="btn filter-trigger"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#saleFilterModal"
            >
                <i class="fa-solid fa-filter"></i>
                <span>Фильтры</span>
            </button>

            <div class="dropdown sort-dropdown">
                <button
                    class="btn sort-trigger dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                    <span class="sort-trigger-copy">
                        <small>Сортировка</small>
                        <strong>{{ sortLabels[sort.field] || sort.field }}</strong>
                    </span>
                    <span class="sort-direction" :aria-label="sort.direction === 'asc' ? 'По возрастанию' : 'По убыванию'">
                        <i :class="sort.direction === 'asc' ? 'fa-solid fa-arrow-up-short-wide' : 'fa-solid fa-arrow-down-short-wide'"></i>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end sort-menu">
                    <li class="dropdown-header">Сортировать сделки по</li>
                    <li v-for="(label, field) in sortLabels" :key="field">
                        <button
                            class="dropdown-item"
                            :class="{ 'active-sort': sort.field === field }"
                            type="button"
                            @click="changeSort(field)"
                        >
                            <span>{{ label }}</span>
                            <i v-if="sort.field === field" class="fa-solid fa-check"></i>
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button class="dropdown-item direction-option" type="button" @click="changeSort(sort.field)">
                            <i :class="sort.direction === 'asc' ? 'fa-solid fa-arrow-up-wide-short' : 'fa-solid fa-arrow-down-wide-short'"></i>
                            {{ sort.direction === 'asc' ? 'По возрастанию' : 'По убыванию' }}
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <div class="modal fade sale-filter-modal" id="saleFilterModal" tabindex="-1" aria-labelledby="saleFilterModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-title-wrap">
                            <span class="modal-title-icon"><i class="fa-solid fa-filter-circle-dollar"></i></span>
                            <div>
                                <h5 class="modal-title" id="saleFilterModalLabel">Фильтры сделок</h5>
                                <p class="modal-subtitle">Укажите параметры для поиска нужных записей</p>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body">
                        <SaleFilterForm @apply-filters="applyFilters" />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'SaleFilterWithSort',
    data() {
        return {
            sort: {
                field: 'id',
                direction: 'asc'
            },
            sortLabels: {
                id: 'ID',
                title: 'Название',
                description: 'Описание',
                status: 'Статус',
                due_date: 'Дата встречи',
                sale_date: 'Дата продажи',
                payment_type: 'Тип оплаты',
                actual_delivery_date: 'Фактическая доставка',
                quantity: 'Количество',
                total_price: 'Цена',
                agent_id: 'Администратор',
                customer_id: 'Клиент',
                supplier_id: 'Поставщик',
                created_by_id: 'Создатель сделки',
                created_at: 'Дата создания'
            }
        }
    },
    methods: {
        changeSort(field) {
            if (this.sort.field === field) {
                this.sort.direction = this.sort.direction === 'asc' ? 'desc' : 'asc'
            } else {
                this.sort.field = field
                this.sort.direction = 'asc'
            }
            this.$emit('apply-sort', { ...this.sort })
        },
        applyFilters(item) {
            const payload = {
                filters: item.filters,
                sort: { ...this.sort }
            }
            this.$emit('apply-filters', payload)
            const modalElement = document.getElementById('saleFilterModal')
            const modalInstance = window.bootstrap?.Modal?.getInstance(modalElement)
            if (modalInstance) modalInstance.hide()
        }
    }
}
</script>

<style scoped>
.sale-filter-toolbar {
    --mp-blue: #2457d6;
    --mp-blue-dark: #173b91;
    --mp-red: #df3548;
    --mp-text: #17233b;
    --mp-muted: #778399;
    --mp-border: #e5eaf2;
    width: 100%;
    padding: 18px;
    border: 1px solid var(--mp-border);
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 8px 28px rgba(23, 35, 59, .06);
    color: var(--mp-text);
}

.toolbar-heading,
.toolbar-actions,
.modal-title-wrap {
    display: flex;
    align-items: center;
}

.toolbar-heading { gap: 12px; margin-bottom: 18px; }
.toolbar-icon,
.modal-title-icon {
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: #edf3ff;
    color: var(--mp-blue);
    font-size: 19px;
}
.toolbar-copy h2 {
    margin: 0 0 4px;
    color: var(--mp-text);
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -.025em;
}
.toolbar-copy p { margin: 0; color: var(--mp-muted); font-size: 13px; }
.toolbar-actions { gap: 10px; flex-wrap: wrap; }

.filter-trigger,
.sort-trigger {
    min-height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    border-radius: 13px;
    padding: 10px 16px;
    font-weight: 700;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}
.filter-trigger {
    border: 1px solid var(--mp-blue);
    background: var(--mp-blue);
    color: #fff;
    box-shadow: 0 5px 12px rgba(36, 87, 214, .18);
}
.filter-trigger:hover { background: var(--mp-blue-dark); border-color: var(--mp-blue-dark); color: #fff; transform: translateY(-1px); }
.sort-trigger {
    border: 1px solid var(--mp-border);
    background: #fff;
    color: var(--mp-text);
    text-align: left;
}
.sort-trigger:hover,
.sort-trigger:focus { border-color: #b7c9f7; background: #f8faff; color: var(--mp-text); box-shadow: 0 4px 14px rgba(23, 35, 59, .06); }
.sort-trigger > i:first-child { color: var(--mp-blue); font-size: 17px; }
.sort-trigger-copy { display: flex; flex-direction: column; gap: 2px; min-width: 120px; }
.sort-trigger-copy small { color: var(--mp-muted); font-size: 11px; font-weight: 500; line-height: 1.1; }
.sort-trigger-copy strong { font-size: 13px; font-weight: 750; }
.sort-direction { display: grid; place-items: center; width: 28px; height: 28px; border-radius: 9px; background: #edf3ff; color: var(--mp-blue); }
.sort-trigger::after { margin-left: 2px; }
.sort-menu { min-width: 240px; padding: 8px; border: 1px solid var(--mp-border); border-radius: 14px; box-shadow: 0 14px 38px rgba(23, 35, 59, .14); }
.sort-menu .dropdown-header { padding: 8px 10px; color: var(--mp-muted); font-size: 11px; text-transform: uppercase; letter-spacing: .06em; }
.sort-menu .dropdown-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px; border-radius: 8px; color: var(--mp-text); font-size: 13px; }
.sort-menu .dropdown-item:hover { background: #f2f6ff; color: var(--mp-blue); }
.sort-menu .dropdown-item.active-sort { background: #edf3ff; color: var(--mp-blue); font-weight: 750; }
.sort-menu .dropdown-item.active-sort i { color: var(--mp-blue); }
.direction-option { color: var(--mp-muted) !important; }

.sale-filter-modal .modal-content { overflow: hidden; border: 1px solid var(--mp-border); border-radius: 20px; box-shadow: 0 24px 70px rgba(23, 35, 59, .22); }
.sale-filter-modal .modal-header { padding: 20px 22px; border-bottom: 1px solid var(--mp-border); background: #fff; }
.modal-title-wrap { gap: 12px; }
.modal-title-icon { width: 42px; height: 42px; border-radius: 13px; font-size: 17px; }
.sale-filter-modal .modal-title { margin: 0; color: var(--mp-text); font-size: 18px; font-weight: 800; }
.modal-subtitle { margin: 4px 0 0; color: var(--mp-muted); font-size: 12px; }
.sale-filter-modal .modal-body { padding: 20px 22px; background: #f8faff; }

@media (max-width: 575.98px) {
    .sale-filter-toolbar { padding: 14px; border-radius: 16px; }
    .toolbar-heading { align-items: flex-start; margin-bottom: 14px; }
    .toolbar-icon { width: 40px; height: 40px; }
    .toolbar-copy h2 { font-size: 16px; }
    .toolbar-copy p { font-size: 12px; }
    .toolbar-actions { align-items: stretch; flex-direction: column; }
    .filter-trigger, .sort-trigger { width: 100%; justify-content: flex-start; }
    .sort-trigger-copy { flex: 1; }
    .sale-filter-modal .modal-dialog { margin: 10px; }
    .sale-filter-modal .modal-header, .sale-filter-modal .modal-body { padding: 16px; }
    .modal-subtitle { max-width: 230px; }
}
</style>
