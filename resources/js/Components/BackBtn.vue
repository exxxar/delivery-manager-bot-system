
<template>
    <div class="back-navigation">
        <button
            type="button"
            class="back-button"
            @click="goBack"
        >
            <span class="back-icon">
                <i class="fa-solid fa-arrow-left"></i>
            </span>
            <span>Назад</span>
        </button>
    </div>
</template>

<script>
export default {
    name: 'BackButton',

    methods: {
        goBack() {
            this.$router.push({ name: 'MenuPage' });
        },
    },
};
</script>

<style scoped>
.back-navigation {
    position: sticky;
    top: 80px;
    z-index: 100;
    display: flex;
    align-items: center;
    margin-bottom: 1.25rem;
    pointer-events: none;
}

.back-button {
    pointer-events: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 44px;
    padding: 8px 18px 8px 10px;

    color: #2457d6;
    background: #ffffff;
    border: 1px solid #e1e8f5;
    border-radius: 12px;

    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.01em;

    box-shadow: 0 3px 12px rgba(23, 59, 145, 0.06);
    cursor: pointer;

    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        color 0.2s ease,
        box-shadow 0.2s ease,
        transform 0.2s ease;
}

.back-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;

    color: #2457d6;
    background: #edf3ff;
    border-radius: 9px;

    transition:
        background-color 0.2s ease,
        color 0.2s ease,
        transform 0.2s ease;
}

.back-icon i {
    font-size: 14px;
}

.back-button:hover {
    color: #173b91;
    background: #f8faff;
    border-color: #c8d7f7;
    box-shadow: 0 6px 18px rgba(36, 87, 214, 0.12);
    transform: translateY(-1px);
}

.back-button:hover .back-icon {
    color: #ffffff;
    background: #2457d6;
    transform: translateX(-2px);
}

.back-button:active {
    transform: translateY(0);
}

.back-button:focus-visible {
    outline: 3px solid rgba(36, 87, 214, 0.25);
    outline-offset: 3px;
}

@media (max-width: 576px) {
    .back-navigation {
        top: 68px;
        margin-bottom: 1rem;
    }

    .back-button {
        min-height: 42px;
        padding-right: 15px;
    }
}
</style>
