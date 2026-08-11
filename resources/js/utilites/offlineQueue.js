// @/utilites/offlineQueue.js

const QUEUE_KEY = 'offline_sales_queue';

/**
 * Получить всю очередь из localStorage
 */
export function getQueue() {
    try {
        const raw = localStorage.getItem(QUEUE_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch (e) {
        console.error('Ошибка чтения очереди:', e);
        return [];
    }
}

/**
 * Сохранить очередь
 */
function saveQueue(queue) {
    localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
    // Оповещаем всех подписчиков об изменении очереди
    window.dispatchEvent(new CustomEvent('offline-queue-changed', {
        detail: { count: queue.length }
    }));
}

/**
 * Добавить заявку в очередь
 * @param {Object} formData - данные формы
 * @param {File|null} file - файл чека (будет сконвертирован в base64)
 * @param {boolean} isEdit - редактирование или создание
 * @returns {Promise<string>} - ID добавленного элемента
 */
export async function addToQueue(formData, file = null, isEdit = false) {
    const queue = getQueue();
    const id = `offline_${Date.now()}_${Math.random().toString(36).slice(2, 9)}`;

    let fileBase64 = null;
    if (file) {
        fileBase64 = await fileToBase64(file);
    }

    const entry = {
        id,
        formData: { ...formData },
        file: fileBase64,
        fileName: file?.name || null,
        fileType: file?.type || null,
        isEdit,
        createdAt: new Date().toISOString(),
        attempts: 0,
    };

    queue.push(entry);
    saveQueue(queue);

    return id;
}

/**
 * Удалить элемент из очереди
 */
export function removeFromQueue(id) {
    const queue = getQueue().filter(item => item.id !== id);
    saveQueue(queue);
}

/**
 * Инкрементировать счётчик попыток
 */
export function incrementAttempts(id) {
    const queue = getQueue();
    const item = queue.find(i => i.id === id);
    if (item) {
        item.attempts += 1;
        saveQueue(queue);
    }
}

/**
 * Конвертация File в base64
 */
function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

/**
 * Восстановить File из base64
 */
export function base64ToFile(base64, fileName, fileType) {
    if (!base64) return null;
    const byteString = atob(base64.split(',')[1]);
    const mimeString = base64.split(',')[0].split(':')[1].split(';')[0];
    const ab = new ArrayBuffer(byteString.length);
    const ia = new Uint8Array(ab);
    for (let i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    return new File([ab], fileName || 'receipt', { type: mimeString || fileType });
}

/**
 * Проверка доступности сети
 */
export function isOnline() {
    return navigator.onLine;
}
