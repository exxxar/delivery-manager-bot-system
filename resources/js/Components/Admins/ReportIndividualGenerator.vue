<template>
    <form class="report-generator" @submit.prevent="generateReport">
        <div class="report-generator__heading">
            <div class="report-generator__icon" aria-hidden="true">
                <i class="fa-solid fa-file-lines"></i>
            </div>
            <div>
                <h2 class="report-generator__title">Индивидуальный отчёт</h2>
                <p class="report-generator__subtitle">Выберите период, за который нужно сформировать отчёт</p>
            </div>
        </div>

        <div class="report-generator__fields">
            <div class="report-field">
                <label class="report-field__label" for="startDate">
                    <i class="fa-regular fa-calendar me-2" aria-hidden="true"></i>Дата начала периода
                </label>
                <div class="report-field__control">
                    <input
                        id="startDate"
                        v-model="report.startDate"
                        class="form-control report-field__input"
                        type="date"
                        required
                        :max="report.endDate || undefined"
                    />
                </div>
            </div>

            <div class="report-field">
                <label class="report-field__label" for="endDate">
                    <i class="fa-regular fa-calendar-check me-2" aria-hidden="true"></i>Дата окончания периода
                </label>
                <div class="report-field__control">
                    <input
                        id="endDate"
                        v-model="report.endDate"
                        class="form-control report-field__input"
                        type="date"
                        required
                        :min="report.startDate || undefined"
                    />
                </div>
            </div>
        </div>

        <div v-if="report.startDate && report.endDate" class="report-generator__period">
            <i class="fa-solid fa-calendar-week" aria-hidden="true"></i>
            <span>Период отчёта:</span>
            <strong>{{ formatDate(report.startDate) }} — {{ formatDate(report.endDate) }}</strong>
        </div>

        <button
            class="btn report-generator__submit"
            :disabled="report.loading || !report.startDate || !report.endDate || report.startDate > report.endDate"
            type="submit"
        >
            <i class="fa-solid fa-file-arrow-down me-2" aria-hidden="true"></i>
            {{ report.loading ? 'Формирование отчёта…' : 'Сформировать отчёт' }}
        </button>
    </form>
</template>

<script>
import { useBaseExports } from "@/stores/baseExports";

export default {
    name: 'ReportIndividualGenerator',
    props: ["agentId"],
    data() {
        return {
            reportStore: useBaseExports(),
            report: {
                startDate: '',
                endDate: '',
            }
        }
    },
    methods: {
        formatDate(value) {
            if (!value) return '';
            const [year, month, day] = value.split('-');
            return `${day}.${month}.${year}`;
        },
        generateReport() {
            if (this.report.startDate > this.report.endDate) return;

            const payload = {
                ...this.report,
                id: this.agentId
            };

            this.reportStore.exportIndividual(payload);

            if (!this.agentId) {
                this.$emit('generate-report', payload);
            }
        }
    }
}
</script>

<style scoped>
.report-generator {
    --report-navy: #172b4d;
    --report-blue: #2563eb;
    --report-blue-soft: #eff6ff;
    --report-red: #dc3545;
    padding: clamp(1rem, 3vw, 1.5rem);
    border: 1px solid #e6ebf2;
    border-radius: 20px;
    background: #fff;
    box-shadow: 0 10px 30px rgba(23, 43, 77, 0.07);
}

.report-generator__heading {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    margin-bottom: 1.5rem;
}

.report-generator__icon {
    display: grid;
    place-items: center;
    flex: 0 0 3.25rem;
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 16px;
    color: var(--report-blue);
    background: var(--report-blue-soft);
    font-size: 1.35rem;
}

.report-generator__title {
    margin: 0 0 0.3rem;
    color: var(--report-navy);
    font-size: 1.15rem;
    font-weight: 750;
    letter-spacing: -0.02em;
}

.report-generator__subtitle {
    margin: 0;
    color: #718096;
    font-size: 0.9rem;
    line-height: 1.45;
}

.report-generator__fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem;
}

.report-field__label {
    display: block;
    margin-bottom: 0.5rem;
    color: #475569;
    font-size: 0.875rem;
    font-weight: 650;
}

.report-field__label i {
    color: var(--report-blue);
}

.report-field__input {
    min-height: 3.15rem;
    border: 1px solid #dce3ed;
    border-radius: 12px;
    color: var(--report-navy);
    background-color: #f9fbfe;
    box-shadow: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.report-field__input:focus {
    border-color: var(--report-blue);
    background-color: #fff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.report-generator__period {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.45rem;
    margin-top: 1rem;
    padding: 0.8rem 0.9rem;
    border: 1px solid #dbeafe;
    border-radius: 12px;
    color: #53657d;
    background: #f5f9ff;
    font-size: 0.875rem;
}

.report-generator__period > i {
    margin-right: 0.2rem;
    color: var(--report-blue);
}

.report-generator__period strong {
    color: var(--report-navy);
    font-weight: 700;
}

.report-generator__submit {
    width: 100%;
    min-height: 3.25rem;
    margin-top: 1.25rem;
    padding: 0.85rem 1.2rem;
    border: 0;
    border-radius: 12px;
    color: #fff;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 7px 16px rgba(37, 99, 235, 0.2);
    font-weight: 700;
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.report-generator__submit:hover:not(:disabled) {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.28);
}

.report-generator__submit:focus-visible {
    outline: 3px solid rgba(37, 99, 235, 0.3);
    outline-offset: 3px;
}

.report-generator__submit:disabled {
    cursor: not-allowed;
    opacity: 0.55;
    box-shadow: none;
}

@media (max-width: 575.98px) {
    .report-generator {
        padding: 1rem;
        border-radius: 16px;
    }

    .report-generator__fields {
        grid-template-columns: minmax(0, 1fr);
        gap: 0.9rem;
    }

    .report-generator__heading {
        align-items: flex-start;
        margin-bottom: 1.2rem;
    }

    .report-generator__icon {
        flex-basis: 2.8rem;
        width: 2.8rem;
        height: 2.8rem;
        border-radius: 13px;
    }
}
</style>
