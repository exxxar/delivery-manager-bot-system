<script setup>
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
import AdminList from "@/Components/Admins/AdminList.vue";
</script>

<template>

        <form @submit.prevent="submitForm">

            <template v-if="tab==='main'">
                <!-- Основные поля -->
                <div class="form-floating mb-2">
                    <input v-model="form.name" type="text" class="form-control" id="name" placeholder="Имя" required>
                    <label for="name">Имя</label>
                </div>

                <div class="form-floating mb-2">
                    <input v-model="form.phone" type="tel" class="form-control" id="phone" placeholder="Телефон" required>
                    <label for="phone">Телефон</label>
                </div>

                <div class="form-floating mb-2">
                    <input v-model="form.email" type="email" class="form-control" id="email" placeholder="Email" required>
                    <label for="email">Email</label>
                </div>

                <div class="form-floating mb-2">
                    <input v-model="form.region" type="text" class="form-control" id="region"
                           placeholder="Регион">
                    <label for="region">Регион</label>
                </div>

                <div
                    class="form-check form-switch mb-2">
                    <input
                        v-model="form.is_test"
                        class="form-check-input" type="checkbox" role="switch" id="is_test">
                    <label class="form-check-label" for="is_test">Тестовый агент
                        <span class="fw-bold text-primary" v-if="form.is_test">включено</span>
                        <span class="fw-bold text-primary" v-else>выключено</span>
                    </label>
                </div>

                <div
                    class="form-check form-switch mb-2">
                    <input
                        v-model="form.in_learning"
                        class="form-check-input" type="checkbox" role="switch" id="in_learning">
                    <label class="form-check-label" for="in_learning">На обучении
                        <span class="fw-bold text-primary" v-if="form.in_learning">включено</span>
                        <span class="fw-bold text-primary" v-else>выключено</span>
                    </label>
                </div>

                <template v-if="form.in_learning">
                    <p class="alert alert-info mb-2">Назначение ответственного за обучение (наставника)</p>
                    <!-- Агент -->
                    <div class="input-group mb-2">
                        <div class="form-floating flex-grow-1">
                            <input type="text" class="form-control" id="admin" :value="mentorName" placeholder="Администратор"
                                   readonly>
                            <label for="admin">Старший администратор</label>
                        </div>
                        <button type="button" class="btn btn-outline-light text-primary" @click="tab='admin'">Выбрать
                        </button>
                    </div>

<!--                    <div class="form-floating mb-2">
                        <input v-model="form.mentor_percent" type="number" class="form-control" id="name" placeholder="Процент за наставничество" required>
                        <label for="name">Процент за наставничество</label>
                    </div>-->

                    <div
                        class="form-check form-switch mb-2">
                        <input
                            v-model="configLearningPeriod"
                            class="form-check-input" type="checkbox" role="switch" id="configLearningPeriod">
                        <label class="form-check-label" for="configLearningPeriod">Настройка периода обучения
                        </label>
                    </div>

                    <template v-if="configLearningPeriod">
                        <p class="alert alert-info mb-2">Период настраивается автоматически после того как вы задали что Админстратор находится на обучении</p>
                        <div class="form-floating mb-2">
                            <input v-model="form.start_learning_date" type="date" class="form-control" id="sale_date">
                            <label for="sale_date">Дата начала обучения</label>
                        </div>
                        <div class="form-floating mb-2">
                            <input v-model="form.end_learning_date" type="date" class="form-control" id="sale_date">
                            <label for="sale_date">Дата окончания обучения</label>
                        </div>
                    </template>
                </template>
            </template>


            <template v-if="tab==='admin'">
                <button
                    @click="tab='main'"
                    class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
                </button>
                <AdminList :for-select="true" @select="selectAdmin"/>
            </template>

            <button type="submit" class="btn btn-primary w-100 p-3">Сохранить</button>
        </form>


</template>

<script>
import axios from 'axios'
import {useAgentsStore} from "@/stores/agents";

export default {
    name: 'AgentForm',
    props: {
        initialData: {
            type: Object,
            default: null
        },

    },
    data() {
        return {
            agentStore: useAgentsStore(),
            isEdit: false,
            configLearningPeriod: false,
            tab:'main',
            form: {
                name: '',
                phone: '',
                email: '',
                region: '',
                start_learning_date:null,
                end_learning_date:null,
                in_learning: false,
                is_test: false,
            },
            mentorName:null,
        }
    },
    created() {
        if (this.initialData) {
            this.form = {...this.initialData}
            this.isEdit = true

            this.mentorName = this.initialData.mentor?.name || ''
        }
    },
    methods: {
        selectAdmin(admin) {
            this.form.mentor_id = admin.id
            this.mentorName = admin.name
            this.tab = 'main'
        },
        async submitForm() {
            if (!this.isEdit)
                this.agentStore.create(this.form)
            else
                this.agentStore.update(this.form.id, this.form)

            this.$emit("callback")
        }
    }
}
</script>
