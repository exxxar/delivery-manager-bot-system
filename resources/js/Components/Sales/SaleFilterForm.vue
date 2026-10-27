<script setup>
import AgentList from "@/Components/Agents/AgentList.vue";
import CustomerList from "@/Components/Customers/CustomerList.vue";
import SupplierList from "@/Components/Suppliers/SupplierList.vue";
import ProductList from "@/Components/Products/ProductList.vue";
import ProductCategoryList from "@/Components/ProductCategory/ProductCategoryList.vue";
</script>
<template>
    <div class="sale-filter-page">
        <div class="sale-filter-heading">
            <div class="sale-filter-heading-icon">
                <i class="fa-solid fa-sliders"></i>
            </div>
            <div class="sale-filter-heading-copy">
                <span class="sale-filter-eyebrow">Мойпроцент · Управление</span>
                <h2>Фильтрация сделок</h2>
                <p>Настройте условия поиска и сортировку заявок</p>
            </div>
        </div>

        <!--    <h5 class="modal-title">
                {{ tab === 'filter' ? 'Фильтрация доставок' : titleMap[tab] }}
            </h5>-->

        <!-- Кнопка фильтра -->
        <div class="sale-filter-toolbar mb-3">
            <button
                class="btn sale-filter-primary" @click="openFilter">
                <i class="fa-solid fa-filter me-2"></i>Настроить фильтры
            </button>

            <!-- Dropdown сортировки -->
            <div class="dropdown d-inline-block ms-2">
                <button
                    class="btn sale-filter-sort-button" type="button" data-bs-toggle="dropdown">
                    {{ sortableFields[salesStore.sort.field].slice(0, 17) }}
                    <span v-if="sortableFields[salesStore.sort.field].length>17">...</span>
                    (<span v-if="salesStore.sort.direction==='asc'"><i class="fa-solid fa-arrow-down"></i></span>
                    <span v-if="salesStore.sort.direction==='desc'"><i class="fa-solid fa-arrow-up"></i></span>)
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" @click="changeSort('id')">№</a></li>
                    <li><a class="dropdown-item" @click="changeSort('title')">Название</a></li>
                    <li><a class="dropdown-item" @click="changeSort('status')">Статус</a></li>
                    <li><a class="dropdown-item" @click="changeSort('sale_date')">Дата продажи</a></li>
                    <li><a class="dropdown-item" @click="changeSort('total_price')">Сумма</a></li>
                </ul>
            </div>
        </div>


        <!-- Модалка фильтрации -->
        <div class="modal fade sale-filter-modal" id="saleFilterModal" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <form @submit.prevent="applyFilters" class="modal-content sale-filter-modal-content">
                    <div class="modal-header sale-filter-modal-header">
                        <div><span class="sale-filter-eyebrow">Параметры поиска</span><h5 class="modal-title">Фильтры сделок</h5></div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body sale-filter-modal-body">
                        <template v-if="tab === 'filter'">
                            <!-- Статус -->
                            <div class="form-floating mb-2">
                                <select
                                    v-model="filters.status"
                                    class="form-select" id="statusSelect">
                                    <option value="">Все</option>
                                    <option value="pending">В ожидании</option>
                                    <!--                                <option value="assigned">Назначено</option>-->
                                    <option value="completed">Завершено</option>
                                    <option value="delivered">Доставляется</option>
                                    <option value="rejected">Отклонено</option>
                                </select>
                                <label for="statusSelect">Статус</label>
                            </div>

                            <template v-if="role>=3">
                                <div
                                    class="form-check form-switch mb-2">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        v-model="onlyMyTask"
                                        :id="`only-my-tasks`"
                                    />
                                    <label class="form-check-label" :for="`only-my-tasks`">
                                        Только мои сделки
                                    </label>
                                </div>
                            </template>

                            <!-- Номер сделки -->
                            <div class="form-floating mb-2">
                                <input class="form-control"
                                       v-model="filters.number"
                                       id="titleInput" placeholder="Номер сделки"/>
                                <label for="titleInput">Номер сделки</label>
                            </div>


                            <!-- Название -->
                            <div class="form-floating mb-2">
                                <input class="form-control"
                                       v-model="filters.title"
                                       id="titleInput" placeholder="Название"/>
                                <label for="titleInput">Название</label>
                            </div>

                            <!-- Описание -->
                            <div class="form-floating mb-2">
                                <input class="form-control"
                                       v-model="filters.description"
                                       id="descInput" placeholder="Описание"/>
                                <label for="descInput">Описание</label>
                            </div>

                            <div class="form-floating mb-2">
                                <select
                                    id="itemsPerPage"
                                    class="form-select"
                                    v-model="filters.payment_type"
                                >
                                    <option :value="null">Любой тип оплаты</option>
                                    <option value="0">Наличная оплата</option>
                                    <option value="1">Безналичная оплата</option>
                                </select>
                                <label for="itemsPerPage">Тип оплаты</label>
                            </div>



                            <!-- Статус -->
                            <div class="form-floating mb-2">
                                <select
                                    v-model="filters.date_type"
                                    class="form-select" id="dateTypeSelect">
                                    <option :value="null">Не имеет значения</option>
                                    <option value="2">Дата оплаты заказа</option>
                                    <!--                                <option value="1">Дата исполнения задачи</option>-->
                                    <option value="0">Дата создания задачи</option>
                                    <!--                                <option value="3">Дата доставки по договору</option>-->
                                    <option value="4">Дата доставки фактическая</option>
                                </select>
                                <label for="dateTypeSelect">Тип даты</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="date"
                                       v-model="filters.date_from"
                                       class="form-control" id="dateFromInput"/>
                                <label for="dateFromInput">Дата от</label>
                            </div>

                            <div class="form-floating mb-2">
                                <input type="date"
                                       v-model="filters.date_to"
                                       class="form-control" id="dateToInput"/>
                                <label for="dateToInput">Дата до</label>
                            </div>

                            <template v-if="role>=3">

                                <div class="input-group mb-2">
                                    <div class="form-floating flex-grow-1">
                                        <input
                                            type="text" disabled
                                            @change="selectedAgent = null"
                                            :value="selectedAgent?.name || 'не указан'"
                                            class="form-control" id="agentInput" placeholder="Агент"/>
                                        <label for="agentInput">Администратор</label>
                                    </div>
                                    <button
                                        type="button"
                                        v-if="selectedAgent"
                                        class="btn btn-outline-light text-primary"
                                        @click="cancelAgent"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-light text-primary"
                                        @click="tab='agent'"
                                    >
                                        Выбрать
                                    </button>
                                </div>


                                <div class="input-group mb-2">
                                    <div class="form-floating flex-grow-1">
                                        <input class="form-control"
                                               type="text" disabled
                                               @change="selectedCustomer = null"
                                               :value="selectedCustomer?.name||'не указан'"
                                               id="customerInput" placeholder="Клиент"/>
                                        <label for="customerInput">Клиент</label>
                                    </div>
                                    <button
                                        type="button"
                                        v-if="selectedCustomer"
                                        class="btn btn-outline-light text-primary"
                                        @click="cancelCustomer"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-light text-primary"
                                        @click="tab='customer'"
                                    >
                                        Выбрать
                                    </button>
                                </div>
                            </template>
                            <!-- Поставщик -->
                            <div class="input-group mb-2">
                                <div class="form-floating flex-grow-1">
                                    <input class="form-control"
                                           type="text" disabled
                                           @change="selectedSupplier = null"
                                           :value="selectedSupplier?.name||'не указан'"
                                           id="supplierInput" placeholder="Поставщик"/>
                                    <label for="supplierInput">Поставщик</label>
                                </div>
                                <button
                                    type="button"
                                    v-if="selectedSupplier"
                                    class="btn btn-outline-light text-primary"
                                    @click="cancelSupplier"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-light text-primary"
                                    @click="tab='supplier'"
                                >
                                    Выбрать
                                </button>
                            </div>

                            <div class="input-group mb-2">
                                <div class="form-floating flex-grow-1">
                                    <input
                                        type="text" disabled
                                        @change="selectedProduct = null"
                                        :value="selectedProduct?.name||'не указан'"
                                        class="form-control" id="agentInput" placeholder="Агент"/>
                                    <label for="agentInput">Продукт</label>
                                </div>
                                <button
                                    type="button"
                                    v-if="selectedProduct"
                                    class="btn btn-outline-light text-primary"
                                    @click="cancelProduct"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-light text-primary"
                                    @click="tab='product'"
                                >
                                    Выбрать
                                </button>
                            </div>

                            <div class="input-group mb-2">
                                <div class="form-floating flex-grow-1">
                                    <input
                                        type="text" disabled
                                        @change="selectedProductCategory = null"
                                        :value="selectedProductCategory?.name||'не указана'"
                                        class="form-control" id="agentInput" placeholder="Агент"/>
                                    <label for="agentInput">Категория товара</label>
                                </div>
                                <button
                                    type="button"
                                    v-if="selectedProductCategory"
                                    class="btn btn-outline-light text-primary"
                                    @click="cancelProductCategory"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-light text-primary"
                                    @click="tab='product_category'"
                                >
                                    Выбрать
                                </button>
                            </div>

                            <template v-if="role>=3">
                                <h6>Отображаемые поля</h6>
                                <div v-for="(label, field) in sortableFields" :key="field"
                                     class="form-check form-switch mb-2">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        v-model="field_visible[field]"
                                        :id="`switch-${field}`"
                                    />
                                    <label class="form-check-label" :for="`switch-${field}`">
                                        {{ label }}
                                    </label>
                                </div>
                            </template>

                            <div class="form-floating mb-2">
                                <select
                                    id="itemsPerPage"
                                    class="form-select"
                                    v-model="size"
                                >
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="500">500</option>
                                    <option value="1000">1000</option>
                                    <option value="5000">5000</option>
                                </select>
                                <label for="itemsPerPage">Элементов на странице</label>
                            </div>

                            <!-- Поле ввода номера страницы -->
                            <div class="form-floating mb-3">
                                <input
                                    type="number"
                                    id="pageNumber"
                                    class="form-control"
                                    v-model="page"
                                    min="0"
                                />
                                <label for="pageNumber">Номер страницы</label>
                            </div>

                            <button type="button"
                                    @click="clearFilters"
                                    class="btn btn-outline-light text-secondary mb-2 w-100 p-3">Очистить фильтры
                            </button>


                        </template>
                        <!-- Вкладка выбора агента -->
                        <template v-if="tab === 'agent'">
                            <button
                                class="btn btn-secondary my-3" @click="tab = 'filter'">Назад
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-secondary p-3 w-100 mb-2"
                                v-if="selectedAgent!=null"
                                @click="selectAgent(null)">Отменить выбор
                            </button>

                            <AgentList :for-select="true" v-on:select="selectAgent"></AgentList>
                            <!-- Заглушка: таблица агентов -->

                        </template>

                        <template v-if="tab === 'product'">
                            <button
                                class="btn btn-secondary my-3" @click="tab = 'filter'">Назад
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-secondary p-3 w-100 mb-2"
                                v-if="selectedProduct!=null"
                                @click="selectProduct(null)">Отменить выбор
                            </button>

                            <ProductList :for-select="true" v-on:select="selectProduct"></ProductList>
                            <!-- Заглушка: таблица агентов -->

                        </template>

                        <template v-if="tab === 'product_category'">
                            <button
                                class="btn btn-secondary my-3" @click="tab = 'filter'">Назад
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-secondary p-3 w-100 mb-2"
                                v-if="selectedProductCategory!=null"
                                @click="selectProductCategory(null)">Отменить выбор
                            </button>

                            <ProductCategoryList :for-select="true"
                                                 v-on:select="selectProductCategory"></ProductCategoryList>
                            <!-- Заглушка: таблица агентов -->

                        </template>

                        <!-- Вкладка выбора клиента -->
                        <template v-if="tab === 'customer'">
                            <button
                                class="btn btn-secondary my-3" @click="tab = 'filter'">Назад
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-secondary p-3 w-100 mb-2"
                                v-if="selectedCustomer!=null"
                                @click="selectCustomer(null)">Отменить выбор
                            </button>

                            <CustomerList :for-select="true" v-on:select="selectCustomer"></CustomerList>
                        </template>

                        <!-- Вкладка выбора поставщика -->
                        <template v-if="tab === 'supplier'">
                            <button
                                class="btn btn-secondary my-3" @click="tab = 'filter'">Назад
                            </button>

                            <button
                                type="button"
                                class="btn btn-outline-secondary p-3 w-100 mb-2"
                                v-if="selectedSupplier!=null"
                                @click="selectSupplier(null)">Отменить выбор
                            </button>

                            <SupplierList :for-select="true" v-on:select="selectSupplier"></SupplierList>

                        </template>

                    </div>
                    <div class="modal-footer sale-filter-modal-footer">
                        <button type="submit" class="btn sale-filter-apply w-100 p-3"><i class="fa-solid fa-check me-2"></i>Применить фильтры</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>

