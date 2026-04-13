/**
 * Service Worker — Portal Pegawai Tirta Perwira
 * Strategy:
 *   - Static assets: Cache First
 *   - Navigation/HTML: Network First with offline fallback
 *   - API calls: Network Only
 */

const CACHE_NAME = 'portal-pegawai-v5';
const OFFLINE_URL = '/offline.html';

const PRECACHE_URLS = [
  '/mobile',
  '/mobile/login',
  '/offline.html',
  '/css/mobile-app.css',
  '/js/mobile-app.js',
  '/images/icons/icon-192x192.png',
  '/images/icons/icon-512x512.png',
  '/manifest.json',
];

// ─── Install ──────────────────────────────────────────────
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(PRECACHE_URLS.map(url => new Request(url, { cache: 'reload' })));
    }).then(() => self.skipWaiting())
  );
});

// ─── Activate ─────────────────────────────────────────────
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME)
          .map((name) => caches.delete(name))
      );
    }).then(() => self.clients.claim())
  );
});

// ─── Fetch ────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests & browser-extensions
  if (request.method !== 'GET') return;
  if (!url.protocol.startsWith('http')) return;

  // API calls → Network Only (never cache)
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(fetch(request));
    return;
  }

  // Navigation (HTML pages) → Network First, fallback to offline
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Clone and cache successful navigation responses
          if (response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
          }
          return response;
        })
        .catch(() => {
          return caches.match(request).then((cached) => {
            return cached || caches.match(OFFLINE_URL);
          });
        })
    );
    return;
  }

  // Static assets (CSS, JS, images) → Cache First, fallback to network
  if (
    url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf)$/)
  ) {
    event.respondWith(
      caches.match(request).then((cached) => {
        return cached || fetch(request).then((response) => {
          if (response.ok) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
          }
          return response;
        });
      })
    );
    return;
  }

  // Default → Network with cache fallback
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});

// ─── Background Sync ──────────────────────────────────────
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-attendance') {
    event.waitUntil(syncPendingAttendance());
  }
});

async function syncPendingAttendance() {
  // Sync pending attendance records when online
  const db = await openIndexedDB();
  const pending = await db.getAll('pendingAttendance');
  for (const record of pending) {
    try {
      await fetch('/mobile/attendance', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': record.csrf },
        body: JSON.stringify(record.data),
      });
      await db.delete('pendingAttendance', record.id);
    } catch (e) {
      console.error('Sync failed for record', record.id);
    }
  }
}

function openIndexedDB() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open('PortalPegawaiDB', 1);
    req.onupgradeneeded = (e) => {
      e.target.result.createObjectStore('pendingAttendance', { keyPath: 'id', autoIncrement: true });
    };
    req.onsuccess = (e) => {
      const db = e.target.result;
      resolve({
        getAll: (store) => new Promise((res, rej) => {
          db.transaction(store).objectStore(store).getAll().onsuccess = (ev) => res(ev.target.result);
        }),
        delete: (store, key) => new Promise((res) => {
          db.transaction(store, 'readwrite').objectStore(store).delete(key).onsuccess = () => res();
        }),
      });
    };
    req.onerror = reject;
  });
}

// ─── Push Notifications ───────────────────────────────────
self.addEventListener('push', (event) => {
  const data = event.data ? event.data.json() : {};
  const options = {
    body: data.body || 'Ada notifikasi baru dari Portal Pegawai',
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/icon-72x72.png',
    vibrate: [200, 100, 200],
    data: { url: data.url || '/mobile' },
  };
  event.waitUntil(
    self.registration.showNotification(data.title || 'Portal Pegawai', options)
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(clients.openWindow(event.notification.data.url));
});
