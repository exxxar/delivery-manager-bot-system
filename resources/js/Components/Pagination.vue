
<template>
    <nav
        v-if="pagination && pagination.total > 0"
        class="pagination-wrapper"
        aria-label="Навигация по страницам"
    >
        <div class="pagination-info">
            <i class="fa-solid fa-table-list"></i>
            <span>Страница</span>
            <strong>{{ pagination.current_page }}</strong>
            <span class="pagination-divider">из</span>
            <strong>{{ pagination.last_page }}</strong>
        </div>

        <div class="pagination-controls">
            <button
                type="button"
                class="pagination-button"
                :disabled="!pagination.prev_page_url"
                aria-label="Предыдущая страница"
                title="Предыдущая страница"
                @click="changePage(pagination.prev_page_url)"
            >
                <i class="fa-solid fa-chevron-left"></i>
                <span class="button-label">Назад</span>
            </button>

            <div class="page-indicator">
                <span class="current-page">{{ pagination.current_page }}</span>
                <span class="page-separator">/</span>
                <span class="total-pages">{{ pagination.last_page }}</span>
            </div>

            <button
                type="button"
                class="pagination-button pagination-button-primary"
                :disabled="!pagination.next_page_url"
                aria-label="Следующая страница"
                title="Следующая страница"
                @click="changePage(pagination.next_page_url)"
            >
                <span class="button-label">Вперёд</span>
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </nav>
</template>

<script>
export default {
    name: 'Pagination',

    props: {
        pagination: {
            type: Object,
            required: true,
        },
    },

    methods: {
        changePage(url) {
            if (!url) return;

            const relativeUrl = this.getRelativeUrl(url);
            this.$emit('page-changed', relativeUrl);
        },

        getRelativeUrl(url) {
            if (!url) return url;

            if (url.startsWith('/')) {
                return url;
            }

            try {
                const urlObj = new URL(url, window.location.origin);
                return urlObj.pathname + urlObj.search;
            } catch (e) {
                return url;
            }
        },
    },
};
</script>

<style scoped>
.pagination-wrapper {
    --pagination-blue: #2457d6;
    --pagination-blue-dark: #173b91;
    --pagination-text: #17233b;
    --pagination-muted: #778399;
    --pagination-border: #e5eaf2;

    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;

    width: 100%;
    margin: 24px 0;
    padding: 16px 18px;

    background: #ffffff;
    border: 1px solid var(--pagination-border);
    border-radius: 15px;
    box-shadow: 0 3px 12px rgba(23, 35, 59, 0.035);
}

.pagination-info {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--pagination-muted);
    font-size: 13px;
}

.pagination-info > i {
    margin-right: 4px;
    color: var(--pagination-blue);
    font-size: 16px;
}

.pagination-info strong {
    color: var(--pagination-text);
    font-weight: 750;
}

.pagination-divider {
    margin: 0 2px;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pagination-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;

    min-height: 42px;
    padding: 9px 14px;

    color: var(--pagination-blue);
    background: #ffffff;
    border: 1px solid var(--pagination-border);
    border-radius: 10px;

    font-size: 13px;
    font-weight: 650;
    cursor: pointer;

    transition:
        color 0.2s ease,
        background 0.2s ease,
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        transform 0.2s ease;
}

.pagination-button:hover:not(:disabled) {
    color: var(--pagination-blue-dark);
    background: #f2f6ff;
    border-color: #c8d7f7;
    transform: translateY(-1px);
}

.pagination-button-primary {
    color: #ffffff;
    background: var(--pagination-blue);
    border-color: var(--pagination-blue);
}

.pagination-button-primary:hover:not(:disabled) {
    color: #ffffff;
    background: var(--pagination-blue-dark);
    border-color: var(--pagination-blue-dark);
    box-shadow: 0 4px 12px rgba(36, 87, 214, 0.18);
}

.pagination-button:disabled {
    color: #aab3c1;
    background: #f7f8fa;
    border-color: #edf0f4;
    cursor: not-allowed;
    opacity: 0.8;
}

.page-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    min-width: 70px;
    min-height: 42px;
    padding: 8px 12px;

    background: #edf3ff;
    border: 1px solid #dce7ff;
    border-radius: 10px;

    font-size: 13px;
    font-weight: 700;
}

.current-page {
    color: var(--pagination-blue);
}

.page-separator,
.total-pages {
    color: var(--pagination-muted);
}

.pagination-button:focus-visible {
    outline: 3px solid rgba(36, 87, 214, 0.25);
    outline-offset: 3px;
}

@media (max-width: 575.98px) {
    .pagination-wrapper {
        flex-direction: column;
        align-items: stretch;
        padding: 14px;
        gap: 13px;
    }

    .pagination-info {
        justify-content: center;
        padding-bottom: 12px;
        border-bottom: 1px solid #eef1f6;
    }

    .pagination-controls {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 8px;
    }

    .pagination-button {
        min-width: 0;
        padding: 9px 10px;
    }

    .pagination-button:first-child {
        justify-content: flex-start;
    }

    .pagination-button-primary {
        justify-content: flex-end;
    }

    .page-indicator {
        min-width: 64px;
    }
}

@media (max-width: 360px) {
    .button-label {
        display: none;
    }

    .pagination-button,
    .pagination-button-primary {
        justify-content: center;
        min-width: 42px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .pagination-button {
        transition: none;
    }
}
</style>
