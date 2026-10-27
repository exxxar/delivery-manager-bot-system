<template>
    <main class="exports-page">
        <div class="exports-page__container">
            <div class="exports-page__back">
                <BackBtn />
            </div>

            <header class="exports-header">
                <div class="exports-header__icon">
                    <i class="fa-solid fa-file-export"></i>
                </div>
                <div class="exports-header__copy">
                    <p class="exports-header__eyebrow">Мойпроцент · Инструменты администратора</p>
                    <h1>Экспорт данных</h1>
                    <p>Выберите раздел, чтобы выгрузить данные системы в файл.</p>
                </div>
                <div class="exports-header__badge">
                    <i class="fa-solid fa-database"></i>
                    <span>9 разделов</span>
                </div>
            </header>

            <section class="exports-section">
                <div class="exports-section__heading">
                    <div>
                        <h2>Доступные выгрузки</h2>
                        <p>Нажмите на карточку нужного раздела</p>
                    </div>
                    <span class="exports-section__hint">
                        <i class="fa-solid fa-download"></i>
                        Экспорт
                    </span>
                </div>

                <div class="position-relative">
                    <Transition name="export-fade">
                        <div v-if="jobStore.loading" class="exports-loading" role="status" aria-live="polite">
                            <span class="exports-loading__spinner"></span>
                            <strong>Выполняется выгрузка…</strong>
                            <span>Пожалуйста, подождите и не запускайте другие операции.</span>
                        </div>
                    </Transition>

                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-xl-4">
                            <button
                                type="button"
                                class="export-card"
                                :class="['export-card--blue', { 'export-card--loading': jobStore.loading }]"
                                :disabled="jobStore.loading"
                                @click="exportAgents()"
                            >
                        <span class="export-card__icon">
                            <i class="fa-solid fa-user-shield"></i>
                        </span>
                                <span class="export-card__copy">
                            <strong class="export-card__title">Администраторы</strong>
                            <span class="export-card__description">Выгрузка списка администраторов</span>
                        </span>
                                <span class="export-card__action" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-to-line"></i>
                        </span>
                            </button>
                        </div>
                        <div class="col-12 col-sm-6 col-xl-4">
                        <button
                            type="button"
                            class="export-card"
                            :class="['export-card--red', { 'export-card--loading': jobStore.loading }]"
                            :disabled="jobStore.loading"
                            @click="exportAdmins()"
                        >
                        <span class="export-card__icon">
                            <i class="fa-solid fa-user-tie"></i>
                        </span>
                            <span class="export-card__copy">
                            <strong class="export-card__title">Старшие администраторы</strong>
                            <span class="export-card__description">Выгрузка старших администраторов</span>
                        </span>
                            <span class="export-card__action" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-to-line"></i>
                        </span>
                        </button>
                    </div>                <div class="col-12 col-sm-6 col-xl-4">
                        <button
                            type="button"
                            class="export-card"
                            :class="['export-card--blue', { 'export-card--loading': jobStore.loading }]"
                            :disabled="jobStore.loading"
                            @click="exportUsers()"
                        >
                        <span class="export-card__icon">
                            <i class="fa-solid fa-users"></i>
                        </span>
                            <span class="export-card__copy">
                            <strong class="export-card__title">Пользователи</strong>
                            <span class="export-card__description">Выгрузка пользователей системы</span>
                        </span>
                            <span class="export-card__action" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-to-line"></i>
                        </span>
                        </button>
                    </div>                <div class="col-12 col-sm-6 col-xl-4">
                        <button
                            type="button"
                            class="export-card"
                            :class="['export-card--red', { 'export-card--loading': jobStore.loading }]"
                            :disabled="jobStore.loading"
                            @click="exportProducts()"
                        >
                        <span class="export-card__icon">
                            <i class="fa-solid fa-box-open"></i>
                        </span>
                            <span class="export-card__copy">
                            <strong class="export-card__title">Продукты</strong>
                            <span class="export-card__description">Выгрузка списка товаров</span>
                        </span>
                            <span class="export-card__action" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-to-line"></i>
                        </span>
                        </button>
                    </div>                <div class="col-12 col-sm-6 col-xl-4">
                        <button
                            type="button"
                            class="export-card"
                            :class="['export-card--blue', { 'export-card--loading': jobStore.loading }]"
                            :disabled="jobStore.loading"
                            @click="exportCategories()"
                        >
                        <span class="export-card__icon">
                            <i class="fa-solid fa-layer-group"></i>
                        </span>
                            <span class="export-card__copy">
                            <strong class="export-card__title">Категории продуктов</strong>
                            <span class="export-card__description">Выгрузка категорий товаров</span>
                        </span>
                            <span class="export-card__action" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-to-line"></i>
                        </span>
                        </button>
                    </div>                <div class="col-12 col-sm-6 col-xl-4">
                        <button
                            type="button"
                            class="export-card"
                            :class="['export-card--red', { 'export-card--loading': jobStore.loading }]"
                            :disabled="jobStore.loading"
                            @click="exportSuppliers()"
                        >
                        <span class="export-card__icon">
                            <i class="fa-solid fa-truck"></i>
                        </span>
                            <span class="export-card__copy">
                            <strong class="export-card__title">Поставщики</strong>
                            <span class="export-card__description">Выгрузка поставщиков</span>
                        </span>
                            <span class="export-card__action" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-to-line"></i>
                        </span>
                        </button>
                    </div>                <div class="col-12 col-sm-6 col-xl-4">
                        <button
                            type="button"
                            class="export-card"
                            :class="['export-card--blue', { 'export-card--loading': jobStore.loading }]"
                            :disabled="jobStore.loading"
                            @click="exportClients()"
                        >
                        <span class="export-card__icon">
                            <i class="fa-solid fa-handshake"></i>
                        </span>
                            <span class="export-card__copy">
                            <strong class="export-card__title">Клиенты</strong>
                            <span class="export-card__description">Выгрузка клиентской базы</span>
                        </span>
                            <span class="export-card__action" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-to-line"></i>
                        </span>
                        </button>
                    </div>                <div class="col-12 col-sm-6 col-xl-4">
                        <button
                            type="button"
                            class="export-card"
                            :class="['export-card--blue', { 'export-card--loading': jobStore.loading }]"
                            :disabled="jobStore.loading"
                            @click="exportSalesHistory()"
                        >
                        <span class="export-card__icon">
                            <i class="fa-solid fa-chart-line"></i>
                        </span>
                            <span class="export-card__copy">
                            <strong class="export-card__title">История продаж</strong>
                            <span class="export-card__description">Выгрузка статистики продаж</span>
                        </span>
                            <span class="export-card__action" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-to-line"></i>
                        </span>
                        </button>
                    </div>                <div class="col-12 col-sm-6 col-xl-4">
                        <button
                            type="button"
                            class="export-card"
                            :class="['export-card--red', { 'export-card--loading': jobStore.loading }]"
                            :disabled="jobStore.loading"
                            @click="exportBirthdaysList()"
                        >
                        <span class="export-card__icon">
                            <i class="fa-solid fa-cake-candles"></i>
                        </span>
                            <span class="export-card__copy">
                            <strong class="export-card__title">Дни рождения</strong>
                            <span class="export-card__description">Выгрузка списка дней рождения</span>
                        </span>
                            <span class="export-card__action" aria-hidden="true">
                            <i class="fa-solid fa-arrow-down-to-line"></i>
                        </span>
                        </button>
                    </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>

