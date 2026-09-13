/**
 * didiAI 镜像采集器（纯 Node，无第三方依赖，Node >= 18）
 *
 * 职责（对照已验证方案）：
 *   1. 抓取给定页面清单/种子链接，遍历站内 HTML（GET 200）
 *   2. 递归解析引用：<link>/<script>/<img>/background url() 等静态资源
 *   3. 静态文件按裸路径保存到 out/static/<path>；HTML 快照保存到 out/html/<key>
 *   4. 汇总 out/manifest.json，供后续上传 R2/KV 使用
 *
 * 用法：
 *   ORIGIN=https://didi.example.com node scripts/mirror.mjs seeds.txt
 *   其中 seeds.txt 每行一个页面路径（如 / 或 /product/abc），# 开头为注释
 */

import { mkdir, writeFile } from 'node:fs/promises';
import { dirname, join, posix } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = dirname(dirname(fileURLToPath(import.meta.url)));
const ORIGIN = (process.env.ORIGIN || '').replace(/\/+$/, '');
if (!ORIGIN) {
  console.error('请设置 ORIGIN 环境变量，如 ORIGIN=https://didi.example.com');
  process.exit(1);
}

const seedsFile = process.argv[2];
const OUT = join(ROOT, 'out');
const outStatic = join(OUT, 'static');
const outHtml = join(OUT, 'html');

const seenHtml = new Set();
const seenAsset = new Set();
const htmlPages = [];
const assets = [];
const failures = [];
let htmlTotal = 0;

const MIME_EXTS = new Map([
  ['text/html', '.html'],
  ['text/css', '.css'],
  ['text/javascript', '.js'],
  ['application/javascript', '.js'],
  ['image/png', '.png'],
  ['image/jpeg', '.jpg'],
  ['image/gif', '.gif'],
  ['image/webp', '.webp'],
  ['image/svg+xml', '.svg'],
  ['image/x-icon', '.ico'],
  ['image/avif', '.avif'],
  ['font/woff2', '.woff2'],
  ['font/woff', '.woff'],
  ['font/ttf', '.ttf'],
  ['application/vnd.ms-fontobject', '.eot'],
  ['application/json', '.json'],
]);

function isSameHost(u) {
  try {
    return new URL(u, ORIGIN).host === new URL(ORIGIN).host;
  } catch {
    return false;
  }
}

function barePath(u) {
  const p = new URL(u, ORIGIN).pathname.replace(/^\/+/, '');
  return p || 'index.html';
}

function isHtmlPath(u) {
  return !/\.(css|js|json|png|jpe?g|gif|webp|svg|ico|avif|woff2?|ttf|eot|map)$/i.test(barePath(u));
}

async function readText(res) {
  const ct = res.headers.get('content-type') || '';
  if (/charset=/i.test(ct)) {
    const m = ct.match(/charset=([\w-]+)/i);
    if (m) {
      try {
        const buf = Buffer.from(await res.arrayBuffer());
        return new TextDecoder(m[1] === 'utf-8' ? 'utf-8' : m[1]).decode(buf);
      } catch {
        /* fallthrough */
      }
    }
  }
  return res.text();
}

function collectRefs(html, base) {
  const refs = new Set();
  const add = (raw) => {
    if (!raw) return;
    const v = raw.trim();
    if (!v || v.startsWith('data:') || v.startsWith('#') || v.startsWith('//')) return;
    if (!isSameHost(v)) return;
    refs.add(v);
  };
  for (const m of html.matchAll(/(?:href|src|data-src|poster)=["']([^"']+)["']/gi)) add(m[1]);
  for (const m of html.matchAll(/url\(\s*["']?([^"')]+)["']?\s*\)/gi)) add(m[1]);
  return [...refs].map((v) => new URL(v, base).pathname);
}

