import { v4 as uuidv4 } from 'uuid'

/**
 * Генерирует уникальный токен идемпотентности
 */
export function generateIdempotencyKey() {
    return uuidv4().replace(/-/g, '')
}

/**
 * Сохраняет ключи для дедупликации на фронте
 * (на случай если два компонента одновременно отправляют один и тот же запрос)
 */
const recentKeys = new Map()
const KEY_TTL = 5000 // 5 секунд

export function getRecentKey(actionId) {
    const record = recentKeys.get(actionId)
    if (record && Date.now() - record.timestamp < KEY_TTL) {
        return record.key
    }
    recentKeys.delete(actionId)
    return null
}

export function setRecentKey(actionId, key) {
    recentKeys.set(actionId, { key, timestamp: Date.now() })
}
