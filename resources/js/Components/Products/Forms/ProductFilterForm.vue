<template>
    <!-- Основная форма -->
    <template v-if="tab === 'form'">
        <form @submit.prevent="applyFilter">
            <!-- Диапазон цены -->
            <div class="form-floating mb-3">
                <input v-model="form.minPrice" type="number" step="0.01" class="form-control" />
                <label>Минимальная цена</label>
            </div>

            <div class="form-floating mb-3">
                <input v-model="form.maxPrice" type="number" step="0.01" class="form-control" />
                <label>Максимальная цена</label>
            </div>

            <!-- Количество -->
            <div class="form-floating mb-3">
                <input v-model="form.count" type="number" class="form-control" />
                <label>Количество</label>
            </div>

            <!-- Кнопки выбора -->
            <div class="mb-3">
                <button
                    type="button"
                    class="btn w-100 py-3"
                    :class="selectedSuppliers.length ? 'btn-success' : 'btn-danger'"
                    @click="tab = 'suppliers'"
                >
                    {{ selectedSuppliers.length ? 'Выбрано поставщиков: ' + selectedSuppliers.length : 'Выберите поставщиков...' }}
                </button>
            </div>

            <div class="mb-3">
                <button
                    type="button"
                    class="btn w-100 py-3"
                    :class="selectedCategories.length ? 'btn-success' : 'btn-danger'"
                    @click="tab = 'categories'"
                >
                    {{ selectedCategories.length ? 'Выбрано категорий: ' + selectedCategories.length : 'Выберите категории...' }}
                </button>
            </div>

            <!-- Отображение выбранных элементов -->
            <div class="mb-3">
        <span
            v-for="supplier in selectedSuppliers"
            :key="'s-' + supplier.id"
            class="badge bg-primary me-2"
            @click="removeSupplier(supplier)"
            style="cursor: pointer;"
        >
          {{ supplier.name }} ✕
        </span>

                <span
                    v-for="category in selectedCategories"
                    :key="'c-' + category.id"
                    class="badge bg-success me-2"
                    @click="removeCategory(category)"
                    style="cursor: pointer;"
                >
          {{ category.name }} ✕
        </span>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Применить фильтр
            </button>
        </form>
    </template>

    <!-- Заглушка для поставщиков -->
    <template v-if="tab === 'suppliers'">
        <h4>Выбор поставщиков</h4>
        <!-- список поставщиков добавишь сам -->
        <ul class="list-group">
            <li
                v-for="supplier in suppliers"
                :key="supplier.id"
                class="list-group-item list-group-item-action"
                @click="selectSupplier(supplier)"
            >
                {{ supplier.name }}
            </li>
        </ul>
        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад</button>
    </template>

    <!-- Заглушка для категорий -->
    <template v-if="tab === 'categories'">
        <h4>Выбор категорий</h4>
        <!-- список категорий добавишь сам -->
        <ul class="list-group">
            <li
                v-for="category in categories"
                :key="category.id"
                class="list-group-item list-group-item-action"
                @click="selectCategory(category)"
            >
                {{ category.name }}
            </li>
        </ul>
        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад</button>
    </template>
</template>

<script>
export default {
    name: 'ProductFilterForm',
    data() {
        return {
            tab: 'form',
            form: {
                minPrice: null,
                maxPrice: null,
                count: null
            },
            suppliers: [
                { id: 1, name: 'Поставщик А' },
                { id: 2, name: 'Поставщик Б' }
            ],
            categories: [
                { id: 1, name: 'Категория X' },
                { id: 2, name: 'Категория Y' }
            ],
            selectedSuppliers: [],
            selectedCategories: []
        }
    },
    methods: {
        selectSupplier(supplier) {
            if (!this.selectedSuppliers.find(s => s.id === supplier.id)) {
                this.selectedSuppliers.push(supplier)
            }
            this.tab = 'form'
        },
        selectCategory(category) {
            if (!this.selectedCategories.find(c => c.id === category.id)) {
                this.selectedCategories.push(category)
            }
            this.tab = 'form'
        },
        removeSupplier(supplier) {
            this.selectedSuppliers = this.selectedSuppliers.filter(s => s.id !== supplier.id)
        },
        removeCategory(category) {
            this.selectedCategories = this.selectedCategories.filter(c => c.id !== category.id)
        },
        applyFilter() {
            console.log('Фильтр применён:', {
                ...this.form,
                suppliers: this.selectedSuppliers.map(s => s.id),
                categories: this.selectedCategories.map(c => c.id)
            })
        }
    }
}
</script>
