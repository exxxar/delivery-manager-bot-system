<script setup>
import SaleList from "@/Components/Sales/SaleList.vue";
import NotVerifiedSaleList from "@/Components/Sales/NotVerifiedSaleList.vue";
import BackBtn from "@/Components/BackBtn.vue";
import SaleForm from "@/Components/Sales/SaleForm.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <BackBtn/>

        <template v-if="user?.role>=3">
            <button
                @click="openVerifyModal"
                class="btn btn-danger w-100 p-3 mb-2"><i class="fa-solid fa-list-check"></i> Заявки на проверку</button>
        </template>
        <h4 class="mb-3">Список доставок</h4>
        <SaleList v-if="!loading"></SaleList>

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

    <!-- Модалка верификации -->
    <div class="modal fade" id="verifiedSales" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Проверка заявок</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <template v-if="user?.role>=3">
                        <NotVerifiedSaleList v-if="!loading"></NotVerifiedSaleList>
                    </template>
                </div>
            </div>
        </div>
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
import {useUsersStore} from "@/stores/users.ts";

export default {
    data() {
        return {
            userStore: useUsersStore(),
            loading: false,
        }
    },
    computed:{
        user() {
            return this.userStore.self || null
        },
    },
    methods: {
        openVerifyModal() {
            this.loading = true
            this.$nextTick(() => {
                this.loading = false
                new bootstrap.Modal(document.getElementById('verifiedSales')).show()
            })

        },
        addSale() {
            this.loading = true
            this.$nextTick(() => {
                this.loading = false
                new bootstrap.Modal(document.getElementById('newSaleModal')).show()
            })

        },
        async fetchData() {
            this.loading = true
            this.$nextTick(() => {
                this.loading = false
                bootstrap.Modal.getInstance(document.getElementById('newSaleModal')).hide()
            })

        },
    }
}
</script>
