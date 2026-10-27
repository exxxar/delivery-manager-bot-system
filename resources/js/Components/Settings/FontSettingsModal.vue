<!-- @/Components/Settings/FontSettingsModal.vue -->

<template>
    <div class="modal fade" id="fontSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content font-settings-modal">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-text-height text-primary me-2"></i>
                        Настройки отображения
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- 🔹 Размер шрифта -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="fontSizeRange" class="form-label mb-0 fw-semibold">
                                <i class="fa-solid fa-font me-1 text-primary"></i>
                                Размер шрифта
                            </label>
                            <span class="badge bg-primary">{{ localSettings.font_size }}px</span>
                        </div>
                        <input
                            type="range"
                            class="form-range"
                            id="fontSizeRange"
                            min="12"
                            max="32"
                            step="1"
                            v-model.number="localSettings.font_size"
                        />
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Мелкий (12)</span>
                            <span>Стандартный (16)</span>
                            <span>Крупный (32)</span>
                        </div>
                    </div>

                    <!-- 🔹 Семейство шрифта -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            <i class="fa-solid fa-font-awesome me-1 text-primary"></i>
                            Шрифт
                        </label>
                        <div class="row g-2">
                            <div
                                v-for="(font, key) in fontFamilies"
                                :key="key"
                                class="col-6"
                            >
                                <button
                                    type="button"
                                    class="font-option w-100 p-2 rounded border"
                                    :class="{
                                        'border-primary bg-primary-subtle': localSettings.font_family === key,
                                        'border-secondary': localSettings.font_family !== key
                                    }"
                                    :style="{ fontFamily: font.value }"
                                    @click="localSettings.font_family = key"
                                >
                                    <div class="small">
                                        <i v-if="localSettings.font_family === key" class="fa-solid fa-check-circle text-primary me-1"></i>
                                        {{ font.label }}
                                    </div>
                                    <div class="font-sample mt-1">Аа Бб Вв</div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 🔹 Межстрочный интервал -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="lineHeightRange" class="form-label mb-0 fw-semibold">
                                <i class="fa-solid fa-arrows-up-down me-1 text-primary"></i>
                                Межстрочный интервал
                            </label>
                            <span class="badge bg-primary">{{ localSettings.line_height.toFixed(1) }}</span>
                        </div>
                        <input
                            type="range"
                            class="form-range"
                            id="lineHeightRange"
                            min="1.2"
                            max="2.0"
                            step="0.1"
                            v-model.number="localSettings.line_height"
                        />
                    </div>

                    <!-- 🔹 Межбуквенный интервал -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label for="letterSpacingRange" class="form-label mb-0 fw-semibold">
                                <i class="fa-solid fa-text-width me-1 text-primary"></i>
                                Межбуквенный интервал
                            </label>
                            <span class="badge bg-primary">{{ localSettings.letter_spacing.toFixed(1) }}px</span>
                        </div>
                        <input
                            type="range"
                            class="form-range"
                            id="letterSpacingRange"
                            min="-0.5"
                            max="2"
                            step="0.1"
                            v-model.number="localSettings.letter_spacing"
                        />
                    </div>

                    <!-- 🔹 Высокий контраст -->
                    <div class="form-check form-switch mb-4">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            id="highContrastSwitch"
                            v-model="localSettings.high_contrast"
                        />
                        <label class="form-check-label" for="highContrastSwitch">
                            <i class="fa-solid fa-circle-half-stroke me-1"></i>
                            Высокий контраст
                        </label>
                    </div>

                    <!-- 🔹 Предпросмотр -->
                    <div class="preview-box border rounded p-3 bg-light">
                        <div class="small text-muted mb-1">Предпросмотр:</div>
                        <p
                            class="mb-0 preview-text"
                            :style="previewStyle"
                        >
                            Съешь же ещё этих мягких французских булок, да выпей чаю.
                            Быстрая коричневая лиса прыгает через ленивую собаку.
                            0123456789
                        </p>
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="resetToDefaults"
                        :disabled="settingsStore.loading"
                    >
                        <i class="fa-solid fa-rotate-left me-1"></i>
                        Сбросить
                    </button>

                    <div class="d-flex gap-2">
                        <button
                            type="button"
                            class="btn btn-light"
                            data-bs-dismiss="modal"
                        >
                            Отмена
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            @click="saveSettings"
                            :disabled="settingsStore.loading"
                        >
                            <span v-if="settingsStore.loading">
                                <i class="fa-solid fa-spinner fa-spin me-1"></i>
                                Сохранение...
                            </span>
                            <span v-else>
                                <i class="fa-solid fa-floppy-disk me-1"></i>
                                Сохранить
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { useUserSettingsStore, FONT_FAMILIES } from '@/stores/userSettings'
import { useModalStore } from '@/stores/utillites/useConfitmModalStore'

