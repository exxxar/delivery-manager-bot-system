<template>

    <div class="container-fluid px-0 py-0">
        <div class="row g-2">

            <!-- Общий список товаров -->
            <div class="col-12 col-md-6 col-xl-4">

                <div
                    class="card border-primary shadow-sm h-100 menu-card"
                    @click="selectMenu('products')"
                    style="cursor:pointer;"
                >

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-boxes-stacked fa-3x text-primary mb-3"></i>

                        <h5 class="card-title">
                            Общий список товаров
                        </h5>

                        <p class="text-muted small mb-0">
                            Просмотр всех товаров
                        </p>

                    </div>
                </div>
            </div>

            <!-- По поставщикам -->
            <div class="col-12 col-md-6 col-xl-4">

                <div
                    class="card border-warning shadow-sm h-100 menu-card"
                    @click="selectMenu('products-by-supplier')"
                    style="cursor:pointer;"
                >

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-truck fa-3x text-warning mb-3"></i>

                        <h5 class="card-title">
                            Товары по поставщикам
                        </h5>

                        <p class="text-muted small mb-0">
                            Группировка товаров по поставщикам
                        </p>

                    </div>
                </div>
            </div>

            <!-- По категориям -->
            <div class="col-12 col-md-6 col-xl-4">

                <div
                    class="card border-success shadow-sm h-100 menu-card"
                    @click="selectMenu('products-by-categories')"
                    style="cursor:pointer;"
                >

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-layer-group fa-3x text-success mb-3"></i>

                        <h5 class="card-title">
                            Товары по категориям
                        </h5>

                        <p class="text-muted small mb-0">
                            Просмотр товаров по категориям
                        </p>

                    </div>
                </div>
            </div>

            <!-- Категории -->
            <div class="col-12 col-md-6 col-xl-4">

                <div
                    class="card border-info shadow-sm h-100 menu-card"
                    @click="selectMenu('categories')"
                    style="cursor:pointer;"
                >

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-tags fa-3x text-info mb-3"></i>

                        <h5 class="card-title">
                            Категории товаров
                        </h5>

                        <p class="text-muted small mb-0">
                            Управление категориями
                        </p>

                    </div>
                </div>
            </div>

            <!-- Импорт -->
            <div
                v-if="(user?.role || 0) >= 3"
                class="col-12 col-md-6 col-xl-4"
            >

                <div
                    class="card border-danger shadow-sm h-100 menu-card"
                    @click="selectMenu('import-products')"
                    style="cursor:pointer;"
                >

                    <div class="card-body text-center p-4">

                        <i class="fa-solid fa-file-import fa-3x text-danger mb-3"></i>

                        <h5 class="card-title">
                            Импорт прайса
                        </h5>

                        <p class="text-muted small mb-0">
                            Загрузка товаров и категорий
                        </p>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="dropdown mt-2" v-if="(user?.role || 0) >= 3">
        <button class="btn btn-outline-primary p-3 dropdown-toggle  w-100" type="button" data-bs-toggle="dropdown"
                aria-expanded="false">
            Скачать таблицу с товарами
        </button>
        <ul class="dropdown-menu w-100">
            <li>
                <button
                    type="button"
                    :disabled="jobStore.loading"
                    class="dropdown-item"
                    @click="exportProducts(0)">
                    Список всех товаров
                </button>
            </li>
            <li>
                <button
                    type="button"
                    :disabled="jobStore.loading"
                    @click="exportProducts(1)"
                    class="dropdown-item">
                    Список по категориям
                </button>
            </li>
            <li>
                <button
                    type="button"
                    :disabled="jobStore.loading"
                    @click="exportProducts(2)"
                    class="dropdown-item">
                    Список по поставщикам
                </button>
            </li>
        </ul>
    </div>
</template>
<script>
import {useBaseExports} from "@/stores/baseExports";
import {useUsersStore} from "@/stores/users";
export default {
    data() {
        return {
            userStore: useUsersStore(),
            jobStore: useBaseExports()
        }
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
    },
    methods: {
        selectMenu(name) {
            this.$emit("select", name)
        },
        exportProducts(type) {
            this.jobStore.exportProducts(type)
        }
    }
}
</script>
