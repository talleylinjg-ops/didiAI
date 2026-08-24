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
