<script setup>
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
import AdminList from "@/Components/Admins/AdminList.vue";
import PercentageList from "@/Components/Percentage/PercentageList.vue";
import PercentageForm from '@/Components/Percentage/PercentageForm.vue'
</script>

<template>


    <form @submit.prevent="submitForm">

        <template v-if="tab==='main'">

            <div class="card mb-2" v-if="initialData">
                <div class="card-header">
                    <h6>Информация о пользователе</h6>
                </div>
                <div class="card-body" v-html="initialData.user_info">
                </div>
            </div>

            <!-- Основные поля -->
            <div class="form-floating mb-2">
                <input v-model="form.name" type="text" class="form-control" id="name" placeholder="Имя" required>
                <label for="name">Имя</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.phone"
                       v-mask="'+7(###) ###-##-##'"
                       minlength="3"
                       type="text" class="form-control" id="phone" placeholder="Телефон" required>
                <label for="phone">Телефон</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.email"
                       minlength="3"
                       type="email" class="form-control" id="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.region"
                       minlength="3"
                       required
                       type="text" class="form-control" id="region"
                       placeholder="Регион">
                <label for="region">Регион</label>
            </div>

            <template v-if="user?.role>=3">
                <div class="form-floating mb-2">
                    <input v-model="form.percent"
                           type="text" class="form-control" id="region"
                           placeholder="Процент">
                    <label for="region">Процент</label>
                </div>
            </template>
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

            <!--
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
                            &lt;!&ndash; Агент &ndash;&gt;
                            <div class="input-group mb-2">
                                <div class="form-floating flex-grow-1">
                                    <input type="text" class="form-control" id="admin" :value="mentorName"
                                           placeholder="Администратор"
                                           readonly>
                                    <label for="admin">Старший администратор</label>
                                </div>
                                <button type="button" class="btn btn-outline-light text-primary" @click="tab='admin'">Выбрать
                                </button>
                            </div>



                            <div
                                class="form-check form-switch mb-2">
                                <input
                                    v-model="configLearningPeriod"
                                    class="form-check-input" type="checkbox" role="switch" id="configLearningPeriod">
                                <label class="form-check-label" for="configLearningPeriod">Настройка периода обучения
                                </label>
                            </div>

                            <template v-if="configLearningPeriod">
                                <p class="alert alert-info mb-2">Период настраивается автоматически после того как вы задали что
                                    Админстратор находится на обучении</p>
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
            -->


            <button
                v-if="form.id"
                type="button" class="btn btn-outline-primary text-primary p-3 w-100 mb-2"
                @click="tab='percentage'">Выплаты процентов
            </button>

            <button type="submit" class="btn btn-primary w-100 p-3">Сохранить</button>
        </template>


        <template v-if="tab==='admin'">
            <button
                @click="tab='main'"
                class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
            </button>
            <AdminList :for-select="true" @select="selectAdmin"/>
        </template>

        <template v-if="tab==='percentage'">


            <template v-if="loadPercentageForm">
                <PercentageForm
                    v-on:saved="saveForm"
                    :agent-id="form.id"
                    :initial-data="selectedPercentage">
                    <template #back>
                        <button
                            @click="tab='main'"
                            class="btn btn-light text-secondary mb-3" style="position: sticky; top:0px; z-index: 100;">Назад
                        </button>
                    </template>
                    <template #list>
                            <PercentageList
                                :for-select="true"
                                v-on:select="selectPercentage"
                                :agent-id="form.id"></PercentageList>
                    </template>
                </PercentageForm>
            </template>


        </template>


    </form>


</template>

<script>
import axios from 'axios'
import {useAgentsStore} from "@/stores/agents";
import {useUsersStore} from "@/stores/users";

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
            selectedPercentage: null,
            loadPercentageForm: true,

            agentStore: useAgentsStore(),
            userStore: useUsersStore(),
            isEdit: false,
            configLearningPeriod: false,
            tab: 'main',
            form: {
                name: '',
                phone: '',
                email: '',
                region: '',
                start_learning_date: null,
                end_learning_date: null,
                in_learning: false,
                is_test: false,
            },
            mentorName: null,
        }
    },
    created() {
        if (this.initialData) {
            this.form = {...this.initialData}
            this.isEdit = true

            this.mentorName = this.initialData.mentor?.name || ''
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },

    },
    methods: {
        saveForm() {
            this.selectedPercentage = null
            this.loadPercentageForm = false
            this.$nextTick(() => {
                this.loadPercentageForm = true

            })
        },
        selectPercentage(percentage) {
            this.selectedPercentage = null
            this.loadPercentageForm = false
            this.$nextTick(() => {
                this.selectedPercentage = percentage
                this.loadPercentageForm = true
            })
        },
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
