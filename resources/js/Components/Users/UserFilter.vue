
<template>
    <div class="user-filter">
        <!-- Панель управления -->
        <div class="user-filter__toolbar">
            <button
                type="button"
                class="user-filter__button user-filter__button--primary"
                @click="openFilter"
            >
                <i class="fa-solid fa-sliders"></i>
                <span>Фильтры</span>
                <span
                    v-if="activeFiltersCount"
                    class="user-filter__counter"
                >
                    {{ activeFiltersCount }}
                </span>
            </button>

            <div class="dropdown user-filter__sort">
                <button
                    type="button"
                    class="user-filter__button user-filter__button--secondary"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="fa-solid fa-arrow-down-wide-short"></i>
                    <span class="user-filter__sort-label">
                        {{ sortableFields[sort.field] || 'Сортировка' }}
                    </span>
                    <i
                        :class="sort.direction === 'asc'
                            ? 'fa-solid fa-arrow-down-short-wide'
                            : 'fa-solid fa-arrow-up-short-wide'"
                        class="user-filter__sort-direction"
                    ></i>
                    <i class="fa-solid fa-chevron-down user-filter__chevron"></i>
                </button>

                <ul class="dropdown-menu user-filter__dropdown">
                    <li
                        v-for="(name, field) in sortableFields"
                        :key="field"
                    >
                        <button
                            type="button"
                            class="dropdown-item user-filter__dropdown-item"
                            :class="{ 'is-active': sort.field === field }"
                            @click="changeSort(field)"
                        >
                            <span>{{ name }}</span>
                            <i
                                v-if="sort.field === field"
                                :class="sort.direction === 'asc'
                                    ? 'fa-solid fa-arrow-down-short-wide'
                                    : 'fa-solid fa-arrow-up-short-wide'"
                            ></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Активные фильтры -->
        <div
            v-if="activeFilterLabels.length"
            class="user-filter__active"
        >
            <span class="user-filter__active-caption">
                <i class="fa-solid fa-filter"></i>
                Активные:
            </span>

            <button
                v-for="item in activeFilterLabels"
                :key="item.key"
                type="button"
                class="user-filter__chip"
                @click="clearFilter(item.key)"
                :title="`Убрать фильтр: ${item.label}`"
            >
                {{ item.label }}
                <i class="fa-solid fa-xmark"></i>
            </button>

            <button
                type="button"
                class="user-filter__clear"
                @click="clearFilters"
            >
                Сбросить всё
            </button>
        </div>

        <!-- Модальное окно -->
        <Teleport to="body">
            <div
                v-if="isModalOpen"
                class="user-filter-modal"
                @click.self="closeFilter"
                @keydown.esc="closeFilter"
            >
                <section
                    class="user-filter-modal__dialog"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="user-filter-title"
                >
                    <header class="user-filter-modal__header">
                        <div class="user-filter-modal__heading">
                            <span class="user-filter-modal__icon">
                                <i class="fa-solid fa-sliders"></i>
                            </span>

                            <div>
                                <h2 id="user-filter-title">
                                    Фильтры пользователей
                                </h2>
                                <p>
                                    Настройте выборку и отображение данных
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="user-filter-modal__close"
                            aria-label="Закрыть"
                            @click="closeFilter"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </header>

                    <form @submit.prevent="applyFilters">
                        <div class="user-filter-modal__body">
                            <!-- Поиск -->
                            <section class="user-filter-section">
                                <div class="user-filter-section__heading">
                                    <span class="user-filter-section__icon">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </span>
                                    <div>
                                        <h3>Поиск пользователя</h3>
                                        <p>По имени, контактам или идентификатору</p>
                                    </div>
                                </div>

                                <div class="user-filter-grid">
                                    <div class="user-filter-field">
                                        <label for="uf-name">Имя</label>
                                        <div class="user-filter-input">
                                            <i class="fa-solid fa-user"></i>
                                            <input
                                                id="uf-name"
                                                v-model="filters.name"
                                                type="text"
                                                placeholder="Введите имя"
                                            />
                                        </div>
                                    </div>

                                    <div class="user-filter-field">
                                        <label for="uf-fio">ФИО из Telegram</label>
                                        <div class="user-filter-input">
                                            <i class="fa-brands fa-telegram"></i>
                                            <input
                                                id="uf-fio"
                                                v-model="filters.fio_from_telegram"
                                                type="text"
                                                placeholder="Введите ФИО"
                                            />
                                        </div>
                                    </div>

                                    <div class="user-filter-field">
                                        <label for="uf-email">Электронная почта</label>
                                        <div class="user-filter-input">
                                            <i class="fa-solid fa-envelope"></i>
                                            <input
                                                id="uf-email"
                                                v-model="filters.email"
                                                type="text"
                                                placeholder="example@mail.ru"
                                            />
                                        </div>
                                    </div>

                                    <div class="user-filter-field">
                                        <label for="uf-tg">Telegram Chat ID</label>
                                        <div class="user-filter-input">
                                            <i class="fa-solid fa-comment-dots"></i>
                                            <input
                                                id="uf-tg"
                                                v-model="filters.telegram_chat_id"
                                                type="text"
                                                placeholder="ID чата"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Роль и параметры -->
                            <section class="user-filter-section">
                                <div class="user-filter-section__heading">
                                    <span class="user-filter-section__icon">
                                        <i class="fa-solid fa-user-shield"></i>
                                    </span>
                                    <div>
                                        <h3>Роль и статус</h3>
                                        <p>Дополнительные условия отбора</p>
                                    </div>
                                </div>

                                <div class="user-filter-grid">
                                    <div class="user-filter-field">
                                        <label for="uf-role">Роль пользователя</label>
                                        <div class="user-filter-select">
                                            <select id="uf-role" v-model="filters.role">
                                                <option value="">Все роли</option>
                                                <option value="0">Пользователь</option>
                                                <option value="1">Администратор</option>
                                                <option value="2">Поставщик</option>
                                                <option value="3">Старший администратор</option>
                                                <option value="4">Суперадмин</option>
                                            </select>
                                            <i class="fa-solid fa-chevron-down"></i>
                                        </div>
                                    </div>

                                    <div class="user-filter-field">
                                        <label for="uf-percent">Процент</label>
                                        <div class="user-filter-input">
                                            <i class="fa-solid fa-percent"></i>
                                            <input
                                                id="uf-percent"
                                                v-model="filters.percent"
                                                type="number"
                                                min="0"
                                                max="100"
                                                step="any"
                                                placeholder="Любой процент"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="user-filter-statuses">
                                    <label
                                        v-for="item in statusOptions"
                                        :key="item.key"
                                        class="user-filter-status"
                                        :class="{ 'is-checked': filters[item.key] }"
                                    >
                                        <input
                                            v-model="filters[item.key]"
                                            type="checkbox"
                                        />

                                        <span class="user-filter-status__check">
                                            <i class="fa-solid fa-check"></i>
                                        </span>

                                        <span class="user-filter-status__text">
                                            <strong>{{ item.label }}</strong>
                                            <small>{{ item.description }}</small>
                                        </span>
                                    </label>
                                </div>
                            </section>

                            <!-- Отображаемые столбцы -->
                            <section class="user-filter-section">
                                <div class="user-filter-section__heading">
                                    <span class="user-filter-section__icon">
                                        <i class="fa-solid fa-table-columns"></i>
                                    </span>
                                    <div>
                                        <h3>Отображаемые поля</h3>
                                        <p>Выберите столбцы для таблицы пользователей</p>
                                    </div>
                                </div>

                                <div class="user-filter-columns">
                                    <label
                                        v-for="(label, field) in sortableFields"
                                        :key="field"
                                        class="user-filter-column"
                                        :class="{ 'is-checked': field_visible[field] }"
                                    >
                                        <input
                                            v-model="field_visible[field]"
                                            type="checkbox"
                                        />
                                        <span class="user-filter-column__check">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                        <span>{{ label }}</span>
                                    </label>
                                </div>
                            </section>
                        </div>

                        <footer class="user-filter-modal__footer">
                            <button
                                type="button"
                                class="user-filter-modal__reset"
                                @click="resetDraft"
                            >
                                <i class="fa-solid fa-rotate-left"></i>
                                Сбросить
                            </button>

                            <div class="user-filter-modal__footer-actions">
                                <button
                                    type="button"
                                    class="user-filter-modal__cancel"
                                    @click="closeFilter"
                                >
                                    Отмена
                                </button>
                                <button
                                    type="submit"
                                    class="user-filter-modal__apply"
                                >
                                    <i class="fa-solid fa-check"></i>
                                    Применить фильтры
                                </button>
                            </div>
                        </footer>
                    </form>
                </section>
            </div>
        </Teleport>
    </div>
