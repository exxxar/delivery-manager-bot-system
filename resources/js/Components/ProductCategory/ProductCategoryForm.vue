<template>
    <div class="container-fluid">
        <form @submit.prevent="submitForm">

            <!-- Название категории -->
            <div class="form-floating mb-3">
                <input
                    v-model="form.name"
                    type="text"
                    class="form-control"
                    id="name"
                    placeholder="Название категории"
                    required
                >
                <label for="name">Название категории</label>
            </div>

            <!-- Описание категории -->
            <div class="form-floating mb-2">
        <textarea
            v-model="form.description"
            class="form-control"
            id="description"
            placeholder="Описание категории"
            style="height: 120px"
            required
        ></textarea>
                <label for="description">Описание категории</label>
            </div>

            <!-- Кнопка -->
            <button type="submit" class="btn btn-primary w-100 p-3">
                {{ isEdit ? 'Сохранить изменения' : 'Добавить категорию' }}
            </button>
        </form>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'ProductCategoryForm',
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
                description: ''
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
                    await axios.put(`/api/product_categories/${this.form.id}`, this.form)
                    alert('Категория обновлена!')
                } else {
                    await axios.post('/api/product_categories', this.form)
                    alert('Категория добавлена!')
                }
                this.$emit('saved') // событие для родителя
            } catch (error) {
                console.error('Ошибка сохранения категории:', error)
            }
        }
    }
}
</script>
