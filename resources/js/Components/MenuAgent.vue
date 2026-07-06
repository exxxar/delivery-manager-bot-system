<script setup>
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";
import ReportIndividualGenerator from "@/Components/Admins/ReportIndividualGenerator.vue";
</script>
<template>

    <div class="container-fluid py-0 px-0">
        <div class="row g-2">


            <!-- Мои доставки -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="card border-primary shadow-sm h-100 menu-card"
                     @click="goTo('SalePage')"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-truck-fast fa-3x text-primary mb-3"></i>

                        <h5 class="card-title">
                            Мои доставки
                        </h5>

                        <p class="text-muted small mb-0">
                            Просмотр доставок
                        </p>
                    </div>
                </div>
            </div>

            <!-- Отчет -->
            <div class="col-12 col-md-6 col-xl-3">

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

            <!-- Поставщики -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="card border-warning shadow-sm h-100 menu-card"
                     @click="goTo('SupplierPage')"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-truck fa-3x text-warning mb-3"></i>

                        <h5 class="card-title">
                            Поставщики
                        </h5>

                        <p class="text-muted small mb-0">
                            Управление поставщиками
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-primary shadow-sm menu-card"
                     @click="goTo('ReportsPage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-file-lines fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Мои отчеты</h5>
                        <p class="text-muted small mb-0">
                            Список всех сгенерированных отчетов
                        </p>
                    </div>
                </div>
            </div>

            <!-- Товары -->
            <div class="col-12 col-md-6 col-xl-3">

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

            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-info shadow-sm menu-card"
                     @click="goTo('UserLogsPage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-clock-rotate-left fa-3x text-info mb-3"></i>
                        <h5 class="card-title">История действий</h5>
                        <p class="text-muted small mb-0">
                            Журнал ваших действий в системе
                        </p>
                    </div>
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
                    <ReportIndividualGenerator
                        v-if="userStore.self"
                        :agent-id="userStore.self.agent.id"
                        @generate-report="handleReport"></ReportIndividualGenerator>
                </div>

            </div>
        </div>
    </div>

</template>
<script>
import {useBaseExports} from "@/stores/baseExports";
import {useUsersStore} from "@/stores/users";

export default {
    data() {
        return {
            userStore: useUsersStore(),
            reportStore: useBaseExports()
        }
    },
    methods: {

        goTo(name) {
            this.$router.push({name: name})

        },
        handleReport(payload) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'))
            modal.hide()
        }
    }
}
</script>