<script>
import {useBaseExports} from '@/stores/baseExports'
import BackBtn from "@/Components/BackBtn.vue";
export default {
    name: 'ExportsMenu',
    components: {BackBtn},

    setup() {
        const jobStore = useBaseExports()

        const exportAgents = () => jobStore.exportAgents()
        const exportAdmins = () => jobStore.exportAdmins()
        const exportUsers = () => jobStore.exportUsers()
        const exportProducts = () => jobStore.exportProducts()
        const exportCategories = () => jobStore.exportCategories()
        const exportClients = () => jobStore.exportClients()
        const exportSuppliers = () => jobStore.exportSuppliers()
        const exportSalesHistory = () => jobStore.exportSalesHistory()
        const exportBirthdaysList = () => jobStore.exportBirthdaysList()

        return {
            jobStore,
            exportAgents,
            exportAdmins,
            exportUsers,
            exportProducts,
            exportCategories,
            exportClients,
            exportSuppliers,
            exportSalesHistory,
            exportBirthdaysList
        }
    }
}
</script>

<style scoped>
.exports-page {
    --exp-blue: #2457d6;
    --exp-blue-dark: #173b91;
    --exp-red: #df3548;
    --exp-ink: #17233b;
    --exp-muted: #778399;
    --exp-border: #e5eaf2;
    min-height: 100%;
    padding: 8px 0 32px;
    color: var(--exp-ink);
    background: #f5f7fb;
}
.exports-page__container {
    width: min(100%, 1500px);
    margin-inline: auto;
    padding: 0 clamp(14px, 2.5vw, 32px);
}
.exports-page__back { margin-bottom: 20px; }
.exports-header {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 24px;
    padding: clamp(20px, 3vw, 32px);
    border: 1px solid var(--exp-border);
    border-radius: 22px;
    background: linear-gradient(130deg, #fff 0%, #f6f8ff 100%);
    box-shadow: 0 10px 30px rgba(23, 59, 145, .055);
}
.exports-header__icon {
    display: grid;
    flex: 0 0 58px;
    width: 58px;
    height: 58px;
    place-items: center;
    color: var(--exp-blue);
    background: #eaf0ff;
    border-radius: 17px;
    font-size: 24px;
}
.exports-header__copy { min-width: 0; flex: 1; }
.exports-header__eyebrow {
    margin: 0 0 5px;
    color: var(--exp-blue);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.exports-header h1 {
    margin: 0;
    color: var(--exp-ink);
    font-size: clamp(23px, 3vw, 31px);
    font-weight: 850;
    letter-spacing: -.04em;
}
.exports-header__copy > p:last-child {
    margin: 7px 0 0;
    color: var(--exp-muted);
    font-size: 14px;
}
.exports-header__badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
    padding: 10px 13px;
    color: var(--exp-blue-dark);
    background: #fff;
    border: 1px solid #dbe5ff;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 750;
}
.exports-section {
    padding: clamp(16px, 2.5vw, 26px);
    border: 1px solid var(--exp-border);
    border-radius: 22px;
    background: #fff;
    box-shadow: 0 8px 26px rgba(23, 35, 59, .035);
}
.exports-section__heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 17px;
    border-bottom: 1px solid #edf0f5;
}
.exports-section__heading h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
    letter-spacing: -.02em;
}
.exports-section__heading p {
    margin: 5px 0 0;
    color: var(--exp-muted);
    font-size: 12px;
}
.exports-section__hint {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 11px;
    color: var(--exp-blue);
    background: #edf3ff;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 750;
}
.export-card {
    position: relative;
    display: flex;
    align-items: center;
    gap: 14px;
    width: 100%;
    min-height: 126px;
    padding: 20px;
    overflow: hidden;
    color: var(--exp-ink);
    text-align: left;
    border: 1px solid var(--exp-border);
    border-radius: 17px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(23, 35, 59, .035);
    cursor: pointer;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.export-card::before {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 4px;
    content: "";
    background: var(--card-accent);
    opacity: .85;
    transition: width .18s ease;
}
.export-card--blue { --card-accent: var(--exp-blue); --card-soft: #edf3ff; }
.export-card--red { --card-accent: var(--exp-red); --card-soft: #fff0f1; }
.export-card:hover:not(:disabled) {
    border-color: color-mix(in srgb, var(--card-accent) 35%, white);
    box-shadow: 0 12px 25px rgba(23, 35, 59, .09);
    transform: translateY(-3px);
}
.export-card:hover:not(:disabled)::before { width: 6px; }
.export-card:focus-visible {
    outline: 3px solid color-mix(in srgb, var(--card-accent) 35%, transparent);
    outline-offset: 3px;
}
.export-card:disabled {
    cursor: wait;
    opacity: .55;
}
.export-card__icon {
    display: grid;
    flex: 0 0 52px;
    width: 52px;
    height: 52px;
    place-items: center;
    color: var(--card-accent);
    background: var(--card-soft);
    border-radius: 15px;
    font-size: 21px;
}
.export-card__copy {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}
.export-card__title {
    color: var(--exp-ink);
    font-size: 14px;
    font-weight: 800;
    line-height: 1.35;
}
.export-card__description {
    color: var(--exp-muted);
    font-size: 12px;
    font-weight: 500;
    line-height: 1.45;
}
.export-card__action {
    display: grid;
    flex: 0 0 34px;
    width: 34px;
    height: 34px;
    place-items: center;
    color: var(--card-accent);
    background: var(--card-soft);
    border-radius: 11px;
    font-size: 13px;
    transition: transform .18s ease;
}
.export-card:hover:not(:disabled) .export-card__action { transform: translateY(2px); }
.exports-loading {
    position: absolute;
    z-index: 5;
    inset: -4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 11px;
    min-height: 300px;
    padding: 28px;
    border: 1px solid #dbe5ff;
    border-radius: 18px;
    background: rgba(255, 255, 255, .94);
    box-shadow: 0 10px 32px rgba(23, 59, 145, .1);
    text-align: center;
    backdrop-filter: blur(5px);
}
.exports-loading strong { color: var(--exp-ink); font-size: 16px; }
.exports-loading > span:last-child { max-width: 360px; color: var(--exp-muted); font-size: 12px; line-height: 1.5; }
.exports-loading__spinner {
    width: 42px;
    height: 42px;
    border: 4px solid #e3eaff;
    border-top-color: var(--exp-blue);
    border-radius: 50%;
    animation: exports-spin .75s linear infinite;
}
@keyframes exports-spin { to { transform: rotate(360deg); } }
.export-fade-enter-active, .export-fade-leave-active { transition: opacity .18s ease; }
.export-fade-enter-from, .export-fade-leave-to { opacity: 0; }
@media (max-width: 575.98px) {
    .exports-page { padding-top: 4px; }
    .exports-page__back { margin-bottom: 14px; }
    .exports-header { align-items: flex-start; gap: 12px; margin-bottom: 16px; padding: 17px; border-radius: 17px; }
    .exports-header__icon { flex-basis: 43px; width: 43px; height: 43px; border-radius: 13px; font-size: 19px; }
    .exports-header__eyebrow { font-size: 9px; }
    .exports-header h1 { font-size: 22px; }
    .exports-header__copy > p:last-child { font-size: 12px; }
    .exports-header__badge { display: none; }
    .exports-section { padding: 14px; border-radius: 17px; }
    .exports-section__heading { margin-bottom: 14px; }
    .exports-section__heading h2 { font-size: 15px; }
    .exports-section__hint { padding: 7px 9px; font-size: 11px; }
    .export-card { min-height: 98px; gap: 11px; padding: 14px; border-radius: 14px; }
    .export-card__icon { flex-basis: 43px; width: 43px; height: 43px; border-radius: 12px; font-size: 18px; }
    .export-card__title { font-size: 13px; }
    .export-card__description { font-size: 11px; }
    .export-card__action { flex-basis: 30px; width: 30px; height: 30px; }
}
@media (prefers-reduced-motion: reduce) {
    .exports-page *, .exports-page *::before, .exports-page *::after {
        animation-duration: .01ms !important;
        transition-duration: .01ms !important;
    }
}
</style>
