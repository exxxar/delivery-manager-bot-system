<script setup>
import Pagination from "@/Components/Pagination.vue";
import ProductFilter from "@/Components/Products/ProductFilter.vue";
import ProductSimpleForm from "@/Components/Products/ProductSimpleForm.vue";
</script>

<template>
    <section class="product-list-page">
        <div class="product-toolbar">
            <div class="product-heading">
                <div class="product-heading-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div>
                    <h2 class="product-heading-title">Товары</h2>
                    <p class="product-heading-subtitle">Поиск и управление каталогом продукции</p>
                </div>
            </div>

            <form class="product-search" role="search" @submit.prevent="findProduct">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input
                    id="productSearchInput"
                    v-model="search"
                    class="form-control"
                    type="search"
                    placeholder="Поиск по названию товара"
                    aria-label="Поиск по названию товара"
                />
                <button type="submit" class="btn btn-primary search-submit">
                    <i class="fa-solid fa-search me-2"></i>Найти
                </button>
            </form>
        </div>

        <div v-if="!forSelect" class="filter-panel">
            <ProductFilter @apply-filters="applyFilters" />
        </div>

        <div class="add-product-panel">
            <div class="add-product-info">
                <span class="add-product-icon"><i class="fa-solid fa-circle-plus"></i></span>
                <div>
                    <div class="add-product-title">Новый товар</div>
                    <div class="add-product-description">Можно создать товар и сразу выбрать его</div>
                </div>
            </div>
            <div class="form-check form-switch add-product-switch mb-0">
                <input
                    id="show-simple-supplier-form"
                    v-model="showProductForm"
                    class="form-check-input"
                    type="checkbox"
                />
                <label class="form-check-label" for="show-simple-supplier-form">
                    {{ showProductForm ? 'Скрыть форму' : 'Добавить товар' }}
                </label>
            </div>
        </div>

        <transition name="panel-fade">
            <div v-if="showProductForm" class="simple-product-panel">
                <ProductSimpleForm
                    :supplier-id="filters?.supplier_id"
                    :is-simple="true"
                    class="mb-0"
                    @saved="addNewProduct"
                />
            </div>
        </transition>

        <div class="results-heading">
            <div>
                <h3>Каталог товаров</h3>
                <p>{{ forSelect ? 'Выберите товар из списка' : 'Просматривайте и редактируйте товары' }}</p>
            </div>
            <span class="results-icon"><i class="fa-solid fa-list-ul"></i></span>
        </div>

        <ul v-if="productStore.items.length > 0" class="product-items">
            <li v-for="product in productStore.items" :key="product.id" class="product-item">
                <button
                    type="button"
                    class="product-main"
                    :aria-label="`Выбрать товар ${product.name}`"
                    @click="$emit('select', product)"
                >
                    <span class="product-avatar"><i class="fa-solid fa-box"></i></span>
                    <span class="product-info">
                        <span class="product-name-row">
                            <span class="product-name">{{ product.name }}</span>
                            <span v-if="field_visible?.id || false" class="product-id">#{{ product.id }}</span>
                        </span>
                        <span v-if="field_visible?.description || false" class="product-description">
                            {{ product.description || 'Описание не указано' }}
                        </span>
                        <span class="product-meta">
                            <span><i class="fa-solid fa-tags"></i>{{ product.category?.name || 'Без категории' }}</span>
                            <span><i class="fa-solid fa-truck"></i>{{ product.supplier?.name || 'Поставщик не указан' }}</span>
                        </span>
                        <span v-if="product.supplier?.description" class="supplier-description">
                            {{ product.supplier.description }}
                        </span>
                        <span v-if="field_visible?.price || false" class="product-detail">
                            <i class="fa-solid fa-ruble-sign"></i> {{ product.price }} руб.
                        </span>
                        <span v-if="field_visible?.count || false" class="product-detail">
                            <i class="fa-solid fa-cubes-stacked"></i> {{ product.count }} ед.
                        </span>
                    </span>
                </button>

                <div class="product-actions dropdown">
                    <button
                        class="btn action-menu-button"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-label="Действия с товаром"
                        aria-expanded="false"
                    >
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <template v-if="forSelect">
                            <li>
                                <a class="dropdown-item" href="#" @click.prevent="$emit('select', product)">
                                    <i class="fa-solid fa-check me-2"></i>Выбрать
                                </a>
                            </li>
                        </template>
                        <template v-else>
                            <li>
                                <a class="dropdown-item" href="#" @click.prevent="openView(product)">
                                    <i class="fa-regular fa-eye me-2"></i>Просмотреть
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" @click.prevent="openEdit(product)">
                                    <i class="fa-regular fa-pen-to-square me-2"></i>Редактировать
                                </a>
                            </li>
                            <li v-if="(user?.role || 0) >= 3">
                                <a class="dropdown-item text-danger" href="#" @click.prevent="confirmDelete(product)">
                                    <i class="fa-regular fa-trash-can me-2"></i>Удалить
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </li>
        </ul>

        <div v-else class="empty-products">
            <div class="empty-products-icon"><i class="fa-solid fa-box-open"></i></div>
            <h4>Товары не найдены</h4>
            <p>Попробуйте изменить поисковый запрос или параметры фильтрации.</p>
        </div>

        <div v-if="productStore.items.length > 0" class="pagination-wrap">
            <Pagination :pagination="productStore.pagination" @page-changed="fetchDataByUrl" />
        </div>

        <!-- Модальное окно редактирования -->
        <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content product-modal-content">
                    <div class="modal-header product-modal-header">
                        <div>
                            <span class="modal-eyebrow">КАТАЛОГ</span>
                            <h5 id="editProductModalTitle" class="modal-title">Редактирование товара</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4">
                        <ProductForm v-if="selectedProduct" :initial-data="selectedProduct" @saved="finishEdit" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно просмотра -->
        <div class="modal fade" id="viewProductModal" tabindex="-1" aria-labelledby="viewProductModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content product-modal-content">
                    <div class="modal-header product-modal-header">
                        <div>
                            <span class="modal-eyebrow">КАРТОЧКА ПРОДУКЦИИ</span>
                            <h5 id="viewProductModalTitle" class="modal-title">Информация о товаре</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4">
                        <ProductCard
                            v-if="selectedProduct"
                            :product="selectedProduct"
                            @close="closeEdit"
                            @edit="openEdit"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from 'axios'
