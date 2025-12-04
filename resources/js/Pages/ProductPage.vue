<script setup>
import ProductList from "@/Components/Products/ProductList.vue";
import MenuProducts from "@/Components/MenuProducts.vue";
import ProductCategoryList from "@/Components/ProductCategory/ProductCategoryList.vue";
import ProductListBySupplier from "@/Components/Products/ProductListBySupplier.vue";
import ProductListByCategory from "@/Components/Products/ProductListByCategory.vue";
import ProductForm from "@/Components/Products/ProductForm.vue";
import ImportProducts from "@/Components/Products/ImportProducts.vue";
import ProductCategoryForm from "@/Components/ProductCategory/ProductCategoryForm.vue";
import BackBtn from "@/Components/BackBtn.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <template v-if="tab==='menu'">
            <BackBtn/>
            <MenuProducts v-on:select="selectMenu"></MenuProducts>
        </template>
        <template v-if="tab==='product'">
            <button
                @click="tab='menu'"
                class="btn btn-outline-light text-secondary mb-3" style="position: sticky; top:80px; z-index: 100;">Назад</button>
            <ProductForm v-if="selectedProduct" :initial-data="selectedProduct"></ProductForm>
            <p class="alert alert-info" v-else>
                Продукт не выбран
            </p>
        </template>
        <template v-if="tab==='products'">
            <button
                @click="tab='menu'"
                class="btn btn-outline-light text-secondary mb-3" style="position: sticky; top:80px; z-index: 100;">Назад</button>
            <h4 class="mb-3">Список товаров</h4>
            <ProductList></ProductList>
        </template>
        <template v-if="tab==='products-by-supplier'">
            <button
                @click="tab='menu'"
                class="btn btn-outline-light text-secondary mb-3" style="position: sticky; top:80px; z-index: 100;">Назад</button>
            <h4 class="mb-3">Список товаров по поставщику</h4>
            <ProductListBySupplier v-on:edit-product="openEditProduct"></ProductListBySupplier>
        </template>
        <template v-if="tab==='products-by-categories'">
            <button
                @click="tab='menu'"
                class="btn btn-outline-light text-secondary mb-3" style="position: sticky; top:80px; z-index: 100;">Назад</button>
            <h4 class="mb-3">Список товаров по категориям</h4>
            <ProductListByCategory v-on:edit-product="openEditProduct"></ProductListByCategory>


        </template>
        <template v-if="tab==='categories'">
            <button
                @click="tab='menu'"
                class="btn btn-outline-light text-secondary mb-3" style="position: sticky; top:80px; z-index: 100;">Назад</button>
            <h4 class="mb-3">Категории товара</h4>
            <ProductCategoryList></ProductCategoryList>


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
        </template>
        <template v-if="tab==='import-products'">
            <button
                @click="tab='menu'"
                class="btn btn-outline-light text-secondary mb-3" style="position: sticky; top:80px; z-index: 100;">Назад</button>
            <ImportProducts></ImportProducts>
        </template>
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
            loading:false,
            selectedProduct:null,
            tab: 'menu',
        }
    },
    mounted() {

    },
    methods: {
        selectMenu(item) {
            this.tab = item
        },
        openEditProduct(product){
            this.selectMenu("product")
            this.selectedProduct = product
        },
        fetchData() {
            this.loading = true
            this.$nextTick(() => {
                this.loading = false
            })
        }
    }
}
</script>
