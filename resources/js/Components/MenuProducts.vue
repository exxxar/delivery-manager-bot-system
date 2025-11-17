<template>
    <div class="container py-3">
        <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню">
            <button type="button"
                    @click="selectMenu('products')"
                    class="btn btn-outline-primary p-3">Список товаров</button>
            <button type="button"
                    @click="selectMenu('categories')"
                    class="btn btn-outline-primary p-3">Список категорий</button>
            <button
                type="button" class="btn btn-outline-primary p-3">Скачать товары</button>
            <button
                type="button" class="btn btn-outline-primary p-3">Загрузить прайс с товарами и категориями</button>
        </div>
    </div>

</template>
<script>
export default {
    methods:{
        selectMenu(name){
            this.$emit("select", name)
        }
    }
}
</script>
