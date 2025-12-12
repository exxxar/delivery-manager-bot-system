<template>
    <!-- Основная форма -->
    <template v-if="tab === 'form'">
        <form @submit.prevent="createTask">
            <div class="form-floating mb-2">
                <input v-model="form.title" type="text" class="form-control" required/>
                <label class="form-label">Название задачи</label>
            </div>

            <div class="form-floating mb-2">
                <textarea v-model="form.description" class="form-control"></textarea>
                <label class="form-label">Описание</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.due_date" type="date" class="form-control" required/>
                <label class="form-label">Дата выполнения</label>
            </div>

            <!-- Кнопки выбора -->
            <p>Поставщик</p>
            <button
                type="button"
                class="btn w-100 p-3 mb-2"
                :class="form.supplier ? 'btn-success' : 'btn-danger'"
                @click="tab = 'suppliers'"
            >
                {{ form.supplier ? form.supplier.name : 'Выберите поставщика...' }}
            </button>


            <p>Продукт</p>
            <button
                type="button"
                class="btn w-100 p-3 mb-2"
                :class="form.product ? 'btn-success' : 'btn-danger'"
                @click="tab = 'products'"
            >
                {{ form.product ? form.product.name : 'Выберите продукт...' }}
            </button>


            <p>Торговый представитель</p>
            <button
                type="button"
                class="btn w-100 p-3 mb-2"
                :class="form.agent ? 'btn-success' : 'btn-danger'"
                @click="tab = 'agents'"
            >
                {{ form.agent ? form.agent.name : 'Выберите младшего администратора...' }}
            </button>

            <p>Клиент</p>
            <button
                type="button"
                class="btn w-100 p-3 mb-2"
                :class="form.client ? 'btn-success' : 'btn-danger'"
                @click="tab = 'clients'"
            >
                {{ form.client ? form.client.name : 'Выберите клиента...' }}
            </button>


            <button type="submit" class="btn btn-primary w-100 p-3">
                Создать задание
            </button>
        </form>
    </template>

    <!-- Заглушка для поставщиков -->
    <template v-if="tab === 'suppliers'">
        <h4>Загрузка данных для поставщика...</h4>
        <!-- тут будет список поставщиков -->
        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад к форме</button>
    </template>

    <!-- Заглушка для продуктов -->
    <template v-if="tab === 'products'">
        <h4>Загрузка данных для продукта...</h4>
        <!-- тут будет список продуктов -->
        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад к форме</button>
    </template>

    <!-- Заглушка для агентов -->
    <template v-if="tab === 'agents'">
        <h4>Загрузка данных</h4>
        <!-- тут будет список агентов -->
        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад к форме</button>
    </template>

    <!-- Заглушка для клиентов -->
    <template v-if="tab === 'clients'">
        <h4>Загрузка данных для клиента...</h4>
        <!-- тут будет список клиентов -->
        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад к форме</button>
    </template>
</template>

<script>
export default {
    name: 'CreateAgentTaskForm',
    data() {
        return {
            tab: 'form',
            form: {
                title: '',
                description: '',
                due_date: '',
                supplier: null,
                product: null,
                agent: null,
                client: null
            }
        }
    },
    methods: {
        createTask() {
            console.log('Создано задание:', this.form)
        }
    }
}
</script>
