<template>

    <div class="btn-group-vertical w-100" role="group" aria-label="Вертикальное меню">
        <button type="button"
                @click="selectMenu('products')"
                class="btn btn-outline-primary p-3">Общий список товаров
        </button>
        <button type="button"
                @click="selectMenu('products-by-supplier')"
                class="btn btn-outline-primary p-3">Список товаров по поставщикам
        </button>
        <button type="button"
                @click="selectMenu('products-by-categories')"
                class="btn btn-outline-primary p-3">Список товаров по категориям
        </button>
        <button type="button"
                @click="selectMenu('categories')"
                class="btn btn-outline-primary p-3">Список категорий товаров
        </button>
        <button
            v-if="(user?.role || 0) >= 3"
            @click="selectMenu('import-products')"
            type="button" class="btn btn-outline-primary p-3">Загрузить прайс с товарами и категориями
        </button>


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
