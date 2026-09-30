/**
 * didiAI 静态镜像层 Worker
 * 流量分层（尽量不打原站）：
 *   1) 动态路径 / POST -> 直接代理到源站（购物车/登录/计费等业务必须实时）
 *   2) /wp-content/ 静态资源 -> CF 缓存 -> R2 镜像命中即返回(x-served-from: r2-static) -> 最后回源
 *   3) HTML 页面 -> HTML_KV 快照命中即返回(x-served-from: html-snapshot) -> 最后回源
 */

export interface Env {
  ORIGIN: string;
  MIRROR_R2: R2Bucket;
  HTML_KV: KVNamespace;
}

const DYNAMIC_MARKERS = [
  '/wp-json/',
  '/wp-admin/',
  '/wp-login.php',
  '/wp-cron.php',
  '/xmlrpc.php',
  '/wp-trackback.php',
  '/admin-ajax.php',
];

function isDynamic(url: URL, method: string): boolean {
  if (method !== 'GET' && method !== 'HEAD') return true;
  if (url.searchParams.has('rest_route')) return true;
  const p = url.pathname;
  return DYNAMIC_MARKERS.some((m) => p.startsWith(m)) || /\.php$/i.test(p);
}

const MIME: Record<string, string> = {
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.mjs': 'text/javascript; charset=utf-8',
  '.woff2': 'font/woff2',
  '.woff': 'font/woff',
  '.ttf': 'font/ttf',
  '.eot': 'application/vnd.ms-fontobject',
  '.svg': 'image/svg+xml',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.gif': 'image/gif',
  '.webp': 'image/webp',
  '.avif': 'image/avif',
  '.ico': 'image/x-icon',
  '.json': 'application/json; charset=utf-8',
  '.map': 'application/json',
};

function mimeOf(path: string): string {
  const idx = path.lastIndexOf('.');
  if (idx === -1) return 'application/octet-stream';
  return MIME[path.slice(idx).toLowerCase()] || 'application/octet-stream';
}

function normalizeKey(pathname: string): string {
  const p = pathname.replace(/^\/+/, '');
  if (p === '' || p.endsWith('/')) return p + 'index.html';
  const last = p.slice(p.lastIndexOf('/') + 1);
  if (!last.includes('.')) return p + '/index.html';
  return p;
}

async function proxyToOrigin(request: Request, env: Env): Promise<Response> {
  const base = (env.ORIGIN || '').replace(/\/+$/, '');
  if (!base || base.includes('pending.invalid')) {
    return new Response('mirror origin not configured', { status: 502 });
  }
  const reqUrl = new URL(request.url);
  const target = base + reqUrl.pathname + reqUrl.search;
  const headers = new Headers(request.headers);
  headers.delete('host');
  headers.set('x-forwarded-host', reqUrl.host);
  const init: RequestInit = {
    method: request.method,
    headers,
    redirect: 'follow',
  };
  if (request.method !== 'GET' && request.method !== 'HEAD') {
    init.body = await request.clone().arrayBuffer();
  }
  return fetch(target, init);
}

export default {
  async fetch(request: Request, env: Env): Promise<Response> {
    const url = new URL(request.url);

    // 1) 动态请求直接回源，保证业务实时
    if (isDynamic(url, request.method)) {
      return proxyToOrigin(request, env);
    }

    // 2) 静态资源：按扩展名判定（/wp-content、/wp-includes、uploads 等一视同仁）
    //    R2 镜像命中即返回，否则回源，保证渲染与原站一致
    if (/\.[a-z0-9]+$/i.test(url.pathname)) {
      const key = normalizeKey(url.pathname);
      const obj = await env.MIRROR_R2.get(key);
      if (obj) {
        const headers = new Headers();
        headers.set('Content-Type', mimeOf(key));
        headers.set('Cache-Control', 'public, max-age=31536000, immutable');
        headers.set('x-served-from', 'r2-static');
        return new Response(obj.body, { headers });
      }
      return proxyToOrigin(request, env);
    }

    // 3) HTML 页面快照：仅无查询串的路径命中 KV（快照按键存储），带参请求回源避免内容错配
    if (url.search === '') {
      const key = normalizeKey(url.pathname);
      const html = await env.HTML_KV.get(key);
      if (html !== null) {
        return new Response(html, {
          headers: {
            'Content-Type': 'text/html; charset=utf-8',
            'Cache-Control': 'public, max-age=300',
            'x-served-from': 'html-snapshot',
          },
        });
      }
    }
    return proxyToOrigin(request, env);
  },
};
