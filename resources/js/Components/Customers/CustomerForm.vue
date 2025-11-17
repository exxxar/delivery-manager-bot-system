<template>
    <div class="container-fluid p-3">
        <form @submit.prevent="submitForm">

            <!-- Имя -->
            <div class="form-floating mb-3">
                <input v-model="form.name" type="text" class="form-control" id="name" placeholder="Имя" required>
                <label for="name">Имя</label>
            </div>

            <!-- Компания -->
            <div class="form-floating mb-3">
                <input v-model="form.company_name" type="text" class="form-control" id="company_name" placeholder="Компания" required>
                <label for="company_name">Компания</label>
            </div>

            <!-- Адрес -->
            <div class="form-floating mb-3">
                <input v-model="form.address" type="text" class="form-control" id="address" placeholder="Адрес" required>
                <label for="address">Адрес</label>
            </div>

            <!-- Телефон -->
            <div class="form-floating mb-3">
                <input v-model="form.phone" type="tel" class="form-control" id="phone" placeholder="Телефон" required>
                <label for="phone">Телефон</label>
            </div>

            <!-- Email -->
            <div class="form-floating mb-3">
                <input v-model="form.email" type="email" class="form-control" id="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>

            <!-- Кнопка -->
            <button type="submit" class="btn btn-primary w-100">
                {{ isEdit ? 'Сохранить изменения' : 'Добавить клиента' }}
            </button>
        </form>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'CustomerForm',
    props: {
        initialData: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            form: {
                name: '',
                company_name: '',
                address: '',
                phone: '',
                email: ''
            },
            isEdit: false
        }
    },
    created() {
        if (this.initialData) {
            this.form = { ...this.initialData }
            this.isEdit = true
        }
    },
    methods: {
        async submitForm() {
            try {
                if (this.isEdit) {
                    await axios.put(`/api/customers/${this.form.id}`, this.form)
                    alert('Клиент обновлён!')
                } else {
                    await axios.post('/api/customers', this.form)
                    alert('Клиент добавлен!')
                }
                this.$emit('saved')
            } catch (error) {
                console.error('Ошибка сохранения клиента:', error)
            }
        }
    }
}
</script>
