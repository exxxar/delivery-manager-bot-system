
<template>
    <div class="container-fluid px-0 py-0">
        <BackBtn/>

        <div class="position-relative">

            <!-- Глобальный overlay -->
            <div v-if="jobStore.loading"
                 class="loading-overlay">

                <div class="text-center">
                    <div class="spinner-border text-primary mb-3"
                         style="width: 4rem; height: 4rem;"
                         role="status">
                    </div>

                    <h5 class="mb-2">
                        Выполняется выгрузка...
                    </h5>
                </div>
            </div>

            <div class="row g-2">

                <!-- Администраторы -->
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-primary shadow-sm h-100 menu-card"
                         @click="!jobStore.loading && exportAgents()"
                         :class="{ 'opacity-50': jobStore.loading }"
                         style="cursor:pointer;">

                        <div class="card-body text-center p-4">

                            <!-- Spinner -->
                            <div v-if="jobStore.loading"
                                 class="spinner-border text-primary mb-3"
                                 role="status">
                            </div>

                            <i v-else
                               class="fa-solid fa-user-shield fa-3x text-primary mb-3"></i>

                            <h5 class="card-title">
                                Администраторы
                            </h5>

                            <p class="text-muted small mb-0">
                                Выгрузка списка администраторов
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Старшие админы -->
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-danger shadow-sm h-100 menu-card"
                         @click="!jobStore.loading && exportAdmins()"
                         :class="{ 'opacity-50': jobStore.loading }"
                         style="cursor:pointer;">

                        <div class="card-body text-center p-4">

                            <div v-if="jobStore.loading"
                                 class="spinner-border text-danger mb-3"
                                 role="status">
                            </div>

                            <i v-else
                               class="fa-solid fa-user-tie fa-3x text-danger mb-3"></i>

                            <h5 class="card-title">
                                Старшие администраторы
                            </h5>

                            <p class="text-muted small mb-0">
                                Выгрузка старших администраторов
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Пользователи -->
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-success shadow-sm h-100 menu-card"
                         @click="!jobStore.loading && exportUsers()"
                         :class="{ 'opacity-50': jobStore.loading }"
                         style="cursor:pointer;">

                        <div class="card-body text-center p-4">

                            <div v-if="jobStore.loading"
                                 class="spinner-border text-success mb-3"
                                 role="status">
                            </div>

                            <i v-else
                               class="fa-solid fa-users fa-3x text-success mb-3"></i>

                            <h5 class="card-title">
                                Пользователи
                            </h5>

                            <p class="text-muted small mb-0">
                                Выгрузка пользователей системы
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Продукты -->
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-warning shadow-sm h-100 menu-card"
                         @click="!jobStore.loading && exportProducts()"
                         :class="{ 'opacity-50': jobStore.loading }"
                         style="cursor:pointer;">

                        <div class="card-body text-center p-4">

                            <div v-if="jobStore.loading"
                                 class="spinner-border text-warning mb-3"
                                 role="status">
                            </div>

                            <i v-else
                               class="fa-solid fa-box-open fa-3x text-warning mb-3"></i>

                            <h5 class="card-title">
                                Продукты
                            </h5>

                            <p class="text-muted small mb-0">
                                Выгрузка списка товаров
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Категории -->
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-info shadow-sm h-100 menu-card"
                         @click="!jobStore.loading && exportCategories()"
                         :class="{ 'opacity-50': jobStore.loading }"
                         style="cursor:pointer;">

                        <div class="card-body text-center p-4">

                            <div v-if="jobStore.loading"
                                 class="spinner-border text-info mb-3"
                                 role="status">
                            </div>

                            <i v-else
                               class="fa-solid fa-layer-group fa-3x text-info mb-3"></i>

                            <h5 class="card-title">
                                Категории продуктов
                            </h5>

                            <p class="text-muted small mb-0">
                                Выгрузка категорий товаров
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Поставщики -->
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-secondary shadow-sm h-100 menu-card"
                         @click="!jobStore.loading && exportSuppliers()"
                         :class="{ 'opacity-50': jobStore.loading }"
                         style="cursor:pointer;">

                        <div class="card-body text-center p-4">

                            <div v-if="jobStore.loading"
                                 class="spinner-border text-secondary mb-3"
                                 role="status">
                            </div>

                            <i v-else
                               class="fa-solid fa-truck fa-3x text-secondary mb-3"></i>

                            <h5 class="card-title">
                                Поставщики
                            </h5>

                            <p class="text-muted small mb-0">
                                Выгрузка поставщиков
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Клиенты -->
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-primary shadow-sm h-100 menu-card"
                         @click="!jobStore.loading && exportClients()"
                         :class="{ 'opacity-50': jobStore.loading }"
                         style="cursor:pointer;">

                        <div class="card-body text-center p-4">

                            <div v-if="jobStore.loading"
                                 class="spinner-border text-primary mb-3"
                                 role="status">
                            </div>

                            <i v-else
                               class="fa-solid fa-handshake fa-3x text-primary mb-3"></i>

                            <h5 class="card-title">
                                Клиенты
                            </h5>

                            <p class="text-muted small mb-0">
                                Выгрузка клиентской базы
                            </p>

                        </div>
                    </div>
                </div>

                <!-- История продаж -->
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-dark shadow-sm h-100 menu-card"
                         @click="!jobStore.loading && exportSalesHistory()"
                         :class="{ 'opacity-50': jobStore.loading }"
                         style="cursor:pointer;">

                        <div class="card-body text-center p-4">

                            <div v-if="jobStore.loading"
                                 class="spinner-border text-dark mb-3"
                                 role="status">
                            </div>

                            <i v-else
                               class="fa-solid fa-chart-line fa-3x text-dark mb-3"></i>

                            <h5 class="card-title">
                                История продаж
                            </h5>

                            <p class="text-muted small mb-0">
                                Выгрузка статистики продаж
                            </p>

                        </div>
                    </div>
                </div>

                <!-- Дни рождения -->
                <div class="col-12 col-md-6 col-xl-4">

                    <div class="card border-danger shadow-sm h-100 menu-card"
                         @click="!jobStore.loading && exportBirthdaysList()"
                         :class="{ 'opacity-50': jobStore.loading }"
                         style="cursor:pointer;">

                        <div class="card-body text-center p-4">

                            <div v-if="jobStore.loading"
                                 class="spinner-border text-danger mb-3"
                                 role="status">
                            </div>

                            <i v-else
                               class="fa-solid fa-cake-candles fa-3x text-danger mb-3"></i>

                            <h5 class="card-title">
                                Дни рождения
                            </h5>

                            <p class="text-muted small mb-0">
                                Выгрузка списка дней рождения
                            </p>

                        </div>
                    </div>
                </div>

            </div>
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
