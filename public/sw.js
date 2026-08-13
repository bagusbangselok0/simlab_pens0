/**
 * SIMLAB — Service Worker untuk Browser Push Notification
 * File ini harus berada di root public/ agar scope-nya mencakup seluruh app.
 */

const CACHE_NAME = 'simlab-sw-v1';

// ─── Install & Activate ───────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

// ─── Push Event ───────────────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
    let data = {};

    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = {
            title: 'SIMLAB',
            message: event.data ? event.data.text() : 'Ada notifikasi baru.',
            url: '/',
            icon: '/images/logo/SIMLAB_logo1.png',
        };
    }

    const title   = data.title   || 'SIMLAB';
    const options = {
        body:             data.message || 'Ada pembaruan baru.',
        icon:             data.icon    || '/images/logo/SIMLAB_logo1.png',
        badge:            '/images/logo/SIMLAB_logo1.png',
        data:             { url: data.url || '/' },
        vibrate:          [200, 100, 200],
        requireInteraction: false,
        tag:              'simlab-notif-' + Date.now(),
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// ─── Notification Click ───────────────────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Jika sudah ada tab yang terbuka, fokus ke sana
            for (const client of clientList) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.focus();
                    client.navigate(targetUrl);
                    return;
                }
            }
            // Kalau tidak ada tab, buka window baru
            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
