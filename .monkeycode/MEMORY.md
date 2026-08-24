# User Instruction Memory

This file records user instructions, preferences, and teachings for reference in future interactions.

## Entries

[Project Knowledge Summary]
- Date: 2026-08-23
- Context: Discovered by Agent while performing member center + ChatCut MCP feature work on didi-ai WordPress theme
- Category: Operations & Deployment
- Instructions:
  - didi-ai 站点的 admin 后台密码已于 2026-08-23 通过改密接口重置为 `admin2026`（原 `admin123` 已失效，改密功能已验证）。
  - 服务由 background terminal 管理：PHP 开发服务器（`php -S 0.0.0.0:8080 -t /workspace/didi_package /workspace/didi_package/router.php`，端口 8080）与 MariaDB。启动前先 `background_terminal_list` 检查是否已在运行，避免重复启动。
  - 会员档位含 `trial`（试用会员，最低档，¥0.01/7 天）/ `free` / `silver` / `gold`；`trial`、`silver`、`gold` 均绕过每日额度并解锁自定义模型，`free` 受限。
  - 消费流水存于用户 meta `didi_billing`（REST：GET `/wp-json/didi/v1/billing`）；改密端点 POST `/wp-json/didi/v1/change-password`。
