import UIKit
import WebKit

/// didi AI iOS 壳应用：全屏 WKWebView 加载站点，内置版本检查（/app-version.json）与下拉刷新。
/// App Store 上传前置：Apple Developer 账号 + App Store Connect 创建应用（Bundle ID com.chacha.didiai）。
@main
class AppDelegate: UIResponder, UIApplicationDelegate {

    var window: UIWindow?
    private var webView: WKWebView!

    private static let appURL = URL(string: "https://www.chacha.asia")!
    private static let versionURL = URL(string: "https://www.chacha.asia/app-version.json")!

    func application(_ application: UIApplication,
                     didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?) -> Bool {
        webView = WKWebView(frame: .zero, configuration: config)
        webView.navigationDelegate = self
        webView.backgroundColor = .white
        config.allowsInlineMediaPlayback = true
        config.mediaTypesRequiringUserActionForPlayback = []

        let config = WKWebViewConfiguration()
        config.allowsInlineMediaPlayback = true
        config.mediaTypesRequiringUserActionForPlayback = []

        webView = WKWebView(frame: .zero, configuration: config)
        webView.navigationDelegate = self
        webView.backgroundColor = .white

        // 支持下拉刷新
        let refresh = UIRefreshControl()
        refresh.addTarget(self, action: #selector(pullToRefresh), for: .valueChanged)
        webView.scrollView.addSubview(refresh)

        let controller = UIViewController()
        controller.view = webView

        // 顶部工具条：刷新 + 分享
        let toolbar = UIToolbar()
        toolbar.translatesAutoresizingMaskIntoConstraints = false
        let spacer = UIBarButtonItem(barButtonSystemItem: .flexibleSpace, target: nil, action: nil)
        let reload = UIBarButtonItem(barButtonSystemItem: .refresh, target: self, action: #selector(reloadPage))
        let share = UIBarButtonItem(barButtonSystemItem: .action, target: self, action: #selector(sharePage))
        toolbar.setItems([spacer, reload, spacer, share, spacer], animated: false)

        let container = UIView()
        container.backgroundColor = .white
        container.addSubview(webView)
        container.addSubview(toolbar)
        webView.translatesAutoresizingMaskIntoConstraints = false
        NSLayoutConstraint.activate([
            webView.topAnchor.constraint(equalTo: container.safeAreaLayoutGuide.topAnchor),
            webView.bottomAnchor.constraint(equalTo: toolbar.topAnchor),
            webView.leadingAnchor.constraint(equalTo: container.leadingAnchor),
            webView.trailingAnchor.constraint(equalTo: container.trailingAnchor),
            toolbar.leadingAnchor.constraint(equalTo: container.leadingAnchor),
            toolbar.trailingAnchor.constraint(equalTo: container.trailingAnchor),
            toolbar.bottomAnchor.constraint(equalTo: container.safeAreaLayoutGuide.bottomAnchor),
        ])

        window = UIWindow(frame: UIScreen.main.bounds)
        window?.rootViewController = controller
        window?.makeKeyAndVisible()

        webView.load(URLRequest(url: Self.appURL))
        scheduleVersionCheck()
        return true
    }

    @objc private func pullToRefresh() {
        webView.reload()
        DispatchQueue.main.asyncAfter(deadline: .now() + 1.2) {
            (self.webView.scrollView.subviews.compactMap { $0 as? UIRefreshControl }).forEach { $0.endRefreshing() }
        }
    }

    @objc private func reloadPage() {
        webView.reload()
    }

    @objc private func sharePage() {
        guard let url = webView.url else { return }
        let vc = UIActivityViewController(activityItems: [url], applicationActivities: nil)
        window?.rootViewController?.present(vc, animated: true)
    }

    /// 启动 30 分钟后检查站点 /app-version.json，与 MARKETING_VERSION 比对，有新版弹提示去下载页
    private func scheduleVersionCheck() {
        DispatchQueue.main.asyncAfter(deadline: .now() + 30 * 60) { [weak self] in
            self?.checkVersion()
        }
    }

    private func checkVersion() {
        URLSession.shared.dataTask(with: Self.versionURL) { [weak self] data, _, _ in
            guard let data = data,
                  let info = try? JSONSerialization.jsonObject(with: data) as? [String: Any],
                  let remote = info["version"] as? String else { return }
            let local = Bundle.main.object(forInfoDictionaryKey: "CFBundleShortVersionString") as? String ?? "1.0.0"
            guard remote != local else { return }
            DispatchQueue.main.async {
                let alert = UIAlertController(title: "发现新版本",
                                              message: "didi AI 有新版本 v\(remote)（当前 v\(local)），请前往下载页获取。",
                                              preferredStyle: .alert)
                alert.addAction(UIAlertAction(title: "打开下载页", style: .default) { _ in
                    if let page = (info["page"] as? String), let u = URL(string: page) {
                        UIApplication.shared.open(u)
                    }
                })
                alert.addAction(UIAlertAction(title: "稍后再说", style: .cancel))
                self?.window?.rootViewController?.present(alert, animated: true)
            }
        }.resume()
    }
}

extension AppDelegate: WKNavigationDelegate {
    /// 外链交给系统浏览器，站内跳转留在应用内
    func webView(_ webView: WKWebView,
                 decidePolicyFor navigationAction: WKNavigationAction,
                 decisionHandler: @escaping (WKNavigationActionPolicy) -> Void) {
        if navigationAction.navigationType == .linkActivated,
           let url = navigationAction.request.url,
           url.host != Self.appURL.host {
            UIApplication.shared.open(url)
            decisionHandler(.cancel)
            return
        }
        decisionHandler(.allow)
    }
}
