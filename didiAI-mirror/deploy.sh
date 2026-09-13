#!/usr/bin/env bash
# didiAI 镜像层一键部署（建资源 -> 部署 Worker -> 抓取上传）
#
# 前置条件：
#   1. 环境变量 CLOUDFLARE_API_TOKEN 具备权限：
#        Account -> Workers Scripts:Edit
#        Account -> Workers R2 Storage:Edit
#        Account -> Workers KV Storage:Edit
#        Account -> Account Settings:Read
#        User -> Memberships:Read
#   2. 环境变量 ORIGIN 为 didiAI 公网源站地址，如 https://didi.example.com
#
# 用法：
#   ORIGIN=https://didi.example.com CLOUDFLARE_API_TOKEN=xxx bash deploy.sh

set -euo pipefail

BUCKET="${R2_BUCKET:-didi-ai-mirror}"
KV_NAME="${KV_NAME:-didi_ai_mirror_kv}"

if [[ -z "${CLOUDFLARE_API_TOKEN:-}" ]]; then
  echo "错误：请设置 CLOUDFLARE_API_TOKEN" >&2
  exit 1
fi
if [[ -z "${ORIGIN:-}" ]]; then
  echo "错误：请设置 ORIGIN（didiAI 公网源站地址）" >&2
  exit 1
fi

echo "==> 1/5 创建 R2 存储桶：${BUCKET}"
wrangler r2 bucket create "$BUCKET" || true

echo "==> 2/5 创建 KV 命名空间：${KV_NAME}"
KV_OUT="$(wrangler kv namespace create "$KV_NAME" 2>&1 || true)"
echo "$KV_OUT"
KV_ID="$(printf '%s' "$KV_OUT" | grep -oE '"id":\s*"[a-f0-9]{32}"' | head -1 | grep -oE '[a-f0-9]{32}' || true)"
if [[ -z "${KV_ID:-}" ]]; then
  echo "未能解析 KV id，请手动把命名空间 id 填入 wrangler.toml 后重试" >&2
  exit 1
fi

echo "==> 3/5 写入 wrangler.toml（ORIGIN / KV id）"
python3 - "$KV_ID" "$ORIGIN" <<'PY'
import re, sys
kv_id, origin = sys.argv[1], sys.argv[2]
p = 'wrangler.toml'
s = open(p, encoding='utf-8').read()
s = re.sub(r'(?m)^ORIGIN\s*=\s*".*"$', 'ORIGIN = "%s"' % origin, s)
s = re.sub(r'(?m)^id\s*=\s*".*"$', 'id = "%s"' % kv_id, s)
open(p, 'w', encoding='utf-8').write(s)
print('wrangler.toml updated')
PY

echo "==> 4/5 部署 Worker"
wrangler deploy

echo "==> 5/5 抓取源站并上传镜像"
ORIGIN="$ORIGIN" node scripts/mirror.mjs seeds.txt
node scripts/apply.mjs

echo "完成。"
