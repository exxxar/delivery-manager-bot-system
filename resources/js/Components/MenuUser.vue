<script setup>
import AdminJobForm from "@/Components/Users/Forms/AdminJobForm.vue";
import AgentJobForm from "@/Components/Users/Forms/AgentJobForm.vue";
import ClientJobForm from "@/Components/Users/Forms/ClientJobForm.vue";
import SupplierJobForm from "@/Components/Users/Forms/SupplierJobForm.vue";
</script>

<template>

        <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню">
            <button type="button" data-bs-toggle="modal" data-bs-target="#adminJobModal" class="btn btn-outline-primary p-3">Стать администратором</button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#agentJobModal" class="btn btn-outline-primary p-3">Стать торговым представителем</button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#supplierJobModal" class="btn btn-outline-primary p-3">Стать поставщиком</button>
            <button type="button" data-bs-toggle="modal" data-bs-target="#clientJobModal" class="btn btn-outline-primary p-3">Стать клиентом (сотрудничество)</button>
        </div>


    <!-- Модалка: Администратор -->
    <div class="modal fade" id="adminJobModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Заявка на администратора</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <AdminJobForm v-on:submit-application="hideModal('adminJobModal')"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка: Торговый агент -->
    <div class="modal fade" id="agentJobModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Заявка на торгового агента</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <AgentJobForm v-on:submit-application="hideModal('agentJobModal')"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка: Поставщик -->
    <div class="modal fade" id="supplierJobModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Заявка поставщика</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SupplierJobForm v-on:submit-application="hideModal('supplierJobModal')"/>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка: Клиент -->
    <div class="modal fade" id="clientJobModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Заявка клиента</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <ClientJobForm v-on:submit-application="hideModal('clientJobModal')"/>
                </div>
            </div>
        </div>
    </div>


</template>
<script>
export default {
        methods:{
            hideModal(modalId){
                const modalEl = document.getElementById(modalId)
                const modalInstance = bootstrap.Modal.getInstance(modalEl)
                modalInstance.hide()
            }
        }
}
</script>
