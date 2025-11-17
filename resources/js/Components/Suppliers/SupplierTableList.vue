<template>
    <div class="container-fluid p-3">
        <h4 class="mb-3">Список поставщиков</h4>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Название</th>
                    <th scope="col">Телефон</th>
                    <th scope="col">Email</th>
                    <th scope="col">Процент</th>
                    <th scope="col">Дата рождения</th>
                    <th scope="col">Действия</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="supplier in suppliers" :key="supplier.id">
                    <td>{{ supplier.id }}</td>
                    <td>{{ supplier.name }}</td>
                    <td>{{ supplier.phone }}</td>
                    <td>{{ supplier.email }}</td>
                    <td>{{ supplier.percent }}%</td>
                    <td>{{ supplier.birthday }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-2" @click="editSupplier(supplier)">
                            Редактировать
                        </button>
                        <button class="btn btn-sm btn-outline-danger" @click="deleteSupplier(supplier.id)">
                            Удалить
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Сообщение если список пуст -->
        <div v-if="suppliers.length === 0" class="alert alert-info mt-3">
            Поставщиков пока нет.
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'SupplierList',
    data() {
        return {
            suppliers: []
        }
    },
    created() {
        this.fetchSuppliers()
    },
    methods: {
        async fetchSuppliers() {
            try {
                const response = await axios.get('/api/suppliers')
                this.suppliers = response.data
            } catch (error) {
                console.error('Ошибка загрузки поставщиков:', error)
            }
        },
        editSupplier(supplier) {
            console.log('Редактируем:', supplier)
            // Здесь можно открыть модальное окно или перейти на страницу редактирования
        },
        async deleteSupplier(id) {
            if (!confirm('Удалить поставщика?')) return
            try {
                await axios.delete(`/api/suppliers/${id}`)
                this.suppliers = this.suppliers.filter(s => s.id !== id)
            } catch (error) {
                console.error('Ошибка удаления:', error)
            }
        }
    }
}
</script>