</template>

<script>
const DEFAULT_FILTERS = () => ({
    name: '',
    fio_from_telegram: '',
    email: '',
    telegram_chat_id: '',
    role: '',
    percent: '',
    is_work: false,
    email_verified: false,
    blocked: false
});

const DEFAULT_VISIBLE_FIELDS = () => ({
    id: false,
    name: true,
    fio_from_telegram: false,
    email: true,
    telegram_chat_id: false,
    role: false,
    percent: false,
    is_work: false,
    email_verified_at: false,
    blocked_at: false,
    created_at: false,
    updated_at: false
});

export default {
    name: 'UserFilter',

    emits: ['apply-filters'],

    data() {
        return {
            isModalOpen: false,

            filters: DEFAULT_FILTERS(),

            sort: {
                field: 'id',
                direction: 'asc'
            },

            field_visible: DEFAULT_VISIBLE_FIELDS(),

            sortableFields: {
                id: '№',
                name: 'Имя',
                fio_from_telegram: 'ФИО из Telegram',
                email: 'Электронная почта',
                telegram_chat_id: 'ID чата Telegram',
                role: 'Роль',
                percent: 'Процент',
                is_work: 'Работает (да/нет)',
                email_verified_at: 'Дата подтверждения email',
                blocked_at: 'Дата блокировки',
                created_at: 'Дата создания',
                updated_at: 'Дата обновления данных'
            },

            statusOptions: [
                {
                    key: 'is_work',
                    label: 'Работает',
                    description: 'Только работающие пользователи'
                },
                {
                    key: 'email_verified',
                    label: 'Email подтверждён',
                    description: 'Есть подтверждение почты'
                },
                {
                    key: 'blocked',
                    label: 'Заблокирован',
                    description: 'Учётная запись заблокирована'
                }
            ]
        };
    },

    computed: {
        activeFiltersCount() {
            return Object.entries(this.filters).filter(([key, value]) => {
                if (['is_work', 'email_verified', 'blocked'].includes(key)) {
                    return value === true;
                }

                return value !== '' && value !== null && value !== undefined;
            }).length;
        },

        activeFilterLabels() {
            const labels = [];
            const textFields = [
                ['name', 'Имя'],
                ['fio_from_telegram', 'ФИО Telegram'],
                ['email', 'Email'],
                ['telegram_chat_id', 'Chat ID'],
                ['percent', 'Процент']
            ];

            textFields.forEach(([key, label]) => {
                if (this.filters[key] !== '') {
                    labels.push({
                        key,
                        label: `${label}: ${this.filters[key]}`
                    });
                }
            });

            if (this.filters.role !== '') {
                const roles = {
                    '0': 'Пользователь',
                    '1': 'Администратор',
                    '2': 'Поставщик',
                    '3': 'Старший администратор',
                    '4': 'Суперадмин'
                };

                labels.push({
                    key: 'role',
                    label: `Роль: ${roles[this.filters.role] || this.filters.role}`
                });
            }

            this.statusOptions.forEach(item => {
                if (this.filters[item.key]) {
                    labels.push({
                        key: item.key,
                        label: item.label
                    });
                }
            });

            return labels;
        }
    },

    methods: {
        openFilter() {
            this.isModalOpen = true;
        },

        closeFilter() {
            this.isModalOpen = false;
        },

        changeSort(field) {
            if (this.sort.field === field) {
                this.sort.direction =
                    this.sort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort.field = field;
                this.sort.direction = 'asc';
            }

            this.$emit('apply-filters', {
                filters: { ...this.filters },
                sort: { ...this.sort },
                field_visible: { ...this.field_visible }
            });
        },

        applyFilters() {
            this.$emit('apply-filters', {
                filters: { ...this.filters },
                sort: { ...this.sort },
                field_visible: { ...this.field_visible }
            });

            this.closeFilter();
        },

        clearFilter(key) {
            if (['is_work', 'email_verified', 'blocked'].includes(key)) {
                this.filters[key] = false;
            } else {
                this.filters[key] = '';
            }

            this.applyFilters();
        },

        clearFilters() {
            this.filters = DEFAULT_FILTERS();
            this.applyFilters();
        },

        resetDraft() {
            this.filters = DEFAULT_FILTERS();
            this.field_visible = DEFAULT_VISIBLE_FIELDS();
        }
    }
};
</script>

