<template>
    <!-- Основная форма -->
    <template v-if="tab === 'form'">
        <form @submit.prevent="updateTask">
            <!-- Нередактируемые поля -->
            <div class="form-floating mb-2">
                <p class="form-control bg-light text-muted">{{ form.title }}</p>
                <label>Название задачи</label>
            </div>

            <div class="form-floating mb-2">
                <p class="form-control bg-light text-muted">{{ form.description }}</p>
                <label>Описание</label>
            </div>

            <div class="form-floating mb-2">
                <p class="form-control bg-light text-muted">{{ form.due_date }}</p>
                <label>Дата начала задачи</label>
            </div>

            <div class="form-floating mb-2">
                <p class="form-control bg-light text-muted">{{ form.agent?.name }}</p>
                <label>Назначенный агент</label>
            </div>

            <!-- Редактируемые поля -->
            <div class="form-floating mb-2">
                <input v-model="form.sale_date" type="date" class="form-control" required />
                <label>Дата продажи</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.total_price" type="number" step="0.01" class="form-control" min="0" required />
                <label>Сумма сделки</label>
            </div>

            <div class="form-floating mb-2">
                <input v-model="form.quantity" type="number" class="form-control" min="1" required />
                <label>Количество товара</label>
            </div>

            <!-- Кнопки выбора -->
            <p>Поставщик</p>

                <button
                    type="button"
                    class="btn w-100 py-3 mb-2"
                    :class="form.supplier ? 'btn-success' : 'btn-danger'"
                    @click="tab = 'suppliers'"
                >
                    {{ form.supplier ? form.supplier.name : 'Выберите поставщика...' }}
                </button>

            <p>Продукт</p>
            <button
                type="button"
                class="btn w-100 py-3 mb-2"
                :class="form.product ? 'btn-success' : 'btn-danger'"
                @click="tab = 'products'"
            >
                {{ form.product ? form.product.name : 'Выберите продукт...' }}
            </button>

            <p>Клиент</p>
            <button
                type="button"
                class="btn w-100 py-3 mb-2"
                :class="form.client ? 'btn-success' : 'btn-danger'"
                @click="tab = 'clients'"
            >
                {{ form.client ? form.client.name : 'Выберите клиента...' }}
            </button>


            <button type="submit" class="btn btn-primary w-100 p-3">
                Обновить задание
            </button>
        </form>

    </template>

    <!-- Заглушки -->
    <template v-if="tab === 'suppliers'">
        <h4>Выбор поставщика</h4>

        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад</button>
    </template>

    <template v-if="tab === 'products'">
        <h4>Выбор продукта</h4>

        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад</button>
    </template>

    <template v-if="tab === 'clients'">
        <h4>Выбор клиента</h4>

        <button class="btn btn-secondary mt-3" @click="tab = 'form'">Назад</button>
    </template>
</template>

<script>
import { useJobStore } from '@/stores/useJobStore'

export default {
    name: 'AgentTaskForm',
    data() {
        return {
            tab: 'form',
            form: {
                title: 'Продажа партии товара',
                description: 'Задача по продаже и доставке',
                due_date: '2025-11-20',
                agent: { id: 1, name: 'Агент Сидоров' },
                sale_date: '',
                total_price: 0,
                quantity: 1,
                supplier: null,
                product: null,
                client: null
            },

        }
    },
    methods: {
        selectSupplier(supplier) {
            this.form.supplier = supplier
            this.tab = 'form'
        },
        selectProduct(product) {
            this.form.product = product
            this.tab = 'form'
        },
        selectClient(client) {
            this.form.client = client
            this.tab = 'form'
        },
        async updateTask() {
            try {
                const jobStore = useJobStore()
                await jobStore.submitForm(
                    'sales/update',
                    {
                        sale_date: this.form.sale_date,
                        total_price: this.form.total_price,
                        quantity: this.form.quantity,
                        supplier_id: this.form.supplier?.id,
                        product_id: this.form.product?.id,
                        customer_id: this.form.client?.id
                    },
                    'Задание обновлено агентом',
                    'Ошибка при обновлении задания'
                )
            } catch (e) {
                console.error(e)
            }
        }
    }
}
</script>
