/**
 * 镜像一致性校验：逐页/逐资源对比 out/ 中的镜像内容与源站实时响应，要求完全一致。
 *
 * 用法：
 *   ORIGIN=http://localhost:8080 node scripts/verify.mjs
 *   ORIGIN=https://didi.example.com PUBLIC_ORIGIN=https://didi.example.com node scripts/verify.mjs
 *
 * 说明：
 *   - ORIGIN 为抓取源站；PUBLIC_ORIGIN 为快照中写死的公开域名（未设置则不做地址重写）。
 *   - HTML 按“与采集相同的地址重写规则”还原后逐字节比较；静态资源直接逐字节比较。
 *   - 任一不一致都返回非零退出码，便于 CI/部署前卡口。
 */

import { readFile } from 'node:fs/promises';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = dirname(dirname(fileURLToPath(import.meta.url)));
const ORIGIN = (process.env.ORIGIN || '').replace(/\/+$/, '');
const PUBLIC_ORIGIN = (process.env.PUBLIC_ORIGIN || '').replace(/\/+$/, '');
const UA = process.env.MIRROR_UA || 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

if (!ORIGIN) {
  console.error('请设置 ORIGIN 环境变量');
  process.exit(2);
}

const manifest = JSON.parse(await readFile(join(ROOT, 'out', 'manifest.json'), 'utf8'));

function rewriteText(text) {
  if (!PUBLIC_ORIGIN || PUBLIC_ORIGIN === ORIGIN) return text;
  const encFrom = encodeURIComponent(ORIGIN);
  const jsonFrom = ORIGIN.replace(/\//g, '\\/');
  let s = text.split(ORIGIN).join(PUBLIC_ORIGIN);
  s = s.split(jsonFrom).join(PUBLIC_ORIGIN);
  if (s.includes(encFrom)) s = s.split(encFrom).join(encodeURIComponent(PUBLIC_ORIGIN));
  return s;
}

async function fetchOrigin(path) {
  const res = await fetch(ORIGIN + path, { headers: { 'User-Agent': UA }, redirect: 'follow' });
  return res;
}

let checked = 0;
let diffs = 0;
const details = [];

for (const page of manifest.htmlPages) {
  const path = new URL(page.url, ORIGIN).pathname + new URL(page.url, ORIGIN).search;
  const res = await fetchOrigin(path);
  if (!res.ok) {
    diffs++;
    details.push(`HTML ${path} 源站返回 ${res.status}`);
    continue;
  }
  const originText = rewriteText(Buffer.from(await res.arrayBuffer()).toString('utf8'));
  const mirrorBuf = await readFile(join(ROOT, 'out', 'html', page.key));
  checked++;
  if (mirrorBuf.toString('utf8') !== originText) {
    diffs++;
    details.push(`HTML ${path} 不一致（origin ${originText.length}B / mirror ${mirrorBuf.length}B）`);
  }
}

for (const rel of manifest.assets) {
  const res = await fetchOrigin('/' + rel);
  if (!res.ok) {
    diffs++;
    details.push(`ASSET /${rel} 源站返回 ${res.status}`);
    continue;
  }
  const originBuf = Buffer.from(await res.arrayBuffer());
  const mirrorBuf = await readFile(join(ROOT, 'out', 'static', rel));
  checked++;
  if (!originBuf.equals(mirrorBuf)) {
    diffs++;
    details.push(`ASSET /${rel} 不一致（origin ${originBuf.length}B / mirror ${mirrorBuf.length}B）`);
  }
}

console.log(`已校验 ${checked} 项，差异 ${diffs} 项`);
for (const d of details.slice(0, 50)) console.log('  - ' + d);
process.exit(diffs === 0 ? 0 : 1);