import {useSalesStore} from '@/stores/sales'
import {useUsersStore} from "@/stores/users";

export default {
    name: 'SaleFilterForm',
    data() {
        return {
            page:0,
            size: 20,
            onlyMyTask: false,
            needCompletedTask: false,
            userStore: useUsersStore(),
            salesStore: useSalesStore(),
            tab: 'filter',
            titleMap: {
                agent: 'Выбор младшего админа',
                customer: 'Выбор клиента',
                supplier: 'Выбор поставщика'
            },
            selectedAgent: null,
            selectedProduct: null,
            selectedProductCategory: null,
            selectedSupplier: null,
            selectedCustomer: null,
            field_visible: [],
            sortableFields: {
                id: "№",
                title: 'Название',
                description: 'Описание',
                status: 'Статус',
                due_date: "Дата назначения встречи",
                sale_date: 'Дата продажи',
                payment_type: 'Тип оплаты',
                actual_delivery_date: 'Фактическая дата доставки',
                quantity: 'Доставляемое число товара',
                total_price: 'Сумма заказа',
                agent_id: 'Администратор',
                customer_id: 'Клиент',
                supplier_id: 'Поставщик',
                created_by_id: 'Создатель заявки',
                product_id: 'Продукт',
            },
            filters: {
                number: '',
                title: '',
                payment_type: null,
                description: '',
                status: '',
                date_type: null,
                date_from: '',
                date_to: '',
                agent_id: '',
                customer_id: '',
                supplier_id: '',
                created_by_id: '',
                product_id: '',
                product_category_id: '',
                quantity: '',
                total_price: ''
            }
        }
    },
    computed: {
        role() {
            return this.userStore.self?.role ?? 0
        }
    },
    mounted() {
        let tmpVisibleFields = ['title', 'description', 'status', 'due_date','agent_id']
        for (const field in this.sortableFields) {
            this.field_visible[field] = tmpVisibleFields.indexOf(field) !== -1
        }

        this.tab = 'filter'
    },
    methods: {
        applyFilters() {
            const filter = {
                ...this.filters,
                size: this.size,
                page: this.page,
                only_my_tasks: this.onlyMyTask,
                field_visible: this.field_visible
            }

            const filterModal = bootstrap.Modal.getInstance(document.getElementById('saleFilterModal'))
            if (filterModal)
                filterModal.hide()

            this.$emit('apply-filters', filter)
        },
        cancelAgent() {
            this.selectedAgent = null
            this.$nextTick(() => {
                this.filters.agent_id =  null
            })
        },
        selectAgent(agent) {
            this.selectedAgent = null
            this.$nextTick(() => {
                this.selectedAgent = agent
                this.filters.agent_id = agent?.id || null
                this.tab = 'filter'
            })

        },
        openFilter() {
            new bootstrap.Modal(document.getElementById('saleFilterModal')).show()
        },
        cancelCustomer() {
            this.selectedCustomer = null
            this.$nextTick(() => {
                this.filters.customer_id =  null
            })
        },
        selectCustomer(customer) {

            this.selectedCustomer = null
            this.$nextTick(() => {
                this.selectedCustomer = customer
                this.filters.customer_id = customer?.id || null
                this.tab = 'filter'
            })
        },
        cancelProductCategory() {
            this.selectedProductCategory = null
            this.$nextTick(() => {
                this.filters.product_category_id =  null
            })
        },
        selectProductCategory(category){
            console.log(category)
            this.selectedProductCategory = null
            this.$nextTick(() => {
                this.selectedProductCategory = category
                this.filters.product_category_id = category?.id || null
                this.tab = 'filter'
            })
        },
        cancelProduct() {
            this.selectedProduct = null
            this.$nextTick(() => {
                this.filters.product_id =  null
            })
        },
        selectProduct(product){
            this.selectedProduct = null
            this.$nextTick(() => {
                this.selectedProduct = product
                this.filters.product_id = product?.id || null
                this.tab = 'filter'
            })
        },
        cancelSupplier() {
            this.selectedSupplier = null
            this.$nextTick(() => {
                this.filters.supplier_id =  null
            })
        },
        selectSupplier(supplier) {
            this.selectedSupplier = null
            this.$nextTick(() => {
                this.selectedSupplier = supplier
                this.filters.supplier_id = supplier?.id || null
                this.tab = 'filter'
            })

        },
        changeSort(field) {
            const current = this.salesStore.sort
            const direction = current.field === field && current.direction === 'asc' ? 'desc' : 'asc'
            this.salesStore.setSort(field, direction)
            this.salesStore.fetchFiltered()
        },
        clearFilters() {
            const filters = {
                number: '',
                title: '',
                description: '',
                status: '',
                date_type: null,
                date_from: '',
                date_to: '',
                agent_id: '',
                customer_id: '',
                product_id: '',
                product_category_id: '',
                supplier_id: '',
                created_by_id: '',
                quantity: '',
                total_price: ''
            }

            this.size = 20
            this.page = 0
            this.onlyMyTask = false
            let tmpVisibleFields = ['title', 'description', 'status', 'due_date','agent_id']
            for (const field in this.sortableFields) {
                this.field_visible[field] = tmpVisibleFields.indexOf(field) !== -1
            }

            this.filters = filters
        }
    }
}
</script>


