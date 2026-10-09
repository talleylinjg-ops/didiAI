# User Instruction Memory

This file records user instructions, preferences, and teachings for reference in future interactions.

## Format

### User Instruction Entry

```
[User Instruction Summary]
- Date: [YYYY-MM-DD]
- Context: [Mentioned scenario or time]
- Instructions:
  - [Content of user teaching or instruction, described line by line]
```

### Project Knowledge Entry

```
[Project Knowledge Summary]
- Date: [YYYY-MM-DD]
- Context: Discovered by Agent while performing [specific task description]
- Category: [Operations & Deployment|Build Methods|Testing Methods|Troubleshooting & Debugging|Workflow & Collaboration|Environment Configuration]
- Instructions:
  - [Specific knowledge points, described line by line]
```

## Deduplication Strategy

- Before adding a new entry, check for similar or identical instructions.
- If a duplicate is found, skip the new entry or merge it with the existing one.
- When merging, update the context or date information.
- This helps avoid redundant entries and keeps the memory file tidy.

## Entries

[Project Knowledge Summary]
- Date: 2026-10-05
- Context: Discovered by Agent while wiring the 音频页 didi Media TTS default (MediaCut service was upgraded and old key invalidated)
- Category: Environment Configuration
- Instructions:
  - 重要：域名 `https://moneyprinterturbo.chacha.asia` 属于 MoneyPrinterTurbo v1.3.7（MPT 视频渠道），与 MediaCut 无关，不要错配。
  - MediaCut 服务升级为开发者注册/登录体系（`/api/v1/dev/register`、`/api/v1/dev/client/login`、`/api/v1/dev/key/info`、`/api/v1/dev/client/reset-key`），旧静态 Key 已作废（401 invalid api key）。
  - 2026-10-09 用户已提供正式配置并核对 media.db：Base URL=`https://mediacut.chacha.asia`，Key=`b0a27d13…`（完整值存 DB `didi_ai_config` mediacut 段，勿入 git）。已写入 DB 并重新生成 VPS 导出；沙箱到 `*.chacha.asia` 被 Cloudflare 拦截（HTTP 000），端到端验证需等 VPS 上线后执行。
  - 新版提交一律 multipart/form-data：tts→`text`；t2i→`prompt`（width/height/model 会被忽略）；i2i→`file`+`prompt`；video 无图→`prompt`(+duration/motion/width/height)；chat 有图→`media`+`text`。JSON 提交会 422。
  - 轮询 `GET /api/v1/tasks/{task_id}`、下载 `GET /api/v1/result/{task_id}/{filename}`（result_url 为相对路径）都要 Bearer；状态 pending/running/succeeded/failed。
  - 适配层 `inc/mediacut.php` 已兼容新版（submit 走 multipart，download 自动补 `/api/v1/result/` 前缀）；新 key 就位后音频页 TTS、图片页 t2i 均可端到端跑通。

[Project Knowledge Summary]
- Date: 2026-09-25
- Context: Discovered by Agent while integrating the didi Media / MediaCut free channel
- Category: Environment Configuration
- Instructions:
  - didi Media（MediaCut）是用户自建渠道，配置写在数据库 option `didi_ai_config` 的 `mediacut` 段（provider/apiKey/baseUrl/model），不要写入 git 或提交到仓库；后台「didi AI 配置」页可维护。
  - MediaCut 非 OpenAI 兼容：Bearer 鉴权，提交异步任务后轮询 `/api/v1/tasks/{id}`，下载结果 `/api/v1/result/{url}` 同样必须带 Bearer。
  - MediaCut 结果为二进制，适配层会落盘到 `wp-content/uploads/didi-mediacut/` 后回链；计费 0 点。
  - 长耗时任务（图片/TTS/i2i 约 120s；视频由前端轮询 `/wp-json/didi/v1/mc/task`）。
  - 上游未实现的端点会返回 `{"detail":"unsupported task type: ..."}`（HTTP 400），据此判断 MediaCut 是否已重启生效。

[Project Knowledge Summary]
- Date: 2026-09-25
- Context: Discovered by Agent while wiring REST handlers
- Category: Troubleshooting & Debugging
- Instructions:
  - REST / 前台上下文调用 WordPress 的 `download_url()` 前必须先 `require_once ABSPATH . 'wp-admin/includes/file.php'`，否则会触发未定义函数致命错误。
  - 本地开发服务通过 `php -S 0.0.0.0:8080 router.php` 运行于 `/workspace/didiAI`，修改 PHP 后需重启该终端进程。

