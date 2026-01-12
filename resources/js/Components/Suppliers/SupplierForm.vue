<template>

    <form @submit.prevent="submitForm">

        <div class="form-floating mb-2">
            <input v-model="form.name" type="text" class="form-control" id="name" placeholder="Название" required>
            <label for="name">Название</label>
        </div>

        <div class="form-floating mb-2">
            <input v-model="form.phone"
                   v-mask="'+7(###) ###-##-##'"
                   type="text" class="form-control" id="phone" placeholder="Телефон" required>
            <label for="phone">Телефон</label>
        </div>


        <template v-if="!isSimple">


            <div class="form-floating mb-2">
            <textarea v-model="form.description" class="form-control" id="description" placeholder="Описание"
                      style="height: 100px"></textarea>
                <label for="description">Описание</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.address" type="text" class="form-control" id="address" placeholder="Адрес">
                <label for="address">Адрес</label>
            </div>


            <div class="form-floating mb-2">
                <input v-model="form.work_phone"
                       v-mask="'+7(###) ###-##-##'"
                       type="text" class="form-control" id="work_phone" placeholder="Телефон">
                <label for="work_phone">Рабочий телефон</label>
            </div>


            <template v-if="(user?.role || 0) >= 3">
                <div class="form-floating mb-2">
                    <input v-model="form.percent" type="number" step="0.01" class="form-control" id="percent"
                           placeholder="Процент">
                    <label for="percent">Процент, %</label>
                </div>
            </template>

            <div class="form-floating mb-2">
                <input v-model="form.birthday" type="date" class="form-control" id="birthday"
                       placeholder="Дата рождения">
                <label for="birthday">Дата рождения</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.email" type="email" class="form-control" id="email" placeholder="Email">
                <label for="email">Email</label>
            </div>
        </template>
        <button type="submit" class="btn btn-primary w-100 p-3">Сохранить</button>
    </form>

</template>

<script>
import {useSuppliersStore} from "@/stores/suppliers";
import {useUsersStore} from "@/stores/users";

export default {
    name: 'SupplierForm',
    props: {
        isSimple: {
            type: Boolean,
            default: false
        },
        initialData: {
            type: Object,
            default: null
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
    },
    data() {
        return {
            userStore: useUsersStore(),
            suppliersStore: useSuppliersStore(),
            isEdit: false,
            form: {
                name: '',
                description: '',
                address: '',
                phone: '',
                work_phone: '',
                percent: 8,
                birthday: '',
                email: ''
            }
        }
    },
    created() {
        if (this.initialData) {
            this.form = {...this.initialData}
            this.isEdit = true
        }
    },
    methods: {
        submitForm() {
            let supplier = this.isEdit ?
                this.suppliersStore.update(this.form.id, this.form) :
                this.suppliersStore.create(this.form)

            supplier.then((resp)=>{
                console.log("test",resp)
                this.$emit("saved", resp)
            })


          //  this.$emit("saved", supplier)
        }
    }
}
</script>
