
<template>
    <div class="container-fluid p-3">
        <BackBtn/>

        <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню действий">
            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportAgents"
                :disabled="jobStore.loading"
            >
                Выгрузить младших админов
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
                @click="exportSuppliers"
                :disabled="jobStore.loading"
            >
                Выгрузить поставщиков
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportClients"
                :disabled="jobStore.loading"
            >
                Выгрузить клиентов
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportSalesHistory"
                :disabled="jobStore.loading"
            >
                Выгрузить историю продаж
            </button>

            <button
                type="button"
                class="btn btn-outline-primary p-3"
                @click="exportBirthdaysList"
                :disabled="jobStore.loading"
            >
                Выгрузить дни рождения
            </button>
        </div>


    </div>
</template>

<script>
import {useBaseExports} from '@/stores/baseExports'
import BackBtn from "@/Components/BackBtn.vue";
export default {
    name: 'ExportsMenu',
    components: {BackBtn},

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
        const exportBirthdaysList = () => jobStore.exportBirthdaysList()

        return {
            jobStore,
            exportAgents,
            exportAdmins,
            exportUsers,
            exportProducts,
            exportCategories,
            exportClients,
            exportSuppliers,
            exportSalesHistory,
            exportBirthdaysList
        }
    }
}
</script>
