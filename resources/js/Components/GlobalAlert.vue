<template>
    <transition name="alert-toast">
        <div v-if="alertStore.visible" class="alert-host" aria-live="polite" aria-atomic="true">
            <div class="global-alert" :class="alertClass" role="status">
                <div class="alert-icon" aria-hidden="true">
                    <i :class="alertIcon"></i>
                </div>

                <div class="alert-content">
                    <div class="alert-title">{{ alertTitle }}</div>
                    <div class="alert-message">{{ alertStore.message }}</div>
                </div>

                <button
                    type="button"
                    class="close-btn"
                    aria-label="Закрыть уведомление"
                    @click="alertStore.hide"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <span class="alert-progress" aria-hidden="true"></span>
            </div>
        </div>
    </transition>
</template>

<script>
import { useAlertStore } from '@/stores/utillites/useAlertStore'

export default {
    name: 'GlobalAlert',
    setup() {
        const alertStore = useAlertStore()
        return { alertStore }
    },
    computed: {
        alertClass() {
            switch (this.alertStore.type) {
                case 'success': return 'alert-success'
                case 'error': return 'alert-error'
                case 'warning': return 'alert-warning'
                default: return 'alert-info'
            }
        },
        alertIcon() {
            switch (this.alertStore.type) {
                case 'success': return 'fa-solid fa-circle-check'
                case 'error': return 'fa-solid fa-circle-exclamation'
                case 'warning': return 'fa-solid fa-triangle-exclamation'
                default: return 'fa-solid fa-circle-info'
            }
        },
        alertTitle() {
            switch (this.alertStore.type) {
                case 'success': return 'Успешно'
                case 'error': return 'Ошибка'
                case 'warning': return 'Внимание'
                default: return 'Уведомление'
            }
        }
    }
}
</script>

<style scoped>
.alert-host {
    position: fixed;
    top: max(16px, env(safe-area-inset-top));
    right: max(16px, env(safe-area-inset-right));
    z-index: 9999;
    width: min(420px, calc(100vw - 32px));
    pointer-events: none;
}

.global-alert {
    --alert-accent: #2563eb;
    --alert-soft: #eff6ff;
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 13px;
    overflow: hidden;
    padding: 17px 48px 19px 16px;
    border: 1px solid rgba(20, 42, 78, 0.08);
    border-left: 4px solid var(--alert-accent);
    border-radius: 16px;
    background: #fff;
    color: #172b4d;
    box-shadow: 0 14px 38px rgba(19, 39, 72, 0.16), 0 3px 8px rgba(19, 39, 72, 0.06);
    pointer-events: auto;
    isolation: isolate;
}

.alert-icon {
    display: grid;
    place-items: center;
    flex: 0 0 38px;
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: var(--alert-soft);
    color: var(--alert-accent);
    font-size: 19px;
}

.alert-content {
    min-width: 0;
    padding-top: 1px;
}

.alert-title {
    margin-bottom: 3px;
    font-size: 14px;
    font-weight: 800;
    line-height: 1.3;
}

.alert-message {
    color: #52627a;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.5;
    overflow-wrap: anywhere;
    white-space: pre-line;
}

.close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    display: grid;
    place-items: center;
    width: 30px;
    height: 30px;
    padding: 0;
    border: 0;
    border-radius: 9px;
    background: transparent;
    color: #7b8799;
    font-size: 15px;
    cursor: pointer;
    transition: background-color 0.18s ease, color 0.18s ease;
}

.close-btn:hover {
    background: #f1f5f9;
    color: #172b4d;
}

.close-btn:focus-visible {
    outline: 2px solid var(--alert-accent);
    outline-offset: 2px;
}

.alert-progress {
    position: absolute;
    right: 0;
    bottom: 0;
    left: 0;
    height: 3px;
    background: var(--alert-soft);
}

.alert-progress::after {
    display: block;
    width: 100%;
    height: 100%;
    background: var(--alert-accent);
    content: '';
    opacity: 0.55;
}

.alert-success {
    --alert-accent: #16845b;
    --alert-soft: #e8f7ef;
}

.alert-error {
    --alert-accent: #dc3545;
    --alert-soft: #fff0f1;
}

.alert-warning {
    --alert-accent: #c77708;
    --alert-soft: #fff6e5;
}

.alert-info {
    --alert-accent: #2563eb;
    --alert-soft: #eff6ff;
}

.alert-toast-enter-active,
.alert-toast-leave-active {
    transition: opacity 0.22s ease, transform 0.22s ease;
}

.alert-toast-enter-from,
.alert-toast-leave-to {
    opacity: 0;
    transform: translate3d(18px, -8px, 0) scale(0.98);
}

@media (max-width: 480px) {
    .alert-host {
        top: max(10px, env(safe-area-inset-top));
        right: 10px;
        width: calc(100vw - 20px);
    }

    .global-alert {
        gap: 10px;
        padding: 14px 42px 16px 12px;
        border-radius: 14px;
    }

    .alert-icon {
        flex-basis: 34px;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        font-size: 17px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .alert-toast-enter-active,
    .alert-toast-leave-active,
    .close-btn {
        transition: none;
    }
}
</style>
