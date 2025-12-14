<script setup>
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";

import CreateAgentTaskForm from "@/Components/Sales/Forms/CreateAgentTaskForm.vue";
import SaleForm from "@/Components/Sales/SaleForm.vue";
</script>
<template>

    <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню действий">
        <button type="button"
                @click="goTo('SalePage')"
                class="btn btn-outline-primary p-3">Список доставок</button>
        <button type="button"
                data-bs-toggle="modal" :data-bs-target="'#newSaleModal'"
                class="btn btn-outline-primary p-3">Внести доставку</button>
        <button type="button"
                data-bs-toggle="modal" :data-bs-target="'#reportModal'"
                class="btn btn-outline-primary p-3">Сформировать отчет</button>
        <button type="button"
                @click="goTo('ProductPage')"
                class="btn btn-outline-primary p-3">Работа с товаром
        </button>
        <button type="button"
                @click="goTo('SupplierPage')"
                class="btn btn-outline-primary p-3">Список поставщиков</button>
    </div>


    <!-- Модалка создания -->
    <div class="modal fade" id="newSaleModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Создание задания</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SaleForm @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка -->
    <div class="modal fade" :id="'reportModal'" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Генератор отчетов</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ReportGenerator @generate-report="handleReport"></ReportGenerator>
                </div>

            </div>
        </div>
    </div>
</template>
<script>
export default {
        methods:{
            goTo(name) {
                this.$router.push({ name: name})

            },
            handleReport(payload) {

                const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'))
                modal.hide()
                console.log('Отчёт сформирован:', payload)
                // тут можно вызвать API или Pinia store
            },
            async fetchData() {
                bootstrap.Modal.getInstance(document.getElementById('newSaleModal')).hide()
            },
        }
}
</script>
