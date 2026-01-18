<script setup>
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";
</script>
<template>

    <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню отчетов">

        <!-- Кнопка вызова модалки -->
        <button
            type="button"
            class="btn btn-outline-primary p-3" @click="openReportModal">
            Сформировать отчет
        </button>

        <button type="button"
                @click="goTo('SalePage')"
                class="btn btn-outline-primary p-3">Список продаж
        </button>
        <button type="button"
                @click="goTo('UserPage')"
                class="btn btn-outline-primary p-3">Список пользователей
        </button>
        <button type="button"
                @click="goTo('SupplierPage')"
                class="btn btn-outline-primary p-3">Список поставщиков
        </button>
        <button type="button"
                @click="goTo('AgentPage')"
                class="btn btn-outline-primary p-3">Список админов
        </button>
        <button type="button"
                @click="goTo('AdminPage')"
                class="btn btn-outline-primary p-3">Список старших админов
        </button>

        <button type="button"
                @click="goTo('ProductPage')"
                class="btn btn-outline-primary p-3">Работа с товаром
        </button>

    </div>

    <hr class="my-3">
    <button type="button"
            @click="goTo('ExcelExportPage')"
            class="btn btn-outline-success p-3 w-100 mb-2">Выгрузка данных в эксель
    </button>
    <button type="button"
            @click="goTo('BirthdayPage')"
            class="btn btn-outline-warning p-3 w-100">Дни рождения
    </button>


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


            this.reportStore.exportFull(payload)
        }

    }
}
</script>
