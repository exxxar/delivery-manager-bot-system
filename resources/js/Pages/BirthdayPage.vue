<script setup>
import BackBtn from "@/Components/BackBtn.vue";
import BirthdayList from "@/Components/Birthdays/BirthdayList.vue";
</script>

<template>
    <main class="birthday-page container-fluid">
        <div class="page-top">
            <BackBtn />
        </div>

        <section class="page-heading">
            <div class="heading-icon" aria-hidden="true">
                <i class="fa-solid fa-cake-candles"></i>
            </div>
            <div class="heading-copy">
                <span class="eyebrow">КОМАНДА И КОНТАКТЫ</span>
                <h1>Дни рождения</h1>
                <p>Ближайшие именинники и экспорт списка для работы.</p>
            </div>
        </section>

        <section class="birthday-content">
            <BirthdayList />
        </section>

        <section class="export-card">
            <div class="export-icon" aria-hidden="true">
                <i class="fa-solid fa-file-excel"></i>
            </div>
            <div class="export-copy">
                <h2>Экспорт списка</h2>
                <p>Скачайте список дней рождения в формате Excel.</p>
            </div>
            <button
                type="button"
                class="btn export-button"
                :disabled="jobStore.loading"
                @click="exportBirthdaysList"
            >
                <span v-if="jobStore.loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                <i v-else class="fa-solid fa-download" aria-hidden="true"></i>
                <span>{{ jobStore.loading ? 'Подготовка файла…' : 'Выгрузить в Excel' }}</span>
            </button>
        </section>
    </main>
</template>

<script>
import { useBaseExports } from "@/stores/baseExports";

export default {
    name: "BirthdayPage",
    data() {
        return {
            jobStore: useBaseExports(),
        };
    },
    methods: {
        exportBirthdaysList() {
            return this.jobStore.exportBirthdaysList();
        },
    },
};
</script>

<style scoped>
.birthday-page {
    --birthday-navy: #142b4a;
    --birthday-blue: #2463eb;
    --birthday-red: #e34b5f;
    --birthday-muted: #6b7890;
    --birthday-border: #e5ebf3;
    padding: 1rem 0 1.5rem;
    color: var(--birthday-navy);
}

.page-top {
    margin-bottom: 1rem;
}

.page-heading {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 0 0 1.25rem;
    padding: 1.25rem 1.35rem;
    border: 1px solid var(--birthday-border);
    border-radius: 1.25rem;
    background: linear-gradient(115deg, #fff 0%, #f5f8ff 100%);
    box-shadow: 0 8px 24px rgba(20, 43, 74, 0.05);
}

.heading-icon,
.export-icon {
    display: grid;
    flex: 0 0 auto;
    place-items: center;
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    color: var(--birthday-blue);
    background: #eaf1ff;
    font-size: 1.45rem;
}

.heading-copy {
    min-width: 0;
}

.eyebrow {
    display: block;
    margin-bottom: 0.25rem;
    color: var(--birthday-blue);
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.12em;
}

.page-heading h1 {
    margin: 0;
    color: var(--birthday-navy);
    font-size: clamp(1.35rem, 3vw, 1.8rem);
    font-weight: 800;
    letter-spacing: -0.03em;
}

.page-heading p {
    margin: 0.35rem 0 0;
    color: var(--birthday-muted);
    font-size: 0.92rem;
}

.birthday-content {
    min-width: 0;
    padding: 1.15rem;
    border: 1px solid var(--birthday-border);
    border-radius: 1.25rem;
    background: #fff;
    box-shadow: 0 8px 24px rgba(20, 43, 74, 0.05);
}

.export-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1.1rem;
    padding: 1.15rem 1.25rem;
    border: 1px solid var(--birthday-border);
    border-radius: 1.25rem;
    background: #fff;
    box-shadow: 0 8px 24px rgba(20, 43, 74, 0.05);
}

.export-icon {
    color: #16834a;
    background: #e7f7ee;
}

.export-copy {
    flex: 1 1 auto;
    min-width: 0;
}

.export-copy h2 {
    margin: 0;
    color: var(--birthday-navy);
    font-size: 1rem;
    font-weight: 750;
}

.export-copy p {
    margin: 0.3rem 0 0;
    color: var(--birthday-muted);
    font-size: 0.875rem;
}

.export-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    min-height: 2.9rem;
    padding: 0.75rem 1.1rem;
    border: 0;
    border-radius: 0.85rem;
    color: #fff;
    background: linear-gradient(135deg, #18794e, #21a366);
    font-weight: 700;
    box-shadow: 0 5px 14px rgba(24, 121, 78, 0.18);
    transition: transform 160ms ease, box-shadow 160ms ease, opacity 160ms ease;
}

.export-button:hover:not(:disabled) {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(24, 121, 78, 0.25);
}

.export-button:focus-visible {
    outline: 3px solid rgba(36, 99, 235, 0.35);
    outline-offset: 3px;
}

.export-button:disabled {
    cursor: wait;
    opacity: 0.72;
}

@media (max-width: 575.98px) {
    .birthday-page {
        padding-top: 0.5rem;
    }

    .page-heading {
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        border-radius: 1rem;
    }

    .heading-icon,
    .export-icon {
        width: 2.8rem;
        height: 2.8rem;
        border-radius: 0.8rem;
        font-size: 1.15rem;
    }

    .page-heading p {
        font-size: 0.85rem;
    }

    .birthday-content {
        padding: 0.8rem;
        border-radius: 1rem;
    }

    .export-card {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.8rem;
        padding: 1rem;
        border-radius: 1rem;
    }

    .export-button {
        grid-column: 1 / -1;
        width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .export-button {
        transition: none;
    }
}
</style>
