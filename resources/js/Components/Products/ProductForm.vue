<script setup>
import ProductCategoryList from "@/Components/ProductCategory/ProductCategoryList.vue";
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
</script>

<template>
    <div class="container-fluid p-0">
        <template v-if="tab==='main'">
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
                    <button type="button" class="btn btn-outline-light text-primary" @click="tab='supplier'">
                        Выбрать
                    </button>
                </div>

                <!-- Категория -->
                <div class="input-group mb-2">
                    <div class="form-floating flex-grow-1">
                        <input type="text" class="form-control" id="category" :value="categoryName" placeholder="Категория" readonly>
                        <label for="category">Категория</label>
                    </div>
                    <button type="button" class="btn btn-outline-light text-primary" @click="tab='category'">
                        Выбрать
                    </button>
                </div>

                <!-- Кнопка -->
                <button
                    :disabled="!supplierName||!categoryName"
                    type="submit" class="btn btn-primary w-100 p-3">
                    {{ isEdit ? 'Сохранить изменения' : 'Добавить продукт' }}
                </button>
            </form>
        </template>


        <template v-if="tab==='supplier'">
            <button
                @click="tab='main'"
                class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
            </button>
            <SupplierList
                :for-select="true"
                @select="selectSupplier" />
        </template>

        <template v-if="tab==='category'">
            <button
                @click="tab='main'"
                class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
            </button>
            <ProductCategoryList
                :for-select="true"
                @select="selectCategory" />
        </template>
    </div>
</template>

<script>
import axios from 'axios'
import {useProductsStore} from "@/stores/products";

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
            tab:'main',
            productStore: useProductsStore(),

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

        selectSupplier(supplier) {
            this.form.supplier_id = supplier.id
            this.supplierName = supplier.name
            this.tab = "main"
        },
        selectCategory(category) {
            this.form.product_category_id = category.id
            this.categoryName = category.name
            this.tab = "main"
        },
        async submitForm() {
            try {
                if (this.isEdit) {
                    await this.productStore.update(this.form.id, this.form)
                } else {
                    await this.productStore.create(this.form)
                }
                this.$emit('saved')
            } catch (error) {
                console.error('Ошибка сохранения продукта:', error)
            }
        }
    }
}
</script>
