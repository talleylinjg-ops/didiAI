/* didi AI 桌面版（Electron）
 * 打包：npx electron-packager . didi-ai --platform=win32,darwin --arch=x64 --out=dist --overwrite
 * 目标站点 URL：部署到正式域名后修改 APP_URL 或打包前设置 DIDI_APP_URL 环境变量 */
const { app, BrowserWindow, shell, Menu, dialog } = require('electron');
const https = require('https');
const path = require('path');

const APP_VERSION = app.getVersion();               // 与站点 /app-version.json 比对
const APP_URL = process.env.DIDI_APP_URL || 'https://www.chacha.asia';
const VERSION_URL = (process.env.DIDI_APP_URL || 'https://www.chacha.asia') + '/app-version.json';
const DOWNLOAD_PAGE = (process.env.DIDI_APP_URL || 'https://www.chacha.asia') + '/download';

let win = null;

function createWindow() {
  win = new BrowserWindow({
    width: 1280,
    height: 860,
    minWidth: 420,
    minHeight: 600,
    title: 'didi AI',
    icon: path.join(__dirname, 'assets', 'icon-512.png'),
    backgroundColor: '#ffffff',
    autoHideMenuBar: false,
    webPreferences: {
      contextIsolation: true,
      nodeIntegration: false,
      sandbox: true,
    },
  });

  win.loadURL(APP_URL);

  /* 外链走系统默认浏览器，站内跳转留在应用内 */
  win.webContents.setWindowOpenHandler(({ url }) => {
    if (url.startsWith(APP_URL)) {
      win.loadURL(url);
      return { action: 'deny' };
    }
    shell.openExternal(url);
    return { action: 'deny' };
  });
}

function buildMenu() {
  const template = [
    {
      label: 'didi AI',
      submenu: [
        { label: '关于 didi AI', click: () => dialog.showMessageBox(win, { type: 'info', title: '关于', message: 'didi AI 桌面版 v' + APP_VERSION, detail: '一站式 AI 工具台\n' + APP_URL }) },
        { type: 'separator' },
        { label: '检查更新…', click: checkUpdate },
        { label: '打开下载页', click: () => win.loadURL(DOWNLOAD_PAGE) },
        { type: 'separator' },
        { role: 'quit', label: '退出' },
      ],
    },
    { label: '视图', submenu: [{ role: 'reload', label: '刷新' }, { role: 'forceReload' }, { role: 'toggleDevTools' }, { type: 'separator' }, { role: 'zoomIn', label: '放大' }, { role: 'zoomOut', label: '缩小' }, { role: 'resetZoom', label: '重置缩放' }, { type: 'separator' }, { role: 'togglefullscreen', label: '全屏' }] },
    { label: '窗口', submenu: [{ role: 'minimize', label: '最小化' }, { role: 'zoom' }, { role: 'close', label: '关闭' }] },
  ];
  if (process.platform === 'darwin') template[1].submenu.unshift({ role: 'copy', label: '复制' }, { role: 'paste', label: '粘贴' });
  Menu.setApplicationMenu(Menu.buildFromTemplate(template));
}

/* 在线升级提示：与站点 /app-version.json 比对版本号，有新版提示打开下载页 */
function checkUpdate() {
  const req = https.get(VERSION_URL, { timeout: 10000 }, (res) => {
    let data = '';
    res.on('data', (c) => (data += c));
    res.on('end', () => {
      try {
        const info = JSON.parse(data);
        const remote = String(info.version || '');
        if (remote && remote !== APP_VERSION) {
          const choice = dialog.showMessageBoxSync(win, {
            type: 'info',
            title: '发现新版本',
            message: 'didi AI 有新版本 v' + remote + '（当前 v' + APP_VERSION + '）',
            buttons: ['前往下载页', '稍后再说'],
            defaultId: 0,
          });
          if (choice === 0) win.loadURL(DOWNLOAD_PAGE);
        } else {
          dialog.showMessageBox(win, { type: 'info', title: '检查更新', message: '当前已是最新版本 v' + APP_VERSION });
        }
      } catch (e) {
        dialog.showErrorBox('检查更新失败', '无法解析版本信息，请稍后再试');
      }
    });
  });
  req.on('error', () => dialog.showErrorBox('检查更新失败', '网络不可用，请检查网络连接'));
  req.on('timeout', () => { req.destroy(); dialog.showErrorBox('检查更新失败', '请求超时，请稍后再试'); });
}

/* 启动 30 分钟后自动静默检查一次新版本 */
function scheduleAutoCheck() {
  setTimeout(() => {
    const req = https.get(VERSION_URL, { timeout: 10000 }, (res) => {
      let data = '';
      res.on('data', (c) => (data += c));
      res.on('end', () => {
        try {
          const info = JSON.parse(data);
          const remote = String(info.version || '');
          if (remote && remote !== APP_VERSION && win && !win.isDestroyed()) {
            const choice = dialog.showMessageBoxSync(win, {
              type: 'info',
              title: '发现新版本',
              message: 'didi AI 有新版本 v' + remote + '（当前 v' + APP_VERSION + '）',
              buttons: ['前往下载页', '稍后再说'],
              defaultId: 0,
            });
            if (choice === 0) win.loadURL(DOWNLOAD_PAGE);
          }
        } catch (e) { /* 静默失败 */ }
      });
    });
    req.on('error', () => {});
    req.on('timeout', () => req.destroy());
  }, 30 * 60 * 1000);
}

app.whenReady().then(() => {
  buildMenu();
  createWindow();
  scheduleAutoCheck();
  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});
