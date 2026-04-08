<script setup>
import UserList from "@/Components/Users/UserList.vue";
</script>

<template>
    <form @submit.prevent="submitForm">
        <template v-if="tab==='main'">
            <slot name="back"></slot>
            <div class="form-floating mb-2">
                <input v-model="form.percentage" type="number"
                       min="0" step="any" max="100"
                       class="form-control" id="name" placeholder="Процент" required>
                <label for="name">Процент</label>
            </div>

            <div class="input-group mb-2">
                <div class="form-floating flex-grow-1">
                    <input type="text" class="form-control" id="user" :value="userName"
                           placeholder="администратор"
                           readonly>
                    <label for="agent">Администратор</label>
                </div>
                <button type="button" class="btn btn-outline-light text-primary find-btn" @click="tab='admin'">
                    Выбрать
                </button>
            </div>

            <div
                class="form-check form-switch mb-2">
                <input
                    v-model="form.is_active"
                    class="form-check-input" type="checkbox" role="switch" id="is_active">
                <label class="form-check-label" for="is_active">Состояние
                    <span class="fw-bold text-primary" v-if="form.is_active">включено</span>
                    <span class="fw-bold text-primary" v-else>выключено</span>
                </label>
            </div>

            <div class="d-flex w-100 gap-2">


                <button class="btn btn-primary p-3 mb-2 flex-grow-1">
                    <span v-if="form.id">Обновить</span>
                    <span v-else>Добавить</span>
                </button>

                <button
                    v-if="form.id"
                    @click="resetForm"
                    class="btn btn-secondary p-3 mb-2 flex-grow-2">
                    Сбросить
                </button>
            </div>

            <slot name="list"></slot>
        </template>

        <template v-if="tab==='admin'">
            <button
                @click="tab='main'"
                class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
            </button>
            <UserList :for-select="true" @select="selectAdmin"/>
        </template>
    </form>
</template>
<script>
import {startTimer} from "@/utilites/commonMethods.js";
import {useAgentsStore} from "@/stores/agents.ts";
import {useAlertStore} from "@/stores/utillites/useAlertStore.ts";

export default {
    props: ["initialData", "agentId"],
    data() {
        return {
            tab: 'main',
            userName: '',
            agentStore: useAgentsStore(),
            alertStore: useAlertStore(),
            form: {
                percentage: 0,
                agent_id: null,
                user_id: null,
                is_active: true,
            }
        }
    },
    created() {
        if (this.initialData) {
            this.userName = this.initialData.user?.name || '-'
            this.form.id = this.initialData.id || null
            this.form.percentage = this.initialData.percentage || 0
            this.form.agent_id = this.initialData.agent_id || null
            this.form.user_id = this.initialData.user_id || null
            this.form.is_active = this.initialData.is_active || true
        }
    },
    methods: {
        resetForm() {
            this.form.percentage = 0
            this.form.agent_id = null
            this.form.user_id = null
            this.form.is_active = true
            this.form.id = null
            this.userName = ''
        },
        selectAdmin(user) {
            this.form.user_id = user.id
            this.userName = user.name
            this.tab = 'main'
        },
        async submitForm() {
            this.alertStore.show("Началось добавление заявки");
            startTimer(10)

            this.form.agent_id = this.agentId
            await this.agentStore.storePercentage(this.form).then(()=>{
                this.resetForm()
            })

            this.$emit('saved')
        }
    }
}
</script>
