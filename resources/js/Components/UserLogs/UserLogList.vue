<template>
    <section class="user-logs">
        <header class="user-logs__header">
            <div class="user-logs__heading">
                <span class="user-logs__icon">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                <div>
                    <p class="user-logs__eyebrow">История активности</p>
                    <h2 class="user-logs__title">Журнал событий</h2>
                    <p class="user-logs__subtitle">
                        Здесь отображаются последние действия и системные события.
                    </p>
                </div>
            </div>

            <div class="user-logs__count">
                <span class="user-logs__count-value">{{ logsStore.items.length }}</span>
                <span class="user-logs__count-label">на странице</span>
            </div>
        </header>

        <div v-if="logsStore.loading" class="user-logs__loading" role="status">
            <span class="user-logs__spinner"></span>
            <span>Загружаем журнал событий…</span>
        </div>

        <div v-else-if="logsStore.error" class="user-logs__state user-logs__state--error" role="alert">
            <span class="user-logs__state-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>
            <div>
                <strong>Не удалось загрузить журнал</strong>
                <p>{{ logsStore.error }}</p>
            </div>
        </div>

        <div v-else-if="logsStore.items.length === 0" class="user-logs__state user-logs__state--empty">
            <span class="user-logs__state-icon">
                <i class="fa-solid fa-inbox"></i>
            </span>
            <strong>Логи пока отсутствуют</strong>
            <p>Когда появятся новые события, они будут отображаться здесь.</p>
        </div>

        <div v-else class="user-logs__list">
            <article
                v-for="log in logsStore.items"
                :key="log.id"
                class="user-logs__item"
            >
                <div class="user-logs__item-icon">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div class="user-logs__item-content">
                    <div class="user-logs__message" v-html="log.message"></div>
                    <div class="user-logs__meta">
                        <i class="fa-regular fa-clock"></i>
                        <time>{{ logsStore.formatDate(log.created_at) }}</time>
                    </div>
                </div>
                <span class="user-logs__item-marker" aria-hidden="true"></span>
            </article>
        </div>

        <div class="user-logs__pagination">
            <Pagination
                :pagination="logsStore.pagination"
                @page-changed="fetchDataByUrl"
            />
        </div>
    </section>
</template>

<script>
import { useUserLogsStore } from "@/stores/userLogs";
import Pagination from "@/Components/Pagination.vue";

