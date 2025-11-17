<template>
    <div class="container-fluid p-3">
        <form @submit.prevent="submitForm">

            <div class="form-floating mb-2">
                <input v-model="form.name" type="text" class="form-control" id="name" placeholder="Название" required>
                <label for="name">Название</label>
            </div>

            <div class="form-floating mb-2">
                <textarea v-model="form.description" class="form-control" id="description" placeholder="Описание" style="height: 100px"></textarea>
                <label for="description">Описание</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.address" type="text" class="form-control" id="address" placeholder="Адрес">
                <label for="address">Адрес</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.phone" type="tel" class="form-control" id="phone" placeholder="Телефон" required>
                <label for="phone">Телефон</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.percent" type="number" step="0.01" class="form-control" id="percent" placeholder="Процент">
                <label for="percent">Процент</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.birthday" type="date" class="form-control" id="birthday" placeholder="Дата рождения">
                <label for="birthday">Дата рождения</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.email" type="email" class="form-control" id="email" placeholder="Email">
                <label for="email">Email</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Сохранить</button>
        </form>
    </div>
</template>

<script>
export default {
    name: 'SupplierForm',
    data() {
        return {
            form: {
                name: '',
                description: '',
                address: '',
                phone: '',
                percent: 0,
                birthday: '',
                email: ''
            }
        }
    },
    methods: {
        submitForm() {
            console.log('Форма отправлена:', this.form)
            // axios.post('/api/suppliers', this.form)
        }
    }
}
</script>
