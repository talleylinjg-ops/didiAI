# User Instruction Memory

This file records user instructions, preferences, and teachings for reference in future interactions.

## Entries

[Project Knowledge Summary]
- Date: 2026-08-24
- Context: Discovered by Agent while recovering didiAI WordPress site after git commit `050e515` deleted the whole `didi_package/` folder; site rebuilt at repo root
- Category: Operations & Deployment
- Instructions:
  - **站点唯一结构**：整个 WordPress 直接放在 git 仓库根 `/workspace/didiAI/`（index.php / wp-config.php / router.php / wp-content / database/didi_wp.sql 都在根下）。不再有 `didi_package` 子目录；用户明确要求 didi_package 仅作参考、用完删除、全部使用唯一文件。
  - didi-ai 站点的 admin 后台密码已重置为 `Test1234!`（通过 `wp_set_password`，CLI 用 `php -r 'require "wp-load.php"; wp_set_password("...",1);'`）。
  - 服务由 background terminal 管理：PHP 开发服务器 `cd /workspace/didiAI && php -S 0.0.0.0:8080 router.php`（端口 8080）与 MariaDB（`mysqld_safe --user=mysql`）。启动前先 `background_terminal_list` 检查避免重复启动。
  - 本环境 PHP 需自行安装扩展：`apt-get install -y php8.2-mysql php8.2-curl`（缺 mysqli 会白屏 "Requirements Not Met"，缺 curl 会 chat/stream 500）。改动 PHP 后需 kill 并重建 PHP 服务器终端。
  - DB：wp_db / wp_user / wp_pass_2026 / localhost（`mariadb-install-db` 已初始化，`mysql -uroot wp_db < database/didi_wp.sql` 导入）。DB 缺 ai-music/ai-write/ai-ppt/ai-avatar 页面时需 INSERT 页面 + `_wp_page_template` 绑定。
  - **空白页根因**：`parts/ai-chat-layout.php` 从未入库、被删除后丢失；所有 `page-ai-*.php` 都 `include` 它。该文件已重建（含国内外多模型下拉，值映射现有 deepseek 后端；`didi_ai_chat_stream` 接受 `model` 参数直传）。
  - 剪辑页 ChatCut 为**唯一工具**（排剪辑指令前、独立面板），不属于编辑模型下拉；按钮文案为「授权连接 ChatCut 账号」；编辑模型下拉含 即梦/可灵/OpenAI/Runway/ModelScope/自定义。
  - 会员档位含 `trial`（试用会员，最低档，¥0.01/7 天）/ `free` / `silver` / `gold`；`trial`、`silver`、`gold` 均绕过每日额度并解锁自定义模型，`free` 受限。
  - 消费流水存于用户 meta `didi_billing`（REST：GET `/wp-json/didi/v1/billing`）；改密端点 POST `/wp-json/didi/v1/change-password`。

[Project Knowledge Summary]
- Date: 2026-08-28
- Context: Discovered by Agent when external `git clone` reset repo to remote HEAD, wiping local commits; restored multi-model dropdowns per user requirement
- Category: Operations & Deployment
- Instructions:
  - **仓库可能被外部重新 clone**：HEAD 会回退到远程（如 `6000bbf`），本地提交全部丢失；同时 `/workspace/didiAI` 目录被删除重建导致 PHP 服务器 cwd 指向 `(deleted)` 目录、站点全部 500。症状=`ls -l /proc/<php_pid>/cwd` 显示 `(deleted)`；恢复方式=kill 旧 PHP 终端 → 重建 `php -S 0.0.0.0:8080 router.php`。
  - **用户核心需求（8-28 明确）**：每个 AI 页 Tab 的模型下拉必须含**国内外多模型**（国内 7 + 海外 4 + 自定义，共 12 项），且**每页默认选中的专属模型不同**。不可砍成单模型。映射：提问/代码=DeepSeek、工作=通义千问(qwen-plus)、音乐=文心ERNIE(ernie-4.0-turbo)、写作=Kimi(moonshot-v1)、PPT=智谱GLM(glm-4)、数字人=豆包(doubao-pro)、语音=腾讯混元(hunyuan-turbo)、文件=GPT-4o。
  - **voice/file 页 section 已改为独立值**（`voice`/`file`），不再与提问页共用 `llm`，否则默认模型会相同。
  - `didi_ai_llm_cfg` 已扩展支持全部 9 个 LLM section（llm/code/work/music/write/ppt/avatar/voice/file），每 section 独立 `USER_<SEC>_BASE_URL/API_KEY/MODEL` 环境变量 + 后台「didi AI 配置」页可分别配置；未配置时回退 DeepSeek。专属模型需用户配置对应厂商 key 才能真实调用。
  - siteurl/home 为 `http://localhost:8080`（DB wp_options），改后登录 cookie 会失效需重新登录。

