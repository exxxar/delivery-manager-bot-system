// sw.js

const CACHE_NAME = 'myprocent-cache-v3.1.2';
const STATIC_CACHE = `${CACHE_NAME}-static`;
const DYNAMIC_CACHE = `${CACHE_NAME}-dynamic`;
const IMAGES_CACHE = `${CACHE_NAME}-images`;

// Статические ассеты (без glob-паттернов!)
// Vite генерирует файлы с хешами, поэтому точные пути нужно обновлять при сборке
const PRECACHE_ASSETS = [
    '/pwa/',
    '/pwa/index.html',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/themes/theme8.bootstrap.min.css',
];

// Пути, которые НЕЛЬЗЯ кешировать
const NO_CACHE_PATTERNS = [
    '/api/',
    '/broadcasting/',
    '/sanctum/',
    '/telescope/',
];

// ==================== УСТАНОВКА ====================
self.addEventListener('install', event => {
    console.log('[SW] Installing...');

    // Позволяем новому SW сразу стать активным
    self.skipWaiting();

    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('[SW] Precaching app shell');
                // addAll может упасть, если хоть один файл недоступен
                // Поэтому кешируем по одному с обработкой ошибок
                return Promise.allSettled(
                    PRECACHE_ASSETS.map(url =>
                        cache.add(url).catch(err => {
                            console.warn(`[SW] Failed to precache: ${url}`, err);
                        })
                    )
                );
            })
            .catch(err => {
                console.error('[SW] Precache failed:', err);
            })
    );
});

// ==================== АКТИВАЦИЯ ====================
self.addEventListener('activate', event => {
    console.log('[SW] Activating...');

    // Берём контроль над всеми открытыми вкладками сразу
    self.clients.claim();

    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => !name.startsWith(CACHE_NAME.split('-v')[0]))
                    .map(name => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        })
    );
});

// ==================== ПЕРЕХВАТ ЗАПРОСОВ ====================
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);

    // Кешируем только GET-запросы
    if (request.method !== 'GET') {
        return;
    }

    // Игнорируем запросы к другим доменам (Telegram API, аналитика и т.д.)
    if (url.origin !== location.origin) {
        return;
    }

    // Игнорируем пути, которые нельзя кешировать
    if (shouldSkipCache(url.pathname)) {
        event.respondWith(networkOnly(request));
        return;
    }

    // Навигация (SPA) — Network First с fallback на index.html
    if (request.mode === 'navigate') {
        event.respondWith(navigateWithFallback(request));
        return;
    }

    // Изображения — Stale While Revalidate
    if (request.destination === 'image') {
        event.respondWith(staleWhileRevalidate(request, IMAGES_CACHE));
        return;
    }

    // Статика Vite (CSS, JS с хешами в именах) — Cache First
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request, STATIC_CACHE));
        return;
    }

    // Остальное — Network First
    event.respondWith(networkFirst(request, DYNAMIC_CACHE));
});

// ==================== СТРАТЕГИИ КЕШИРОВАНИЯ ====================

/**
 * Cache First — сначала кеш, если нет — сеть
 * Идеально для статики с хешами в именах (Vite ассеты)
 */
async function cacheFirst(request, cacheName) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('[SW] Cache-first failed:', error);
        return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
    }
}

/**
 * Network First — сначала сеть, если нет — кеш
 * Идеально для HTML и API
 */
async function networkFirst(request, cacheName) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(cacheName);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        console.error('[SW] Network-first failed:', error);
        return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
    }
}

/**
 * Stale While Revalidate — сразу отдаём кеш, в фоне обновляем
 * Идеально для изображений
 */
async function staleWhileRevalidate(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    const networkPromise = fetch(request).then(networkResponse => {
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    }).catch(() => cached);

    return cached || await networkPromise;
}

/**
 * Network Only — всегда идём в сеть, ничего не кешируем
 * Для API и некешируемых путей
 */
async function networkOnly(request) {
    try {
        return await fetch(request);
    } catch (error) {
        return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
    }
}

/**
 * Навигация с fallback на index.html для SPA
 */
async function navigateWithFallback(request) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) return cached;

        // Fallback на app shell
        const appShell = await caches.match('/pwa/index.html') ||
            await caches.match('/pwa/');
        if (appShell) return appShell;

        return new Response('Offline', {
            status: 503,
            statusText: 'Service Unavailable',
            headers: { 'Content-Type': 'text/plain' }
        });
    }
}

// ==================== УТИЛИТЫ ====================

/**
 * Проверка, нужно ли пропустить кеширование
 */
function shouldSkipCache(pathname) {
    return NO_CACHE_PATTERNS.some(pattern => pathname.includes(pattern));
}

/**
 * Проверка, является ли запрос статическим ассетом
 */
function isStaticAsset(pathname) {
    return (
        pathname.startsWith('/build/assets/') ||
        pathname.startsWith('/icons/') ||
        pathname.startsWith('/public/icons/') ||
        pathname.startsWith('/themes/') ||
        pathname.endsWith('.css') ||
        pathname.endsWith('.js') ||
        pathname.endsWith('.woff') ||
        pathname.endsWith('.woff2') ||
        pathname.endsWith('.ttf')
    );
}

// ==================== СООБЩЕНИЯ ОТ КЛИЕНТА ====================

/**
 * Принудительное обновление кеша из приложения:
 * navigator.serviceWorker.controller?.postMessage({ type: 'SKIP_WAITING' });
 */
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then(keys => Promise.all(
                keys.map(key => caches.delete(key))
            ))
        );
    }
});

console.log('[SW] Service Worker loaded');
