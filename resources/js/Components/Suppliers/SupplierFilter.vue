<template>
    <div class="supplier-filter-toolbar">
        <button type="button" class="toolbar-btn filter-trigger" @click="openFilter">
            <span class="toolbar-icon"><i class="fa-solid fa-sliders"></i></span>
            <span>Фильтр</span>
        </button>

        <div class="dropdown sort-dropdown">
            <button
                type="button"
                class="toolbar-btn sort-trigger"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >
                <span class="toolbar-icon"><i class="fa-solid fa-arrow-down-wide-short"></i></span>
                <span class="sort-label">{{ sortableFields[sort.field].slice(0, 17) }}<span v-if="sortableFields[sort.field].length > 17">…</span></span>
                <i :class="sort.direction === 'asc' ? 'fa-solid fa-arrow-down-short-wide' : 'fa-solid fa-arrow-up-short-wide'" class="sort-direction"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end sort-menu">
                <li class="dropdown-header">Сортировать по</li>
                <li v-for="(name, field) in sortableFields" :key="field">
                    <button
                        type="button"
                        class="dropdown-item"
                        :class="{ active: sort.field === field }"
                        @click="changeSort(field)"
                    >
                        <span>{{ name }}</span>
                        <i v-if="sort.field === field" :class="sort.direction === 'asc' ? 'fa-solid fa-arrow-down' : 'fa-solid fa-arrow-up'"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Модальное окно фильтров -->
    <div class="modal fade supplier-filter-modal" id="supplierFilterModal" tabindex="-1" aria-labelledby="supplierFilterTitle" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <form class="modal-content" @submit.prevent="applyFilters">
                <div class="modal-header filter-modal-header">
                    <div>
                        <div class="modal-eyebrow"><i class="fa-solid fa-sliders me-2"></i>Настройка списка</div>
                        <h5 class="modal-title" id="supplierFilterTitle">Фильтры поставщиков</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body filter-modal-body">
                    <section class="filter-section">
                        <div class="section-heading">
                            <span class="section-icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <div>
                                <h6>Поиск поставщиков</h6>
                                <p>Укажите данные, чтобы сузить список</p>
                            </div>
                        </div>

                        <div class="filter-fields">
                            <div class="form-floating filter-field">
                                <input v-model="filters.name" class="form-control" id="nameInput" placeholder="Название" />
                                <label for="nameInput">Название</label>
                            </div>
                            <div class="form-floating filter-field">
                                <input v-model="filters.description" class="form-control" id="descInput" placeholder="Описание" />
                                <label for="descInput">Описание</label>
                            </div>
                            <div class="form-floating filter-field">
                                <input v-model="filters.address" class="form-control" id="addressInput" placeholder="Адрес" />
                                <label for="addressInput">Адрес</label>
                            </div>
                            <div class="form-floating filter-field">
                                <input v-model="filters.phone" class="form-control" id="phoneInput" placeholder="Телефон" />
                                <label for="phoneInput">Телефон</label>
                            </div>
                            <div class="form-floating filter-field">
                                <input v-model="filters.email" class="form-control" id="emailInput" placeholder="Email" />
                                <label for="emailInput">Email</label>
                            </div>
                            <div class="form-floating filter-field">
                                <input type="number" v-model="filters.percent" class="form-control" id="percentInput" placeholder="Процент" />
                                <label for="percentInput">Процент</label>
                            </div>
                            <div class="form-floating filter-field">
                                <input type="date" v-model="filters.birthday" class="form-control" id="birthdayInput" />
                                <label for="birthdayInput">Дата рождения</label>
                            </div>
                        </div>
                    </section>

                    <section class="filter-section visible-fields-section">
                        <div class="section-heading">
                            <span class="section-icon section-icon-red"><i class="fa-solid fa-table-columns"></i></span>
                            <div>
                                <h6>Отображаемые поля</h6>
                                <p>Выберите, какие данные показывать в списке</p>
                            </div>
                        </div>

                        <div class="visible-fields-grid">
                            <label v-for="(label, field) in sortableFields" :key="field" class="visible-field-option" :for="`switch-${field}`">
                                <span class="visible-field-name">{{ label }}</span>
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    v-model="field_visible[field]"
                                    :id="`switch-${field}`"
                                />
                            </label>
                        </div>
                    </section>
                </div>

                <div class="modal-footer filter-modal-footer">
                    <button type="submit" class="apply-filters-btn">
                        <i class="fa-solid fa-check me-2"></i>Применить фильтры
                    </button>
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
                id: '№',
                name: 'Название',
                description: 'Описание',
                address: 'Адрес',
                phone: 'Телефон',
                email: 'Email',
                percent: 'Процент',
                birthday: 'Дата рождения'
            }
        }
    },
    created() {
        const defaultVisible = ['name', 'id', 'description', 'phone']
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
            bootstrap.Modal.getInstance(document.getElementById('supplierFilterModal'))?.hide()
        }
    }
}
</script>

