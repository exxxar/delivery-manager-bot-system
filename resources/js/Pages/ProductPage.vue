<script setup>
import ProductList from "@/Components/Products/ProductList.vue";
import MenuProducts from "@/Components/MenuProducts.vue";
import ProductCategoryList from "@/Components/ProductCategory/ProductCategoryList.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <template v-if="tab==='menu'">
            <MenuProducts v-on:select="selectMenu"></MenuProducts>
        </template>
        <template v-if="tab==='products'">
            <ProductList></ProductList>
        </template>
        <template v-if="tab==='categories'">
            <ProductCategoryList></ProductCategoryList>
        </template>
    </div>
</template>
<script>
export default {
    data() {
        return {
            tab: 'menu',
        }
    },
    mounted() {

    },
    methods: {
        selectMenu(item) {
            this.tab = item
        }
    }
}
</script>