import ProductForm from './ProductForm.vue'
import ProductCard from './ProductCard.vue'
import { useProductsStore } from "@/stores/products";
import { useModalStore } from "@/stores/utillites/useConfitmModalStore";
import { useUsersStore } from "@/stores/users";
import debounce from 'lodash.debounce'

export default {
    name: 'ProductList',
    components: { ProductForm, ProductCard },
    props: ["forSelect", "filters"],
    emits: ['select'],
    data() {
        return {
            showProductForm: false,
            search: null,
            field_visible: null,
            productStore: useProductsStore(),
            modalStore: useModalStore(),
            userStore: useUsersStore(),
            products: [],
            selectedProduct: null
        }
    },
    watch: {
        search() {
            this.findProduct()
        },
    },
    computed: {
        user() {
            return this.userStore.self || null
        },
    },
    created() {
        if (this.filters) {
            this.productStore.setFilters({ supplier_id: this.filters.supplier_id || null })
            this.productStore.setSort('id', 'asc')
            this.productStore.fetchFilteredProducts(0, 30)
        } else {
            this.fetchData()
        }
    },
    methods: {
        searchDebounced() {
            debounce(() => this.findProduct(), 300)
        },
        addNewProduct(product) {
            this.$emit("select", product)
        },
        finishEdit() {
            this.fetchData()
            const modal = bootstrap.Modal.getInstance(document.getElementById('editProductModal'))
            if (modal) modal.hide()
        },
        findProduct() {
            this.productStore.setFilters({ name: this.search || '' })
            this.productStore.setSort('id', 'asc')
            this.productStore.fetchFilteredProducts(0, 30)
        },
        async applyFilters(filters) {
            this.field_visible = filters.field_visible
            const size = filters.size || 30
            const page = filters.page || 1
            delete filters.field_visible
            this.productStore.setFilters(filters.filters)
            this.productStore.setSort(filters.sort.field, filters.sort.direction)
            await this.productStore.fetchFilteredProducts(page, size)
        },
        async fetchData(page = 1) {
            await this.productStore.fetchAllByPage(page)
        },
        async fetchDataByUrl(url) {
            await this.productStore.fetchByUrl(url)
        },
        closeEdit() {
            const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewProductModal'))
            if (viewModal) viewModal.hide()
        },
        openEdit(product) {
            this.selectedProduct = product
            const editModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editProductModal'))
            editModal.show()
            this.closeEdit()
        },
        confirmDelete(product) {
            this.selectedProduct = product
            this.modalStore.open(
                `Вы уверены, что хотите удалить <b>${this.selectedProduct?.name}</b>?`,
                async () => {
                    await this.productStore.remove(this.selectedProduct.id)
                    this.modalStore.close()
                },
                () => this.modalStore.close()
            )
        },
        async deleteProduct() {
            await this.productStore.remove(this.selectedProduct.id)
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteProductModal'))
            if (deleteModal) deleteModal.hide()
        },
        openView(product) {
            this.selectedProduct = product
            bootstrap.Modal.getOrCreateInstance(document.getElementById('viewProductModal')).show()
        }
    }
}
</script>

