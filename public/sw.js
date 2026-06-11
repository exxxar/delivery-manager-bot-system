
const CACHE_NAME = 'myprocent-cache-v1'


const ASSETS = [
    '/pwa/',
    '/icons/icon-192.png',
    '/public/icons/icon-192.png',
    '/icons/icon-512.png',
    '/public/icons/icon-512.png',
    '/themes/theme8.bootstrap.min.css',
    '/',
    '/build/assets/*.css',
    '/build/assets/*.js'
]

// Установка SW
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS))
    );
});

// Активация
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
            )
        )
    );
});

// Интерсепт запросов
self.addEventListener('fetch', event => {
    const req = event.request;

    // SPA fallback для маршрутов Vue Router
    if (req.mode === 'navigate') {
        event.respondWith(
            fetch(req).catch(() => caches.match('/pwa/index.html'))
        );
        return;
    }

    // Cache-first для статики
    event.respondWith(
        caches.match(req).then(cached =>
            cached ||
            fetch(req).then(res => {
                // Кешируем новые файлы
                return caches.open(CACHE_NAME).then(cache => {
                    cache.put(req, res.clone());
                    return res;
                });
            })
        )
    );
});
