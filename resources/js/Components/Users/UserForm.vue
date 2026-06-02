<template>

    <div class="card mb-2">
        <div class="card-header">
            <h6 class="mb-0">Инфо</h6>
        </div>
        <div class="card-body" v-html="initialData.agent.user_info">
        </div>
    </div>
    <form @submit.prevent="submitForm">

        <!-- Имя -->
        <div class="form-floating mb-2">
            <input v-model="form.name" type="text" class="form-control" id="name" placeholder="Имя" required>
            <label for="name">Имя</label>
        </div>

        <div class="form-floating mb-2">
            <input type="text"
                   required
                   v-mask="'+7(###) ###-##-##'"
                   v-model="form.phone" class="form-control" placeholder="Телефон">
            <label>Телефон</label>
        </div>

        <!-- Email -->
        <div class="form-floating mb-2">
            <input v-model="form.email" type="email" class="form-control" id="email" placeholder="Email" required>
            <label for="email">Email</label>
        </div>

        <div class="form-floating mb-2">
            <input
                required
                type="date" v-model="form.birthday" class="form-control">
            <label>Дата рождения</label>
        </div>



        <div class="form-floating mb-2">
            <input type="text" v-model="form.region" class="form-control" placeholder="Регион">
            <label>Регион</label>
        </div>

        <!-- Роль -->
        <div class="form-floating mb-2">
            <select v-model="form.role" class="form-select" id="role" required>
                <option :value="0">Пользователь</option>
                <option :value="1">Администратор</option>
                <option :value="2">Поставщик</option>
                <option :value="3">Старший администратор</option>
                <option :value="4">Суперадмин</option>
            </select>
            <label for="role">Роль</label>
        </div>

<!--
        &lt;!&ndash; Процент &ndash;&gt;
        <div class="form-floating mb-2">
            <input v-model="form.percent" type="number" step="0.01" class="form-control" id="percent"
                   placeholder="Процент">
            <label for="percent">Процент</label>
        </div>

        <div class="form-floating mb-2">
            <input v-model="form.mentor_percent" type="number" step="0.01" class="form-control" id="mentor_percent"
                   placeholder="Процент">
            <label for="mentor_percent">Процент наставника</label>
        </div>
-->



        <!--            &lt;!&ndash; Пароль &ndash;&gt;
                    <div class="form-floating mb-2">
                        <input v-model="form.password" type="password" class="form-control" id="password" placeholder="Пароль" required>
                        <label for="password">Пароль</label>
                    </div>-->

        <!-- Кнопка -->
        <button type="submit" class="btn btn-primary w-100 p-3">
            {{ isEdit ? 'Сохранить изменения' : 'Создать пользователя' }}
        </button>
    </form>

</template>

<script>
import axios from 'axios'
import {useUsersStore} from "@/stores/users";
import moment from 'moment';
export default {
    name: 'UserForm',
    props: {
        initialData: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            userStore: useUsersStore(),
            form: {
                name: "",
                email: "",
                birthday: "",
                password: "",
                phone: "",
                region: "",
                role: 0,
            },
            isEdit: false
        }
    },
    created() {
        if (this.initialData) {
            this.form = {...this.initialData}
            this.form.phone = this.initialData.agent.phone || ''
            this.form.email = this.initialData.agent.email || ''
            this.form.region = this.initialData.agent.region || ''
            this.form.birthday = new Date( this.form.birthday).toISOString().slice(0, 10)
            this.isEdit = true
        }
    },
    methods: {
        async submitForm() {
            await this.userStore.update(this.form.id, this.form)
            this.$emit("saved")
        }
    }
}
</script>
