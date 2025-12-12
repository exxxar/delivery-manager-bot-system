<script setup>
import AgentInfo from "@/Components/Agents/AgentInfo.vue";
</script>
<template>
    <div class="container-fluid p-3">
        <h4 class="mb-3">Список агентов</h4>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Имя</th>
                    <th scope="col">Телефон</th>
                    <th scope="col">Email</th>
                    <th scope="col">Регион</th>

                    <th scope="col">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="agent in agents" :key="agent.id">
                    <td>{{ agent.id }}</td>
                    <td>{{ agent.name }}</td>
                    <td>{{ agent.phone }}</td>
                    <td>{{ agent.email }}</td>
                    <td>{{ agent.region }}</td>

                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Действия
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" @click.prevent="openAgentInfo(agent)">Просмотр</a></li>
                                <li><a class="dropdown-item" href="#" @click.prevent="openEditAgent(agent)">Редактировать</a></li>
                                <li><a class="dropdown-item text-danger" href="#" @click.prevent="confirmDelete(agent)">Удалить</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Модалки -->
        <div class="modal fade" id="agentInfoModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Информация</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <AgentInfo :agent="selectedAgent"></AgentInfo>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Удаление</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Вы уверены, что хотите удалить <strong>{{ selectedAgent?.name }}</strong>?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-danger" @click="deleteAgent">Удалить</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editAgentModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Редактирование </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <AgentForm v-if="selectedAgent" :initialData="selectedAgent" @saved="fetchAgents" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import AgentForm from './AgentForm.vue'

export default {
    name: 'AgentTable',
    components: { AgentForm },
    data() {
        return {
            agents: [],
            selectedAgent: null
        }
    },
    created() {
        this.fetchAgents()
    },
    methods: {
        async fetchAgents() {
            try {
                const response = await axios.get('/api/agents')
                this.agents = response.data
            } catch (error) {
            }
        },
        openAgentInfo(agent) {
            this.selectedAgent = agent
            new bootstrap.Modal(document.getElementById('agentInfoModal')).show()
        },
        openPercents(agent) {
            this.selectedAgent = agent
            new bootstrap.Modal(document.getElementById('percentsModal')).show()
        },
        confirmDelete(agent) {
            this.selectedAgent = agent
            new bootstrap.Modal(document.getElementById('deleteModal')).show()
        },
        async deleteAgent() {
            try {
                await axios.delete(`/api/agents/${this.selectedAgent.id}`)
                this.agents = this.agents.filter(a => a.id !== this.selectedAgent.id)
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide()
            } catch (error) {
                console.error('Ошибка удаления:', error)
            }
        },
        openEditAgent(agent) {
            this.selectedAgent = agent
            new bootstrap.Modal(document.getElementById('editAgentModal')).show()
        }
    }
}
</script>
