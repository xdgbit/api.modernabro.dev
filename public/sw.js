const CACHE_NAME = 'sakna-static-v1'
const OFFLINE_URL = '/offline.html'

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
              .then(cache => cache.add(OFFLINE_URL))
    )

    self.skipWaiting()
})

self.addEventListener('activate', event => {
    event.waitUntil(
        Promise.all([
            caches.keys().then(keys =>
                Promise.all(
                    keys
                    .filter(key =>
                        key.startsWith('sakna-') &&
                        key !== CACHE_NAME
                    )
                    .map(key => caches.delete(key))
                )
            ),

            self.clients.claim()
        ])
    )
})

self.addEventListener('fetch', event => {
    const request = event.request

    if (request.method !== 'GET') return

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(async () => {
                const cachedPage = await caches.match(OFFLINE_URL)

                return cachedPage || new Response(
                    'اتصال اینترنت برقرار نیست',
                    {
                        status: 503,
                        headers: {
                            'Content-Type': 'text/plain; charset=utf-8'
                        }
                    }
                )
            })
        )
    }
})