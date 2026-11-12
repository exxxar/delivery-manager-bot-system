<template>
    <section class="report-filters">
        <div class="report-filters__top">
            <div class="report-filters__heading">
                <div class="report-filters__icon">
                    <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                </div>
                <div>
                    <div class="report-filters__eyebrow">АНАЛИТИКА И ФИНАНСЫ</div>
                    <h2 class="report-filters__title">Параметры отчёта</h2>
                    <p class="report-filters__description">
                        Выберите период, за который нужно сформировать сводку.
                    </p>
                </div>
            </div>

            <div class="report-filters__auto">
                <span class="report-filters__auto-dot"></span>
                Автоматический подбор
            </div>
        </div>

        <div class="report-filters__body">
            <div class="report-filters__date-grid">
                <div class="report-date-field">
                    <label class="report-date-field__label" for="report-start-date">
                        <span>Начало периода</span>
                        <span class="report-date-field__hint">С</span>
                    </label>
                    <div class="report-date-field__control">
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                        <input
                            id="report-start-date"
                            v-model="localFilters.startDate"
                            type="date"
                            class="report-date-field__input"
                            :max="localFilters.endDate || undefined"
                        >
                    </div>
                </div>

                <div class="report-date-field">
                    <label class="report-date-field__label" for="report-end-date">
                        <span>Конец периода</span>
                        <span class="report-date-field__hint">ПО</span>
                    </label>
                    <div class="report-date-field__control">
                        <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                        <input
                            id="report-end-date"
                            v-model="localFilters.endDate"
                            type="date"
                            class="report-date-field__input"
                            :min="localFilters.startDate || undefined"
                        >
                    </div>
                </div>
            </div>

            <div class="report-filters__bottom">
                <div class="report-filters__note">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <span>Агенты и поставщики с активными продажами будут добавлены автоматически.</span>
                </div>

                <div class="report-filters__actions">
                    <button
                        type="button"
                        class="report-button report-button--reset"
                        :disabled="loading"
                        @click="$emit('reset')"
                    >
                        <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
                        Сбросить
                    </button>

                    <button
                        type="button"
                        class="report-button report-button--submit"
                        :disabled="loading || !isValid"
                        @click="submit"
                    >
                        <span v-if="loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                        <i v-else class="fa-solid fa-chart-column" aria-hidden="true"></i>
                        {{ loading ? 'Формируем…' : 'Сформировать отчёт' }}
                        <i v-if="!loading" class="fa-solid fa-arrow-right report-button__arrow" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <transition name="report-validation">
                <div v-if="validationMessage" class="report-filters__validation" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    {{ validationMessage }}
                </div>
            </transition>
        </div>
    </section>
</template>

<script>
export default {
    name: 'ReportFilters',

    props: {
        filters: { type: Object, required: true },
        loading: { type: Boolean, default: false },
    },

    emits: ['submit', 'reset', 'update:filters'],

    data() {
        return {
            localFilters: { ...this.filters },
        }
    },

    computed: {
        isValid() {
            return !!this.localFilters.startDate &&
                !!this.localFilters.endDate &&
                this.localFilters.endDate >= this.localFilters.startDate
        },

        validationMessage() {
            if (!this.localFilters.startDate || !this.localFilters.endDate) {
                return 'Укажите начало и конец отчётного периода.'
            }
            if (this.localFilters.endDate < this.localFilters.startDate) {
                return 'Конец периода не может быть раньше его начала.'
            }
            return ''
        },
    },

    watch: {
        filters: {
            deep: true,
            handler(value) {
                this.localFilters = { ...value }
            },
        },
    },

    methods: {
        submit() {
            if (!this.isValid) return
            this.$emit('update:filters', { ...this.localFilters })
            this.$emit('submit')
        },
    },
}
</script>

<style scoped>
.report-filters {
    position: relative;
    overflow: hidden;
    border: 1px solid #e4eaf3;
    border-radius: 20px;
    background:
        radial-gradient(circle at 100% 0%, rgba(77, 126, 219, .09), transparent 32%),
        linear-gradient(135deg, #fff 0%, #fbfcff 100%);
    box-shadow: 0 12px 34px rgba(25, 48, 84, .065);
}

.report-filters::before {
    position: absolute;
    inset: 0 auto 0 0;
    width: 4px;
    content: "";
    background: linear-gradient(180deg, #4779d2, #244b91);
}

.report-filters__top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.25rem;
    padding: 1.35rem 1.5rem 1.1rem;
    border-bottom: 1px solid #edf1f7;
}

.report-filters__heading {
    display: flex;
    align-items: flex-start;
    gap: .95rem;
}

.report-filters__icon {
    display: grid;
    flex: 0 0 46px;
    width: 46px;
    height: 46px;
    place-items: center;
    border: 1px solid #dce7fb;
    border-radius: 14px;
    color: #315da7;
    background: linear-gradient(145deg, #f1f6ff, #e7efff);
    font-size: 1.05rem;
}

.report-filters__eyebrow {
    margin: 0 0 .25rem;
    color: #8090a8;
    font-size: .64rem;
    font-weight: 800;
    letter-spacing: .13em;
}

.report-filters__title {
    margin: 0;
    color: #1d2b43;
    font-size: 1.08rem;
    font-weight: 800;
    letter-spacing: -.025em;
}

.report-filters__description {
    margin: .3rem 0 0;
    color: #7b899e;
    font-size: .84rem;
}

.report-filters__auto {
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    gap: .5rem;
    padding: .48rem .75rem;
    border: 1px solid #dceee6;
    border-radius: 999px;
    color: #34785b;
    background: #f2faf6;
    font-size: .72rem;
    font-weight: 700;
    white-space: nowrap;
}

.report-filters__auto-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #39a879;
    box-shadow: 0 0 0 3px rgba(57, 168, 121, .12);
}

.report-filters__body {
    padding: 1.25rem 1.5rem 1.35rem;
}

.report-filters__date-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
    max-width: 760px;
}

