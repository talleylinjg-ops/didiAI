# VPS 迁移部署指南

本站从沙箱（php -S + router.php）迁移到标准 VPS（Nginx + PHP-FPM + MySQL）的操作步骤。

## 1. 环境要求

- Nginx（或 Apache）
- PHP 8.2 + FPM（扩展：mysqli / curl / gd / mbstring / zip / imagick 或 gd）
- MySQL 5.7+ / 8.0
- HTTPS 证书（certbot）

## 2. 代码与数据

```bash
# 克隆代码
git clone https://github.com/talleylinjg-ops/didiAI.git /var/www/didiAI

# 导入数据库（含渠道 Key，此文件在 .gitignore 中，需手动从沙箱传输，勿公开）
mysql -u root -p -e "CREATE DATABASE wp_db CHARACTER SET utf8mb4"
mysql -u root -p wp_db < database/didi_wp_vps_20261008.sql

# 创建 DB 用户（或直接用 root，改 wp-config.php 对应项）
```

## 3. wp-config.php

修改 `DB_NAME` / `DB_USER` / `DB_PASSWORD` / `DB_HOST` 为 VPS 实际值。
文件顶部的 `zlib.output_compression` 两行可保留（输出 gzip）；若交给 Nginx gzip，删除亦可。

## 4. 替换站点域名（关键）

库内 URL 均为 `http://localhost:8080`，必须整体替换：

```bash
# 方式一：wp-cli（推荐）
wp search-replace 'http://localhost:8080' 'https://你的域名' --all-tables

# 方式二：SQL（图片附件 guid/序列化数据需用工具，勿裸 UPDATE）
```

同时确认 `wp_options` 的 `siteurl` / `home` 已替换为 https 域名。

## 5. Nginx 配置要点

```nginx
server {
    listen 443 ssl;
    server_name 你的域名;
    root /var/www/didiAI;
    index index.php;

    # 静态文件直出 + 长缓存（替代沙箱 router.php 的静态自管）
    location ~* \.(css|js|mjs|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|eot|mp3|wav|m4a|aac|mp4|webm|json)$ {
        expires 1y;
        add_header Cache-Control "public, max-age=31536000, immutable";
        add_header X-Content-Type-Options nosniff;
        try_files $uri =404;
    }
    location /wp-content/uploads/ {
        expires 30d;
        try_files $uri =404;
    }

    gzip on;
    gzip_types text/css application/javascript application/json text/plain image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

沙箱专用的 `router.php` 与 `.user.ini` 在 VPS 上不需要，可忽略。

## 6. PHP-FPM 上传限制

图片素材上传实测需求（默认 2M 会静默失败，报“请选择图片文件”）：

```ini
upload_max_filesize = 15M
post_max_size = 16M
```

写入 `/etc/php/8.2/fpm/php.ini` 后 `systemctl reload php8.2-fpm`。

## 7. 权限

```bash
chown -R www-data:www-data /var/www/didiAI/wp-content/uploads
```

## 8. 渠道配置现状（存于 DB `didi_ai_config`，随导入生效）

- 智谱（文本 GLM-4.5-Flash / 图片）：已配，当前可用
- MPT 门户（免费成片）：已配
- didi Media（MediaCut 新版）：**Key 待用户提供**——图片页/音频页/剪辑页的 didi Media 功能在其提供 Key 前会报 invalid api key
- 后台「didi AI 配置」页可在线维护各渠道

## 9. 可选

- Cloudflare 镜像工程在 `../didiAI-mirror`（wrangler.toml 的 ORIGIN 换成新域名后 `wrangler deploy`）
- 迁移后跑一遍各 TAB 生成功能做冒烟测试