function localCssRefs(css) {
  const out = [];
  for (const m of css.matchAll(/url\(\s*["']?([^"')]+)["']?\s*\)/gi)) {
    const v = m[1];
    if (!v || v.startsWith('data:') || v.startsWith('http')) continue;
    out.push(v);
  }
  return out;
}

function extOf(url) {
  const p = url.pathname;
  const i = p.lastIndexOf('.');
  return i === -1 ? '' : p.slice(i).toLowerCase();
}

function storeFileKey(rel, buf) {
  assets.push(rel);
  return join(outStatic, rel);
}

async function fetchAsset(url, rel) {
  try {
    const res = await fetch(url, { redirect: 'follow' });
    if (!res.ok) {
      if (res.status >= 400 && res.status < 500) failures.push({ url, status: res.status });
      return null;
    }
    const buf = Buffer.from(await res.arrayBuffer());
    const file = storeFileKey(rel, buf);
    await mkdir(dirname(file), { recursive: true });
    await writeFile(file, buf);
    return res.headers.get('content-type') || '';
  } catch {
    return null;
  }
}

async function crawlHtml(seedPath) {
  const url = new URL(seedPath, ORIGIN);
  const key = barePath(url);
  if (seenHtml.has(key) || !isHtmlPath(url.pathname)) return;
  seenHtml.add(key);

  const res = await fetch(url, { redirect: 'follow' });
  if (!res.ok) {
    if (res.status >= 400) failures.push({ url: seedPath, status: res.status });
    return;
  }
  const ct = res.headers.get('content-type') || '';
  if (!ct.includes('html')) return;

  const html = await readText(res);
  htmlTotal++;
  const file = join(outHtml, key);
  await mkdir(dirname(file), { recursive: true });
  await writeFile(file, html);
  htmlPages.push({ key, url: url.href });

  const refPaths = collectRefs(html, url);
  for (const p of refPaths) {
    const rel = barePath(p);
    if (seenAsset.has(rel) || seenHtml.has(rel)) continue;
    const full = new URL(p, ORIGIN).href;
    if (extOf(new URL(full)) === '.css') {
      seenAsset.add(rel);
      const file = join(outStatic, rel);
      await mkdir(dirname(file), { recursive: true });
      try {
        const cssRes = await fetch(full, { redirect: 'follow' });
        if (cssRes.ok) {
          const buf = Buffer.from(await cssRes.arrayBuffer());
          await writeFile(file, buf);
          assets.push(rel);
          const css = buf.toString('utf8');
          for (const inner of localCssRefs(css)) {
            const iu = new URL(inner, full);
            if (!isSameHost(iu)) continue;
            const irel = barePath(iu);
            if (seenAsset.has(irel)) continue;
            seenAsset.add(irel);
            await fetchAsset(iu.href, irel);
          }
        }
      } catch {
        /* ignore css */
      }
    } else if (isHtmlPath(new URL(full))) {
      await crawlHtml(full);
    } else {
      seenAsset.add(rel);
      await fetchAsset(full, rel);
    }
  }
}

async function main() {
  let seeds = ['/'];
  if (seedsFile) {
    const txt = await import('node:fs/promises').then((fs) => fs.readFile(seedsFile, 'utf8'));
    seeds = txt.split('\n').map((s) => s.trim()).filter((s) => s && !s.startsWith('#'));
  }
  for (const s of seeds) await crawlHtml(s);
  await mkdir(OUT, { recursive: true });
  await writeFile(join(OUT, 'manifest.json'), JSON.stringify({
    origin: ORIGIN,
    generatedAt: new Date().toISOString(),
    htmlPages,
    assets,
    failures,
    counts: { htmlPages: htmlPages.length, assets: assets.length, failures: failures.length },
  }, null, 2));
  console.log(`完成：HTML 页面 ${htmlPages.length} 个，静态资源 ${assets.length} 个，失败 ${failures.length} 个`);
  if (failures.length) console.table(failures.slice(0, 20));
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});