<style scoped>
.supplier-filter-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.toolbar-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 42px;
    padding: 9px 15px;
    border: 1px solid #dce5f2;
    border-radius: 12px;
    background: #fff;
    color: #17345f;
    font-size: 13px;
    font-weight: 700;
    box-shadow: 0 3px 12px rgba(25, 55, 95, .05);
    transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
}

.toolbar-btn:hover {
    border-color: #8db5ef;
    box-shadow: 0 6px 18px rgba(25, 80, 150, .11);
    transform: translateY(-1px);
}

.toolbar-icon {
    display: inline-grid;
    place-items: center;
    width: 25px;
    height: 25px;
    border-radius: 8px;
    background: #eaf2ff;
    color: #1765c0;
}

.filter-trigger {
    border-color: #1765c0;
    background: #1765c0;
    color: #fff;
}

.filter-trigger .toolbar-icon {
    background: rgba(255, 255, 255, .16);
    color: #fff;
}

.filter-trigger:hover {
    background: #1055a5;
    border-color: #1055a5;
    color: #fff;
}

.sort-dropdown { min-width: 0; }
.sort-trigger { max-width: 100%; }
.sort-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sort-direction { color: #1765c0; }
.sort-menu {
    min-width: 220px;
    padding: 7px;
    border: 1px solid #e4ebf5;
    border-radius: 14px;
    box-shadow: 0 12px 35px rgba(19, 43, 77, .14);
}
.sort-menu .dropdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 8px;
    color: #243b5a;
}
.sort-menu .dropdown-item.active,
.sort-menu .dropdown-item:hover { background: #edf4ff; color: #145eb3; }
.sort-menu .dropdown-item.active i { color: #1765c0; }

.supplier-filter-modal .modal-content {
    border: 0;
    background: #f5f8fc;
}
.filter-modal-header {
    position: sticky;
    top: 0;
    z-index: 2;
    padding: 20px clamp(16px, 4vw, 36px);
    border-bottom: 1px solid #e2eaf5;
    background: #fff;
}
.modal-eyebrow {
    margin-bottom: 5px;
    color: #1765c0;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase;
}
.supplier-filter-modal .modal-title { color: #172f52; font-size: 22px; font-weight: 800; }
.filter-modal-body { width: 100%; max-width: 1050px; margin: 0 auto; padding: 24px clamp(16px, 4vw, 36px); }
.filter-section {
    margin-bottom: 20px;
    padding: clamp(16px, 3vw, 24px);
    border: 1px solid #e4ebf4;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 5px 20px rgba(25, 55, 95, .04);
}
.section-heading { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.section-icon {
    display: grid;
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    place-items: center;
    border-radius: 13px;
    background: #eaf2ff;
    color: #1765c0;
    font-size: 17px;
}
.section-icon-red { background: #fff0f0; color: #d83b47; }
.section-heading h6 { margin: 0 0 4px; color: #193455; font-size: 16px; font-weight: 800; }
.section-heading p { margin: 0; color: #7b8ba2; font-size: 12px; }
.filter-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.filter-field { min-width: 0; }
.filter-field .form-control { border-color: #dce5f0; border-radius: 11px; }
.filter-field .form-control:focus { border-color: #7eafea; box-shadow: 0 0 0 .2rem rgba(23, 101, 192, .1); }
.visible-fields-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
.visible-field-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    min-height: 48px;
    padding: 10px 13px;
    border: 1px solid #e5ebf3;
    border-radius: 11px;
    background: #fbfcfe;
    color: #304764;
    cursor: pointer;
    transition: border-color .2s ease, background .2s ease;
}
.visible-field-option:has(.form-check-input:checked) { border-color: #b8d2f5; background: #f0f6ff; }
.visible-field-name { font-size: 13px; font-weight: 600; }
.visible-field-option .form-check-input { flex: 0 0 auto; margin: 0; cursor: pointer; }
.visible-field-option .form-check-input:checked { background-color: #1765c0; border-color: #1765c0; }
.filter-modal-footer {
    position: sticky;
    bottom: 0;
    z-index: 2;
    padding: 14px clamp(16px, 4vw, 36px);
    border-top: 1px solid #e2eaf5;
    background: rgba(255, 255, 255, .96);
    backdrop-filter: blur(12px);
}
.apply-filters-btn {
    width: 100%;
    max-width: 1050px;
    min-height: 50px;
    margin: 0 auto;
    border: 0;
    border-radius: 13px;
    background: linear-gradient(120deg, #1765c0, #267fe0);
    color: #fff;
    font-size: 14px;
    font-weight: 800;
    box-shadow: 0 7px 18px rgba(23, 101, 192, .2);
    transition: transform .2s ease, box-shadow .2s ease;
}
.apply-filters-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(23, 101, 192, .27); }

@media (max-width: 575.98px) {
    .supplier-filter-toolbar { gap: 8px; }
    .toolbar-btn { min-height: 40px; padding: 8px 11px; }
    .filter-fields, .visible-fields-grid { grid-template-columns: minmax(0, 1fr); }
    .filter-modal-body { padding: 14px 12px; }
    .filter-section { padding: 15px; border-radius: 14px; }
    .section-heading { align-items: flex-start; }
    .supplier-filter-modal .modal-title { font-size: 19px; }
}
</style>
