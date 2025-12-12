<script setup>
import SaleFilterForm from "@/Components/Sales/SaleFilterForm.vue";
</script>

<template>
    <div class="d-flex gap-2 mb-3">
        <!-- Кнопка фильтра -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#saleFilterModal">
            Фильтр
        </button>

        <!-- Dropdown сортировки -->
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Сортировка: {{ sortLabels[sort.field] }} ({{ sort.direction }})
            </button>
            <ul class="dropdown-menu">
                <li v-for="(label, field) in sortLabels" :key="field">
                    <a class="dropdown-item" href="#" @click.prevent="changeSort(field)">
                        {{ label }}
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Модалка фильтра -->
    <div class="modal fade" id="saleFilterModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <SaleFilterForm v-on:apply-filters="applyFilters"></SaleFilterForm>
            </div>
        </div>
    </div>

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
                payment_tye: 'Тип оплаты',
                actual_delivery_date: 'Фактическая доставка',
                quantity: 'Количество',
                total_price: 'Цена',
                agent_id: 'Младший администратор',
                customer_id: 'Клиент',
                supplier_id: 'Поставщик',
                created_by_id: 'Администратор',
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
            this.$emit('apply-sort', this.sort)
        },
        applyFilters(item) {
            const payload = {
                filters: item.filters,
                sort: this.sort
            }
            this.$emit('apply-filters', payload)
            bootstrap.Modal.getInstance(document.getElementById('saleFilterModal')).hide()
        }
    }
}
</script>
