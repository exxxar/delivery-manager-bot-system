<script setup>
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";

import CreateAgentTaskForm from "@/Components/Sales/Forms/CreateAgentTaskForm.vue";
</script>
<template>

    <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню действий">
        <button type="button"
                @click="goTo('SalePage')"
                class="btn btn-outline-primary p-3">Общий список доставок</button>
        <button type="button"
                data-bs-toggle="modal" :data-bs-target="'#agentTaskModal'"
                class="btn btn-outline-primary p-3">Внести доставку</button>
        <button type="button"
                @click="goTo('AdminTasksPage')"
                class="btn btn-outline-primary p-3">Мои доставки</button>
        <button type="button"
                data-bs-toggle="modal" :data-bs-target="'#reportModal'"
                class="btn btn-outline-primary p-3">Сформировать отчет</button>
        <button type="button"
                @click="goTo('SupplierPage')"
                class="btn btn-outline-primary p-3">Список поставщиков</button>
    </div>


    <!-- Модалка -->
    <div class="modal fade" :id="'agentTaskModal'" tabindex="-1">
        <div class="modal-dialog">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Форма создания задачи для торгового представителя</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <CreateAgentTaskForm></CreateAgentTaskForm>
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
            }
        }
}
</script>
