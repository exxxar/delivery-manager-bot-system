<script setup>
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";

import CreateAgentTaskForm from "@/Components/Sales/Forms/CreateAgentTaskForm.vue";
import SaleForm from "@/Components/Sales/SaleForm.vue";
</script>
<template>

    <div class="container-fluid">
        <div class="row g-2">

            <!-- Список доставок -->
            <div class="col-12 col-md-6 col-xl-4">

                <div class="card border-primary shadow-sm h-100 menu-card"
                     @click="goTo('SalePage')"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-truck-fast fa-3x text-primary mb-3"></i>

                        <h5 class="card-title">
                            Список доставок
                        </h5>

                        <p class="text-muted small mb-0">
                            Просмотр всех доставок
                        </p>

                    </div>
                </div>
            </div>

            <!-- Внести доставку -->
            <div class="col-12 col-md-6 col-xl-4">

                <div class="card border-success shadow-sm h-100 menu-card"
                     data-bs-toggle="modal"
                     :data-bs-target="'#newSaleModal'"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-plus-circle fa-3x text-success mb-3"></i>

                        <h5 class="card-title">
                            Внести доставку
                        </h5>

                        <p class="text-muted small mb-0">
                            Добавление новой доставки
                        </p>

                    </div>
                </div>
            </div>

            <!-- Отчет -->
            <div class="col-12 col-md-6 col-xl-4">

                <div class="card border-info shadow-sm h-100 menu-card"
                     data-bs-toggle="modal"
                     :data-bs-target="'#reportModal'"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-file-lines fa-3x text-info mb-3"></i>

                        <h5 class="card-title">
                            Сформировать отчет
                        </h5>

                        <p class="text-muted small mb-0">
                            Генерация отчетности
                        </p>

                    </div>
                </div>
            </div>

            <!-- Работа с товаром -->
            <div class="col-12 col-md-6 col-xl-4">

                <div class="card border-danger shadow-sm h-100 menu-card"
                     @click="goTo('ProductPage')"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-box-open fa-3x text-danger mb-3"></i>

                        <h5 class="card-title">
                            Работа с товаром
                        </h5>

                        <p class="text-muted small mb-0">
                            Управление товарами
                        </p>

                    </div>
                </div>
            </div>

            <!-- Поставщики -->
            <div class="col-12 col-md-6 col-xl-4">

                <div class="card border-warning shadow-sm h-100 menu-card"
                     @click="goTo('SupplierPage')"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-truck fa-3x text-warning mb-3"></i>

                        <h5 class="card-title">
                            Список поставщиков
                        </h5>

                        <p class="text-muted small mb-0">
                            Управление поставщиками
                        </p>

                    </div>
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
