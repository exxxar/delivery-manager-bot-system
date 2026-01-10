<script setup>
import Pagination from "@/Components/Pagination.vue";
import ProductCategoryForm from "@/Components/ProductCategory/ProductCategoryForm.vue";
</script>

<template>

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
    <ul class="list-group">
        <li
            v-for="category in productCategoryStore.items"
            :key="category.id"
            v-bind:class="{'border-primary': selection.indexOf(category.id)!==-1}"
            class="list-group-item d-flex justify-content-between align-items-center"
        >
            <div @click="toggleSelection(category)">
                <div class="fw-bold">{{ category.name }}</div>
                <small class="text-muted">{{ category.description }}</small>
            </div>

            <!-- Dropdown меню -->
            <div class="dropdown">
                <button class="btn btn-sm" type="button"
                        data-bs-toggle="dropdown">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <template v-if="forSelect">
                        <li><a class="dropdown-item" href="#" @click.prevent="$emit('select', category)">Выбрать</a>
                        </li>

                    </template>
                    <template v-if="!forSelect">
                        <li>
                            <a
                                class="dropdown-item"
                                href="#"
                                @click.prevent="openViewModal(category)"
                            >Просмотреть</a>
                        </li>
                        <li>
                            <a
                                class="dropdown-item"
                                href="#"
                                @click.prevent="openEditModal(category)"
                            >Редактировать</a>
                        </li>
                        <li>
                            <a
                                class="dropdown-item text-danger"
                                href="#"
                                @click.prevent="openDeleteModal(category)"
                            >Удалить</a>
                        </li>
                    </template>

                </ul>
            </div>
        </li>
    </ul>

    <Pagination
        :pagination="productCategoryStore.pagination"
        @page-changed="fetchDataByUrl"
    />

    <!-- Сообщение если список пуст -->
    <div
        v-if="productCategoryStore.items.length === 0"
        class="alert alert-info mt-3"
    >
        Категорий пока нет.
    </div>


    <!-- Модалка просмотра -->
    <div class="modal fade" id="viewCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Просмотр категории</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div v-if="selectedCategory">
                        <p><strong>Название:</strong> {{ selectedCategory.name }}</p>
                        <p><strong>Описание:</strong> {{ selectedCategory.description }}</p>
                        <p><strong>ID:</strong> {{ selectedCategory.id }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка редактирования -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирование категории</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Заглушка под форму -->
                    <ProductCategoryForm
                        v-if="selectedCategory"
                        :initial-data="selectedCategory" @saved="fetchData"/>
                </div>
            </div>
        </div>
    </div>


</template>

<script>
import {useProductCategoriesStore} from "@/stores/product-categories";
import {useModalStore} from "@/stores/utillites/useConfitmModalStore";

export default {
    name: "ProductCategoryList",
    props:["forSelect"],
    data() {
        return {

            modalStore: useModalStore(),
            productCategoryStore: useProductCategoriesStore(),
            selectedCategory: null,
            selection: []
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        removeAll() {
            this.modalStore.open(
                `Вы уверены, что хотите удалить все выбранные элементы?`,
                () => {
                    this.productCategoryStore.removeAll(this.selection)
                    this.selection = []
                },
                () => {
                    this.modalStore.close()
                    this.selection = []
                }
            )


        },
        toggleSelection(category) {
            if (this.forSelect)
            {
                this.$emit('select', category)
                return
            }

            let index = this.selection.findIndex(i => i === category.id)
            if (index === -1)
                this.selection.push(category.id)
            else
                this.selection.splice(index, 1)
        },
        selectAll() {
            if (this.selection.length === 0)
                this.productCategoryStore.items.forEach(i => {
                    if (this.selection.indexOf(i.id) === -1)
                        this.selection.push(i.id)
                })
            else
                this.selection = []
        },
        async fetchData(page = 1) {

            const editModal = bootstrap.Modal.getInstance(document.getElementById("editCategoryModal"));

            if (editModal)
                editModal.hide()

            await this.productCategoryStore.fetchAllByPage(page);
        },
        async fetchDataByUrl(url) {
            await this.productCategoryStore.fetchByUrl(url);
        },
        openViewModal(category) {
            this.selectedCategory = category;
            new bootstrap.Modal(document.getElementById("viewCategoryModal")).show();
        },
        openEditModal(category) {
            this.selectedCategory = null
            this.$nextTick(() => {
                this.selectedCategory = category;
                new bootstrap.Modal(document.getElementById("editCategoryModal")).show();
            })

        },
        openDeleteModal(category) {
            this.selectedCategory = category;
            this.modalStore.open(
                `Вы уверены, что хотите удалить ${this.selectedCategory.name}?`,
                () => {
                    this.productCategoryStore.remove(this.selectedCategory.id)
                    this.selection = []
                    this.productCategoryStore.fetchAllByPage();
                },
                () => {
                    this.modalStore.close()
                    this.selection = []
                })
        },


    }
};
</script>
