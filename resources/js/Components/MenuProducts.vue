
<template>
    <div class="products-menu">
        <!-- Заголовок -->
        <div class="menu-header">
            <div class="header-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>

            <div class="header-content">
                <h2>Товары и каталог</h2>
                <p>Просмотр, группировка и управление товарным ассортиментом</p>
            </div>

            <div class="header-count">
                <i class="fa-solid fa-layer-group"></i>
                <span>{{ visibleMenuItems.length }} раздела</span>
            </div>
        </div>

        <!-- Карточки разделов -->
        <div class="products-grid">
            <button
                v-for="item in visibleMenuItems"
                :key="item.name"
                type="button"
                class="product-card"
                :class="`accent-${item.accent}`"
                @click="selectMenu(item.name)"
            >
                <div class="card-top">
                    <div class="product-icon">
                        <i :class="item.icon"></i>
                    </div>

                    <span class="card-arrow">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </span>
                </div>

                <div class="card-content">
                    <span class="card-label">{{ item.label }}</span>
                    <h3>{{ item.title }}</h3>
                    <p>{{ item.description }}</p>
                </div>

                <div class="card-footer">
                    <span>Открыть раздел</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </button>
        </div>

        <!-- Экспорт -->
        <section v-if="(user?.role || 0) >= 3" class="export-panel">
            <div class="export-heading">
                <div class="export-icon">
                    <i class="fa-solid fa-file-export"></i>
                </div>

                <div>
                    <h3>Экспорт товарного каталога</h3>
                    <p>Скачайте таблицу в нужном представлении</p>
                </div>
            </div>

            <div class="dropdown export-dropdown">
                <button
                    class="export-toggle dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    :disabled="jobStore.loading"
                >
                    <span class="export-toggle-icon">
                        <i
                            :class="jobStore.loading
                                ? 'fa-solid fa-spinner fa-spin'
                                : 'fa-solid fa-file-excel'"
                        ></i>
                    </span>

                    <span>
                        {{ jobStore.loading ? 'Подготовка файла...' : 'Скачать таблицу с товарами' }}
                    </span>

                    <i class="fa-solid fa-chevron-down toggle-chevron"></i>
                </button>

                <ul class="dropdown-menu export-menu">
                    <li>
                        <button
                            type="button"
                            class="dropdown-item"
                            :disabled="jobStore.loading"
                            @click="exportProducts(0)"
                        >
                            <span class="option-icon blue">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </span>
                            <span>
                                <strong>Все товары</strong>
                                <small>Общий список каталога</small>
                            </span>
                        </button>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="dropdown-item"
                            :disabled="jobStore.loading"
                            @click="exportProducts(1)"
                        >
                            <span class="option-icon red">
                                <i class="fa-solid fa-layer-group"></i>
                            </span>
                            <span>
                                <strong>По категориям</strong>
                                <small>Группировка по категориям</small>
                            </span>
                        </button>
                    </li>

                    <li>
                        <button
                            type="button"
                            class="dropdown-item"
                            :disabled="jobStore.loading"
                            @click="exportProducts(2)"
                        >
                            <span class="option-icon blue">
                                <i class="fa-solid fa-truck-fast"></i>
                            </span>
                            <span>
                                <strong>По поставщикам</strong>
                                <small>Группировка по поставщикам</small>
                            </span>
                        </button>
                    </li>
                </ul>
            </div>
        </section>
    </div>
</template>

<script>
import { useBaseExports } from "@/stores/baseExports";
import { useUsersStore } from "@/stores/users";