<style scoped>
.product-list-page {
    --product-navy: #172b4d;
    --product-blue: #2463eb;
    --product-red: #e5484d;
    --product-muted: #718096;
    color: var(--product-navy);
    min-width: 0;
}
.product-toolbar,
.filter-panel,
.add-product-panel,
.simple-product-panel,
.product-items,
.empty-products,
.pagination-wrap {
    background: #fff;
    border: 1px solid #e8edf5;
    border-radius: 18px;
    box-shadow: 0 8px 24px rgba(23, 43, 77, .045);
}
.product-toolbar { padding: 20px; margin-bottom: 16px; }
.product-heading { display: flex; align-items: center; gap: 13px; margin-bottom: 18px; }
.product-heading-icon {
    display: grid; place-items: center; flex: 0 0 48px; height: 48px; border-radius: 15px;
    color: var(--product-blue); background: #edf3ff; font-size: 1.25rem;
}
.product-heading-title { margin: 0; font-size: 1.2rem; font-weight: 800; letter-spacing: -.025em; }
.product-heading-subtitle { margin: 4px 0 0; color: var(--product-muted); font-size: .88rem; }
.product-search { display: flex; align-items: center; gap: 10px; position: relative; }
.product-search .form-control { min-width: 0; height: 48px; padding-left: 42px; border-radius: 12px; border-color: #dfe6f0; }
.product-search .form-control:focus { border-color: #8bb0ff; box-shadow: 0 0 0 3px rgba(36, 99, 235, .12); }
.search-icon { position: absolute; z-index: 2; left: 15px; color: #8492a6; }
.search-submit { height: 48px; padding: 0 20px; white-space: nowrap; border-radius: 12px; background: var(--product-blue); border-color: var(--product-blue); font-weight: 700; }
.filter-panel { padding: 16px; margin-bottom: 16px; }
.add-product-panel { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 15px 18px; margin-bottom: 16px; }
.add-product-info { display: flex; align-items: center; gap: 12px; min-width: 0; }
.add-product-icon { display: grid; place-items: center; flex: 0 0 40px; height: 40px; border-radius: 12px; background: #eaf8f0; color: #16834a; font-size: 1.1rem; }
.add-product-title { font-weight: 750; }
.add-product-description { margin-top: 3px; color: var(--product-muted); font-size: .83rem; }
.add-product-switch { display: flex; align-items: center; gap: 8px; white-space: nowrap; }
.add-product-switch .form-check-input { width: 2.5em; height: 1.3em; margin: 0; cursor: pointer; }
.add-product-switch .form-check-input:checked { background-color: #16834a; border-color: #16834a; }
.add-product-switch .form-check-label { cursor: pointer; font-size: .9rem; font-weight: 650; }
.simple-product-panel { padding: 18px; margin-bottom: 16px; border-color: #dce9ff; }
.results-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin: 24px 2px 12px; }
.results-heading h3 { margin: 0; font-size: 1.05rem; font-weight: 800; }
.results-heading p { margin: 4px 0 0; color: var(--product-muted); font-size: .85rem; }
.results-icon { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 12px; background: #f0f4fa; color: #64748b; }
.product-items { list-style: none; padding: 0; margin: 0; overflow: visible; }
.product-item { display: flex; align-items: center; gap: 12px; padding: 16px; border-bottom: 1px solid #edf1f6; }
.product-item:first-child { border-radius: 18px 18px 0 0; }
.product-item:last-child { border-bottom: 0; border-radius: 0 0 18px 18px; }
.product-item:hover { background: #fbfcff; }
.product-main { display: flex; align-items: flex-start; gap: 13px; flex: 1; min-width: 0; padding: 0; text-align: left; color: inherit; border: 0; background: transparent; }
.product-avatar { display: grid; place-items: center; flex: 0 0 42px; height: 42px; border-radius: 13px; background: #eef3ff; color: var(--product-blue); font-size: 1rem; }
.product-info { display: flex; flex-direction: column; gap: 5px; min-width: 0; width: 100%; }
.product-name-row { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.product-name { font-size: .98rem; font-weight: 750; overflow-wrap: anywhere; }
.product-id { padding: 2px 7px; border-radius: 6px; background: #edf3ff; color: var(--product-blue); font-size: .72rem; font-weight: 700; }
.product-description { color: #59677d; font-size: .86rem; line-height: 1.45; }
.product-meta { display: flex; flex-wrap: wrap; gap: 6px 16px; color: var(--product-muted); font-size: .8rem; }
.product-meta span { display: inline-flex; align-items: center; gap: 6px; }
.product-meta i, .product-detail i { color: var(--product-blue); }
.supplier-description { color: #8792a3; font-size: .76rem; font-style: italic; }
.product-detail { color: #4b5b72; font-size: .82rem; font-weight: 650; }
.action-menu-button { display: grid; place-items: center; width: 38px; height: 38px; border: 1px solid #e4eaf3; border-radius: 11px; color: #64748b; background: #fff; }
.action-menu-button:hover, .action-menu-button:focus { color: var(--product-blue); background: #f1f5ff; border-color: #cddcff; }
.product-actions .dropdown-menu { min-width: 190px; padding: 6px; border: 1px solid #e8edf5; border-radius: 12px; box-shadow: 0 12px 30px rgba(23, 43, 77, .12); }
.product-actions .dropdown-item { padding: 9px 10px; border-radius: 8px; font-size: .9rem; }
.empty-products { padding: 40px 20px; text-align: center; }
.empty-products-icon { display: grid; place-items: center; width: 60px; height: 60px; margin: 0 auto 14px; border-radius: 18px; color: #8291a8; background: #f1f4f8; font-size: 1.5rem; }
.empty-products h4 { margin: 0 0 6px; font-size: 1rem; font-weight: 800; }
.empty-products p { max-width: 390px; margin: 0 auto; color: var(--product-muted); font-size: .88rem; }
.pagination-wrap { padding: 12px 16px; margin-top: 14px; overflow-x: auto; }
.product-modal-content { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 70px rgba(18, 36, 67, .22); }
.product-modal-header { align-items: center; padding: 20px 24px; background: #f8faff; border-bottom: 1px solid #e8edf5; }
.modal-eyebrow { display: block; margin-bottom: 5px; color: var(--product-blue); font-size: .68rem; font-weight: 800; letter-spacing: .12em; }
.product-modal-header .modal-title { color: var(--product-navy); font-size: 1.15rem; font-weight: 800; }
.panel-fade-enter-active, .panel-fade-leave-active { transition: opacity .18s ease, transform .18s ease; }
.panel-fade-enter-from, .panel-fade-leave-to { opacity: 0; transform: translateY(-5px); }
@media (max-width: 600px) {
    .product-toolbar { padding: 15px; }
    .product-search { align-items: stretch; flex-wrap: wrap; }
    .product-search .form-control { flex: 1 1 100%; }
    .search-submit { width: 100%; }
    .add-product-panel { align-items: flex-start; flex-direction: column; padding: 14px; }
    .add-product-switch { width: 100%; justify-content: space-between; }
    .product-item { padding: 13px 11px; gap: 7px; }
    .product-avatar { flex-basis: 36px; height: 36px; border-radius: 11px; }
    .product-meta { flex-direction: column; gap: 4px; }
    .action-menu-button { width: 34px; height: 34px; }
    .product-modal-header { padding: 16px; }
}
</style>
