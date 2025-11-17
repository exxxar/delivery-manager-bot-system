<script setup>
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
</script>

<template>
    <div class="container-fluid p-3">
        <form @submit.prevent="submitForm">

            <!-- Основные поля -->
            <div class="form-floating mb-3">
                <input v-model="form.name" type="text" class="form-control" id="name" placeholder="Имя" required>
                <label for="name">Имя</label>
            </div>

            <div class="form-floating mb-3">
                <input v-model="form.phone" type="tel" class="form-control" id="phone" placeholder="Телефон" required>
                <label for="phone">Телефон</label>
            </div>

            <div class="form-floating mb-3">
                <input v-model="form.email" type="email" class="form-control" id="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>

            <div class="form-floating mb-3">
                <input v-model="form.region" type="text" class="form-control" id="region" placeholder="Регион" required>
                <label for="region">Регион</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Сохранить агента</button>
        </form>


    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'AgentForm',
    data() {
        return {
            form: {
                name: '',
                phone: '',
                email: '',
                region: '',
                percents: [] // [{ value: 0, supplier_id: null }]
            },
            currentPercentIndex: null
        }
    },
    methods: {

        async submitForm() {
            console.log('Форма агента:', this.form)
            try {
                await axios.post('/api/agents', this.form)
                alert('Агент сохранён!')
            } catch (error) {
                console.error('Ошибка сохранения агента:', error)
            }
        }
    }
}
</script>
