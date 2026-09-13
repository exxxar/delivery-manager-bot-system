<!-- @/Components/Settings/FontSettingsModal.vue -->

<template>
    <div class="modal fade" id="fontSettingsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
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
.font-option {
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.font-option:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.font-sample {
    font-size: 18px;
    color: #495057;
}

.preview-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.preview-text {
    transition: all 0.3s ease;
}
</style>
