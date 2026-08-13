/**
 * Универсальный детектор онлайн-статуса
 * Работает на десктопе, мобильных, Telegram WebApp
 */

const PING_URL = '/api/version' // лёгкий эндпоинт
const PING_INTERVAL = 15000    // пинг каждые 15 секунд
const PING_TIMEOUT = 5000      // таймаут 5 секунд

let pingTimer = null
let currentState = navigator.onLine
let listeners = []

/**
 * Подписаться на изменения статуса
 * @param {Function} callback - (isOnline: boolean) => void
 * @returns {Function} - функция отписки
 */
export function onStatusChange(callback) {
    listeners.push(callback)
    // Сразу сообщаем текущее состояние
    callback(currentState)
    return () => {
        listeners = listeners.filter(l => l !== callback)
    }
}

function setState(isOnline) {
    if (currentState === isOnline) return
    currentState = isOnline
    listeners.forEach(cb => cb(isOnline))
    // Глобальное событие для других частей приложения
    window.dispatchEvent(new CustomEvent('network-status-changed', {
        detail: { isOnline }
    }))
}

/**
 * Активный пинг сервера
 */
async function ping() {
    // Не пингуем, если вкладка неактивна (экономим батарею)
    if (document.visibilityState === 'hidden') return

    try {
        const controller = new AbortController()
        const timeoutId = setTimeout(() => controller.abort(), PING_TIMEOUT)

        const response = await fetch(PING_URL + '?t=' + Date.now(), {
            method: 'GET',
            cache: 'no-store',
            signal: controller.signal,
        })

        clearTimeout(timeoutId)

        if (response.ok) {
            setState(true)
        } else {
            setState(false)
        }
    } catch (e) {
        // fetch упал — нет сети
        setState(false)
    }
}

/**
 * Запустить мониторинг
 */
export function startMonitoring() {
    // Нативные события (работают на десктопе)
    window.addEventListener('online', () => setState(true))
    window.addEventListener('offline', () => setState(false))

    // Активный пинг (работает везде)
    ping() // сразу первая проверка
    pingTimer = setInterval(ping, PING_INTERVAL)

    // При возвращении на вкладку — сразу пингуем
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            ping()
        }
    })
}

/**
 * Остановить мониторинг
 */
export function stopMonitoring() {
    if (pingTimer) {
        clearInterval(pingTimer)
        pingTimer = null
    }
}

/**
 * Принудительно пометить как офлайн (вызывается из axios при ошибке)
 */
export function markOffline() {
    setState(false)
}

/**
 * Принудительно пометить как онлайн
 */
export function markOnline() {
    setState(true)
}

/**
 * Текущее состояние
 */
export function isOnline() {
    return currentState
}
