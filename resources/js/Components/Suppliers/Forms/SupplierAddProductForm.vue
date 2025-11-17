<template>
    <!-- Основная форма -->
    <template v-if="tab === 'form'">
        <form @submit.prevent="createProduct">
            <div class="mb-3">
                <label class="form-label">Название товара</label>
                <input v-model="form.name" type="text" class="form-control" required />
            </div>

            <div class="mb-3">
                <label class="form-label">Описание</label>
                <textarea v-model="form.description" class="form-control"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Цена</label>
                <input v-model="form.price" type="number" step="0.01" class="form-control" min="0" required />
            </div>

            <div class="mb-3">
                <label class="form-label">Количество</label>
                <input v-model="form.count" type="number" class="form-control" min="0" required />
            </div>

            <!-- Кнопка выбора категории -->
            <div class="mb-3">
                <button
                    type="button"
                    class="btn w-100 p-3"
                    :class="form.category ? 'btn-success' : 'btn-danger'"
                    @click="tab = 'categories'"
                >
                    {{ form.category ? form.category.name : 'Выберите категорию...' }}
                </button>
            </div>

            <!-- Кнопка выбора поставщика -->
            <div class="mb-3">
                <button
                    type="button"
                    class="btn w-100 p-3"
                    :class="form.supplier ? 'btn-success' : 'btn-danger'"
                    @click="tab = 'suppliers'"
                >
                    {{ form.supplier ? form.supplier.name : 'Выберите поставщика...' }}
                </button>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Добавить товар
            </button>
        </form>
    </template>

    <!-- Заглушка для категорий -->
    <template v-if="tab === 'categories'">
        <h4>Выбор категории</h4>

        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад к форме</button>
    </template>

    <!-- Заглушка для поставщиков -->
    <template v-if="tab === 'suppliers'">
        <h4>Выбор поставщика</h4>

        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад к форме</button>
    </template>
</template>

<script>
import { useJobStore } from '@/stores/useJobStore'

export default {
    name: 'SupplierAddProductForm',
    data() {
        return {
            tab: 'form',
            form: {
                name: '',
                description: '',
                price: 0,
                count: 0,
                category: null,
                supplier: null
            },

        }
    },
    methods: {
        selectCategory(category) {
            this.form.category = category
            this.tab = 'form'
        },
        selectSupplier(supplier) {
            this.form.supplier = supplier
            this.tab = 'form'
        },
        async createProduct() {
            try {
                const jobStore = useJobStore()
                await jobStore.submitForm(
                    'products',
                    {
                        name: this.form.name,
                        description: this.form.description,
                        price: this.form.price,
                        count: this.form.count,
                        supplier_id: this.form.supplier?.id,
                        product_category_id: this.form.category?.id
                    },
                    'Товар добавлен',
                    'Ошибка при добавлении товара'
                )
            } catch (e) {
                console.error(e)
            }
        }
    }
}
</script>
