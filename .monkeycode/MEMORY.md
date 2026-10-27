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
- Date: 2026-10-19
- Context: Discovered by Agent while resuming the CJ Dropshipping bulk import into WooCommerce
- Category: Operations & Deployment
- Instructions:
  - CJ 导入三阶段脚本位于 `didiAI/scripts/cj-import/`（cj-import.php 入口 + import-tasks.php 实现），在 `/workspace/didiAI` 下执行：`list`→抓列表到 staging.jsonl、`import`→详情+WC 导入、`retry`→重跑 state.json failed 数组里的 pid、`images [LIMIT] [shardIdx shardTotal]`→图片下载压缩后传 R2。
  - 断点续跑：state.json（importCursor/imported/skipped/failed）；staging.jsonl 24172 条已全部导入完成（imported 21282、skipped 16、failed 2874，failed 主因是 CJ API 每日配额耗尽 "Insufficient"，按 UTC 日重置后用 retry 命令恢复）。
  - 产品导入为 draft、价格 CJ 价 ×1.5、变体挂 pa_cj-variant 属性；图片用伪 attachment（_wp_attached_file 存外链），阶段3把外链替换为 R2 URL（didi-ai-mirror bucket，每产品最多 10 张，GD 压长边 1200/q78）。
  - 阶段3已改为 ID 列表文件方案（images-pending-{shard}，按 post_id%shardTotal 拆分，处理完即出队即断点），避免 wp_postmeta（1160 万行）meta_query 重复扫描；已加索引 k_mv (meta_key(191), meta_value(32))。
  - 图片迁移性能要点：GD 未装时原图直传慢 3 倍（已装 php8.2-gd）；CJ CDN 偶发 60s 挂死，下载 timeout 已降 20s+重试 1 次；R2 put 偶发 HTTP 0 已加重试；attachment 外链用单条 UPDATE...CASE 批量替换。4 shard 并行约 2.2s/个，2.4 万产品约 4 小时。
  - 脚本自身每批（import 500 / images 40）exit(0) 防内存泄漏，需 shell 循环重启续跑；PHP 处理复杂变体产品约 30-40s/个，2873 个重试约需 30 小时（实际 3 小时跑完）。
  - 系统内存紧张（8GB，available 常 <300MB），导入与图片迁移串行执行，勿并行两个 PHP CLI。

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

[Project Knowledge Summary]
- Date: 2026-10-22
- Context: Discovered by Agent while building the daqihospital (大气AI医院) WordPress site
- Category: Operations & Deployment
- Instructions:
  - 双站布局：`/workspace/didiAI`（php -S 8080，库 wp_db）与 `/workspace/daqihospital`（8081，库 daqi_hosp_db），共用 MySQL 用户 wp_user/wp_pass_2026；`mysql -uroot` 免密可用。daqihospital 管理 admin/Daqi2026!。
  - daqihospital 复用 didiAI 主题（目录名仍 didi-ai、REST didi/v1），品牌文案已全量替换为大气AI医院；核心差异在 inc-medical-presets.php（11 条问诊通道预设，服务端 system prompt 含免责与 120 红旗规则）+ page-consult.php（病史表单+SSE 问诊页 /consult-{key}）。
  - 问诊游客限免：functions.php chat_stream 对 preset 请求免登录，按 IP 每日 5 次（transient daqi_guest_{md5(ip)}_{Ymd}）；守卫白名单需含 /consult- 前缀。
  - 模型配置迁移：didiAI 库 option didi_ai_config（GLM-4.5-Flash llm/code/work、cogview-3-flash image）已 mysqli 直连复制到 daqi_hosp_db，不打印 key；新站未配 USER_LLM_* 环境变量，靠 DB 兜底。
  - 坑：根目录静态 llms.txt 会被 php -S router 直接命中，挡住 template_redirect 动态 handler（输出旧版）；两站根目录 llms.txt 均已归档至 wp-content/uploads/_old/，以 handler 版为准。
  - 内容结构：博客列表页是 slug=blog 的普通页面（page-blog.php 模板，page_for_posts=0）；论坛 topic 是 forum_topic post type + category 分类法；文章根级 URL 游客被守卫 302（爬虫 UA 放行），两站一致。
   - daqihospital 预览：https://8081-*.monkeycode-ai.online（reverse-proxy mu-plugin 动态 siteurl 跟随访问域）；router.php 启动参数与 8080 相同（PHP_CLI_SERVER_WORKERS=8 + upload 限制）。

[Project Knowledge Summary]
- Date: 2026-10-25
- Context: Discovered by Agent while building the daqihospital bilingual layer (EN site ai.daqihospital.com / ZH site ai.daqihospital.net)
- Category: Environment Configuration
- Instructions:
  - 双域单站语言机制：`inc-site-lang.php` 的 `daqi_site_lang()` 按 Host 判定（含 `daqihospital.com` 且非 `.net` → en，否则 zh；`?lang=en|zh` 可覆盖，localStorage 仍优先）。`daqi_t($zh,$en)` 是唯一文案开关；动态中文名用 `daqi_membership_name/daqi_pricing_unit/daqi_pricing_model/daqi_term_name` 映射。
  - 内容翻译走 `_en_title/_en_content/_en_excerpt` postmeta + `_en_name` termmeta（the_title/the_content/excerpt filter 读取）；`/tmp/opencode/daqi-translate.php` 用 GLM 翻译（幂等，注意脚本不能加 `WP_INSTALLING=true`——会跳过主题加载导致 `didi_ai_llm_cfg` 未定义）。
  - `<title>` 由内核 `wp_get_document_title()` 输出（Rank Math 未接管 title，其 frontend/* filters 在该安装里不被调用；meta/JSON-LD 实际由主题 `didi_ai_seo_*` 函数输出）。EN title 靠 `document_title_parts` filter + `_en_title` meta；`/forum/` URL 命中 forum_topic post type archive（与页面冲突），title part 是「论坛主题」，需 strtr 兜底。
  - page-cache key 含 HTTP_HOST（双域缓存互不污染，验证过 X-Cache HIT）；改 SEO/语言代码后验证需换新查询串防旧缓存。
  - `page-member.php` 权益表与定价表已医疗化（只渲染问诊类条目，pricing 循环过滤 chat 组）；`/wp-json/didi/v1/recharge` 是模拟充值（直接加余额），上线需接真实支付渠道并向用户披露。

[Project Knowledge Summary]
- Date: 2026-10-27
- Context: Discovered by Agent while verifying R2 image accessibility from the sandbox (workers.dev DNS is poisoned)
- Category: Troubleshooting & Debugging
- Instructions:
  - 沙箱 DNS 污染：`*.workers.dev` 被解析到无关 IP（Facebook 网段），本地 curl 直连失败；`*.monkeycode-ai.online` 也曾被 Cloudflare 拦截（HTTP 000）。
  - 绕过方法：先用 DoH 查真实 CF IP（`curl "https://dns.google/resolve?name=<domain>&type=A"` 或 1.1.1.1 的 dns-query），再用 `curl --resolve <domain>:443:<real-ip>` 强制直连，HTTPS SNI 正常即可命中 Cloudflare 边缘（实测 workers.dev 图片 200 OK）。
  - 已验证：didiAI CJ 图片迁移后 R2 链路端到端可用（随机 7 张抽样全部 HTTP 200，content-type image/jpeg，cache-control 1 年 immutable）；VPS 上线后仅需浏览器肉眼复核商品页。