export default {
    name: "ProductsMenu",

    data() {
        return {
            userStore: useUsersStore(),
            jobStore: useBaseExports(),

            menuItems: [
                {
                    name: "products",
                    title: "Общий список товаров",
                    description: "Просмотр всего товарного ассортимента",
                    icon: "fa-solid fa-boxes-stacked",
                    label: "Каталог",
                    accent: "blue",
                },
                {
                    name: "products-by-supplier",
                    title: "Товары по поставщикам",
                    description: "Просмотр ассортимента в разрезе поставщиков",
                    icon: "fa-solid fa-truck-fast",
                    label: "Поставки",
                    accent: "red",
                },
                {
                    name: "products-by-categories",
                    title: "Товары по категориям",
                    description: "Поиск и просмотр товаров по категориям",
                    icon: "fa-solid fa-layer-group",
                    label: "Структура",
                    accent: "blue",
                },
                {
                    name: "categories",
                    title: "Категории товаров",
                    description: "Управление категориями товарного каталога",
                    icon: "fa-solid fa-tags",
                    label: "Настройки",
                    accent: "red",
                },
                {
                    name: "import-products",
                    title: "Импорт прайса",
                    description: "Загрузка товаров и категорий из файла",
                    icon: "fa-solid fa-file-import",
                    label: "Импорт",
                    accent: "red",
                    minRole: 3,
                },
            ],
        };
    },

    computed: {
        user() {
            return this.userStore.self || null;
        },

        visibleMenuItems() {
            return this.menuItems.filter(
                item => !item.minRole || (this.user?.role || 0) >= item.minRole
            );
        },
    },

    methods: {
        selectMenu(name) {
            this.$emit("select", name);
        },

        exportProducts(type) {
            if (this.jobStore.loading) return;

            this.jobStore.exportProducts(type);
        },
    },
};
</script>

<style scoped>
.products-menu {
    --menu-blue: #2457d6;
    --menu-blue-dark: #173b91;
    --menu-red: #df3548;
    --menu-text: #17233b;
    --menu-muted: #778399;
    --menu-border: #e5eaf2;

    width: 100%;
    color: var(--menu-text);
}

/* Заголовок */

.menu-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
}

.header-icon {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 58px;
    height: 58px;
    color: white;
    background: linear-gradient(135deg, var(--menu-blue), var(--menu-blue-dark));
    border-radius: 17px;
    box-shadow: 0 7px 18px rgba(36, 87, 214, 0.18);
}

.header-icon i {
    font-size: 25px;
}

.header-content {
    flex: 1;
    min-width: 0;
}

.header-content h2 {
    margin: 0 0 5px;
    font-size: clamp(20px, 2.5vw, 26px);
    font-weight: 750;
    letter-spacing: -0.5px;
}

.header-content p {
    margin: 0;
    color: var(--menu-muted);
    font-size: 14px;
    line-height: 1.5;
}

.header-count {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 13px;
    color: var(--menu-blue);
    background: #edf3ff;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 650;
    white-space: nowrap;
}

/* Сетка карточек */

.products-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.product-card {
    --card-accent: var(--menu-blue);
    --card-accent-light: #edf3ff;

    position: relative;
    display: flex;
    flex-direction: column;
    min-width: 0;
    min-height: 230px;
    padding: 22px;
    overflow: hidden;
    text-align: left;
    color: var(--menu-text);
    background: #fff;
    border: 1px solid var(--menu-border);
    border-radius: 17px;
    cursor: pointer;

    box-shadow: 0 3px 12px rgba(23, 35, 59, 0.035);
    transition:
        transform 0.22s ease,
        box-shadow 0.22s ease,
        border-color 0.22s ease;
}

.product-card::before {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    content: "";
    background: var(--card-accent);
    opacity: 0.85;
}

.product-card.accent-red {
    --card-accent: var(--menu-red);
    --card-accent-light: #fff0f1;
}

.product-card:hover {
    border-color: color-mix(in srgb, var(--card-accent) 38%, white);
    box-shadow: 0 12px 28px rgba(23, 35, 59, 0.09);
    transform: translateY(-4px);
}

.product-card:focus-visible {
    outline: 3px solid rgba(36, 87, 214, 0.25);
    outline-offset: 3px;
}

.card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}

.product-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    color: var(--card-accent);
    background: var(--card-accent-light);
    border-radius: 15px;
    transition: transform 0.22s ease;
}

.product-icon i {
    font-size: 24px;
}

.product-card:hover .product-icon {
    transform: scale(1.06);
}

.card-arrow {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: #9aa6b8;
    background: #f7f9fc;
    border-radius: 9px;
    font-size: 13px;
    transition: color 0.2s ease, background 0.2s ease;
}

.product-card:hover .card-arrow {
    color: white;
    background: var(--card-accent);
}

.card-content {
    flex: 1;
}

.card-label {
    display: inline-block;
    margin-bottom: 8px;
    color: var(--card-accent);
    font-size: 11px;
    font-weight: 750;
    letter-spacing: 0.07em;
    text-transform: uppercase;
}