.report-date-field__label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .5rem;
    color: #40516b;
    font-size: .78rem;
    font-weight: 750;
}

.report-date-field__hint {
    color: #9aa7b9;
    font-size: .62rem;
    font-weight: 800;
    letter-spacing: .08em;
}

.report-date-field__control {
    display: flex;
    align-items: center;
    gap: .7rem;
    min-height: 49px;
    padding: 0 .85rem;
    border: 1px solid #dfe6f0;
    border-radius: 12px;
    background: #fff;
    transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
}

.report-date-field__control > i {
    color: #6f87ad;
    font-size: .95rem;
}

.report-date-field__control:focus-within {
    border-color: #6b91d2;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(65, 111, 190, .11);
}

.report-date-field__input {
    width: 100%;
    min-width: 0;
    height: 47px;
    padding: 0;
    border: 0;
    outline: 0;
    color: #263750;
    background: transparent;
    font: inherit;
    font-size: .88rem;
    font-weight: 650;
    color-scheme: light;
}

.report-date-field__input::-webkit-calendar-picker-indicator {
    cursor: pointer;
    opacity: .65;
}

.report-filters__bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 1.25rem;
    padding-top: 1.1rem;
    border-top: 1px solid #edf1f7;
}

.report-filters__note {
    display: flex;
    align-items: flex-start;
    gap: .55rem;
    max-width: 520px;
    color: #8290a4;
    font-size: .76rem;
    line-height: 1.5;
}

.report-filters__note i {
    margin-top: .12rem;
    color: #6f8fbe;
}

.report-filters__actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: .65rem;
}

.report-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .55rem;
    min-height: 43px;
    padding: .65rem 1rem;
    border: 1px solid transparent;
    border-radius: 11px;
    font-size: .8rem;
    font-weight: 750;
    transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}

.report-button:disabled {
    cursor: not-allowed;
    opacity: .55;
    transform: none;
    box-shadow: none;
}

.report-button--reset {
    border-color: #e1e7ef;
    color: #68778d;
    background: #fff;
}

.report-button--reset:not(:disabled):hover {
    border-color: #cbd6e5;
    color: #334761;
    background: #f8faff;
}

.report-button--submit {
    min-width: 205px;
    color: #fff;
    background: linear-gradient(115deg, #3768b8 0%, #244b91 100%);
    box-shadow: 0 5px 13px rgba(39, 78, 145, .2);
}

.report-button--submit:not(:disabled):hover {
    transform: translateY(-1px);
    background: linear-gradient(115deg, #2f5fae 0%, #1d407f 100%);
    box-shadow: 0 8px 18px rgba(39, 78, 145, .26);
}

.report-button__arrow {
    margin-left: .2rem;
    font-size: .72rem;
    opacity: .8;
}

.report-filters__validation {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-top: .85rem;
    color: #b54747;
    font-size: .78rem;
    font-weight: 600;
}

.report-validation-enter-active,
.report-validation-leave-active {
    transition: opacity .16s ease, transform .16s ease;
}

.report-validation-enter-from,
.report-validation-leave-to {
    transform: translateY(-4px);
    opacity: 0;
}

@media (max-width: 767.98px) {
    .report-filters__top {
        flex-direction: column;
        padding: 1.1rem 1rem .95rem;
    }

    .report-filters__body {
        padding: 1rem;
    }

    .report-filters__date-grid {
        grid-template-columns: 1fr;
        gap: .85rem;
    }

    .report-filters__bottom {
        align-items: stretch;
        flex-direction: column;
    }

    .report-filters__actions {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
    }

    .report-button--submit {
        min-width: 0;
    }
}

@media (max-width: 420px) {
    .report-filters__heading {
        gap: .7rem;
    }

    .report-filters__icon {
        flex-basis: 40px;
        width: 40px;
        height: 40px;
    }

    .report-filters__actions {
        grid-template-columns: 1fr;
    }
}
</style>