<style>
/* =========================================================
   USER FILTER — TOOLBAR
   ========================================================= */

.user-filter {
    --uf-navy: #172b4d;
    --uf-blue: #246bfd;
    --uf-blue-dark: #1557e8;
    --uf-muted: #718096;
    --uf-border: #e2e8f0;
    --uf-bg: #f6f8fc;
    color: var(--uf-navy);
    font-family: inherit;
}

.user-filter,
.user-filter * {
    box-sizing: border-box;
}

.user-filter__toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}

.user-filter__button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    min-height: 40px;
    margin: 0;
    padding: 9px 14px;
    border: 1px solid transparent;
    border-radius: 11px;
    font-family: inherit;
    font-size: 0.84rem;
    font-weight: 700;
    line-height: 1.3;
    cursor: pointer;
    transition:
        background 0.18s ease,
        border-color 0.18s ease,
        color 0.18s ease,
        transform 0.18s ease;
}

.user-filter__button:focus-visible {
    outline: 3px solid rgba(36, 107, 253, 0.22);
    outline-offset: 2px;
}

.user-filter__button--primary {
    color: #fff;
    background: var(--uf-blue);
    border-color: var(--uf-blue);
    box-shadow: 0 4px 10px rgba(36, 107, 253, 0.15);
}

.user-filter__button--primary:hover {
    color: #fff;
    background: var(--uf-blue-dark);
    border-color: var(--uf-blue-dark);
    transform: translateY(-1px);
}

