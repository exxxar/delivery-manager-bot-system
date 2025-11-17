<script setup>
import Pagination from "@/Components/Pagination.vue";
import ProductCategoryForm from "@/Components/ProductCategory/ProductCategoryForm.vue";
</script>

<template>

        <h4 class="mb-3">Категории продуктов</h4>

        <ul class="list-group">
            <li
                v-for="category in productCategoryStore.items"
                :key="category.id"
                class="list-group-item d-flex justify-content-between align-items-center"
            >
                <div>
                    <div class="fw-bold">{{ category.name }}</div>
                    <small class="text-muted">{{ category.description }}</small>
                </div>

                <!-- Dropdown меню -->
                <div class="dropdown">
                    <button
                        class="btn btn-sm btn-outline-secondary dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown"
                    >
                        Действия
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
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
                        :initial-data="selectedCategory" @saved="fetchData" />
                </div>
            </div>
        </div>
    </div>

    <!-- Модалка удаления -->
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Удаление категории</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>
                        Вы уверены, что хотите удалить категорию
                        <strong>{{ selectedCategory?.name }}</strong>?
                    </p>
                </div>
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >Отмена</button>
                    <button
                        type="button"
                        class="btn btn-danger"
                        @click="confirmDelete"
                    >Удалить</button>
                </div>
            </div>
        </div>
    </div>


</template>

<script>
import { useProductCategoriesStore } from "@/stores/product-categories";

export default {
    name: "ProductCategoryList",
    data() {
        return {
            productCategoryStore: useProductCategoriesStore(),
            selectedCategory: null
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        async fetchData(page = 1) {
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
            this.$nextTick(()=>{
                this.selectedCategory = category;
                new bootstrap.Modal(document.getElementById("editCategoryModal")).show();
            })

        },
        openDeleteModal(category) {
            this.selectedCategory = category;
            new bootstrap.Modal(document.getElementById("deleteCategoryModal")).show();
        },
        async confirmDelete() {
            if (!this.selectedCategory) return;
            await this.productCategoryStore.delete(this.selectedCategory.id);
            this.fetchData();
            bootstrap.Modal.getInstance(
                document.getElementById("deleteCategoryModal")
            ).hide();
        }
    }
};
</script>
