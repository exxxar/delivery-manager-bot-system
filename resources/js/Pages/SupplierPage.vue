<script setup>
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <SupplierList></SupplierList>

        <nav
            class="navbar bg-transparent position-fixed bottom-0 start-0 w-100">
            <div class="container-fluid">
                <button
                    type="button"
                    class="btn btn-primary w-100 p-3">
                    Добавить поставщика
                </button>
            </div>
        </nav>

    </div>
</template>
<script>
export default {}
</script>