<style>
.sale-filter-page {
    --mp-blue: #2457d6;
    --mp-blue-dark: #173b91;
    --mp-red: #df3548;
    --mp-text: #17233b;
    --mp-muted: #778399;
    --mp-border: #e5eaf2;
    color: var(--mp-text);
    width: 100%;
}
.sale-filter-heading {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 22px;
}
.sale-filter-heading-icon {
    display: grid;
    place-items: center;
    flex: 0 0 54px;
    width: 54px;
    height: 54px;
    border-radius: 17px;
    color: #fff;
    font-size: 22px;
    background: linear-gradient(135deg, var(--mp-blue), var(--mp-blue-dark));
    box-shadow: 0 8px 20px rgba(36, 87, 214, .2);
}
.sale-filter-heading-copy { min-width: 0; }
.sale-filter-eyebrow {
    display: block;
    color: var(--mp-muted);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.sale-filter-heading h2 {
    margin: 0;
    font-size: clamp(20px, 3vw, 27px);
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: -.025em;
}
.sale-filter-heading p {
    color: var(--mp-muted);
    margin: 5px 0 0;
    font-size: 13px;
}
.sale-filter-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px;
    border: 1px solid var(--mp-border);
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 5px 18px rgba(23, 35, 59, .045);
}
.sale-filter-toolbar .dropdown { margin-left: 0 !important; }
.sale-filter-primary, .sale-filter-apply {
    border: 0;
    border-radius: 11px;
    padding: 11px 16px;
    color: #fff;
    font-weight: 700;
    background: linear-gradient(135deg, var(--mp-blue), var(--mp-blue-dark));
    box-shadow: 0 5px 12px rgba(36, 87, 214, .15);
    transition: transform .18s ease, box-shadow .18s ease;
}
.sale-filter-primary:hover, .sale-filter-apply:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 8px 17px rgba(36, 87, 214, .22);
}
.sale-filter-sort-button {
    border: 1px solid var(--mp-border);
    border-radius: 11px;
    padding: 10px 13px;
    background: #f8faff;
    color: var(--mp-text);
    font-size: 13px;
    font-weight: 650;
}
.sale-filter-sort-button:hover, .sale-filter-sort-button:focus {
    border-color: #b8c9f5;
    color: var(--mp-blue);
    background: #f0f5ff;
}
.sale-filter-page .dropdown-menu {
    border: 1px solid var(--mp-border);
    border-radius: 12px;
    padding: 6px;
    box-shadow: 0 12px 30px rgba(23, 35, 59, .13);
}
.sale-filter-page .dropdown-item {
    border-radius: 8px;
    padding: 9px 12px;
    cursor: pointer;
    font-size: 13px;
}
.sale-filter-page .dropdown-item:hover { background: #eef3ff; color: var(--mp-blue); }
.sale-filter-modal .modal-dialog { max-width: 100%; margin: 0; }
.sale-filter-modal-content {
    min-height: 100vh;
    border: 0;
    border-radius: 0;
    background: #f7f9fd;
}
.sale-filter-modal-header {
    position: sticky;
    top: 0;
    z-index: 5;
    padding: 18px clamp(16px, 4vw, 36px);
    border-bottom: 1px solid var(--mp-border);
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(12px);
}
.sale-filter-modal-header .modal-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--mp-text);
}
.sale-filter-modal-body {
    width: 100%;
    max-width: 920px;
    margin: 0 auto;
    padding: 24px clamp(16px, 4vw, 36px);
}
.sale-filter-modal-body .form-floating { margin-bottom: 14px !important; }
.sale-filter-modal-body .form-control,
.sale-filter-modal-body .form-select {
    min-height: 56px;
    border: 1px solid #dfe6f1;
    border-radius: 12px;
    color: var(--mp-text);
    background-color: #fff;
    box-shadow: none;
}
.sale-filter-modal-body textarea.form-control { min-height: 120px; }
.sale-filter-modal-body .form-control:focus,
.sale-filter-modal-body .form-select:focus {
    border-color: #8ba9f0;
    box-shadow: 0 0 0 3px rgba(36, 87, 214, .1);
}
.sale-filter-modal-body .form-floating > label { color: var(--mp-muted); }
.sale-filter-modal-body .input-group {
    align-items: stretch;
    gap: 7px;
}
.sale-filter-modal-body .input-group .form-floating { min-width: 0; }
.sale-filter-modal-body .input-group > .btn {
    align-self: stretch;
    border-radius: 11px !important;
    border: 1px solid #dce5f7;
    background: #edf3ff;
    color: var(--mp-blue) !important;
    font-weight: 700;
    padding: 0 16px;
}
.sale-filter-modal-body .form-check {
    padding: 12px 14px 12px 42px;
    border: 1px solid var(--mp-border);
    border-radius: 11px;
    background: #fff;
}
.sale-filter-modal-body .form-check-input { cursor: pointer; }
.sale-filter-modal-body .form-check-input:checked {
    border-color: var(--mp-blue);
    background-color: var(--mp-blue);
}
.sale-filter-modal-body .form-check-label { cursor: pointer; }
.sale-filter-modal-body .alert {
    border: 1px solid #dbe7ff;
    border-radius: 12px;
    background: #edf4ff;
    color: #24457f;
}
.sale-filter-modal-body h6 {
    margin: 22px 0 12px;
    color: var(--mp-text);
    font-size: 14px;
    font-weight: 800;
}
.sale-filter-modal-body .btn-secondary,
.sale-filter-modal-body .btn-outline-secondary {
    border-radius: 10px;
}
.sale-filter-modal-footer {
    position: sticky;
    bottom: 0;
    z-index: 5;
    padding: 12px clamp(16px, 4vw, 36px);
    border-top: 1px solid var(--mp-border);
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(12px);
}
.sale-filter-modal-footer .sale-filter-apply { max-width: 920px; margin: 0 auto; }
@media (max-width: 575.98px) {
    .sale-filter-heading { align-items: flex-start; gap: 12px; margin-bottom: 16px; }
    .sale-filter-heading-icon { width: 46px; height: 46px; flex-basis: 46px; border-radius: 14px; font-size: 19px; }
    .sale-filter-heading p { font-size: 12px; }
    .sale-filter-toolbar { padding: 10px; }
    .sale-filter-primary { flex: 1 1 100%; }
    .sale-filter-toolbar .dropdown { width: 100%; }
    .sale-filter-sort-button { width: 100%; text-align: left; }
    .sale-filter-modal-body { padding-top: 16px; padding-bottom: 22px; }
    .sale-filter-modal-body .input-group > .btn { padding: 0 10px; font-size: 12px; }
    .sale-filter-modal-header .modal-title { font-size: 19px; }
}
</style>
