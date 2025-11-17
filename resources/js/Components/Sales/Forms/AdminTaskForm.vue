<template>
    <form @submit.prevent="updateSale">
        <!-- Название -->
        <div class="mb-3">
            <label class="form-label">Название задачи</label>
            <input v-model="form.title" type="text" class="form-control" required />
        </div>

        <!-- Описание -->
        <div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea v-model="form.description" class="form-control"></textarea>
        </div>

        <!-- Статус -->
        <div class="mb-3">
            <label class="form-label">Статус</label>
            <select v-model="form.status" class="form-select" required>
                <option value="pending">Ожидает</option>
                <option value="assigned">Назначено</option>
                <option value="delivered">Доставлено</option>
                <option value="completed">Завершено</option>
                <option value="rejected">Отклонено</option>
            </select>
        </div>

        <!-- Даты -->
        <div class="mb-3">
            <label class="form-label">Дата встречи (due_date)</label>
            <input v-model="form.due_date" type="date" class="form-control" />
        </div>

        <div class="mb-3">
            <label class="form-label">Дата продажи (sale_date)</label>
            <input v-model="form.sale_date" type="date" class="form-control" />
        </div>

        <div class="mb-3">
            <label class="form-label">Планируемая дата доставки</label>
            <input v-model="form.planned_delivery_date" type="date" class="form-control" />
        </div>

        <div class="mb-3">
            <label class="form-label">Фактическая дата доставки</label>
            <input v-model="form.actual_delivery_date" type="date" class="form-control" />
        </div>

        <!-- Количество и сумма -->
        <div class="mb-3">
            <label class="form-label">Количество товара</label>
            <input v-model="form.quantity" type="number" class="form-control" min="0" />
        </div>

        <div class="mb-3">
            <label class="form-label">Сумма заказа</label>
            <input v-model="form.total_price" type="number" step="0.01" class="form-control" min="0" />
        </div>

        <!-- Выборы -->
        <div class="mb-3">
            <label class="form-label">Агент</label>
            <select v-model="form.agent_id" class="form-select">
                <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                    {{ agent.name }}
                </option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Клиент</label>
            <select v-model="form.customer_id" class="form-select">
                <option v-for="client in clients" :key="client.id" :value="client.id">
                    {{ client.name }}
                </option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Поставщик</label>
            <select v-model="form.supplier_id" class="form-select">
                <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                    {{ supplier.name }}
                </option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Продукт</label>
            <select v-model="form.product_id" class="form-select">
                <option v-for="product in products" :key="product.id" :value="product.id">
                    {{ product.name }}
                </option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Сохранить изменения
        </button>
    </form>
</template>

<script>
import { useJobStore } from '@/stores/useJobStore'

export default {
    name: 'AdminTaskForm',
    data() {
        return {
            form: {
                title: '',
                description: '',
                status: 'pending',
                due_date: '',
                sale_date: '',
                planned_delivery_date: '',
                actual_delivery_date: '',
                quantity: 0,
                total_price: 0,
                agent_id: null,
                customer_id: null,
                supplier_id: null,
                product_id: null
            },
            agents: [
                { id: 1, name: 'Агент Сидоров' },
                { id: 2, name: 'Агент Иванов' }
            ],
            clients: [
                { id: 1, name: 'Клиент Петров' },
                { id: 2, name: 'Клиент Смирнов' }
            ],
            suppliers: [
                { id: 1, name: 'Поставщик А' },
                { id: 2, name: 'Поставщик Б' }
            ],
            products: [
                { id: 1, name: 'Продукт X' },
                { id: 2, name: 'Продукт Y' }
            ]
        }
    },
    methods: {
        async updateSale() {
            try {
                const jobStore = useJobStore()
                await jobStore.submitForm(
                    'sales/update',
                    this.form,
                    'Задача обновлена администратором',
                    'Ошибка при обновлении задачи'
                )
            } catch (e) {
                console.error(e)
            }
        }
    }
}
</script>
