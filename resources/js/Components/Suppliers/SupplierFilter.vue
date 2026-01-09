<template>
    <div class="d-flex mb-2">
        <!-- Кнопка вызова модалки -->
        <button
            style="font-size:12px;"
            class="btn btn-secondary" @click="openFilter">Фильтр</button>

        <!-- Dropdown сортировки -->
        <div class="dropdown d-inline-block ms-2">
            <button
                style="font-size:12px;"
                class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                {{ sortableFields[sort.field].slice(0, 17) }}
                <span v-if="sortableFields[sort.field].length>17">...</span>
                (<span v-if="sort.direction==='asc'"><i class="fa-solid fa-arrow-down"></i></span>
                <span v-if="sort.direction==='desc'"><i class="fa-solid fa-arrow-up"></i></span>)
            </button>
            <ul class="dropdown-menu">
                <li v-for="(name, field) in sortableFields" :key="field">
                    <a class="dropdown-item" @click="changeSort(field)">
                        {{ name }}
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Модалка фильтрации -->
    <div class="modal fade" id="supplierFilterModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <form class="modal-content" @submit.prevent="applyFilters">

                    <div class="modal-header">
                        <h5 class="modal-title">Фильтры поставщиков</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Имя -->
                        <div class="form-floating mb-2">
                            <input v-model="filters.name" class="form-control" id="nameInput" placeholder="Название"/>
                            <label for="nameInput">Название</label>
                        </div>

                        <!-- Описание -->
                        <div class="form-floating mb-2">
                            <input v-model="filters.description" class="form-control" id="descInput" placeholder="Описание"/>
                            <label for="descInput">Описание</label>
                        </div>

                        <!-- Адрес -->
                        <div class="form-floating mb-2">
                            <input v-model="filters.address" class="form-control" id="addressInput" placeholder="Адрес"/>
                            <label for="addressInput">Адрес</label>
                        </div>

                        <!-- Телефон -->
                        <div class="form-floating mb-2">
                            <input v-model="filters.phone" class="form-control" id="phoneInput" placeholder="Телефон"/>
                            <label for="phoneInput">Телефон</label>
                        </div>

                        <!-- Email -->
                        <div class="form-floating mb-2">
                            <input v-model="filters.email" class="form-control" id="emailInput" placeholder="Email"/>
                            <label for="emailInput">Email</label>
                        </div>

                        <!-- Процент -->
                        <div class="form-floating mb-2">
                            <input type="number" v-model="filters.percent" class="form-control" id="percentInput" placeholder="Процент"/>
                            <label for="percentInput">Процент</label>
                        </div>

                        <!-- Дата рождения -->
                        <div class="form-floating mb-2">
                            <input type="date" v-model="filters.birthday" class="form-control" id="birthdayInput"/>
                            <label for="birthdayInput">Дата рождения</label>
                        </div>

                        <h6 class="mt-3">Отображаемые поля</h6>
                        <div v-for="(label, field) in sortableFields" :key="field" class="form-check form-switch mb-2">
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
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary w-100 p-3">Применить</button>
                    </div>

            </form>
        </div>
    </div>
</template>

<script>
export default {
    name: 'SupplierFilter',
    data() {
        return {
            field_visible: [],
            filters: {
                name: '',
                description: '',
                address: '',
                phone: '',
                email: '',
                percent: '',
                birthday: ''
            },
            sort: {
                field: 'id',
                direction: 'asc'
            },
            sortableFields: {
                id: "ID",
                name: "Название",
                description: "Описание",
                address: "Адрес",
                phone: "Телефон",
                email: "Email",
                percent: "Процент",
                birthday: "Дата рождения",
            }
        }
    },
    created() {
        // по умолчанию показываем name и phone
        const defaultVisible = ['name', 'id','description','phone']
        for (const field in this.sortableFields) {
            this.field_visible[field] = defaultVisible.includes(field)
        }
    },
    methods: {
        openFilter() {
            new bootstrap.Modal(document.getElementById('supplierFilterModal')).show()
        },
        changeSort(field) {
            if (this.sort.field === field) {
                this.sort.direction = this.sort.direction === 'asc' ? 'desc' : 'asc'
            } else {
                this.sort.field = field
                this.sort.direction = 'asc'
            }
            this.$emit('apply-filters', { filters: this.filters, sort: this.sort })
        },
        applyFilters() {
            const payload = {
                filters: this.filters,
                sort: this.sort,
                field_visible: this.field_visible
            }
            this.$emit('apply-filters', payload)
            bootstrap.Modal.getInstance(document.getElementById('supplierFilterModal')).hide()



        }
    }
}
</script>
