<script setup>
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
</script>

<template>

        <form @submit.prevent="submitForm">

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
                <input v-model="form.region" type="text" class="form-control" id="region" placeholder="Регион" required>
                <label for="region">Регион</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 p-3">Сохранить агента</button>
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
            form: {
                name: '',
                phone: '',
                email: '',
                region: '',
            },
        }
    },
    created() {
        if (this.initialData) {
            this.form = {...this.initialData}
            this.isEdit = true
        }
    },
    methods: {

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
