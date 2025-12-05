<script setup>
import Pagination from "@/Components/Pagination.vue";
import ProductFilter from "@/Components/Products/ProductFilter.vue";
</script>

<template>


    <template v-if="!forSelect">
        <ProductFilter></ProductFilter>
    </template>
    <ul class="list-group">
        <li v-for="product in productStore.items" :key="product.id"
            class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-bold">{{ product.name }}</div>
                <small class="text-muted">{{ product.price }} ₽</small>
            </div>

            <!-- Dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm" type="button"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <template v-if="forSelect">
                        <li><a class="dropdown-item" href="#" @click.prevent="$emit('select', product)">Выбрать</a></li>
                    </template>
                    <template v-if="!forSelect">
                    <li><a class="dropdown-item" href="#" @click.prevent="openView(product)">Просмотреть</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="openEdit(product)">Редактировать</a></li>
                    <li><a class="dropdown-item text-danger" href="#"
                           @click.prevent="confirmDelete(product)">Удалить</a></li>
                    </template>
                </ul>
            </div>
        </li>
    </ul>

    <Pagination
        :pagination="productStore.pagination"
        @page-changed="fetchDataByUrl"
    />

    <!-- Сообщение если список пуст -->
    <div v-if="productStore.items.length === 0" class="alert alert-info mt-3">
        Товаров пока нет.
    </div>



    <!-- Модалка редактирования -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование товара</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ProductForm v-if="selectedProduct" :initialData="selectedProduct" @saved="fetchProducts"/>
                </div>
            </div>
        </div>
    </div>


    <!-- Модалка просмотра -->
    <div class="modal fade" id="viewProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Карточка товара</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ProductCard v-if="selectedProduct" :product="selectedProduct"
                                 @close="closeEdit"
                                 @edit="openEdit"/>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import axios from 'axios'
import ProductForm from './ProductForm.vue'
import ProductCard from './ProductCard.vue'
import {useProductsStore} from "@/stores/products";
import {useModalStore} from "@/stores/utillites/useConfitmModalStore";

export default {
    name: 'ProductList',
    components: {ProductForm, ProductCard},
    props: ["forSelect"],
    data() {
        return {
            productStore: useProductsStore(),
            modalStore: useModalStore(),
            products: [],
            selectedProduct: null
        }
    },
    created() {
        this.fetchData()
    },
    methods: {
        async fetchData(page = 1) {
            await this.productStore.fetchAllByPage(page)
        },
        async fetchDataByUrl(url) {
            await this.productStore.fetchByUrl(url)
        },
        closeEdit() {
            const editModal = bootstrap.Modal.getInstance(document.getElementById('viewProductModal'));
            if (editModal)
                editModal.hide()
        },
        openEdit(product) {
            this.selectedProduct = product
            new bootstrap.Modal(document.getElementById('editProductModal')).show()
            this.closeEdit()
        },
        confirmDelete(product) {
            this.selectedProduct = product
            this.modalStore.open(
                `Вы уверены, что хотите удалить <b>${this.selectedProduct?.name}</b>?`,
                () => this.productStore.remove(this.selectedProduct.id),
                () => this.modalStore.close()
            )
        },
        async deleteProduct() {
            this.productStore.remove(this.selectedProduct.id)
            bootstrap.Modal.getInstance(document.getElementById('deleteProductModal')).hide()

        },
        openView(product) {
            this.selectedProduct = product
            new bootstrap.Modal(document.getElementById('viewProductModal')).show()
        }
    }
}
</script>
