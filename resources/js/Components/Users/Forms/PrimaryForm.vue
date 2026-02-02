<template>
    <form v-on:submit.prevent="submitForm">
        <p class="alert alert-primary">
            Необходимо заполнить информацию о себе!
        </p>
        <div class="form-floating mb-2">
            <input type="text"
                   required
                   v-model="form.name" class="form-control" placeholder="Имя">
            <label>Имя</label>
        </div>

        <div class="form-floating mb-2">
            <input type="text"
                   required
                   v-mask="'+7(###) ###-##-##'"
                   v-model="form.phone" class="form-control" placeholder="Телефон">
            <label>Телефон</label>
        </div>


        <div class="form-floating mb-2">
            <input type="email" v-model="form.email" class="form-control" placeholder="Email" required>
            <label>Email</label>
        </div>

        <div class="form-floating mb-2">
            <input
                required
                type="date" v-model="form.birthday" class="form-control">
            <label>Дата рождения</label>
        </div>


        <!-- AGENT BLOCK -->
        <template v-if="selectedRole === 1" >
            <div class="form-floating mb-2">
                <input type="text" v-model="form.agent.region" class="form-control" placeholder="Регион">
                <label>Регион</label>
            </div>
        </template>

        <div class="alert alert-light mb-2 border-primary">
            <p class="mb-2">
                Нажимая кнопку, вы даете согласие на обработку персональных данных согласно
                <a href="https://www.consultant.ru/document/cons_doc_LAW_61801/" target="_blank">
                    Федеральному закону №152-ФЗ
                </a>
            </p>
            <div class="form-check form-switch">
                <input
                    v-model="offer_agreement"
                    class="form-check-input" type="checkbox" role="switch" id="offerSwitch" checked>
                <label class="form-check-label fw-bold" for="offerSwitch">Я даю согласие</label>
            </div>
        </div>

        <!-- SUBMIT -->
        <button
            type="submit"
            :disabled="!offer_agreement"
            class="btn btn-primary w-100 p-3">
            Сохранить
        </button>

    </form>
</template>

<script>
import {useUsersStore} from "@/stores/users";

export default {
    name: "PrimaryForm",
    props:{
        initialData: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            userStore: useUsersStore(),
            selectedRole: useUsersStore().self.role || 0,
            offer_agreement: true,
            form: {
                name: "",
                email: "",
                birthday: "",
                password: "",
                phone: "",
                region: "",
            }
        }
    },
    created() {
        if (this.initialData) {
            this.form = {...this.initialData}
            this.form.birthday = new Date( this.form.birthday).toISOString().slice(0, 10)
            this.isEdit = true
        }
    },
    methods: {
        async submitForm() {
            try {
                await this.userStore
                    .createPrimary(this.form)
                    .then(()=>{
                        this.$emit("callback")
                })
            } catch (e) {
                console.error(e)
            }
        }
    }
}
</script>
