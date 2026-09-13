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
  'music'   => array('label' => '音频', 'url' => '/ai-music',   'icon' => '&#127925;'),
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
        <button id="searchBtn" type="button">didi</button>
        <div class="home-blank-inner"></div>
      </div>
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

  // 首页默认无选择：仅显示功能 Tab，下方留空 2 行；点击 Tab / didi 后加载
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