export default {
    name: 'FontSettingsModal',
    data() {
        return {
            settingsStore: useUserSettingsStore(),
            modalStore: useModalStore(),
            localSettings: {
                font_family: 'system',
                font_size: 16,
                line_height: 1.5,
                letter_spacing: 0,
                high_contrast: false,
            },
            fontFamilies: FONT_FAMILIES,
        }
    },
    computed: {
        previewStyle() {
            return {
                fontFamily: this.fontFamilies[this.localSettings.font_family]?.value,
                fontSize: `${this.localSettings.font_size}px`,
                lineHeight: this.localSettings.line_height,
                letterSpacing: `${this.localSettings.letter_spacing}px`,
            }
        },
    },
    methods: {
        /**
         * Загружает актуальные настройки в локальное состояние
         */
        loadCurrentSettings() {
            this.localSettings = { ...this.settingsStore.settings }
        },

        async saveSettings() {
            const success = await this.settingsStore.saveSettings(this.localSettings)
            if (success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('fontSettingsModal'))
                modal?.hide()
            }
        },

        resetToDefaults() {
            this.modalStore.open(
                'Сбросить все настройки отображения к значениям по умолчанию?',
                async () => {
                    await this.settingsStore.resetSettings()
                    this.loadCurrentSettings()
                    this.modalStore.close()
                },
                () => this.modalStore.close()
            )
        },
    },
    mounted() {
        // При открытии модалки загружаем актуальные настройки
        const modalEl = document.getElementById('fontSettingsModal')
        modalEl.addEventListener('show.bs.modal', () => {
            this.loadCurrentSettings()
        })
    },
}
</script>

