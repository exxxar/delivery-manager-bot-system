<script setup>
import AdminJobForm from "@/Components/Users/Forms/AdminJobForm.vue";
import AgentJobForm from "@/Components/Users/Forms/AgentJobForm.vue";
import ClientJobForm from "@/Components/Users/Forms/ClientJobForm.vue";
import SupplierJobForm from "@/Components/Users/Forms/SupplierJobForm.vue";
</script>

<template>

    <div class="container-fluid py-0 px-0">
        <div class="row g-2">

            <!-- Уже сотрудник -->
            <div class="col-12">

                <div class="card border-primary shadow-sm menu-card">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-user-check fa-3x text-primary mb-3"></i>

                        <h5 class="card-title mb-3">
                            Подтверждение сотрудника
                        </h5>

                        <button
                            @click="requestInvite"
                            :disabled="spent_time > 0"
                            type="button"
                            class="btn btn-primary btn-lg w-100">

                        <span v-if="spent_time > 0">
                            <i class="fa-solid fa-clock me-2"></i>
                            {{ spent_time }} сек.
                        </span>

                            <span v-else>
                            <i class="fa-solid fa-check me-2"></i>
                            Я уже являюсь сотрудником
                        </span>

                        </button>

                    </div>
                </div>
            </div>

            <!-- Старший администратор -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="card border-danger shadow-sm h-100 menu-card"
                     data-bs-toggle="modal"
                     data-bs-target="#adminJobModal"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-crown fa-3x text-danger mb-3"></i>

                        <h5 class="card-title">
                            Старший администратор
                        </h5>

                        <p class="text-muted small mb-0">
                            Получение расширенных прав
                        </p>

                    </div>
                </div>
            </div>

            <!-- Администратор -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="card border-primary shadow-sm h-100 menu-card"
                     data-bs-toggle="modal"
                     data-bs-target="#agentJobModal"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-user-shield fa-3x text-primary mb-3"></i>

                        <h5 class="card-title">
                            Администратор
                        </h5>

                        <p class="text-muted small mb-0">
                            Работа с системой
                        </p>

                    </div>
                </div>
            </div>

            <!-- Поставщик -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="card border-warning shadow-sm h-100 menu-card"
                     data-bs-toggle="modal"
                     data-bs-target="#supplierJobModal"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-truck fa-3x text-warning mb-3"></i>

                        <h5 class="card-title">
                            Поставщик
                        </h5>

                        <p class="text-muted small mb-0">
                            Сотрудничество по поставкам
                        </p>

                    </div>
                </div>
            </div>

            <!-- Клиент -->
            <div class="col-12 col-md-6 col-xl-3">

                <div class="card border-success shadow-sm h-100 menu-card"
                     data-bs-toggle="modal"
                     data-bs-target="#clientJobModal"
                     style="cursor:pointer;">

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-handshake fa-3x text-success mb-3"></i>

                        <h5 class="card-title">
                            Клиент
                        </h5>

                        <p class="text-muted small mb-0">
                            Сотрудничество с компанией
                        </p>

                    </div>
                </div>
            </div>

        </div>
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
                    <h5 class="modal-title">Заявка на младшего администратора</h5>
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
import {startTimer, checkTimer, getSpentTimeCounter} from "../utilites/commonMethods.js";
import {useUsersStore} from "@/stores/users";
import {useAlertStore} from "@/stores/utillites/useAlertStore";

export default {
    data() {
        return {
            spent_time: 0,
            userStore: useUsersStore(),
            alertStore: useAlertStore(),
        }
    },
    mounted() {
        checkTimer();

        window.addEventListener("trigger-spent-timer", (event) => { // (1)
            this.spent_time = event.detail
        });
    },
    methods: {
        hideModal(modalId) {
            const modalEl = document.getElementById(modalId)
            const modalInstance = bootstrap.Modal.getInstance(modalEl)
            modalInstance.hide()
        },
        requestInvite() {
            startTimer(10);
            this.userStore.selfRoleRequest().then(() => {
                this.alertStore.show("Запрос успешно отправлен", "success");
            }).catch(() => {
                this.alertStore.show("Ошибка отправки запроса", "error");
            })
        }
    }
}
</script>
