<script setup>
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";
import ReportIndividualGenerator from "@/Components/Admins/ReportIndividualGenerator.vue";
</script>
<template>

    <button
        type="button"
        v-if="!userStore.self?.registration_at && userStore.self?.role > 0"
        @click="openPrimaryRegistration"
        class="btn btn-success p-3 w-100 mb-2">Заполнить данные о себе
    </button>
    <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню">
        <button type="button"
                @click="goTo('SalePage')"
                class="btn btn-outline-primary p-3">Мои доставки
        </button>
        <button type="button"
                data-bs-toggle="modal" :data-bs-target="'#reportModal'"
                class="btn btn-outline-primary p-3">Сформировать отчет
        </button>
    </div>

    <hr>
    <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню">
        <button type="button"
                @click="goTo('SupplierPage')"
                class="btn btn-outline-primary p-3">Список поставщиков
        </button>

        <button type="button"
                @click="goTo('ProductPage')"
                class="btn btn-outline-primary p-3">Работа с товаром
        </button>
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
        openPrimaryRegistration() {
            new bootstrap.Modal(document.getElementById('primaryUserModal')).show()
        },
        goTo(name) {
            this.$router.push({name: name})

        },
        handleReport(payload) {

            const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'))
            modal.hide()


            this.reportStore.exportFull(payload)
        }
    }
}
</script>
