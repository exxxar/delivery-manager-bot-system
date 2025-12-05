<script setup>
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
import BackBtn from "@/Components/BackBtn.vue";
import SupplierForm from "@/Components/Suppliers/SupplierForm.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <BackBtn/>
        <h4 class="mb-3">Список поставщиков</h4>
        <SupplierList></SupplierList>

        <nav
            class="navbar bg-transparent position-fixed bottom-0 start-0 w-100">
            <div class="container-fluid">
                <button
                    @click="addSupplier"
                    type="button"
                    class="btn btn-primary w-100 p-3">
                    Добавить поставщика
                </button>
            </div>
        </nav>

    </div>

    <!-- Модалка редактирования -->
    <div class="modal fade" id="newSupplierModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Добавление поставщика</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SupplierForm @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    methods:{
        addSupplier() {
            new bootstrap.Modal(document.getElementById('newSupplierModal')).show()
        },
        async fetchData() {
            bootstrap.Modal.getInstance(document.getElementById('newSupplierModal')).hide()
        },
    }
}
</script>
