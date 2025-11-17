<template>
    <form @submit.prevent="changeStatus">
        <div class="mb-3">
            <label class="form-label">Статус задачи</label>
            <select v-model="status" class="form-select" required>
                <option value="pending">Ожидает</option>
                <option value="assigned">Назначено</option>
                <option value="delivered">Доставлено</option>
                <option value="completed">Завершено</option>
                <option value="rejected">Отклонено</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="loading">
            {{ loading ? 'Обновляем...' : 'Сменить статус' }}
        </button>
    </form>
</template>

<script>
import { useJobStore } from '@/stores/useJobStore'

export default {
    name: 'ChangeStatusForm',
    props: {
        saleId: { type: Number, required: true } // ID задачи
    },
    data() {
        return {
            status: 'pending',
            loading: false
        }
    },
    methods: {
        async changeStatus() {
            this.loading = true
            try {
                const jobStore = useJobStore()
                await jobStore.submitForm(
                    `sales/${this.saleId}/status`,
                    { status: this.status },
                    'Статус задачи обновлён',
                    'Ошибка при смене статуса'
                )
            } catch (e) {
                console.error(e)
            } finally {
                this.loading = false
            }
        }
    }
}
</script>
