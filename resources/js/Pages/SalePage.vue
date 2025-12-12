<script setup>
import SaleList from "@/Components/Sales/SaleList.vue";
import BackBtn from "@/Components/BackBtn.vue";
import SaleForm from "@/Components/Sales/SaleForm.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <BackBtn/>

        <h4 class="mb-3">Список доставок</h4>
        <SaleList></SaleList>

        <nav
            class="navbar bg-transparent position-fixed bottom-0 start-0 w-100">
            <div class="container-fluid">
                <button
                    @click="addSale"
                    type="button"
                    class="btn btn-primary w-100 p-3">
                    Добавить доставку
                </button>
            </div>
        </nav>
    </div>

    <!-- Модалка создания -->
    <div class="modal fade" id="newSaleModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Создание задания</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SaleForm
                        v-if="!loading"
                        @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    data(){
      return {
          loading:false,
      }
    },
    methods:{
        addSale() {
            this.loading = true
            this.$nextTick(()=>{
                this.loading = false
                new bootstrap.Modal(document.getElementById('newSaleModal')).show()
            })

        },
        async fetchData() {
            bootstrap.Modal.getInstance(document.getElementById('newSaleModal')).hide()
        },
    }
}
</script>
