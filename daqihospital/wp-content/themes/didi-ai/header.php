<?php
/**
 * Header 模板
 * 左侧：访客使用记录（分模块图标横排）；右侧：didi 品牌 + 导航链接 + 语言
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
<style id="didi-usage-inline">
.nav-usage .usage-panel{display:none;position:absolute;top:38px;left:0;min-width:140px;max-width:180px;height:calc(100vh - 48px);height:calc(100dvh - 48px);overflow-y:auto;scrollbar-width:none;background:#fff;border:1px solid #e5e6eb;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.12);padding:10px 12px;z-index:120}
.nav-usage .usage-panel::-webkit-scrollbar{display:none}
.nav-usage.open .usage-panel{display:block}
.nav-usage .usage-list{display:flex;flex-direction:column;gap:4px}
.nav-usage .usage-cat{border-bottom:1px dashed #f2f3f5;padding:6px 0}
.nav-usage .usage-cat:last-child{border-bottom:none}
.nav-usage .usage-cat-head{display:flex;align-items:center;gap:6px;font-size:13px;color:#4e5969;text-decoration:none;white-space:nowrap;cursor:pointer;overflow:hidden;transition:color .2s}
.nav-usage .usage-cat-head:hover{color:#1668dc}
.nav-usage .usage-cat-head .usage-ico{font-size:14px}
.nav-usage .usage-cat-head .usage-label{font-size:12.5px;min-width:0;overflow:hidden;text-overflow:ellipsis}
.nav-usage .usage-cat-head .usage-num{font-size:11px;color:#1668dc;font-weight:700;margin-left:auto}
.nav-usage .usage-cat-head .usage-arrow{font-size:10px;color:#c9cdd4;transition:transform .2s}
.nav-usage .usage-cat .usage-cat-head .usage-arrow{transform:rotate(90deg)}
.nav-usage .usage-cat.closed .usage-cat-head .usage-arrow{transform:rotate(0deg)}
.nav-usage .usage-cat.closed .usage-logs,.nav-usage .usage-cat.closed .usage-more{display:none}
.nav-usage .usage-logs{list-style:none;margin:4px 0 0 22px;display:flex;flex-direction:column;gap:3px}
.nav-usage .usage-log{display:flex;flex-direction:column;gap:2px;font-size:12px;color:#86909c;line-height:1.5;word-break:break-all}
.nav-usage .usage-log-link{display:flex;flex-direction:column;gap:2px;text-decoration:none;color:inherit;border-radius:6px;padding:3px 4px;transition:background .2s}
.nav-usage .usage-log-link:hover{background:#f2f3f5}
.nav-usage .usage-log-link:hover .usage-log-note{color:#1668dc}
.nav-usage .usage-log-note{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.nav-usage .usage-log-time{font-size:11px;color:#c9cdd4;white-space:nowrap}
.nav-usage .usage-empty,.nav-usage .usage-loading{font-size:12.5px;color:#c9cdd4;white-space:nowrap;padding:4px 2px}
.nav-usage .usage-more{display:block;margin:4px 0 0 22px;font-size:12px;color:#1668dc;background:none;border:none;cursor:pointer;padding:2px 0;transition:color .2s}
.nav-usage .usage-more:hover{color:#0e42d2}
</style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header>
  <div class="nav-right">
    <div class="nav-left">
      <a class="logo-small" href="<?php echo esc_url(home_url('/')); ?>">大气AI医院</a>
      <nav class="nav-links">
        <?php if (is_user_logged_in()) : ?>
          <a href="<?php echo esc_url(home_url('/recharge')); ?>">充值</a>
          <a href="<?php echo esc_url(home_url('/member')); ?>" style="display:flex;align-items:center;gap:4px;"><span data-i18n="member"></span><b id="nav-balance" style="color:#1668dc;font-size:12px;" title="我的点数"></b></a>
          <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">退出</a>
        <?php else : ?>
          <a href="<?php echo esc_url(home_url('/recharge')); ?>">充值</a>
          <a href="<?php echo esc_url(home_url('/member')); ?>"><span data-i18n="member"></span></a>
        <?php endif; ?>
      </nav>
    </div>
    <div class="lang-box">
      <button class="lang-btn" id="langBtn">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        <span id="langLabel"></span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <div class="lang-panel" id="langPanel"></div>
    </div>
  </div>
</header>
<script>
(function () {
  var isLogged = <?php echo is_user_logged_in() ? 'true' : 'false'; ?>;
  if (isLogged) {
    var el = document.getElementById('nav-balance');
    fetch(<?php echo wp_json_encode(rest_url('didi/v1/quota')); ?>, {
      headers: { 'X-WP-Nonce': (window.didiRestNonce || '') }
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (el && d && typeof d.balance === 'number') {
          el.textContent = d.balance.toLocaleString() + ' 点';
        }
      }).catch(function () {});
  }
})();
</script>
<script>
(function () {
  function growInit() {
    var boxes = document.querySelectorAll('.ai-chat-input textarea, #codexPrompt, textarea#prompt, .prompt-row textarea, #promptText, .gen-shell textarea.prompt');
    if (!boxes.length) return;
    for (var i = 0; i < boxes.length; i++) {
      (function (ta) {
        if (ta.getAttribute('data-grown') === '1') return;
        ta.setAttribute('data-grown', '1');
        var base = Math.max(44, ta.offsetHeight || 44);
        var cap = Math.round(base * 1.5);
        var fit = function () {
          if (!document.body.contains(ta)) return;
          var h = Math.min(Math.max(base, ta.scrollHeight), cap);
          ta.style.overflowY = (ta.scrollHeight > h) ? 'auto' : 'hidden';
          ta.style.height = h + 'px';
        };
        ta.addEventListener('input', fit);
        ta.addEventListener('focus', fit);
        window.addEventListener('resize', fit);
        fit();
      })(boxes[i]);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', growInit);
  } else {
    growInit();
  }
})();
</script>