export default {
    name: "UserLogList",
    components: { Pagination },
    data() {
        return {
            logsStore: useUserLogsStore(),
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        async fetchData(page = 1) {
            await this.logsStore.fetchAll(page);
        },
        async fetchDataByUrl(url) {
            await this.logsStore.fetchByUrl(url);
        },
    },
};
</script>

<style scoped>
.user-logs {
    --logs-blue: #2457d6;
    --logs-blue-dark: #173b91;
    --logs-red: #df3548;
    --logs-ink: #17233b;
    --logs-muted: #778399;
    --logs-border: #e5eaf2;
    width: 100%;
    min-width: 0;
    color: var(--logs-ink);
}

.user-logs__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 22px;
    padding: 22px 24px;
    border: 1px solid var(--logs-border);
    border-radius: 20px;
    background: linear-gradient(135deg, #fff 0%, #f7f9ff 100%);
    box-shadow: 0 8px 24px rgba(23, 59, 145, .06);
}

.user-logs__heading {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 0;
}

.user-logs__icon {
    display: grid;
    flex: 0 0 52px;
    width: 52px;
    height: 52px;
    place-items: center;
    color: var(--logs-blue);
    background: #eaf0ff;
    border-radius: 16px;
    font-size: 22px;
}

.user-logs__eyebrow {
    margin: 0 0 4px;
    color: var(--logs-blue);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase;
}

.user-logs__title {
    margin: 0;
    color: var(--logs-ink);
    font-size: clamp(20px, 2.5vw, 27px);
    font-weight: 800;
    letter-spacing: -.035em;
}

.user-logs__subtitle {
    margin: 6px 0 0;
    color: var(--logs-muted);
    font-size: 13px;
    line-height: 1.5;
}

.user-logs__count {
    display: flex;
    flex: 0 0 auto;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 88px;
    padding: 10px 14px;
    border: 1px solid #dbe5ff;
    border-radius: 14px;
    background: #fff;
}

.user-logs__count-value {
    color: var(--logs-blue);
    font-size: 23px;
    font-weight: 800;
    line-height: 1.1;
}

.user-logs__count-label {
    margin-top: 4px;
    color: var(--logs-muted);
    font-size: 11px;
    font-weight: 700;
}

.user-logs__list {
    display: grid;
    gap: 11px;
}

.user-logs__item {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 17px 18px;
    overflow: hidden;
    border: 1px solid var(--logs-border);
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 4px 15px rgba(23, 35, 59, .035);
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.user-logs__item:hover {
    border-color: #cbd8fb;
    box-shadow: 0 8px 22px rgba(23, 59, 145, .07);
    transform: translateY(-1px);
}

.user-logs__item-icon {
    display: grid;
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    place-items: center;
    color: var(--logs-blue);
    background: #edf3ff;
    border-radius: 12px;
    font-size: 15px;
}

.user-logs__item-content {
    flex: 1;
    min-width: 0;
    padding-right: 8px;
}

.user-logs__message {
    color: var(--logs-ink);
    font-size: 14px;
    font-weight: 600;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.user-logs__message :deep(a) {
    color: var(--logs-blue);
    font-weight: 750;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.user-logs__meta {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 9px;
    color: var(--logs-muted);
    font-size: 12px;
    font-weight: 500;
}

.user-logs__meta i {
    color: #9aa6b8;
}

.user-logs__item-marker {
    flex: 0 0 7px;
    width: 7px;
    height: 7px;
    margin-top: 7px;
    border-radius: 50%;
    background: #7ea1ff;
    box-shadow: 0 0 0 4px #edf3ff;
}

.user-logs__pagination {
    margin-top: 20px;
}

.user-logs__loading,
.user-logs__state {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 13px;
    min-height: 170px;
    padding: 24px;
    border: 1px dashed var(--logs-border);
    border-radius: 18px;
    background: #fff;
    text-align: left;
}

.user-logs__loading {
    color: var(--logs-muted);
    font-size: 14px;
    font-weight: 650;
}

.user-logs__spinner {
    width: 23px;
    height: 23px;
    border: 2px solid #dce6ff;
    border-top-color: var(--logs-blue);
    border-radius: 50%;
    animation: logs-spin .7s linear infinite;
}

@keyframes logs-spin {
    to { transform: rotate(360deg); }
}

.user-logs__state {
    flex-direction: column;
    gap: 8px;
    text-align: center;
}

.user-logs__state-icon {
    display: grid;
    width: 48px;
    height: 48px;
    margin-bottom: 4px;
    place-items: center;
    color: var(--logs-blue);
    background: #edf3ff;
    border-radius: 15px;
    font-size: 20px;
}

.user-logs__state strong {
    color: var(--logs-ink);
    font-size: 15px;
    font-weight: 800;
}

.user-logs__state p {
    max-width: 420px;
    margin: 0;
    color: var(--logs-muted);
    font-size: 13px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.user-logs__state--error .user-logs__state-icon {
    color: var(--logs-red);
    background: #fff0f1;
}

@media (max-width: 575.98px) {
    .user-logs__header {
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 16px;
        padding: 16px;
        border-radius: 16px;
    }

    .user-logs__heading {
        align-items: flex-start;
        gap: 11px;
    }

    .user-logs__icon {
        flex-basis: 42px;
        width: 42px;
        height: 42px;
        border-radius: 13px;
        font-size: 18px;
    }

    .user-logs__eyebrow {
        font-size: 9px;
    }

    .user-logs__title {
        font-size: 20px;
    }

    .user-logs__subtitle {
        font-size: 12px;
    }

    .user-logs__count {
        min-width: 60px;
        padding: 8px;
    }

    .user-logs__count-value {
        font-size: 19px;
    }

    .user-logs__count-label {
        font-size: 9px;
        text-align: center;
    }

    .user-logs__item {
        gap: 10px;
        padding: 13px;
        border-radius: 13px;
    }

    .user-logs__item-icon {
        flex-basis: 32px;
        width: 32px;
        height: 32px;
        border-radius: 10px;
        font-size: 13px;
    }

    .user-logs__message {
        font-size: 13px;
    }

    .user-logs__meta {
        flex-wrap: wrap;
        font-size: 11px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .user-logs *,
    .user-logs *::before,
    .user-logs *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
    }
}
</style>
