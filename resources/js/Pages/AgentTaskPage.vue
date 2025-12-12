<script setup>
import SaleList from "@/Components/Sales/SaleList.vue";
import AgentForm from "@/Components/Agents/AgentForm.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <SaleList
            v-if="user"
            :agent-id="user.id"></SaleList>


        <!-- Модалка фильтрации -->
        <div class="modal fade" id="add-agent-form" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Данные</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <AgentForm v-on:callback="callback"></AgentForm>
                    </div>
                </div>
            </div>
        </div>

        <nav
            v-if="!user.agent"
            class="navbar bg-transparent position-fixed bottom-0 start-0 w-100">
            <div class="container-fluid">
                <button
                    @click="openAddAgentModal"
                    type="button"
                    class="btn btn-primary w-100 p-3">
                    Заполнить данные о себе
                </button>
            </div>
        </nav>


    </div>
</template>
<script>
import {useUsersStore} from "@/stores/users";

export default {
    name: 'TaskView',
    data(){
        return {
            userStore: useUsersStore()
        }
    },
    computed:{
        user(){
            return this.userStore.self || null
        },
    },
    methods:{
        openAddAgentModal() {
            new bootstrap.Modal(document.getElementById('add-agent-form')).show()
        },
        callback(){
            bootstrap.Modal.getInstance(document.getElementById('add-agent-form')).hide()
        }
    }
}
</script>
