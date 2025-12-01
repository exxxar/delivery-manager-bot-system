<script setup>
import ProductCategoryList from "@/Components/ProductCategory/ProductCategoryList.vue";

import ProductCategoryForm from "@/Components/ProductCategory/ProductCategoryForm.vue";


</script>
<template>

    <div class="container-fluid p-3">
        <h4 class="mb-3">Категории товара</h4>
        <ProductCategoryList v-if="!loading"></ProductCategoryList>

        <nav class="navbar fixed-bottom p-3">
            <button
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#createCategoryModal"
                class="btn w-100 p-3 btn-primary"
            >
                Добавить категорию
            </button>
        </nav>
    </div>

    <!-- Модалка создания -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Создание новой категории товара</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ProductCategoryForm @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>


</template>
<script>
export default {
    data() {
        return {
            loading: false,
        }
    },
    methods: {
        fetchData() {
            this.loading = true
            this.$nextTick(() => {
                this.loading = false
            })
        }
    }
}
</script>
