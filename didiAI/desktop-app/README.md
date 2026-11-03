# didi AI 桌面版（Electron）

Windows 与 macOS 桌面客户端，加载 didi AI 站点，内置菜单栏「检查更新」与 30 分钟静默检查。

## 开发调试

```bash
npm install
npm start
```

默认加载 `https://www.chacha.asia`，可用环境变量覆盖：

```bash
DIDI_APP_URL=http://localhost:8080 npm start
```

## 打包发布

```bash
npm run package
# 产物在 dist/：didi-ai-win32-x64/ 与 didi-ai-darwin-x64/
```

打包为 zip 后上传站点：

```bash
cd dist && zip -rq didi-ai-win-x64.zip didi-ai-win32-x64 && zip -rq didi-ai-mac-x64.zip didi-ai-darwin-x64
cp didi-ai-*.zip /workspace/didiAI/wp-content/uploads/apps/
```

## 版本升级流程

1. 修改 `package.json` 的 `version` 与站点主题 `functions.php` 中 `didi_ai_app_version()` 的 `version`
2. 重新打包并上传 zip 到 `wp-content/uploads/apps/`
3. 桌面客户端启动或点击「检查更新」时读取站点 `/app-version.json`，版本不同则提示前往下载页

## 注意

- macOS 包未签名，首次打开需右键 →「打开」绕过 Gatekeeper
- Windows 包未做图标（无 Wine 环境），需自定义图标时在装有 Wine 的机器上加 `--icon=assets/icon.ico` 重新打包
- 正式域名确定后修改 `main.js` 中的 `APP_URL`（或打包时传 `DIDI_APP_URL`）
