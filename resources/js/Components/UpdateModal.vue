<template>
    <div
        class="modal fade update-modal"
        id="updateModal"
        tabindex="-1"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
        aria-labelledby="updateModalTitle"
        aria-describedby="updateModalDescription"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="update-modal__topline"></div>

                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="update-modal__title-icon">
                            <i class="fa-solid fa-cloud-arrow-down"></i>
                        </div>
                        <div>
                            <span class="update-modal__eyebrow">ОБНОВЛЕНИЕ СИСТЕМЫ</span>
                            <h5 id="updateModalTitle" class="modal-title">Доступно обновление</h5>
                        </div>
                    </div>
                </div>

                <div class="modal-body pt-4">
                    <div class="update-modal__hero-icon" aria-hidden="true">
                        <i class="fa-solid fa-percent"></i>
                    </div>

                    <p id="updateModalDescription" class="update-modal__message">
                        {{ message }}
                    </p>

                    <div v-if="forceUpdate" class="update-modal__required" role="alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>
                            <strong>Обновление обязательно</strong>
                            <p>Работа в старой версии невозможна.</p>
                        </div>
                    </div>

                    <div class="update-modal__versions">
                        <div class="update-modal__version-row">
                            <span><i class="fa-regular fa-circle-dot me-2"></i>Текущая версия</span>
                            <code>{{ localVersion }}</code>
                        </div>
                        <div class="update-modal__version-row update-modal__version-row--new">
                            <span><i class="fa-solid fa-sparkles me-2"></i>Новая версия</span>
                            <code>{{ newVersion }}</code>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button
                        type="button"
                        class="btn update-modal__action w-100"
                        @click="handleUpdate"
                        :disabled="updating"
                    >
                        <template v-if="updating">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Обновление…
                        </template>
                        <template v-else>
                            <i class="fa-solid fa-rotate me-2"></i>
                            Обновить сейчас
                            <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </template>
                    </button>
                    <p class="update-modal__note mb-0">
                        <i class="fa-solid fa-shield-halved me-1"></i>
                        Пожалуйста, не закрывайте приложение во время обновления
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { forceUpdate, setLocalVersion } from '@/utilites/versionCheck.js'

export default {
    name: 'UpdateModal',
    props: {
        newVersion: { type: String, required: true },
        localVersion: { type: String, required: true },
        message: {
            type: String,
            default: 'Доступна новая версия приложения'
        },
        forceUpdate: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            updating: false
        }
    },
    methods: {
        async handleUpdate() {
            if (this.updating) return
            this.updating = true
            // Сохраняем новую версию до перезагрузки приложения.
            setLocalVersion(this.newVersion)
            await forceUpdate()
        }
    }
}
</script>

<style scoped>
.update-modal .modal-dialog {
    width: calc(100% - 1.5rem);
    max-width: 460px;
}

.update-modal .modal-content {
    position: relative;
    overflow: hidden;
    border: 1px solid #e5eaf3;
    border-radius: 24px;
    background: #fff;
    box-shadow: 0 24px 70px rgba(15, 35, 75, 0.22);
}

.update-modal__topline {
    height: 5px;
    background: linear-gradient(90deg, #174ea6 0%, #3478f6 65%, #e23b4b 100%);
}

.update-modal .modal-header,
.update-modal .modal-body,
.update-modal .modal-footer {
    padding-right: 1.5rem;
    padding-left: 1.5rem;
}

.update-modal .modal-header {
    padding-top: 1.5rem;
}

.update-modal__title-icon {
    display: grid;
    flex: 0 0 48px;
    width: 48px;
    height: 48px;
    place-items: center;
    border-radius: 15px;
    background: #eaf1ff;
    color: #245fc2;
    font-size: 1.25rem;
}

.update-modal__eyebrow {
    display: block;
    margin-bottom: 0.25rem;
    color: #64748b;
    font-size: 0.65rem;
    font-weight: 800;
    letter-spacing: 0.12em;
}

.update-modal .modal-title {
    color: #14233d;
    font-size: 1.2rem;
    font-weight: 750;
}

.update-modal__hero-icon {
    display: grid;
    width: 76px;
    height: 76px;
    margin: 0 auto 1rem;
    place-items: center;
    border: 1px solid #dce8ff;
    border-radius: 24px;
    background: linear-gradient(145deg, #f4f7ff, #e6efff);
    color: #2866ce;
    font-size: 2rem;
}

.update-modal__message {
    max-width: 340px;
    margin: 0 auto 1.5rem;
    color: #43516a;
    font-size: 1.05rem;
    line-height: 1.6;
    text-align: center;
}

.update-modal__required {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 1rem;
    padding: 1rem;
    border: 1px solid #f4d3a0;
    border-radius: 14px;
    background: #fff8e9;
    color: #9a4d0a;
}

.update-modal__required > i {
    margin-top: 0.15rem;
    font-size: 1.15rem;
}

.update-modal__required strong {
    display: block;
    margin-bottom: 0.2rem;
    font-size: 0.9rem;
}

.update-modal__required p {
    margin: 0;
    color: #815d35;
    font-size: 0.82rem;
}

.update-modal__versions {
    overflow: hidden;
    border: 1px solid #e7ecf4;
    border-radius: 14px;
    background: #f8faff;
}

.update-modal__version-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem 1rem;
    color: #66748a;
    font-size: 0.83rem;
}

.update-modal__version-row + .update-modal__version-row {
    border-top: 1px solid #e7ecf4;
}

.update-modal__version-row span {
    min-width: 0;
}

.update-modal__version-row code {
    padding: 0.25rem 0.5rem;
    border-radius: 7px;
    background: #e9eef7;
    color: #263b5c;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
}

.update-modal__version-row--new span {
    color: #174ea6;
    font-weight: 700;
}

.update-modal__version-row--new code {
    background: #e4edff;
    color: #174ea6;
}

.update-modal .modal-footer {
    padding-bottom: 1.5rem;
}

.update-modal__action {
    display: flex;
    min-height: 54px;
    align-items: center;
    padding: 0.9rem 1.1rem;
    border: 0;
    border-radius: 14px;
    background: linear-gradient(105deg, #174ea6, #3478f6);
    box-shadow: 0 8px 18px rgba(39, 100, 207, 0.23);
    color: #fff;
    font-weight: 700;
    transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
}

.update-modal__action:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 11px 24px rgba(39, 100, 207, 0.3);
    color: #fff;
}

.update-modal__action:disabled {
    cursor: wait;
    opacity: 0.75;
}

.update-modal__note {
    margin-top: 0.9rem;
    color: #7b8799;
    font-size: 0.72rem;
    text-align: center;
}

@media (max-width: 420px) {
    .update-modal .modal-header,
    .update-modal .modal-body,
    .update-modal .modal-footer {
        padding-right: 1rem;
        padding-left: 1rem;
    }

    .update-modal__title-icon {
        flex-basis: 42px;
        width: 42px;
        height: 42px;
    }

    .update-modal .modal-title {
        font-size: 1.05rem;
    }

    .update-modal__version-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 0.35rem;
    }
}
</style>
