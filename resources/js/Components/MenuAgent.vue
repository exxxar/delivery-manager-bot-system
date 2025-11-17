<script setup>
import ReportGenerator from "@/Components/Admins/ReportGenerator.vue";
</script>
<template>
    <div class="container py-3">
        <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню">
            <button type="button"
                    @click="goTo('AgentTaskPage')"
                    class="btn btn-outline-primary p-3">Мои доставки</button>
            <button type="button"
                    data-bs-toggle="modal" :data-bs-target="'#reportModal'"
                    class="btn btn-outline-primary p-3">Сформировать отчет</button>
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
