<script setup>
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";
</script>
<template>

    <div class="container-fluid py-0 px-0">
        <div class="row g-2">

            <!-- Сформировать отчет -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-primary shadow-sm menu-card"
                     @click="openReportModal"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-file-lines fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Сформировать отчет</h5>
                        <p class="text-muted small mb-0">
                            Генерация отчетов по системе
                        </p>
                    </div>
                </div>
            </div>

            <!-- Продажи -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-primary shadow-sm menu-card"
                     @click="goTo('SalePage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-cart-shopping fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Список продаж</h5>
                        <p class="text-muted small mb-0">
                            Управление продажами
                        </p>
                    </div>
                </div>
            </div>

            <!-- Пользователи -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-primary shadow-sm menu-card"
                     @click="goTo('UserPage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-users fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Список пользователей</h5>
                        <p class="text-muted small mb-0">
                            Работа с пользователями
                        </p>
                    </div>
                </div>
            </div>

            <!-- Поставщики -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-primary shadow-sm menu-card"
                     @click="goTo('SupplierPage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-truck fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Список поставщиков</h5>
                        <p class="text-muted small mb-0">
                            Управление поставщиками
                        </p>
                    </div>
                </div>
            </div>

            <!-- Админы -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-primary shadow-sm menu-card"
                     @click="goTo('AgentPage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-user-shield fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Список админов</h5>
                        <p class="text-muted small mb-0">
                            Управление администраторами
                        </p>
                    </div>
                </div>
            </div>

            <!-- Старшие админы -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-primary shadow-sm menu-card"
                     @click="goTo('AdminPage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-user-tie fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Список старших админов</h5>
                        <p class="text-muted small mb-0">
                            Управление старшими администраторами
                        </p>
                    </div>
                </div>
            </div>

            <!-- Товары -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-primary shadow-sm menu-card"
                     @click="goTo('ProductPage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-box-open fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Работа с товаром</h5>
                        <p class="text-muted small mb-0">
                            Управление товарами
                        </p>
                    </div>
                </div>
            </div>

            <!-- Excel -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-success shadow-sm menu-card"
                     @click="goTo('ExcelExportPage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-file-excel fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Выгрузка в Excel</h5>
                        <p class="text-muted small mb-0">
                            Экспорт данных системы
                        </p>
                    </div>
                </div>
            </div>

            <!-- Дни рождения -->
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card h-100 border-warning shadow-sm menu-card"
                     @click="goTo('BirthdayPage')"
                     style="cursor:pointer;">
                    <div class="card-body text-center p-4">
                        <i class="fa-solid fa-cake-candles fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Дни рождения</h5>
                        <p class="text-muted small mb-0">
                            Просмотр ближайших дней рождения
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
                    <ReportGenerator
                        v-if="load"
                        @generate-report="handleReport"></ReportGenerator>
                </div>

            </div>
        </div>
    </div>
</template>
<script>
import {useBaseExports} from "@/stores/baseExports";

export default {
    data() {
        return {
            load:true,
            reportStore: useBaseExports()
        }
    },
    methods: {
        goTo(name) {
            this.$router.push({name: name})
        },
        openReportModal(){
            this.load = false

            this.$nextTick(()=>{
                this.load = true

                const modal = new bootstrap.Modal(document.getElementById('reportModal'))
                modal.show()
            })

        },
        handleReport(payload) {

            const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'))
            modal.hide()


            this.reportStore.exportSalary(payload)
        }

    }
}
</script>
