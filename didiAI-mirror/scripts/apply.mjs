/**
 * 将 out/ 采集结果上传到 CF R2 与 KV（对照已验证方案的 key 规则）
 * 需要已安装 wrangler 且完成认证（wrangler login 或 CLOUDFLARE_API_TOKEN）。
 *
 * 用法：
 *   node scripts/apply.mjs
 *   node scripts/apply.mjs --dry-run   # 只打印将要执行的命令
 */

import { readFile } from 'node:fs/promises';
import { join } from 'node:path';
import { execSync } from 'node:child_process';
import { dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = dirname(dirname(fileURLToPath(import.meta.url)));
const dry = process.argv.includes('--dry-run');
const manifest = JSON.parse(await readFile(join(ROOT, 'out', 'manifest.json'), 'utf8'));

function run(cmd) {
  console.log(cmd);
  if (!dry) execSync(cmd, { stdio: 'inherit', cwd: ROOT });
}

const bucket = process.env.R2_BUCKET || 'didi-ai-mirror';

for (const rel of manifest.assets) {
  run(`wrangler r2 object put ${bucket}/${rel} --file=${join(ROOT, 'out', 'static', rel)} --content-type=${detect(rel)} --cache-control="public, max-age=31536000, immutable"`);
}

for (const page of manifest.htmlPages) {
  run(`wrangler kv:key put --binding=HTML_KV "${page.key}" --path=${join(ROOT, 'out', 'html', page.key)}`);
}

function detect(rel) {
  const m = rel.match(/\.([a-z0-9]+)$/i);
  const map = { css: 'text/css', js: 'text/javascript', woff2: 'font/woff2', woff: 'font/woff', ttf: 'font/ttf', eot: 'application/vnd.ms-fontobject', svg: 'image/svg+xml', png: 'image/png', jpg: 'image/jpeg', jpeg: 'image/jpeg', gif: 'image/gif', webp: 'image/webp', ico: 'image/x-icon' };
  return map[(m && m[1].toLowerCase())] || 'application/octet-stream';
}

console.log(`完成。共 ${manifest.assets.length} 个静态资源、${manifest.htmlPages.length} 个 HTML 快照${dry ? '（dry-run）' : ''}`);
