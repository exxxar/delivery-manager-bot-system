// @/stores/userSettings.js

import { defineStore } from 'pinia'
import axios from 'axios'
import { useAlertStore } from '@/stores/utillites/useAlertStore'

const DEFAULT_SETTINGS = {
    font_family: 'system',
    font_size: 16,
    line_height: 1.5,
    letter_spacing: 0,
    high_contrast: false,
}

// Карта шрифтов
export const FONT_FAMILIES = {
    system: {
        label: 'Системный',
        value: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
    },
    sans: {
        label: 'Без засечек',
        value: 'Arial, Helvetica, "Liberation Sans", sans-serif',
    },
    serif: {
        label: 'С засечками',
        value: 'Georgia, "Times New Roman", "Liberation Serif", serif',
    },
    mono: {
        label: 'Моноширинный',
        value: '"Courier New", "Liberation Mono", Consolas, monospace',
    },
}

export const useUserSettingsStore = defineStore('userSettings', {
    state: () => ({
        settings: { ...DEFAULT_SETTINGS },
        loading: false,
        loaded: false,
    }),

    getters: {
        /**
         * Готовый CSS-шрифт для применения
         */
        fontFamilyCss() {
            return FONT_FAMILIES[this.settings.font_family]?.value || FONT_FAMILIES.system.value
        },
    },

    actions: {
        /**
         * Загрузить настройки с сервера
         */
        async fetchSettings() {
            this.loading = true
            try {
                const { data } = await axios.get('/user/font-settings')
                this.settings = { ...DEFAULT_SETTINGS, ...(data.settings || {}) }
                this.loaded = true
                this.applySettings()
            } catch (e) {
                console.error('Ошибка загрузки настроек:', e)
                // Применяем дефолты, если не удалось загрузить
                this.settings = { ...DEFAULT_SETTINGS }
                this.applySettings()
            } finally {
                this.loading = false
            }
        },

        /**
         * Сохранить настройки на сервере
         */
        async saveSettings(newSettings) {
            this.loading = true
            try {
                const { data } = await axios.post('/api/user/font-settings', newSettings)
                this.settings = data.settings
                this.applySettings()
                useAlertStore().show('Настройки сохранены', 'success')
                return true
            } catch (e) {
                console.error('Ошибка сохранения настроек:', e)
                useAlertStore().show(
                    e?.response?.data?.message || 'Не удалось сохранить настройки',
                    'error'
                )
                return false
            } finally {
                this.loading = false
            }
        },

        /**
         * Сбросить к дефолтным
         */
        async resetSettings() {
            this.loading = true
            try {
                const { data } = await axios.post('/api/user/font-settings/reset')
                this.settings = data.settings
                this.applySettings()
                useAlertStore().show('Настройки сброшены', 'success')
            } catch (e) {
                console.error('Ошибка сброса настроек:', e)
                useAlertStore().show('Не удалось сбросить настройки', 'error')
            } finally {
                this.loading = false
            }
        },

        /**
         * Применить настройки через CSS-переменные
         */
        applySettings() {
            const root = document.documentElement

            root.style.setProperty('--user-font-family', this.fontFamilyCss)
            root.style.setProperty('--user-font-size', `${this.settings.font_size}px`)
            root.style.setProperty('--user-line-height', this.settings.line_height)
            root.style.setProperty('--user-letter-spacing', `${this.settings.letter_spacing}px`)

            // Режим высокого контраста
            document.body.classList.toggle('high-contrast', this.settings.high_contrast)
        },
    },
})