[Project Knowledge Summary]
- Date: 2026-09-27
- Context: Discovered by Agent while integrating the MPT 门户网关 free video channel
- Category: Environment Configuration
- Instructions:
  - MPT（MPT API 开放平台）是用户自建的门户网关视频渠道，配置写在数据库 option `didi_ai_config` 的 `mpt` 段（provider/apiKey/baseUrl），Key 不进 git；后台「didi AI 配置」页可维护。适配层为 `inc/mpt.php`。
  - 门户 Base URL：`https://3000-fe5e3452b4fb7c4e.monkeycode-ai.online`（另有 `*.monkeycode-ai.online` 端口型地址会变动）；鉴权头 `x-api-key: mpt_xxx`，非 OpenAI 兼容。
  - 接口：`POST /api/proxy/v1/videos`（JSON，最小 `video_subject`，可选 `aspect`=9:16/16:9/1:1、`video_source`=pexels/pixabay/auto）→ `{task_id,state:"queued"}`；`GET /api/proxy/v1/videos/{task_id}` 查进度；`GET /api/proxy/v1/videos/{task_id}/preview|download` 取二进制（也支持 `?api_key=`）。
  - 状态机：`queued → processing → complete|failed`，完成态 `state==="complete"`；进度字段 `progress`/`stage`。成片约 180s，文案上限 1500 字。
  - 成片落盘到 `wp-content/uploads/didi-mpt/`，前端轮询 `/wp-json/didi/v1/mpt/task`，计费 0 点（economy 档）。
  - 账号注册走 `POST /api/auth/register`（body `{email,username,password}`）返回 `{token, api_key}`；控制台可轮换 Key。轮询接口偶发 30s 超时，前端需容忍瞬时网络错误。

[Project Knowledge Summary]
- Date: 2026-09-29
- Context: Discovered by Agent while wiring didi Media into the edit page (image/audio)
- Category: Troubleshooting & Debugging
- Instructions:
  - `php -S` 开发服务默认单线程：若处理器内部再向本站 URL（如 uploads 素材）发起 HTTP 请求会自请求死锁。启动时设置 `PHP_CLI_SERVER_WORKERS=8 php -S 0.0.0.0:8080 router.php` 可多 worker 并发；生产 Apache/nginx+FPM 本身并发，无此问题。
  - MediaCut 适配层取素材时对本站 uploads URL 直接复制本地文件到临时文件（`didi_ai_mediacut_temp_from_url`），避免自请求；远端 URL 才走 `download_url()`。生产同样受益（省一次自回源）。

[Project Knowledge Summary]
- Date: 2026-09-29
- Context: Discovered by Agent while refreshing the CF static mirror and hardening SEO/GEO
- Category: Operations & Deployment
- Instructions:
  - didiAI 的访客登录守卫会 302 跳 `/login`，采集镜像时必须带搜索引擎爬虫 UA；`scripts/mirror.mjs` 支持 `MIRROR_UA`（默认 Googlebot），并支持 `PUBLIC_ORIGIN` 把快照中的站点根地址（含 `http:\/\/` JSON 转义与 URL 编码形式）重写为公开域名，否则 canonical/og:url 与静态/接口地址会指向采集地址。
  - 标准刷新流程：`ORIGIN=http://localhost:8080 PUBLIC_ORIGIN=https://didi-ai-mirror.talley-linjg.workers.dev node scripts/mirror.mjs seeds.txt`，再用 CF_API_TOKEN 运行 `node scripts/apply.mjs`（先 R2 后 KV）。
  - CF 免费额度会拦截 KV 写入：报错 `your account has reached the free usage limit for this operation for today [code: 10048]`，当日无法刷新 HTML 快照，需次日重试；R2 资源写入通常不受影响。
  - 静态页必须与原站完全一致：采集后运行 `ORIGIN=... PUBLIC_ORIGIN=... node scripts/verify.mjs` 做逐字节校验（HTML 按采集时相同的地址重写规则还原，静态资源直接比较），差异为 0 才算通过；快照变更后必须重采再上传，否则旧快照会与源站不一致。
  - Worker 回源依赖 `env.ORIGIN`（原为占位符）；`proxyToOrigin` 曾只读 `X-Origin-Override` 请求头导致所有未镜像请求回源失败，现已改为使用 `env.ORIGIN`，ORIGIN 未配置时返回 502。Worker 静态资源按扩展名统一走 R2（不再限 `/wp-content/`），带查询串的请求不走 HTML 快照以免内容错配。


[Project Knowledge Summary]
- Date: 2026-10-08
- Context: Discovered by Agent while fixing 剪辑/图片页上传素材失败（upload_max_filesize=2M 默认值静默丢弃 $_FILES）
- Category: Environment Configuration
- Instructions:
  - 本地 PHP 服务启动命令已带上传限制参数，重启时必须保留：`PHP_CLI_SERVER_WORKERS=8 php -d upload_max_filesize=15M -d post_max_size=16M -S 0.0.0.0:8080 router.php`（8081 同参数），工作目录 `/workspace/didiAI`。
  - upload_max_filesize/post_max_size 属 PHP_INI_PERDIR，运行时 ini_set 无效，只能通过 php -d 参数或 php.ini；本环境 `.user.ini` 实测对 php -S 不生效（zlib.output_compression 也不生效），全局输出 gzip 改在 wp-config.php 顶部 ini_set，SSE 接口（chat_stream）运行时关闭。
  - 两个服务均为受管后台终端：8080=term_1791518451227_24、8081=term_1791518456368_25（重启后 ID 会变，以 background_terminal_list 为准）。
  - 上传走 `/wp-json/didi/v1/upload`（multipart FormData），前端用 X-WP-Nonce；e2e 时 402 no_quota=登录态/nonce 过期，重新登录并从页面抓 didiRestNonce。
