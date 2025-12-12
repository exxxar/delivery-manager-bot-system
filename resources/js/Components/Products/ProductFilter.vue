<template>
    <div>
        <div class="d-flex mb-2">
            <!-- Кнопка вызова модалки -->
            <button
                style="font-size:12px;"
                class="btn btn-secondary" @click="openFilter">Фильтр</button>

            <!-- Dropdown сортировки -->
            <div class="dropdown d-inline-block ms-2">
                <button
                    style="font-size:12px;"
                    class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                    {{ sortableFields[sort.field].slice(0, 17) }}
                    <span v-if="sortableFields[sort.field].length>17">...</span>
                    (<span v-if="sort.direction==='asc'"><i class="fa-solid fa-arrow-down"></i></span>
                    <span v-if="sort.direction==='desc'"><i class="fa-solid fa-arrow-up"></i></span>)
                </button>
                <ul class="dropdown-menu">
                    <li v-for="(name, field) in sortableFields" :key="field">
                        <a class="dropdown-item" @click="changeSort(field)">
                            {{ name }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Модалка фильтрации -->
        <div class="modal fade" id="productFilterModal" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <form class="modal-content"  @submit.prevent="applyFilters">
                        <div class="modal-header">
                            <h5 class="modal-title">Фильтры товаров</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Название -->
                            <div class="form-floating mb-2">
                                <input v-model="filters.name" class="form-control" id="nameInput" placeholder="Название"/>
                                <label for="nameInput">Название</label>
                            </div>

                            <!-- Описание -->
                            <div class="form-floating mb-2">
                                <input v-model="filters.description" class="form-control" id="descInput" placeholder="Описание"/>
                                <label for="descInput">Описание</label>
                            </div>

                            <!-- Цена -->
                            <div class="form-floating mb-2">
                                <input type="number" v-model="filters.price" class="form-control" id="priceInput" placeholder="Цена"/>
                                <label for="priceInput">Цена</label>
                            </div>

                            <!-- Количество -->
                            <div class="form-floating mb-2">
                                <input type="number" v-model="filters.count" class="form-control" id="countInput" placeholder="Количество"/>
                                <label for="countInput">Количество</label>
                            </div>

                            <!-- Поставщик -->
                            <div class="form-floating mb-2">
                                <input type="number" v-model="filters.supplier_id" class="form-control" id="supplierInput" placeholder="ID поставщика"/>
                                <label for="supplierInput">ID поставщика</label>
                            </div>

                            <!-- Категория -->
                            <div class="form-floating mb-2">
                                <input type="number" v-model="filters.product_category_id" class="form-control" id="categoryInput" placeholder="ID категории"/>
                                <label for="categoryInput">ID категории</label>
                            </div>

                            <h6 class="mt-3">Отображаемые поля</h6>
                            <div v-for="(label, field) in sortableFields" :key="field" class="form-check form-switch mb-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    v-model="field_visible[field]"
                                    :id="`switch-${field}`"
                                />
                                <label class="form-check-label" :for="`switch-${field}`">
                                    {{ label }}
                                </label>
                            </div>

                            <button type="button"
                                    @click="clearFilters"
                                    class="btn btn-outline-light text-secondary mb-2 w-100 p-3">Очистить фильтры
                            </button>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary w-100 p-3">Применить</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProductFilter',
    data() {
        return {
            field_visible: {},
            filters: {
                name: '',
                description: '',
                price: '',
                count: '',
                supplier_id: '',
                product_category_id: ''
            },
            sort: {
                field: 'id',
                direction: 'asc'
            },
            sortableFields: {
                id: "ID",
                name: "Название",
                description: "Описание",
                price: "Цена",
                count: "Количество",
                supplier_id: "Поставщик",
                product_category_id: "Категория",
                created_at: "Дата создания",
                updated_at: "Дата обновления"
            }
        }
    },
    created() {
        // по умолчанию показываем name и price
        const defaultVisible = ['name', 'price']
        for (const field in this.sortableFields) {
            this.field_visible[field] = defaultVisible.includes(field)
        }
    },
    methods: {
        clearFilters() {
            const filters = {
                name: '',
                description: '',
                price: '',
                count: '',
                supplier_id: '',
                product_category_id: ''
            }

            this.size = 20
            this.page = 1

            let tmpVisibleFields = [ 'name', 'price']
            for (const field in this.sortableFields) {
                this.field_visible[field] = tmpVisibleFields.indexOf(field) !== -1
            }

            this.filters = filters
        },
        openFilter() {
            new bootstrap.Modal(document.getElementById('productFilterModal')).show()
        },
        changeSort(field) {
            if (this.sort.field === field) {
                this.sort.direction = this.sort.direction === 'asc' ? 'desc' : 'asc'
            } else {
                this.sort.field = field
                this.sort.direction = 'asc'
            }
            this.$emit('apply-filters', { filters:this.filters, sort: this.sort, field_visible: this.field_visible })
        },
        applyFilters() {
            this.$emit('apply-filters', { filters:this.filters, sort: this.sort, field_visible: this.field_visible })
            bootstrap.Modal.getInstance(document.getElementById('productFilterModal')).hide()
        }
    }
}
</script>