.user-filter__counter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 21px;
    height: 21px;
    padding: 0 5px;
    color: var(--uf-blue);
    background: #fff;
    border-radius: 7px;
    font-size: 0.72rem;
    font-weight: 800;
}

.user-filter__button--secondary {
    color: #34445b;
    background: #fff;
    border-color: #dce3ed;
}

.user-filter__button--secondary:hover,
.user-filter__sort.show > .user-filter__button--secondary {
    color: var(--uf-blue);
    background: #f3f7ff;
    border-color: #b9d0ff;
}

.user-filter__sort {
    position: relative;
}

.user-filter__sort-label {
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-filter__sort-direction {
    color: var(--uf-blue);
    font-size: 0.9rem;
}

.user-filter__chevron {
    margin-left: 2px;
    color: #94a3b8;
    font-size: 0.7rem;
}

.user-filter__dropdown {
    min-width: 230px;
    max-height: 340px;
    overflow-y: auto;
    padding: 6px;
    border: 1px solid #e5eaf2;
    border-radius: 13px;
    box-shadow: 0 14px 40px rgba(23, 43, 77, 0.14);
}

.user-filter__dropdown-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 10px;
    border-radius: 8px;
    color: #34445b;
    font-size: 0.84rem;
}

.user-filter__dropdown-item:hover {
    color: var(--uf-blue);
    background: #f1f5ff;
}

.user-filter__dropdown-item.is-active {
    color: var(--uf-blue);
    background: #eaf1ff;
    font-weight: 700;
}

/* Активные фильтры */

.user-filter__active {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 7px;
    margin-top: 12px;
}

.user-filter__active-caption {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-right: 3px;
    color: var(--uf-muted);
    font-size: 0.78rem;
    font-weight: 650;
}

.user-filter__active-caption i {
    color: var(--uf-blue);
}

.user-filter__chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    max-width: 100%;
    padding: 6px 9px;
    color: #1d4ed8;
    background: #edf3ff;
    border: 1px solid #dce8ff;
    border-radius: 8px;
    font-family: inherit;
    font-size: 0.75rem;
    line-height: 1.3;
    overflow-wrap: anywhere;
    cursor: pointer;
}

.user-filter__chip:hover {
    color: #b4232d;
    background: #fff0f1;
    border-color: #ffd9dc;
}