<style scoped>
.font-settings-modal {
    overflow: hidden;
    border: 1px solid #e5eaf2;
    border-radius: 22px;
    color: #17243b;
    box-shadow: 0 24px 70px rgba(18, 35, 66, .2);
}
.font-settings-modal .modal-header {
    padding: 22px 24px;
    background: linear-gradient(135deg, #f8faff, #fff);
    border-bottom: 1px solid #e9eef6;
}
.font-settings-modal .modal-title {
    display: flex;
    align-items: center;
    color: #17243b;
    font-size: 1.08rem;
    font-weight: 800;
    letter-spacing: -.02em;
}
.font-settings-modal .modal-title i {
    display: grid;
    width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 12px;
    color: #2457d6 !important;
    background: #eaf0ff;
    font-size: 1rem;
}
.font-settings-modal .modal-body { padding: 24px; }
.font-settings-modal .form-label {
    color: #24334d;
    font-size: .9rem;
}
.font-settings-modal .form-label > i { color: #2457d6 !important; }
.font-settings-modal .badge.bg-primary {
    padding: 7px 10px;
    border-radius: 8px;
    background: #eaf0ff !important;
    color: #2457d6 !important;
    font-size: .78rem;
    font-weight: 800;
}
.font-settings-modal .form-range { cursor: pointer; accent-color: #2457d6; }
.font-settings-modal .form-range::-webkit-slider-thumb {
    background: #2457d6;
    box-shadow: 0 0 0 4px rgba(36, 87, 214, .12);
}
.font-settings-modal .form-range::-moz-range-thumb {
    background: #2457d6;
    box-shadow: 0 0 0 4px rgba(36, 87, 214, .12);
}
.font-settings-modal .font-option {
    min-height: 82px;
    cursor: pointer;
    border: 1px solid #e2e8f2 !important;
    border-radius: 13px !important;
    background: #fff;
    color: #24334d;
    transition: border-color .18s ease, background .18s ease, transform .18s ease, box-shadow .18s ease;
}
.font-settings-modal .font-option:hover {
    transform: translateY(-2px);
    border-color: #a9befa !important;
    box-shadow: 0 7px 18px rgba(30, 64, 175, .08);
}
.font-settings-modal .font-option.border-primary {
    border-color: #2457d6 !important;
    background: #f1f5ff !important;
    box-shadow: 0 0 0 2px rgba(36, 87, 214, .08);
}
.font-settings-modal .font-option .small { font-weight: 750; }
.font-settings-modal .font-sample {
    margin-top: 7px;
    color: #53627a;
    font-size: 18px;
}
.font-settings-modal .form-check.form-switch {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0 !important;
    padding: 14px 16px 14px 3.1rem;
    border: 1px solid #e2e8f2;
    border-radius: 13px;
    background: #fbfcff;
}
.font-settings-modal .form-check-input {
    cursor: pointer;
    box-shadow: none;
}
.font-settings-modal .form-check-input:checked {
    border-color: #2457d6;
    background-color: #2457d6;
}
.font-settings-modal .form-check-label {
    color: #24334d;
    font-weight: 700;
}
.font-settings-modal .preview-box {
    padding: 17px !important;
    border: 1px solid #dfe7f4 !important;
    border-radius: 15px !important;
    background: linear-gradient(135deg, #f7f9ff, #f1f5fb) !important;
}
.font-settings-modal .preview-box > .small {
    margin-bottom: 10px !important;
    color: #2457d6 !important;
    font-weight: 800;
    letter-spacing: .03em;
    text-transform: uppercase;
}
.font-settings-modal .preview-text {
    color: #26354d;
    overflow-wrap: anywhere;
    transition: all .2s ease;
}
.font-settings-modal .modal-footer {
    gap: 10px;
    padding: 16px 24px;
    background: #fbfcff;
    border-top: 1px solid #e9eef6;
}
.font-settings-modal .modal-footer .btn {
    min-height: 42px;
    padding: 9px 14px;
    border-radius: 11px;
    font-size: .86rem;
    font-weight: 750;
    transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
}
.font-settings-modal .modal-footer .btn:hover { transform: translateY(-1px); }
.font-settings-modal .btn-primary {
    border-color: #2457d6;
    background: linear-gradient(135deg, #3269ed, #204fc7);
    box-shadow: 0 5px 13px rgba(36, 87, 214, .18);
}
.font-settings-modal .btn-primary:hover {
    border-color: #173b91;
    background: linear-gradient(135deg, #2457d6, #173b91);
}
.font-settings-modal .btn-outline-secondary {
    color: #59677c;
    border-color: #dce3ed;
    background: #fff;
}
.font-settings-modal .btn-light {
    color: #53627a;
    border: 1px solid #e0e6ef;
    background: #fff;
}
@media (max-width: 575.98px) {
    .font-settings-modal { border-radius: 18px; }
    .font-settings-modal .modal-header { padding: 17px 18px; }
    .font-settings-modal .modal-body { padding: 18px; }
    .font-settings-modal .modal-footer {
        align-items: stretch;
        flex-wrap: wrap;
        padding: 14px 18px;
    }
    .font-settings-modal .modal-footer > .btn { flex: 1 1 100%; }
    .font-settings-modal .modal-footer > .d-flex { width: 100%; }
    .font-settings-modal .modal-footer > .d-flex .btn { flex: 1; }
}
</style>
