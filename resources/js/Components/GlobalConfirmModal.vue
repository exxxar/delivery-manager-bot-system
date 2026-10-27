<script setup>
import { computed } from 'vue'
import { useModalStore } from '../stores/utillites/useConfitmModalStore'

const modal = useModalStore()

const messageText = computed(() => modal.message || '')
</script>

<template>
    <Teleport to="body">
        <Transition name="confirm-modal">
            <div
                v-if="modal.show"
                class="confirm-overlay"
                role="presentation"
                @click.self="modal.close()"
            >
                <section
                    class="confirm-dialog"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="confirm-modal-title"
                >
                    <header class="confirm-header">
                        <div class="confirm-heading-icon" aria-hidden="true">
                            <i class="fa-solid fa-circle-question"></i>
                        </div>
                        <div class="confirm-heading-copy">
                            <span class="confirm-eyebrow">Требуется ваше решение</span>
                            <h2 id="confirm-modal-title" class="confirm-title">Подтверждение действия</h2>
                        </div>
                        <button
                            type="button"
                            class="confirm-close"
                            aria-label="Закрыть окно"
                            @click="modal.close()"
                        >
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </header>

                    <div class="confirm-body">
                        <p class="confirm-message" v-html="messageText"></p>
                    </div>

                    <footer class="confirm-footer">
                        <button type="button" class="confirm-button confirm-button--secondary" @click="modal.reject()">
                            <i class="fa-solid fa-xmark me-2"></i>
                            Отмена
                        </button>
                        <button type="button" class="confirm-button confirm-button--danger" @click="modal.confirm()">
                            <i class="fa-solid fa-check me-2"></i>
                            Подтвердить
                        </button>
                    </footer>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.confirm-overlay {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(13, 30, 57, 0.58);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
}

.confirm-dialog {
    width: min(100%, 470px);
    overflow: hidden;
    color: #172b4d;
    background: #fff;
    border: 1px solid rgba(218, 227, 240, 0.9);
    border-radius: 22px;
    box-shadow: 0 24px 80px rgba(9, 28, 58, 0.28);
}

.confirm-header {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 1.35rem 1.35rem 1.15rem;
    border-bottom: 1px solid #edf1f7;
    background: linear-gradient(135deg, #f7faff 0%, #fff 75%);
}

.confirm-heading-icon {
    display: grid;
    flex: 0 0 48px;
    width: 48px;
    height: 48px;
    place-items: center;
    color: #1765c0;
    font-size: 1.35rem;
    background: #eaf3ff;
    border: 1px solid #d9eaff;
    border-radius: 15px;
}

.confirm-heading-copy {
    min-width: 0;
    flex: 1;
}

.confirm-eyebrow {
    display: block;
    margin-bottom: 0.2rem;
    color: #6b7c96;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
}

.confirm-title {
    margin: 0;
    color: #142744;
    font-size: clamp(1.05rem, 3vw, 1.25rem);
    font-weight: 750;
    line-height: 1.3;
}

.confirm-close {
    display: grid;
    flex: 0 0 36px;
    width: 36px;
    height: 36px;
    place-items: center;
    color: #687991;
    background: transparent;
    border: 0;
    border-radius: 11px;
    font-size: 1.15rem;
    transition: color 0.2s ease, background 0.2s ease;
}

.confirm-close:hover {
    color: #c6283d;
    background: #fff0f2;
}

.confirm-body {
    padding: 1.5rem 1.5rem 1.35rem;
}

.confirm-message {
    margin: 0;
    color: #40516b;
    font-size: 1rem;
    line-height: 1.65;
    overflow-wrap: anywhere;
}

.confirm-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.7rem;
    padding: 1rem 1.35rem 1.35rem;
    background: #fbfcfe;
    border-top: 1px solid #edf1f7;
}

.confirm-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0.7rem 1rem;
    border: 1px solid transparent;
    border-radius: 12px;
    font: inherit;
    font-size: 0.92rem;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}

.confirm-button:hover {
    transform: translateY(-1px);
}

.confirm-button:focus-visible,
.confirm-close:focus-visible {
    outline: 3px solid rgba(23, 101, 192, 0.35);
    outline-offset: 2px;
}

.confirm-button--secondary {
    color: #3e506b;
    background: #fff;
    border-color: #d9e1ed;
}

.confirm-button--secondary:hover {
    background: #f0f4fa;
}

.confirm-button--danger {
    color: #fff;
    background: #d9364f;
    box-shadow: 0 5px 14px rgba(217, 54, 79, 0.2);
}

.confirm-button--danger:hover {
    background: #bd263e;
    box-shadow: 0 7px 18px rgba(217, 54, 79, 0.28);
}

.confirm-modal-enter-active,
.confirm-modal-leave-active {
    transition: opacity 0.2s ease;
}

.confirm-modal-enter-active .confirm-dialog,
.confirm-modal-leave-active .confirm-dialog {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.confirm-modal-enter-from,
.confirm-modal-leave-to {
    opacity: 0;
}

.confirm-modal-enter-from .confirm-dialog,
.confirm-modal-leave-to .confirm-dialog {
    opacity: 0;
    transform: translateY(12px) scale(0.98);
}

@media (max-width: 480px) {
    .confirm-overlay {
        align-items: flex-end;
        padding: 0.65rem;
    }

    .confirm-dialog {
        width: 100%;
        border-radius: 20px;
    }

    .confirm-header {
        padding: 1.1rem 1rem 1rem;
    }

    .confirm-body {
        padding: 1.25rem 1rem;
    }

    .confirm-footer {
        flex-direction: column-reverse;
        padding: 0.9rem 1rem 1rem;
    }

    .confirm-button {
        width: 100%;
    }
}
</style>
