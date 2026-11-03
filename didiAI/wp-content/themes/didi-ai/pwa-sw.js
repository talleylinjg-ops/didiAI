/* didi AI Service Worker
 * 版本升级：发布新版本时递增 CACHE_VERSION，页面会检测到新 SW 并弹出升级提示 */
const CACHE_VERSION = 'v1.0.0';
const STATIC_CACHE = 'didi-static-' + CACHE_VERSION;
const PAGE_CACHE = 'didi-pages-' + CACHE_VERSION;
const OFFLINE_URL = '/?pwa-offline=1';

/* 预缓存：离线兜底页 + 核心图标 */
const PRECACHE = [
  OFFLINE_URL,
  '/wp-content/themes/didi-ai/assets/pwa/icon-192.png',
];

self.addEventListener('install', (event) => {
  /* 不立即 skipWaiting：等页面弹出升级提示、用户确认后再接管（见 message 监听） */
  event.waitUntil(
    caches.open(PAGE_CACHE).then((cache) => cache.addAll(PRECACHE))
  );
});

/* 页面确认升级后触发接管 */
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((k) => k !== STATIC_CACHE && k !== PAGE_CACHE)
          .map((k) => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  const path = url.pathname;
  /* 动态接口与管理端不缓存 */
  if (
    path.startsWith('/wp-admin') ||
    path.startsWith('/wp-json') ||
    path.includes('admin-ajax.php') ||
    path.startsWith('/member') ||
    path.startsWith('/recharge')
  ) {
    return;
  }

  /* 静态资源：stale-while-revalidate（先缓存后更新） */
  if (/\.(png|jpe?g|gif|webp|svg|ico|css|js|woff2?|ttf|eot)$/i.test(path) || path.startsWith('/wp-content/')) {
    event.respondWith(
      caches.open(STATIC_CACHE).then(async (cache) => {
        const cached = await cache.match(req);
        const fetchPromise = fetch(req).then((res) => {
          if (res && res.status === 200) cache.put(req, res.clone());
          return res;
        }).catch(() => cached);
        return cached || fetchPromise;
      })
    );
    return;
  }

  /* 页面导航：network-first，失败回缓存，再失败回离线页 */
  if (req.mode === 'navigate' || (req.headers.get('accept') || '').includes('text/html')) {
    event.respondWith(
      fetch(req).then((res) => {
        const copy = res.clone();
        caches.open(PAGE_CACHE).then((cache) => cache.put(req, copy));
        return res;
      }).catch(async () => {
        const cached = await caches.match(req);
        if (cached) return cached;
        const offline = await caches.match(OFFLINE_URL);
        return offline || new Response('<h1>网络不可用</h1>', { headers: { 'Content-Type': 'text/html; charset=utf-8' }, status: 503 });
      })
    );
  }
});
