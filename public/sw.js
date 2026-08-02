// ─────────────────────────────────────────────────────────────────────
//  SERVICE WORKER - CRM Taller Celulares (PWA)
//  Estrategia: Cache-first para assets estáticos
//              Network-first para navegación (datos siempre frescos)
// ─────────────────────────────────────────────────────────────────────

const CACHE_VERSION = 'crm-pwa-v2';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;

// Assets que se cachean al instalar (offline shell)
const PRECACHE_ASSETS = [
    '/',
    '/login',
    '/offline.html',
    // Bootstrap
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    // Font Awesome
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    // Google Fonts
    'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap',
    // Chart.js
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js'
];

// ─── INSTALL: Precargar shell de la app ─────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                // No bloquear si algún asset falla
                return Promise.allSettled(
                    PRECACHE_ASSETS.map(url => cache.add(url))
                );
            })
            .then(() => self.skipWaiting())
    );
});

// ─── ACTIVATE: Limpiar caches viejos ─────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => {
                return Promise.all(
                    keys.filter(key => !key.startsWith(CACHE_VERSION))
                        .map(key => caches.delete(key))
                );
            })
            .then(() => self.clients.claim())
    );
});

// ─── FETCH: Estrategia de caché ─────────────────────────────────────
self.addEventListener('fetch', (event) => {
    const { request } = event;

    // Solo manejar GET
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // No cachear manifest ni iconos dinámicos (siempre red fresca)
    if (url.pathname === '/manifest.json' || url.pathname.startsWith('/pwa/icon/')) {
        event.respondWith(fetch(request));
        return;
    }

    // No cachear rutas API (siempre red fresca)
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request).catch(() => {
                return new Response(JSON.stringify({ error: 'Sin conexión' }), {
                    status: 503,
                    headers: { 'Content-Type': 'application/json' }
                });
            })
        );
        return;
    }

    // Navegación: Network-first con fallback a cache/offline
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Guardar en runtime cache
                    const clone = response.clone();
                    caches.open(RUNTIME_CACHE).then(cache => cache.put(request, clone));
                    return response;
                })
                .catch(() => {
                    return caches.match(request).then(cached => {
                        return cached || caches.match('/offline.html').then(offline => {
                            return offline || new Response('Sin conexión', {
                                status: 503,
                                headers: { 'Content-Type': 'text/html' }
                            });
                        });
                    });
                })
        );
        return;
    }

    // Assets (CSS, JS, imágenes, fuentes): Cache-first
    if (
        request.destination === 'style' ||
        request.destination === 'script' ||
        request.destination === 'font' ||
        request.destination === 'image'
    ) {
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) {
                    // Actualizar en background (stale-while-revalidate)
                    fetch(request).then((response) => {
                        if (response.ok) {
                            caches.open(RUNTIME_CACHE).then(cache => cache.put(request, response));
                        }
                    }).catch(() => {});
                    return cached;
                }

                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(RUNTIME_CACHE).then(cache => cache.put(request, clone));
                    }
                    return response;
                }).catch(() => caches.match('/offline.html'));
            })
        );
        return;
    }

    // Otros: Network-first silencioso
    event.respondWith(
        fetch(request).catch(() => caches.match(request))
    );
});

// ─── NOTIFICACIONES PUSH ────────────────────────────────────────────
self.addEventListener('push', (event) => {
    let data = { title: 'Taller CRM', body: 'Tienes una nueva notificación', icon: '/icons/icon-192.png', url: '/' };

    try {
        if (event.data) {
            const parsed = event.data.json();
            data = { ...data, ...parsed };
        }
    } catch (e) {
        if (event.data) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon || '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        vibrate: [200, 100, 200],
        data: { url: data.url || '/' },
        actions: [
            { action: 'open', title: 'Abrir' },
            { action: 'close', title: 'Cerrar' }
        ]
    };

    event.waitUntil(self.registration.showNotification(data.title, options));
});

// Click en la notificación
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'close') return;

    const url = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                for (const client of windowClients) {
                    if ('focus' in client) {
                        client.navigate(url);
                        return client.focus();
                    }
                }
                return clients.openWindow(url);
            })
    );
});

// Cerrar notificación
self.addEventListener('notificationclose', (event) => {
    event.notification.close();
});