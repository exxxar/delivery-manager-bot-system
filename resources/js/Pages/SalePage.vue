<script setup>
import SaleList from "@/Components/Sales/SaleList.vue";
import BackBtn from "@/Components/BackBtn.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <BackBtn/>

        <h4 class="mb-3">Список продаж</h4>
        <SaleList></SaleList>

        <nav
            class="navbar bg-transparent position-fixed bottom-0 start-0 w-100">
            <div class="container-fluid">
                <button
                    @click="addSale"
                    type="button"
                    class="btn btn-primary w-100 p-3">
                    Добавить продажу
                </button>
            </div>
        </nav>
    </div>
</template>
<script>
export default {

}
</script>