.user-filter__clear {
    padding: 5px 7px;
    color: #b4232d;
    background: transparent;
    border: 0;
    font-family: inherit;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
}

.user-filter__clear:hover {
    text-decoration: underline;
}

/* =========================================================
   MODAL
   ========================================================= */

.user-filter-modal {
    position: fixed;
    z-index: 20050;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    overflow-y: auto;
    background: rgba(12, 24, 43, 0.65);
    -webkit-backdrop-filter: blur(5px);
    backdrop-filter: blur(5px);
    animation: uf-fade-in 0.18s ease both;
}

.user-filter-modal,
.user-filter-modal * {
    box-sizing: border-box;
}

.user-filter-modal__dialog {
    width: 100%;
    max-width: 690px;
    max-height: calc(100dvh - 40px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    color: var(--uf-navy, #172b4d);
    background: #fff;
    border: 1px solid rgba(230, 235, 242, 0.9);
    border-radius: 22px;
    box-shadow: 0 24px 80px rgba(8, 20, 40, 0.28);
    animation: uf-dialog-in 0.2s ease both;
}

.user-filter-modal__header {
    display: flex;
    flex-shrink: 0;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    padding: 23px 26px;
    background: linear-gradient(135deg, #f0f5ff 0%, #fff 100%);
    border-bottom: 1px solid #edf1f7;
}

.user-filter-modal__heading {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.user-filter-modal__icon {
    display: inline-flex;
    flex: 0 0 46px;
    align-items: center;
    justify-content: center;
    width: 46px;
    height: 46px;
    color: #246bfd;
    background: #e1ebff;
    border: 1px solid #d1e0ff;
    border-radius: 14px;
    font-size: 19px;
}

.user-filter-modal__heading h2 {
    margin: 0 0 5px;
    color: #172b4d;
    font-size: 1.16rem;
    font-weight: 800;
    line-height: 1.35;
    letter-spacing: -0.02em;
}

.user-filter-modal__heading p {
    margin: 0;
    color: #718096;
    font-size: 0.82rem;
    font-weight: 400;
    line-height: 1.45;
}

.user-filter-modal__close {
    display: inline-flex;
    flex: 0 0 36px;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    padding: 0;
    color: #64748b;
    background: #fff;
    border: 1px solid #e5eaf2;
    border-radius: 11px;
    font-size: 1rem;
    cursor: pointer;
}

.user-filter-modal__close:hover {
    color: #e5484d;
    background: #fff3f3;
}

.user-filter-modal form {
    display: flex;
    flex: 1 1 auto;
    flex-direction: column;
    min-height: 0;
    overflow: hidden;
}

.user-filter-modal__body {
    flex: 1 1 auto;
    min-height: 0;
    padding: 22px 26px;
    overflow-y: auto;
    overscroll-behavior: contain;
}

/* Секции */

.user-filter-section + .user-filter-section {
    margin-top: 24px;
    padding-top: 22px;
    border-top: 1px solid #edf0f5;
}

.user-filter-section__heading {
    display: flex;
    align-items: center;
    gap: 11px;
    margin-bottom: 16px;
}

.user-filter-section__icon {
    display: inline-flex;
    flex: 0 0 36px;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    color: #246bfd;
    background: #edf3ff;
    border-radius: 11px;
    font-size: 0.95rem;
}

.user-filter-section__heading h3 {
    margin: 0 0 3px;
    color: #172b4d;
    font-size: 0.91rem;
    font-weight: 800;
    line-height: 1.35;
}

.user-filter-section__heading p {
    margin: 0;
    color: #8793a5;
    font-size: 0.76rem;
    line-height: 1.4;
}

/* Поля ввода */

.user-filter-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
}

.user-filter-field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 0;
}

.user-filter-field > label {
    display: block;
    margin: 0;
    color: #34445b;
    font-size: 0.79rem;
    font-weight: 750;
    line-height: 1.4;
}

.user-filter-input {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 0;
    min-height: 44px;
    padding: 0 11px;
    background: #fff;
    border: 1px solid #dce3ed;
    border-radius: 11px;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.user-filter-input:focus-within,
.user-filter-select:focus-within {
    border-color: #78a4ff;
    box-shadow: 0 0 0 3px rgba(36, 107, 253, 0.1);
}

.user-filter-input > i {
    flex: 0 0 16px;
    color: #8a9bb5;
    font-size: 0.84rem;
    text-align: center;
}

.user-filter-input input {
    display: block;
    flex: 1 1 auto;
    width: 0;
    min-width: 0;
    min-height: 42px;
    margin: 0;
    padding: 9px 0;
    color: #172b4d;
    background: transparent;
    border: 0;
    border-radius: 0;
    outline: none;
    box-shadow: none;
    font-family: inherit;
    font-size: 0.84rem;
    font-weight: 400;
    line-height: 1.4;
}

.user-filter-input input:focus {
    outline: none;
    border: 0;
    box-shadow: none;
}

.user-filter-input input::placeholder {
    color: #a0aabc;
    opacity: 1;
}

.user-filter-select {
    position: relative;
    display: flex;
    align-items: center;
    min-height: 44px;
    background: #fff;
    border: 1px solid #dce3ed;
    border-radius: 11px;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.user-filter-select select {
    display: block;
    width: 100%;
    min-width: 0;
    min-height: 42px;
    margin: 0;
    padding: 9px 34px 9px 11px;
    color: #172b4d;
    background: transparent;
    border: 0;
    border-radius: 11px;
    outline: none;
    box-shadow: none;
    appearance: none;
    font-family: inherit;
    font-size: 0.84rem;
    line-height: 1.4;
    cursor: pointer;
}

.user-filter-select select:focus {
    outline: none;
    box-shadow: none;
}

.user-filter-select > i {
    position: absolute;
    right: 12px;
    color: #8a9bb5;
    font-size: 0.7rem;
    pointer-events: none;
}

/* Статусы */

.user-filter-statuses {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 16px;
}

.user-filter-status {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 9px;
    min-width: 0;
    padding: 12px 10px;
    background: #f8faff;
    border: 1px solid #e4eaf4;
    border-radius: 12px;
    cursor: pointer;
    transition: background 0.18s ease, border-color 0.18s ease;
}

.user-filter-status:hover {
    border-color: #c6d8ff;
}

.user-filter-status.is-checked {
    background: #edf3ff;
    border-color: #b9d0ff;
}

.user-filter-status input,
.user-filter-column input {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    clip-path: inset(50%);
}

.user-filter-status__check,
.user-filter-column__check {
    display: inline-flex;
    flex: 0 0 18px;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    margin-top: 1px;
    color: transparent;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    font-size: 0.65rem;
    transition: background 0.18s ease, border-color 0.18s ease;
}

.user-filter-status.is-checked .user-filter-status__check,
.user-filter-column.is-checked .user-filter-column__check {
    color: #fff;
    background: #246bfd;
    border-color: #246bfd;
}

.user-filter-status input:focus-visible + .user-filter-status__check,
.user-filter-column input:focus-visible + .user-filter-column__check {
    outline: 3px solid rgba(36, 107, 253, 0.22);
    outline-offset: 2px;
}

.user-filter-status__text {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.user-filter-status__text strong {
    color: #34445b;
    font-size: 0.77rem;
    font-weight: 750;
    line-height: 1.35;
}

.user-filter-status__text small {
    color: #8793a5;
    font-size: 0.69rem;
    font-weight: 400;
    line-height: 1.4;
}

/* Выбор отображаемых столбцов */

.user-filter-columns {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 9px;
}

.user-filter-column {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 9px;
    min-width: 0;
    padding: 10px;
    color: #475569;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.76rem;
    font-weight: 600;
    line-height: 1.4;
    overflow-wrap: anywhere;
    cursor: pointer;
    transition: background 0.18s ease, border-color 0.18s ease;
}

.user-filter-column:hover {
    border-color: #b9d0ff;
}

.user-filter-column.is-checked {
    color: #1d4ed8;
    background: #f0f5ff;
    border-color: #c9d9ff;
}

/* Нижняя панель */

.user-filter-modal__footer {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 26px;
    background: #fff;
    border-top: 1px solid #edf0f5;
}

.user-filter-modal__footer-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 9px;
}

.user-filter-modal__reset,
.user-filter-modal__cancel,
.user-filter-modal__apply {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 42px;
    margin: 0;
    padding: 10px 13px;
    border: 1px solid transparent;
    border-radius: 10px;
    font-family: inherit;
    font-size: 0.81rem;
    font-weight: 750;
    line-height: 1.35;
    cursor: pointer;
    transition: background 0.18s ease, border-color 0.18s ease;
}

.user-filter-modal__reset {
    color: #b4232d;
    background: #fff;
    border-color: #ffd9dc;
}

.user-filter-modal__reset:hover {
    background: #fff0f1;
}

.user-filter-modal__cancel {
    color: #475569;
    background: #f7f9fc;
    border-color: #e2e8f0;
}

.user-filter-modal__cancel:hover {
    background: #edf1f7;
}

.user-filter-modal__apply {
    color: #fff;
    background: #246bfd;
    border-color: #246bfd;
    box-shadow: 0 4px 10px rgba(36, 107, 253, 0.15);
}

.user-filter-modal__apply:hover {
    background: #1557e8;
    border-color: #1557e8;
}

.user-filter-modal__close:focus-visible,
.user-filter-modal__reset:focus-visible,
.user-filter-modal__cancel:focus-visible,
.user-filter-modal__apply:focus-visible {
    outline: 3px solid rgba(36, 107, 253, 0.25);
    outline-offset: 2px;
}

/* Анимация */

@keyframes uf-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes uf-dialog-in {
    from {
        opacity: 0;
        transform: translateY(8px) scale(0.99);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Адаптивность */

@media (max-width: 700px) {
    .user-filter-modal {
        padding: 12px;
    }

    .user-filter-modal__dialog {
        max-height: calc(100dvh - 24px);
    }

    .user-filter-statuses {
        grid-template-columns: 1fr;
    }

    .user-filter-status {
        align-items: center;
    }

    .user-filter-columns {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 520px) {
    .user-filter__toolbar {
        gap: 8px;
    }

    .user-filter__button {
        min-height: 39px;
        padding: 8px 11px;
        font-size: 0.8rem;
    }

    .user-filter__sort {
        min-width: 0;
        flex: 1 1 auto;
    }

    .user-filter__sort > .user-filter__button {
        width: 100%;
        justify-content: flex-start;
    }

    .user-filter__sort-label {
        flex: 1;
        min-width: 0;
        max-width: none;
    }

    .user-filter-modal {
        align-items: flex-end;
        padding: 0;
    }

    .user-filter-modal__dialog {
        width: 100%;
        max-width: none;
        max-height: 94dvh;
        border-right: 0;
        border-bottom: 0;
        border-left: 0;
        border-radius: 22px 22px 0 0;
        padding-bottom: env(safe-area-inset-bottom, 0px);
    }

    .user-filter-modal__header {
        padding: 18px 18px 16px;
    }

    .user-filter-modal__heading {
        gap: 10px;
    }

    .user-filter-modal__icon {
        flex-basis: 40px;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        font-size: 16px;
    }

    .user-filter-modal__heading h2 {
        font-size: 1rem;
    }

    .user-filter-modal__heading p {
        font-size: 0.74rem;
    }

    .user-filter-modal__body {
        padding: 18px;
    }

    .user-filter-grid {
        grid-template-columns: 1fr;
        gap: 13px;
    }

    .user-filter-section + .user-filter-section {
        margin-top: 20px;
        padding-top: 18px;
    }

    .user-filter-columns {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .user-filter-column {
        padding: 9px;
        font-size: 0.73rem;
    }

    .user-filter-modal__footer {
        flex-wrap: wrap;
        padding: 13px 18px;
    }

    .user-filter-modal__reset {
        flex: 1 1 100%;
    }

    .user-filter-modal__footer-actions {
        width: 100%;
    }

    .user-filter-modal__cancel,
    .user-filter-modal__apply {
        flex: 1 1 0;
    }
}

@media (max-width: 360px) {
    .user-filter-columns {
        grid-template-columns: 1fr;
    }

    .user-filter-modal__footer-actions {
        flex-direction: column-reverse;
    }

    .user-filter-modal__cancel,
    .user-filter-modal__apply {
        width: 100%;
        flex: auto;
    }
}

@media (prefers-reduced-motion: reduce) {
    .user-filter-modal,
    .user-filter-modal__dialog {
        animation: none;
    }

    .user-filter *,
    .user-filter-modal * {
        transition: none !important;
    }
}
</style>
