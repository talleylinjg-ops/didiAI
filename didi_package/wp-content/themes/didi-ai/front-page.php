<?php
/**
 * 前台首页（原生样式：大渐变 Logo + 功能 Tab + 输入方式图标 + 内嵌一体化）
 * 输入框由 iframe 功能页承载，顶部仅保留功能 Tab 与输入方式图标
 */
get_header();
$logged = is_user_logged_in();
$apps_left = array(
  'code'    => array('label' => '代码', 'url' => '/ai-code',    'icon' => '&#128187;'),
  'office'  => array('label' => '工作', 'url' => '/ai-work',    'icon' => '&#128202;'),
  'video'   => array('label' => '视频', 'url' => '/ai-video',   'icon' => '&#128250;'),
  'avatar'  => array('label' => '数字人', 'url' => '/ai-avatar','icon' => '&#128100;'),
  'animate' => array('label' => '动画', 'url' => '/ai-animate','icon' => '&#128126;'),
  'image'   => array('label' => '图片', 'url' => '/ai-image',   'icon' => '&#127912;'),
  'ask'     => array('label' => '提问', 'url' => '/ai-chat',    'icon' => '&#128172;'),
  'music'   => array('label' => '音乐', 'url' => '/ai-music',   'icon' => '&#127925;'),
  'write'   => array('label' => '写作', 'url' => '/ai-write',   'icon' => '&#9997;'),
  'ppt'     => array('label' => 'PPT', 'url' => '/ai-ppt',      'icon' => '&#128209;'),
  'edit'    => array('label' => '剪辑', 'url' => '/ai-edit',    'icon' => '&#9998;'),
);
?>
<style>
  main{padding-top:0;}
  body.didi-show-frame main{padding-top:80px;}
  .ws-frame{width:100%;max-width:1080px;margin:0 auto 40px;}
  .ws-frame iframe{width:100%;height:calc(100vh - 220px);min-height:480px;border:1px solid #e5e6eb;border-radius:16px;display:block;background:#fff;box-shadow:0 6px 30px rgba(22,104,220,.08);}
  .ws-login{max-width:480px;margin:20px auto 40px;text-align:center;padding:40px;border:1px solid #e5e6eb;border-radius:16px;background:#fff;display:none;}
  #frameHost{display:none;}
  body.didi-show-frame #frameHost{display:block;}
  body.didi-show-frame .logo{font-size:40px;margin-bottom:20px;}
  .home-module{width:100%;max-width:1080px;margin:0 auto 24px;}
  .home-module .search-wrap{width:100%;max-width:1080px;margin:0 auto;}
  .top-bar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 20px;border:2px solid #e5e6eb;border-radius:28px;background:#fff;transition:border-color .2s,box-shadow .2s;flex-wrap:wrap}
  .top-bar:focus-within{border-color:#1668dc;box-shadow:0 6px 28px rgba(22,104,220,.15)}
  .top-bar .home-tab-row{display:flex;align-items:center;gap:24px;flex:1;flex-wrap:wrap}
  .top-bar .home-tab{font-size:15px;color:#4e5969;text-decoration:none;padding:4px 2px;border-bottom:2px solid transparent;transition:color .2s,border-color .2s;white-space:nowrap}
  .top-bar .home-tab:hover{color:#1668dc}
  .top-bar .home-tab.active{color:#1668dc;font-weight:700;border-bottom:2px solid #1668dc}
  .top-tools{display:flex;align-items:center;gap:8px;flex-shrink:0}
  .tool-icon{width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #e5e6eb;border-radius:10px;background:#f7f8fa;font-size:20px;cursor:pointer;transition:all .2s;color:#4e5969}
  .tool-icon:hover{color:#1668dc;border-color:#1668dc;background:#eef4ff}
  .tool-icon svg{width:20px;height:20px}
  .top-bar #searchBtn{width:90px;height:46px;padding:0;border:none;border-radius:16px;background:linear-gradient(180deg,#1668dc,#6a3de8);color:#fff;font-size:20px;font-weight:600;cursor:pointer;transition:opacity .2s;flex-shrink:0}
  .top-bar #searchBtn:hover{opacity:.88}
  .home-hero{text-align:center;padding:18px 20px 0;margin-top:-182px;}
  .home-hero .logo{display:inline-block;font-size:64px;letter-spacing:4px;margin-bottom:0;}
  .home-blank-inner{flex-basis:100%;height:65px;}
  body.didi-show-frame .home-hero{display:none;}
  body.didi-show-frame .home-blank-inner{display:none;}
  body.didi-show-frame .home-module{margin-top:20px;}
  body.didi-show-frame .top-bar{padding:12px 18px;border-radius:22px;}
  body.didi-show-frame .top-bar #searchBtn{display:none;}
  .tool-icon svg{display:block}
  #toolVoice svg{width:22px;height:22px}
</style>
<main>
  <div class="home-hero" id="homeHero">
    <span class="logo">didi AI</span>
  </div>
  <div class="home-module">
    <div class="search-wrap">
      <div class="top-bar">
        <div class="home-tab-row" id="homeTabs">
          <?php foreach ($apps_left as $key => $app): ?>
            <a href="javascript:void(0)" class="home-tab" data-key="<?php echo esc_attr($key); ?>" data-url="<?php echo esc_attr(home_url($app['url'])); ?>"><?php echo $app['icon']; ?> <?php echo esc_html($app['label']); ?></a>
          <?php endforeach; ?>
        </div>
        <div class="top-tools">
          <button type="button" class="tool-icon" id="toolVoice" title="语音输入">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
          </button>
          <button type="button" class="tool-icon" id="toolFile" title="上传文件">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          </button>
          <button type="button" class="tool-icon" id="toolImage" title="上传图片">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </button>
          <button id="searchBtn" type="button">didi</button>
        </div>
        <div class="home-blank-inner"></div>
      </div>
      <input type="file" id="toolImageInput" accept="image/*" style="display:none;">
    </div>
  </div>
  <div class="ws-frame" id="frameHost"></div>
</main>
<script>
(function () {
  var loggedIn = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
  var tabs = document.querySelectorAll('.home-tab');
  var frameHost = document.getElementById('frameHost');
  var iframe = null;
  var curUrl = null;
  var iframeReady = false;
  function gotoLogin() {
    location.href = <?php echo wp_json_encode(home_url('/login')); ?>;
  }
  function load(key, url) {
    if (!loggedIn) { gotoLogin(); return; }
    curUrl = url;
    iframeReady = false;
    document.body.classList.add('didi-show-frame');
    tabs.forEach(function (t) { t.classList.toggle('active', t.getAttribute('data-key') === key); });
    if (iframe) iframe.remove();
    iframe = document.createElement('iframe');
    iframe.src = url + '?embed=1';
    iframe.addEventListener('load', function () { iframeReady = true; });
    frameHost.appendChild(iframe);
  }
  function postToIframe(msg) {
    if (!iframe || !iframeReady) return;
    try { iframe.contentWindow.postMessage(msg, '*'); } catch (e) {}
  }
  tabs.forEach(function (t) {
    t.addEventListener('click', function () {
      load(t.getAttribute('data-key'), t.getAttribute('data-url'));
    });
  });

  // 语音输入（识别后通过 postMessage 发送到 iframe）
  document.getElementById('toolVoice').addEventListener('click', function () {
    if (!loggedIn) { gotoLogin(); return; }
    var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) { if (window.toast) toast('当前浏览器不支持语音输入', 'error'); return; }
    var recog = new SR();
    recog.lang = 'zh-CN';
    recog.interimResults = false;
    recog.maxAlternatives = 1;
    recog.onresult = function (e) {
      var t = e.results[0][0].transcript;
      postToIframe({ type: 'didi-submit', text: t });
    };
    recog.onerror = function (e) {
      if (window.toast) toast('语音识别失败：' + (e.error || ''), 'error');
    };
    recog.start();
  });

  // 文件输入（读取文本内容通过 postMessage 发送到 iframe）
  document.getElementById('toolFile').addEventListener('click', function () {
    if (!loggedIn) { gotoLogin(); return; }
    var input = document.createElement('input');
    input.type = 'file';
    input.accept = '.txt,.md,.csv,.json,.log,.js,.py,.html,.css,.xml,.yml,.yaml';
    input.style.display = 'none';
    document.body.appendChild(input);
    input.addEventListener('change', function () {
      var f = input.files[0];
      if (!f) return;
      var reader = new FileReader();
      reader.onload = function () {
        var text = String(reader.result || '').slice(0, 6000);
        postToIframe({ type: 'didi-submit', text: '请分析以下文件内容：\n\n' + text });
      };
      reader.readAsText(f);
      input.remove();
    });
    input.click();
  });

  // 图片输入（上传到服务器，URL 通过 postMessage 发送到 iframe）
  document.getElementById('toolImage').addEventListener('click', function () {
    if (!loggedIn) { gotoLogin(); return; }
    document.getElementById('toolImageInput').click();
  });
  document.getElementById('toolImageInput').addEventListener('change', function () {
    var f = this.files[0];
    if (!f) return;
    var fd = new FormData();
    fd.append('file', f);
    var btnT = document.getElementById('toolImage');
    btnT.disabled = true;
    fetch('/wp-json/didi/v1/upload', {
      method: 'POST',
      headers: { 'X-WP-Nonce': (window.didiRestNonce || '') },
      body: fd,
      credentials: 'same-origin'
    }).then(function (r) { return r.json(); })
    .then(function (d) {
      if (d.ok && d.url) {
        postToIframe({ type: 'didi-submit', text: '图片参考：' + d.url + '\n请根据这张图片处理：' });
      } else {
        throw new Error((d && d.message) || '上传失败');
      }
    }).catch(function (err) {
      if (window.toast) toast(err.message, 'error');
    }).finally(function () {
      btnT.disabled = false;
      document.getElementById('toolImageInput').value = '';
    });
  });

  // 首页默认无选择：仅显示功能 Tab 与输入方式图标，下方留空 2 行；点击 Tab / didi 后加载
  var firstUrl = tabs[0].getAttribute('data-url');
  var firstKey = tabs[0].getAttribute('data-key');

  document.getElementById('searchBtn').addEventListener('click', function () {
    if (!loggedIn) { gotoLogin(); return; }
    if (!iframe) { load(firstKey, firstUrl); }
    postToIframe({ type: 'didi-focus' });
  });
})();
</script>
<?php get_footer(); ?>
