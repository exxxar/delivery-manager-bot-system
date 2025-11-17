<script setup>
import ProductCategoryList from "@/Components/ProductCategory/ProductCategoryList.vue";
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
</script>

<template>
    <div class="container-fluid p-0">
        <form @submit.prevent="submitForm">

            <!-- Название -->
            <div class="form-floating mb-2">
                <input v-model="form.name" type="text" class="form-control" id="name" placeholder="Название" required>
                <label for="name">Название продукта</label>
            </div>

            <!-- Описание -->
            <div class="form-floating mb-2">
                <textarea v-model="form.description" class="form-control" id="description" placeholder="Описание" style="height: 120px" required></textarea>
                <label for="description">Описание</label>
            </div>

            <!-- Цена -->
            <div class="form-floating mb-2">
                <input v-model="form.price" type="number" step="0.01" class="form-control" id="price" placeholder="Цена" required>
                <label for="price">Цена</label>
            </div>

            <!-- Количество -->
            <div class="form-floating mb-2">
                <input v-model="form.count" type="number" class="form-control" id="count" placeholder="Количество" required>
                <label for="count">Количество</label>
            </div>

            <!-- Поставщик -->
            <div class="input-group mb-2">
                <div class="form-floating flex-grow-1">
                    <input type="text" class="form-control" id="supplier" :value="supplierName" placeholder="Поставщик" readonly>
                    <label for="supplier">Поставщик</label>
                </div>
                <button type="button" class="btn btn-outline-secondary" @click="openSupplierModal">
                    <i :class="form.supplier_id ? 'fas fa-lock' : 'fas fa-lock-open'"></i>
                </button>
            </div>

            <!-- Категория -->
            <div class="input-group mb-2">
                <div class="form-floating flex-grow-1">
                    <input type="text" class="form-control" id="category" :value="categoryName" placeholder="Категория" readonly>
                    <label for="category">Категория</label>
                </div>
                <button type="button" class="btn btn-outline-secondary" @click="openCategoryModal">
                    <i :class="form.product_category_id ? 'fas fa-lock' : 'fas fa-lock-open'"></i>
                </button>
            </div>

            <!-- Кнопка -->
            <button type="submit" class="btn btn-primary w-100 p-3">
                {{ isEdit ? 'Сохранить изменения' : 'Добавить продукт' }}
            </button>
        </form>

        <!-- Модалка выбора поставщика -->
        <div class="modal fade" id="supplierModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Выбор поставщика</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <SupplierList @select="selectSupplier" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Модалка выбора категории -->
        <div class="modal fade" id="categoryModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Выбор категории</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ProductCategoryList @select="selectCategory" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'ProductForm',
    props: {
        initialData: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            form: {
                name: '',
                description: '',
                price: 0,
                count: 0,
                supplier_id: null,
                product_category_id: null
            },
            supplierName: '',
            categoryName: '',
            isEdit: false
        }
    },
    created() {
        if (this.initialData) {
            this.form = { ...this.initialData }
            this.isEdit = true
            this.supplierName = this.initialData.supplier?.name || ''
            this.categoryName = this.initialData.category?.name || ''
        }
    },
    methods: {
        openSupplierModal() {
            new bootstrap.Modal(document.getElementById('supplierModal')).show()
        },
        openCategoryModal() {
            new bootstrap.Modal(document.getElementById('categoryModal')).show()
        },
        selectSupplier(supplier) {
            this.form.supplier_id = supplier.id
            this.supplierName = supplier.name
            bootstrap.Modal.getInstance(document.getElementById('supplierModal')).hide()
        },
        selectCategory(category) {
            this.form.product_category_id = category.id
            this.categoryName = category.name
            bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide()
        },
        async submitForm() {
            try {
                if (this.isEdit) {
                    await axios.put(`/api/products/${this.form.id}`, this.form)
                    alert('Продукт обновлён!')
                } else {
                    await axios.post('/api/products', this.form)
                    alert('Продукт добавлен!')
                }
                this.$emit('saved')
            } catch (error) {
                console.error('Ошибка сохранения продукта:', error)
            }
        }
    }
}
</script>
