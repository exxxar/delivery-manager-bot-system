<script setup>
import Pagination from "@/Components/Pagination.vue";
</script>

<template>

        <div
            v-for="supplier in supplierStore.items"
            :key="supplier.id"
            class="card mb-2"
        >
            <div class="card-header">
                <h6 class="mb-0">{{ supplier.name }} ({{supplier.products_count|| 0}}ед.)</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small" v-if="supplier.description">{{ supplier.description }}</p>
                <p class="text-muted small" v-if="supplier.address">{{ supplier.address }}</p>
                <ul class="list-group list-group-flush">
                    <li
                        v-for="product in supplier.products"
                        :key="product.id"
                        class="list-group-item d-flex justify-content-between align-items-center"
                    >
                        <span
                            @click.prevent="openView(product)"
                            class="small text-primary btn-link">{{ product.name }}</span>
                        <span class="badge bg-success">{{ product.price }} ₽</span>
                    </li>
                </ul>
                <div
                    v-if="supplier.products?.length<supplier.products_count"
                    class="mt-3 text-center">
                    <button
                        class="btn btn-outline-primary btn-sm"
                        @click="loadMore(supplier.id)"
                    >
                        Загрузить ещё
                    </button>
                </div>
            </div>
        </div>

    <Pagination
        :pagination="supplierStore.pagination"
        @page-changed="fetchDataByUrl"
    />

    <!-- Сообщение если список пуст -->
    <div v-if="supplierStore.items.length === 0" class="alert alert-info mt-3">
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
import {useSuppliersStore} from "@/stores/suppliers";
import ProductCard from "@/Components/Products/ProductCard.vue";

export default {
    name: 'Suppliers',
    components: {ProductCard},
    data() {
        return {
            supplierStore: useSuppliersStore(),
            selectedProduct:null,
            pageMap: {} // хранит текущую страницу для каждого поставщика
        }
    },
    mounted(){
      this.fetchSuppliers()
    },
    methods: {

        async fetchDataByUrl(url) {
            await this.supplierStore.fetchByUrl(url)
        },
        openView(product) {
            this.selectedProduct = product
            new bootstrap.Modal(document.getElementById('viewProductModal')).show()
        },
        async fetchSuppliers() {
            await this.supplierStore.fetchSupplierWithProducts()
        },
        async loadMore(supplierId) {
            // увеличиваем страницу для конкретного поставщика
            if (!this.pageMap[supplierId]) {
                this.pageMap[supplierId] = 1
            }
            this.pageMap[supplierId]++

            await this.supplierStore.loadMoreSupplierProducts(supplierId, this.pageMap[supplierId])
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
