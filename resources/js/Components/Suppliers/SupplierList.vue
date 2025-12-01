<script setup>
import Pagination from "@/Components/Pagination.vue";
import SupplierFilter from "@/Components/Suppliers/SupplierFilter.vue";
</script>

<template>


    <div class="form-floating mb-3">
        <input type="search"
               v-model="search"
               class="form-control" id="searchInput" placeholder="Поиск..." />
        <label for="searchInput">Поиск</label>
    </div>

    <template v-if="!forSelect">
        <SupplierFilter></SupplierFilter>
    </template>


    <ul class="list-group">
        <li
            @click="selectSupplier(supplier)"
            v-for="supplier in filteredSuppliers" :key="supplier.id"
            class="list-group-item d-flex justify-content-between align-items-start">
            <div class="ms-2 me-auto">
                <div class="fw-bold">{{ supplier.name }}
                    <span class="badge bg-primary rounded-pill">{{ supplier.percent }}%</span>
                </div>
                <small class="text-muted">
                    Телефон: {{ supplier.phone }}<br>
                    Email: {{ supplier.email }}<br>
                    Адрес: {{ supplier.address }}
                </small>
            </div>

        </li>
    </ul>

    <Pagination
        :pagination="suppliersStore.pagination"
        @page-changed="fetchDataByUrl"
    />

    <!-- Сообщение если список пуст -->
    <div v-if="suppliersStore.items?.length === 0" class="alert alert-info mt-3">
        Поставщиков пока нет.
    </div>


</template>

<script>
import axios from 'axios'
import {useSuppliersStore} from "@/stores/suppliers";

export default {
    name: 'SupplierListGroup',
    props: ["forSelect"],
    data() {
        return {
            search:null,
            suppliersStore: useSuppliersStore(),
        }
    },
    computed: {
        filteredSuppliers() {
            if (!this.search) return this.suppliersStore.items || []
            const q = this.search.toLowerCase()
            return this.suppliersStore.items.filter(supplier =>
                Object.values(supplier).some(val =>
                    val ? String(val).toLowerCase().includes(q) : false
                )
            )
        }
    },
    created() {
        this.fetchData()
    },
    methods: {
        async fetchData(page = 1) {
            await this.suppliersStore.fetchAllByPage(page)
        },
        async fetchDataByUrl(url) {
            await this.suppliersStore.fetchByUrl(url)
        },
        selectSupplier(supplier) {
            if (!this.forSelect)
                return
            this.$emit("select-supplier", supplier)
        }

    }
}
</script>
