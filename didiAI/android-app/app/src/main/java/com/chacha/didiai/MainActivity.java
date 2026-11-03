package com.chacha.didiai;

import android.app.Activity;
import android.content.ComponentName;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import androidx.browser.customtabs.CustomTabsClient;
import androidx.browser.customtabs.CustomTabsServiceConnection;
import androidx.browser.customtabs.CustomTabsSession;
import androidx.browser.trusted.ScreenOrientation;
import androidx.browser.trusted.TrustedWebActivityIntentBuilder;

/**
 * TWA 启动入口：绑定 Chrome Custom Tabs 服务后以全屏 Trusted Web Activity 打开站点。
 * 需要站点 /.well-known/assetlinks.json 与本包签名匹配（未验证时 Chrome 回退为带地址栏的 Custom Tab）。
 * 站点内容升级提示由页面内 Service Worker toast 实现；安装包升级由用户访问站点 /download 页下载新 APK。
 */
public class MainActivity extends Activity {

    private static final String APP_URL = "https://www.chacha.asia";
    private static final String CHROME_PACKAGE = "com.android.chrome";

    private CustomTabsServiceConnection mConnection;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        mConnection = new CustomTabsServiceConnection() {
            @Override
            public void onCustomTabsServiceConnected(ComponentName name, CustomTabsClient client) {
                CustomTabsSession session = client.newSession(null);
                if (session == null) {
                    fallbackToBrowser();
                    return;
                }
                launch(session);
            }

            @Override
            public void onServiceDisconnected(ComponentName name) {
                finish();
            }
        };

        boolean bound = CustomTabsClient.bindCustomTabsService(this, CHROME_PACKAGE, mConnection);
        if (!bound) {
            fallbackToBrowser();
        }
    }

    private void fallbackToBrowser() {
        // Chrome 不可用：回退系统浏览器打开站点
        try {
            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(APP_URL)));
        } catch (Exception ignored) {
        }
        finish();
    }

    private void launch(CustomTabsSession session) {
        new TrustedWebActivityIntentBuilder(Uri.parse(APP_URL))
                .setScreenOrientation(ScreenOrientation.PORTRAIT)
                .build(session)
                .launchTrustedWebActivity(this);
        finish();
    }

    @Override
    protected void onDestroy() {
        if (mConnection != null) {
            unbindService(mConnection);
            mConnection = null;
        }
        super.onDestroy();
    }
}