.card-content h3 {
    margin: 0 0 8px;
    font-size: 17px;
    font-weight: 750;
    line-height: 1.4;
}

.card-content p {
    margin: 0;
    color: var(--menu-muted);
    font-size: 13px;
    line-height: 1.6;
}

.card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-top: 22px;
    padding-top: 14px;
    color: var(--card-accent);
    border-top: 1px solid #eef1f6;
    font-size: 12px;
    font-weight: 700;
}

.card-footer i {
    transition: transform 0.2s ease;
}

.product-card:hover .card-footer i {
    transform: translateX(4px);
}

/* Экспорт */

.export-panel {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-top: 24px;
    padding: 22px;
    background: #fff;
    border: 1px solid var(--menu-border);
    border-radius: 17px;
    box-shadow: 0 3px 12px rgba(23, 35, 59, 0.035);
}

.export-heading {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}

.export-icon {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    color: var(--menu-blue);
    background: #edf3ff;
    border-radius: 13px;
    font-size: 21px;
}

.export-heading h3 {
    margin: 0 0 5px;
    font-size: 16px;
    font-weight: 750;
}

.export-heading p {
    margin: 0;
    color: var(--menu-muted);
    font-size: 13px;
    line-height: 1.5;
}

.export-dropdown {
    flex-shrink: 0;
    width: min(100%, 320px);
}

.export-toggle {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    width: 100%;
    min-height: 48px;
    padding: 10px 14px;
    color: #fff;
    background: var(--menu-blue);
    border: 1px solid var(--menu-blue);
    border-radius: 11px;
    font-size: 13px;
    font-weight: 650;
    text-align: left;
    transition: background 0.2s ease, box-shadow 0.2s ease;
}

.export-toggle:hover:not(:disabled) {
    background: var(--menu-blue-dark);
    box-shadow: 0 5px 14px rgba(36, 87, 214, 0.18);
}

.export-toggle:disabled {
    opacity: 0.65;
    cursor: wait;
}

.export-toggle-icon {
    font-size: 17px;
}

.toggle-chevron {
    margin-left: auto;
    font-size: 12px;
}

.export-menu {
    width: 100%;
    margin-top: 8px !important;
    padding: 7px;
    border: 1px solid var(--menu-border);
    border-radius: 13px;
    box-shadow: 0 12px 30px rgba(23, 35, 59, 0.12);
}

.export-menu .dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 10px;
    white-space: normal;
    border-radius: 9px;
}

.export-menu .dropdown-item:hover:not(:disabled) {
    background: #f5f8ff;
}

.export-menu .dropdown-item:disabled {
    opacity: 0.5;
}

.option-icon {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    font-size: 16px;
}

.option-icon.blue {
    color: var(--menu-blue);
    background: #edf3ff;
}

.option-icon.red {
    color: var(--menu-red);
    background: #fff0f1;
}

.export-menu strong,
.export-menu small {
    display: block;
}

.export-menu strong {
    color: var(--menu-text);
    font-size: 13px;
    font-weight: 700;
}

.export-menu small {
    margin-top: 3px;
    color: var(--menu-muted);
    font-size: 11px;
}

/* Адаптивность */

@media (max-width: 1199.98px) {
    .products-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 767.98px) {
    .menu-header {
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
    }

    .header-icon i {
        font-size: 21px;
    }

    .header-count {
        margin-left: 60px;
    }

    .products-grid {
        gap: 12px;
    }

    .product-card {
        min-height: 220px;
        padding: 17px;
    }

    .export-panel {
        align-items: stretch;
        flex-direction: column;
        gap: 18px;
        padding: 18px;
    }

    .export-dropdown {
        width: 100%;
    }
}

@media (max-width: 479.98px) {
    .products-grid {
        grid-template-columns: 1fr;
    }

    .product-card {
        min-height: auto;
    }

    .card-footer {
        margin-top: 18px;
    }

    .header-count {
        margin-left: 0;
    }

    .header-content {
        flex-basis: calc(100% - 62px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .product-card,
    .product-icon,
    .card-arrow,
    .card-footer i,
    .export-toggle {
        transition: none;
    }
}
</style>
