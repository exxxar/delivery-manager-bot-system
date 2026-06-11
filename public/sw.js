
const CACHE_NAME = 'myprocent-cache-v1'


const ASSETS = ['/', '/css/app.css', '/js/app.js']


self.addEventListener('install', event => {
    console.log("test")
    self.skipWaiting()
})


self.addEventListener('activate', event => {
    event.waitUntil(self.clients.claim())
})


self.addEventListener('push', event => {
    let data = {}

    try {
        data = event.data ? event.data.json() : {}
    } catch (e) {
        data = { title: 'Новое уведомление', body: event.data.text() }
    }

    const title = data.title || 'Новое уведомление'
    const options = {
        body: data.body || '',
        icon: data.icon || '/icons/icon-192.png',
        badge: data.badge || '/icons/badge.png',
        data: {
            url: data.url || '/', // куда открыть при клике
            ...data
        }
    }

    event.waitUntil(
        self.registration.showNotification(title, options)
    )
})


self.addEventListener('notificationclick', event => {
    event.notification.close()

    const url = event.notification.data && event.notification.data.url
        ? event.notification.data.url
        : '/'

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(windowClients => {
                for (const client of windowClients) {
                    if (client.url.includes(url) && 'focus' in client) {
                        return client.focus()
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(url)
                }
            })
    )
})

self.addEventListener('message', event => {
    // if (event.data && event.data.type === 'PING') {
    //     event.ports[0].postMessage({ ok: true })
    // }
})

// self.addEventListener('fetch', event => {
//     event.respondWith(
//         caches.match(event.request).then(response => {
//             return response || fetch(event.request)
//         })
//     )
// })
