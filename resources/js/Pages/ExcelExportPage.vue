<template>
    <div class="container-fluid p-3">
        <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню действий">
            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportAgents"
                :disabled="jobStore.loading"
            >
                Выгрузить агентов
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportAdmins"
                :disabled="jobStore.loading"
            >
                Выгрузить администраторов
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportUsers"
                :disabled="jobStore.loading"
            >
                Выгрузить пользователей
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportProducts"
                :disabled="jobStore.loading"
            >
                Выгрузить продукты
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportCategories"
                :disabled="jobStore.loading"
            >
                Выгрузить категории продуктов
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportClients"
                :disabled="jobStore.loading"
            >
                Выгрузить покупателей
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportSuppliers"
                :disabled="jobStore.loading"
            >
                Выгрузить поставщиков
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportSalesHistory"
                :disabled="jobStore.loading"
            >
                Выгрузить историю продаж
            </button>
        </div>


    </div>
</template>

<script>
import { useBaseExports } from '@/stores/baseExports'

export default {
    name: 'ExportsMenu',
    setup() {
        const jobStore = useBaseExports()

        const exportAgents = () => jobStore.exportAgents()
        const exportAdmins = () => jobStore.exportAdmins()
        const exportUsers = () => jobStore.exportUsers()
        const exportProducts = () => jobStore.exportProducts()
        const exportCategories = () => jobStore.exportCategories()
        const exportClients = () => jobStore.exportClients()
        const exportSuppliers = () => jobStore.exportSuppliers()
        const exportSalesHistory = () => jobStore.exportSalesHistory()

        return {
            jobStore,
            exportAgents,
            exportAdmins,
            exportUsers,
            exportProducts,
            exportCategories,
            exportClients,
            exportSuppliers,
            exportSalesHistory
        }
    }
}
</script>
