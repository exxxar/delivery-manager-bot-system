<script setup>
import Pagination from "@/Components/Pagination.vue";
import SupplierFilter from "@/Components/Suppliers/SupplierFilter.vue";
import SupplierForm from "@/Components/Suppliers/SupplierForm.vue";
</script>

<template>

    <div class="input-group mb-2">
        <div class="form-floating ">
            <input class="form-control"
                   type="search"
                   @keyup="findSupplier"
                   v-model="search"
                   id="supplierInput" placeholder="Поставщик"/>
            <label for="supplierInput">Поставщик</label>
        </div>

        <button
            @click="findSupplier"
            type="button"
            class="btn btn-outline-light text-primary"
        >
            Найти
        </button>
    </div>

    <div
        class="form-check form-switch mb-2">
        <input
            class="form-check-input"
            type="checkbox"
            v-model="showSimpleSupplierForm"
            :id="`show-simple-supplier-form`"
        />
        <label class="form-check-label" :for="`show-simple-supplier-form`">
            Добавить и выбрать нового поставщика
        </label>
    </div>

    <template v-if="showSimpleSupplierForm">
        <SupplierForm
            v-on:saved="addNewSupplier"
            class="mb-2"
            :is-simple="true"></SupplierForm>
    </template>


    <!--
        <div class="form-floating mb-3">
            <input type="search"
                   v-model="search"
                   class="form-control" id="searchInput" placeholder="Поиск..." />
            <label for="searchInput">Поиск</label>
        </div>-->

    <template v-if="!forSelect">
        <SupplierFilter v-on:apply-filters="applyFilters"></SupplierFilter>

        <div class="d-flex">
            <a href="javascript:void(0)"
               @click="selectAll"
               class="small">Выделить все</a>
            <template v-if="selection.length>0">
                <a href="javascript:void(0)"
                   @click="removeAll"
                   class="small text-danger mx-2">Удалить выделенное</a>
            </template>

        </div>
    </template>


    <ul class="list-group">
        <li
            v-for="supplier in suppliersStore.items"
            :key="supplier.id"
            class="list-group-item d-flex justify-content-between align-items-start"
            v-bind:class="{'border-primary': selection.indexOf(supplier.id)!==-1}"
        >
            <!-- Левая часть -->
            <div class="flex-grow-1 me-3 text-break" @click="selectSupplier(supplier)">
                <div class="fw-bold" @click="toggleSelection(supplier.id)">
                    <span class="badge bg-primary" v-if="field_visible?.id||false">#{{
                            supplier.id
                        }}</span>{{ supplier.name }}
                    <span
                        v-if="field_visible?.percent||false"
                        class="badge bg-primary rounded-pill">{{ supplier.percent }}%</span>
                </div>

                <p class="mb-0" v-if="field_visible?.description||true">{{ supplier.description || '-' }}</p>
                <p class="mb-0 fw-bold small" v-if="field_visible?.phone||true">{{ supplier.phone }}</p>
                <p class="mb-2" v-if="field_visible?.email||false">{{ supplier.email }}</p>
                <p class="mb-2" v-if="field_visible?.address||false">{{ supplier.address }}</p>
                <p class="mb-2" v-if="field_visible?.birthday||false">{{ supplier.birthday }}</p>

            </div>

            <!-- Правая часть (меню) -->
            <div class="dropdown flex-shrink-0">
                <button class="btn btn-sm" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <template v-if="forSelect">
                        <li>
                            <a class="dropdown-item" href="#" @click.prevent="selectSupplier(supplier)">Выбрать</a>
                        </li>
                    </template>
                    <template v-else>
                        <li>
                            <a class="dropdown-item" href="#" @click.prevent="openEditModal(supplier)">Редактировать</a>
                        </li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" @click.prevent="openDeleteModal(supplier)">Удалить</a>
                        </li>
                    </template>
                </ul>
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

    <!-- Модалка редактирования -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование категории</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Заглушка под форму -->
                    <SupplierForm
                        v-if="selectedSupplier"
                        :initial-data="selectedSupplier" @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>

</template>

<script>
import axios from 'axios'
import {useSuppliersStore} from "@/stores/suppliers";
import {useModalStore} from "@/stores/utillites/useConfitmModalStore";

export default {
    name: 'SupplierListGroup',
    props: ["forSelect"],
    data() {
        return {
            field_visible: null,
            modalStore: useModalStore(),
            selection: [],
            search: null,
            showSimpleSupplierForm: false,
            selectedSupplier: null,
            suppliersStore: useSuppliersStore(),
        }
    },
    computed: {
        /* filteredSuppliers() {
             if (!this.search) return this.suppliersStore.items || []
             const q = this.search.toLowerCase()
             return this.suppliersStore.items.filter(supplier =>
                 Object.values(supplier).some(val =>
                     val ? String(val).toLowerCase().includes(q) : false
                 )
             )
         }*/
    },
    created() {
        this.fetchData()
    },
    methods: {
        findSupplier() {
            this.suppliersStore.setFilters({
                name: this.search || ''
            })
            this.suppliersStore.setSort('id', 'asc')
            this.suppliersStore.fetchFiltered(0, 30)
        },
        addNewSupplier(supplier){
            this.$emit("select", supplier)
        },
        applyFilters(filters) {

            this.field_visible = filters.field_visible
            let size = filters.size || 30
            let page = filters.page || 0
            delete filters.field_visible
            this.suppliersStore.setFilters(filters.filters)
            this.suppliersStore.setSort(filters.sort.field, filters.sort.direction)
            this.suppliersStore.fetchFiltered(page, size)
        },
        removeAll() {
            this.modalStore.open(
                `Вы уверены, что хотите удалить все выбранные элементы?`,
                () => {
                    this.suppliersStore.removeAll(this.selection)
                    this.selection = []
                },
                () => {
                    this.modalStore.close()
                    this.selection = []
                }
            )


        },
        openEditModal(supplier) {
            this.selectedSupplier = null
            this.$nextTick(() => {
                this.selectedSupplier = supplier;
                new bootstrap.Modal(document.getElementById("editSupplierModal")).show();
            })

        },
        toggleSelection(id) {
            let index = this.selection.findIndex(i => i === id)
            if (index === -1)
                this.selection.push(id)
            else
                this.selection.splice(index, 1)
        },
        selectAll() {
            if (this.selection.length === 0)
                this.suppliersStore.items.forEach(i => {
                    if (this.selection.indexOf(i.id) === -1)
                        this.selection.push(i.id)
                })
            else
                this.selection = []
        },
        async fetchData(page = 0) {
            await this.suppliersStore.fetchAllByPage(page)
        },
        async fetchDataByUrl(url) {
            await this.suppliersStore.fetchByUrl(url)
        },
        selectSupplier(supplier) {
            if (!this.forSelect)
                return
            this.$emit("select", supplier)
        },
        openDeleteModal(supplier) {
            this.selectedSupplier = supplier;
            this.modalStore.open(
                `Вы уверены, что хотите удалить ${this.selectedSupplier.name}?`,
                () => {
                    this.suppliersStore.remove(this.selectedSupplier.id)
                    this.selection = []
                    this.fetchData()
                },
                () => {
                    this.modalStore.close()
                    this.selection = []
                })
        },

    }
}
</script>
