# didi AI iOS 版（App Store 壳应用）

WKWebView 全屏壳应用，内置下拉刷新、外链跳系统浏览器、版本检查（/app-version.json → 提示前往下载页）。

## 前置条件（必须在 macOS 上完成）

1. **Apple Developer 账号**（$99/年，appstoreconnect.apple.com 注册）——沙箱 Linux 环境无法构建/签名 iOS 包，此为苹果硬性要求
2. 一台装有 **Xcode 14+** 的 Mac
3. [XcodeGen](https://github.com/yonaskolb/XcodeGen)：`brew install xcodegen`

## 构建步骤

```bash
cd ios-app
xcodegen                    # 由 project.yml 生成 didi-ai.xcodeproj
open didi-ai.xcodeproj
```

Xcode 中：

1. 选中 didi-ai target → Signing & Capabilities → 选择你的 Team（自动签名）
2. Bundle ID 默认 `com.chacha.didiai`，可在 App Store Connect 先注册此 ID
3. 目标设备选 Generic iOS Device → Product → Archive
4. 上传 App Store Connect（Window → Organizer → Distribute App）

## App Store 审核注意

- 纯网页壳存在被 4.2「Minimum Functionality」条款拒绝的风险，建议：
  - 在 App Store Connect 描述中强调应用内独有能力（推送通知、原生分享、离线缓存等）
  - 首个版本可先上线 iOS PWA（已实现：Safari 添加主屏幕），商店版本后续迭代
- 正式域名确定后同步修改 `Sources/AppDelegate.swift` 中的 `appURL` 与 `versionURL`
- 版本号升级：Xcode 中改 MARKETING_VERSION，并同步站点 `functions.php` 的 `didi_ai_app_version()`

## 与其他端的一致性

- 包名：Android `com.chacha.didiai` / iOS `com.chacha.didiai`（统一品牌归属）
- 版本接口：全部读取站点 `/app-version.json`（含 apk/win/mac/linux 下载地址与 version）
