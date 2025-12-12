<script setup>
import AgentInfo from "@/Components/Agents/AgentInfo.vue";
import Pagination from "@/Components/Pagination.vue";
</script>

<template>


    <div class="form-floating mb-3">
        <input type="search"
               v-model="search"
               class="form-control" id="searchInput" placeholder="Поиск..." />
        <label for="searchInput">Поиск</label>
    </div>

    <ul class="list-group">
        <li

            v-for="agent in filteredAgents" :key="agent.id"
            class="list-group-item d-flex justify-content-between align-items-center">
            <div  @click="selectAgent(agent)">
                <div class="fw-bold">{{ agent.name }}</div>
                <small class="text-muted">{{ agent.phone }} | {{ agent.email }}</small>
            </div>

            <!-- Dropdown кнопка -->
            <div class="dropdown">
                <button class="btn btn-sm" type="button"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <template v-if="forSelect">
                        <li><a class="dropdown-item" href="#" @click.prevent="selectAgent(agent)">Выбрать</a>
                        </li>
                    </template>

                    <template v-if="!forSelect">
                        <li><a class="dropdown-item" href="#" @click.prevent="openAgentInfo(agent)">Просмотр</a></li>
                        <li><a class="dropdown-item" href="#" @click.prevent="openEditAgent(agent)">Редактировать</a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="#" @click.prevent="confirmDelete(agent)">Удалить</a></li>
                    </template>

                </ul>
            </div>
        </li>
    </ul>

    <!-- Пагинация -->
    <Pagination
        :pagination="agentStore.pagination"
        @page-changed="fetchAgentsByUrl"
    />

    <!-- Модалка просмотра агента -->
    <div class="modal fade" id="agentInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Информация</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <AgentInfo :agent="selectedAgent" v-if="selectedAgent"></AgentInfo>
                </div>
            </div>
        </div>
    </div>


    <!-- Модалка подтверждения удаления -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Удаление</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Вы уверены, что хотите удалить  <strong>{{ selectedAgent?.name }}</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" @click="deleteAgent">Удалить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка редактирования агента -->
    <div class="modal fade" id="editAgentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <AgentForm v-if="selectedAgent" :initialData="selectedAgent" @saved="fetchSuppliers"/>
                </div>
            </div>
        </div>
    </div>

</template>

<script>

import AgentForm from './AgentForm.vue'
import {useAgentsStore} from "@/stores/agents";


export default {
    name: 'SupplierListWithDropdown',
    components: {AgentForm},
    props: ["forSelect"],
    data() {
        return {
            search:'',
            agentStore: useAgentsStore(),
            selectedAgent: null
        }
    },
    computed:{
        filteredAgents() {
            if (!this.search) return this.agentStore.items || []
            const q = this.search.toLowerCase()
            return this.agentStore.items.filter(agent =>
                Object.values(agent).some(val =>
                    val ? String(val).toLowerCase().includes(q) : false
                )
            )
        }
    },
    created() {
        this.fetchAgents()

    },
    methods: {
        async fetchAgents(page = 1) {
            await this.agentStore.fetchAllByPage(page)
        },
        async fetchAgentsByUrl(url) {
            await this.agentStore.fetchByUrl(url)
        },
        selectAgent(agent) {
            if (!this.forSelect)
                return
            this.$emit("select", agent)
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
                await this.agentStore.remove(this.selectedAgent.id)
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
