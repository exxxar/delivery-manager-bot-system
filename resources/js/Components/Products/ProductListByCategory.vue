<script setup>
import Pagination from "@/Components/Pagination.vue";
</script>

<template>
    <div
        v-for="category in productCategoryStore.items"
        :key="category.id"
        class="card mb-2"
    >
        <div class="card-header ">
            <h6 class="mb-0">{{ category.name }} ({{ category.products_count || 0 }} ед.)</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small" v-if="category.description">{{ category.description }}</p>
            <ul class="list-group list-group-flush">
                <li
                    v-for="product in category.products"
                    :key="product.id"
                    class="list-group-item d-flex justify-content-between align-items-center"
                >
                    <span
                        @click.prevent="openView(product)"
                        class="small">{{ product.name }}</span>
                    <span class="badge bg-success">{{ product.price }} ₽</span>
                </li>
            </ul>
            <div class="mt-2 text-center" v-if="category.products?.length<category.products_count">
                <button
                    class="btn btn-outline-primary btn-sm"
                    @click="loadMore(category.id)"
                >
                    Загрузить ещё
                </button>
            </div>
        </div>
    </div>

    <Pagination
        :pagination="productCategoryStore.pagination"
        @page-changed="fetchDataByUrl"
    />

    <!-- Сообщение если список пуст -->
    <div v-if="productCategoryStore.items.length === 0" class="alert alert-info mt-3">
        Товаров пока нет.
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


import {useProductCategoriesStore} from "@/stores/product-categories";
import ProductCard from "@/Components/Products/ProductCard.vue";

export default {
    name: 'Categories',
    components: { ProductCard},
    data() {
        return {
            productCategoryStore: useProductCategoriesStore(),
            selectedProduct:null,
            pageMap: {} // хранит текущую страницу для каждого поставщика
        }
    },
    mounted() {
        this.fetchProductsByCategory()
    },
    methods: {

        async fetchDataByUrl(url) {
            await this.productCategoryStore.fetchByUrl(url)
        },
        async fetchProductsByCategory() {
            await this.productCategoryStore.fetchProductsByCategory()
        },
        openView(product) {
           this.selectedProduct = product
            new bootstrap.Modal(document.getElementById('viewProductModal')).show()
        },
        async loadMore(supplierId) {
            // увеличиваем страницу для конкретного поставщика
            if (!this.pageMap[supplierId]) {
                this.pageMap[supplierId] = 1
            }
            this.pageMap[supplierId]++

            await this.productCategoryStore.loadMoreProductsInCategory(supplierId, this.pageMap[supplierId])
        },
        closeEdit(){
            bootstrap.Modal.getInstance(document.getElementById('viewProductModal')).hide()
        },
        openEdit(product) {
            this.$emit("edit-product", product)
            bootstrap.Modal.getInstance(document.getElementById('viewProductModal')).hide()
        },
    },

}
</script>
