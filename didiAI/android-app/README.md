# didi AI Android APK（TWA）

Trusted Web Activity 壳应用：全屏打开 didi AI 站点，站点内 Service Worker 负责缓存与升级提示。

## 构建

环境依赖：JDK 17、Android SDK（脚本自动安装）、Gradle 8.7（脚本自动下载）。

```bash
bash build-apk.sh
# 产物：wp-content/uploads/apps/didi-ai.apk
```

## 发布新版本

1. `app/build.gradle` 中递增 `versionCode`（整数）与 `versionName`
2. 站点主题 `functions.php` 中 `didi_ai_app_version()` 的 `version` 同步为新的 `versionName`
3. 重新构建 `bash build-apk.sh`，APK 自动复制到站点下载目录
4. 用户端提示升级：APK 内打开站点即走 PWA 的 SW 升级 toast（站点内容层面）；安装包本体升级由用户访问 /download 下载新 APK

## 网站关联（Digital Asset Links）

- 签名指纹（SHA256）已写入站点 `/.well-known/assetlinks.json`（主题 functions.php 输出）
- 包名 `com.chacha.didiai`；正式域名上线后 TWA 全屏生效（未验证时 Chrome 回退为带地址栏的 Custom Tab）
- 换 keystore 必须同步更新 assetlinks.json 指纹

## 注意

- 默认 URL `https://www.chacha.asia`，正式域名确定后在 `MainActivity.java` 中修改
- keystore 密码为开发用默认值（didi-ai-2026），上线前建议更换并重新登记指纹
