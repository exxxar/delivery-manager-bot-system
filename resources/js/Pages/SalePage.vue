<script setup>
import SaleList from "@/Components/Sales/SaleList.vue";
import NotVerifiedSaleList from "@/Components/Sales/NotVerifiedSaleList.vue";
import BackBtn from "@/Components/BackBtn.vue";
import SaleForm from "@/Components/Sales/SaleForm.vue";
import AgentInfo from "@/Components/Agents/AgentInfo.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <BackBtn/>

        <template v-if="user?.role>=3">
            <button
                @click="openVerifyModal"
                class="btn btn-danger w-100 p-3 mb-2"><i class="fa-solid fa-list-check"></i> Заявки на проверку
            </button>
        </template>
        <h4 class="mb-3">Список доставок</h4>
        <SaleList v-if="!loading"></SaleList>

        <nav
            style="z-index:100;"
            class="navbar bg-transparent position-fixed bottom-0 start-0 w-100"
        >
            <div class="container-fluid justify-content-center">

                <button
                    @click="addSale"
                    type="button"
                    class="btn btn-primary p-3 add-sale-btn"
                >
                    <i class="fa-solid fa-plus me-2"></i>
                    Добавить доставку
                </button>

            </div>
        </nav>
    </div>

    <!-- Модалка верификации -->
    <div class="modal fade" id="verifiedSales" tabindex="-1">
        <!--
            modal-fullscreen-sm-down:
            fullscreen только на маленьких экранах
        -->
        <div class="modal-dialog modal-dialog-centered modal-md modal-fullscreen-md-down">

            <div class="modal-content border-0 shadow-lg rounded-4">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-shield-check me-2 text-primary"></i>
                        Проверка заявок
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">
                    <template v-if="user?.role >= 3">
                        <NotVerifiedSaleList
                            v-on:agent-info="openAgentInfoModal"
                            v-if="!loading">
                        </NotVerifiedSaleList>
                    </template>
                </div>

            </div>
        </div>
    </div>

    <!-- Модалка создания -->
    <div class="modal fade" id="newSaleModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen-sm-down modal-md">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Создание задания</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <SaleForm
                        v-if="!loading"
                        @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="agentInfoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Информация по админу</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <AgentInfo
                        :edit="false"
                        :agent="selectedAgent" v-if="selectedAgent"></AgentInfo>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {useUsersStore} from "@/stores/users.ts";

export default {
    data() {
        return {
            userStore: useUsersStore(),
            loading: false,
            selectedAgent: null,
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
    },
    methods: {
        openAgentInfoModal(agent){
            this.selectedAgent = null
            this.$nextTick(() => {
                this.selectedAgent = agent
                new bootstrap.Modal(document.getElementById('agentInfoModal')).show()
            })
        },
        openVerifyModal() {
            this.loading = true
            this.$nextTick(() => {
                this.loading = false
                new bootstrap.Modal(document.getElementById('verifiedSales')).show()
            })

        },
        addSale() {
            this.loading = true
            this.$nextTick(() => {
                this.loading = false
                new bootstrap.Modal(document.getElementById('newSaleModal')).show()
            })

        },
        async fetchData() {
            this.loading = true
            this.$nextTick(() => {
                this.loading = false
                bootstrap.Modal.getInstance(document.getElementById('newSaleModal')).hide()
            })

        },
    }
}
</script>
<style>
.add-sale-btn {
    width: 100%;
    max-width: 100%;
    border-radius: 1rem;
    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
}

/* Большие экраны */
@media (min-width: 992px) {
    .add-sale-btn {
        width: 400px;
    }
}
</style>