[Project Knowledge Summary]
- Date: 2026-09-15
- Context: Discovered by Agent while deploying didiAI 静态镜像层（R2 + KV + Worker）到 Cloudflare 生产环境
- Category: Operations & Deployment
- Instructions:
  - **镜像工程位置**：`/workspace/didiAI-mirror`（`wrangler.toml` / `src/index.ts` / `scripts/mirror.mjs` 采集 / `scripts/apply.mjs` 上传 / `scripts/apply.mjs` / `deploy.sh` 一键 / `seeds.txt` 页面清单）。架构=HTML 快照进 KV、静态资源进 R2、动态请求回源。
  - **CF 凭据必须用 User API Token**（My Profile → API Tokens），账号页 `Account API Tokens` 给不了 R2/KV（会报 10000 Authentication error）。需 5 项权限：Account 的 Workers Scripts:Edit、Workers R2 Storage:Edit、Workers KV Storage:Edit、Account Settings:Read，以及 User 的 Memberships:Read。凭据存在 `/tmp/opencode/cf.env`（键名 `CF_API_TOKEN`，脚本里需 export 为 `CLOUDFLARE_API_TOKEN`）。
  - **wrangler v4 本地/远端坑**：`wrangler r2 object put` 与 `wrangler kv key put` 默认写**本地模拟环境**（日志显示 `Resource location: local`），必须显式加 `--remote`；旧命令 `wrangler kv:key put` 在 v4 已移除，新语法为 `wrangler kv key put <key> --binding=HTML_KV --path=<file> --remote`。
  - **沙箱访问不了 `*.workers.dev`**：本环境对 `*.workers.dev` 的 DNS 被解析到非 CF 地址且 TLS 直接被重置（`SSL_ERROR_SYSCALL`），因此无法在沙箱内直接验证 Worker；只能用 `wrangler ... --remote` 读回 R2/KV 内容 + Cloudflare API 间接确认，最终渲染效果需用户在浏览器打开 `https://didi-ai-mirror.talley-linjg.workers.dev` 查看。
  - **已部署资源**：R2 桶 `didi-ai-mirror`；KV `didi_ai_mirror_kv` = `4d12b1b4f3f64e3ca992506cf1a452c6`；Worker `didi-ai-mirror`。`wrangler.toml` 的 `ORIGIN` 暂为占位 `https://origin.pending.invalid`（域名未申请），动态/未缓存请求会失败，仅 KV/R2 命中页可访问。
  - **本地 WP 安装缺 `wp-includes/css/dist/block-library/common.min.css`**：`router.php` 对不存在的静态文件回退到 `index.php`（302→/login，200 HTML）。采集器因此曾把登录页当成 CSS 存进 R2；`mirror.mjs` 已加按 `content-type` 过滤 HTML 回退的护栏（`fetchAsset` 跳过 text/html、CSS 分支要求 content-type 含 css）。
  - 部署命令：`ORIGIN=<源站> CLOUDFLARE_API_TOKEN=<token> bash /workspace/didiAI-mirror/deploy.sh`；本地抓取校验用 `ORIGIN=http://localhost:8080 node scripts/mirror.mjs seeds.txt`。
