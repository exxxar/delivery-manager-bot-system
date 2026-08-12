// @/utilites/versionCheck.js

const VERSION_KEY = 'app_version'

export function getLocalVersion() {
    return localStorage.getItem(VERSION_KEY) || null
}

export function setLocalVersion(version) {
    localStorage.setItem(VERSION_KEY, version)
}

/**
 * Принудительное обновление приложения
 */
export async function forceUpdate() {
    // 1. Говорим Service Worker'у очиститься
    if (navigator.serviceWorker?.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'CLEAR_CACHE' })
    }

    // 2. Регистрируем новый SW (если был зарегистрирован)
    if ('serviceWorker' in navigator) {
        try {
            const registration = await navigator.serviceWorker.getRegistration()
            if (registration) {
                await registration.update()
            }
        } catch (e) {
            console.warn('SW update failed:', e)
        }
    }

    // 3. Очищаем localStorage (кроме критичных данных)
    const keysToKeep = ['offline_sales_queue', 'auth_token', 'app_version']
    const preserved = {}
    keysToKeep.forEach(key => {
        const value = localStorage.getItem(key)
        if (value) preserved[key] = value
    })
    localStorage.clear()
    keysToKeep.forEach(key => {
        if (preserved[key]) localStorage.setItem(key, preserved[key])
    })

    // 4. Ждём, пока SW получит сигнал и очистится
    await new Promise(r => setTimeout(r, 300))

    // 5. Перезагружаем страницу с жёстким обходом кеша браузера
    window.location.href = window.location.pathname + '?v=' + Date.now()
}
