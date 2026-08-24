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
.nav-usage .usage-panel{display:none;position:absolute;top:38px;left:0;min-width:140px;max-width:180px;max-height:70vh;overflow-y:auto;background:#fff;border:1px solid #e5e6eb;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.12);padding:10px 12px;z-index:120}
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
.nav-usage .usage-cat.open .usage-cat-head .usage-arrow{transform:rotate(90deg)}
.nav-usage .usage-cat .usage-logs,.nav-usage .usage-cat .usage-more{display:none}
.nav-usage .usage-cat.open .usage-logs{display:flex}
.nav-usage .usage-cat.open .usage-more{display:block}
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
<header>
  <div class="nav-usage" id="navUsage">
    <button class="usage-title" id="usageToggle" type="button">使用记录</button>
    <div class="usage-panel" id="usagePanel"></div>
  </div>
  <div class="nav-right">
    <div class="nav-left">
      <a class="logo-small" href="<?php echo esc_url(home_url('/')); ?>">didi</a>
      <nav class="nav-links">
        <?php if (is_user_logged_in()) : ?>
          <a href="<?php echo esc_url(home_url('/blog')); ?>"><span data-i18n="blog"></span></a>
          <a href="<?php echo esc_url(home_url('/forum')); ?>">论坛</a>
          <a href="<?php echo esc_url(home_url('/recharge')); ?>">充值</a>
          <a href="<?php echo esc_url(home_url('/member')); ?>" style="display:flex;align-items:center;gap:4px;"><span data-i18n="member"></span><b id="nav-balance" style="color:#1668dc;font-size:12px;" title="我的点数"></b></a>
          <a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">退出</a>
        <?php else : ?>
          <a href="<?php echo esc_url(home_url('/blog')); ?>"><span data-i18n="blog"></span></a>
          <a href="<?php echo esc_url(home_url('/forum')); ?>">论坛</a>
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
  var usageBox = document.getElementById('navUsage');
  var usagePanel = document.getElementById('usagePanel');
  var usageToggle = document.getElementById('usageToggle');
  if (usageBox && usagePanel) {
    if (usageToggle) {
      usageToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        usageBox.classList.toggle('open');
      });
      document.addEventListener('click', function (e) {
        if (!usageBox.contains(e.target)) usageBox.classList.remove('open');
      });
    }
    if (!isLogged) {
      var glist = document.createElement('div');
      glist.className = 'usage-list';
      glist.innerHTML = '<span class="usage-empty">登录后查看使用记录</span>';
      usagePanel.appendChild(glist);
    } else {
    fetch(<?php echo wp_json_encode(rest_url('didi/v1/history')); ?>, {
      headers: { 'X-WP-Nonce': (window.didiRestNonce || '') }
    })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        var items = d && d.items ? d.items : [];
        var list = document.createElement('div');
        list.className = 'usage-list';
        var has = false;
        items.forEach(function (it) {
          if (!it || !it.count) return;
          has = true;
          var wrap = document.createElement('div');
          wrap.className = 'usage-cat open';
          var head = document.createElement('a');
          head.className = 'usage-cat-head';
          head.href = it.url || '#';
          head.title = '点击展开该分类内容';
          head.innerHTML = '<span class="usage-ico">' + (it.icon || '') + '</span><span class="usage-label">' + (it.label || '') + '</span><span class="usage-num">' + it.count + '</span><span class="usage-arrow">&#9654;</span>';
          head.addEventListener('click', function (e) {
            e.preventDefault();
            wrap.classList.toggle('open');
          });
          wrap.appendChild(head);
          var logs = it.logs || [];
          if (logs.length > 0) {
            var logsBox = document.createElement('ul');
            logsBox.className = 'usage-logs';
            var p = function (n) { return (n < 10 ? '0' : '') + n; };
            var buildLog = function (lg) {
              if (!lg || !lg.note) return null;
              var li = document.createElement('li');
              li.className = 'usage-log';
              var t = lg.t ? new Date(lg.t * 1000) : null;
              var ts = '';
              if (t) {
                ts = t.getFullYear() + '-' + p(t.getMonth() + 1) + '-' + p(t.getDate()) + ' ' + p(t.getHours()) + ':' + p(t.getMinutes());
              }
              var href = encodeURI(it.url || '#') + '?q=' + encodeURIComponent(typeof lg.note === 'string' ? lg.note : '');
              li.innerHTML = '<a class="usage-log-link" href="' + href + '" title="点击打开这条记录"><span class="usage-log-note">' + (typeof lg.note === 'string' ? lg.note : '') + '</span>' + (ts ? '<span class="usage-log-time">' + ts + '</span>' : '') + '</a>';
              return li;
            };
            var MAX_LOGS = 5;
            var shownCount = 0;
            logs.forEach(function (lg) {
              if (shownCount >= MAX_LOGS) return;
              var li = buildLog(lg);
              if (li) { logsBox.appendChild(li); shownCount++; }
            });
            if (logsBox.children.length) wrap.appendChild(logsBox);
            if (logs.length > shownCount) {
              var rest = logs.slice(shownCount);
              var moreBtn = document.createElement('button');
              moreBtn.type = 'button';
              moreBtn.className = 'usage-more';
              moreBtn.textContent = '更多 ' + rest.length + ' 条';
              moreBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                rest.forEach(function (lg) {
                  var li = buildLog(lg);
                  if (li) logsBox.appendChild(li);
                });
                moreBtn.remove();
              });
              wrap.appendChild(moreBtn);
            }
          }
          list.appendChild(wrap);
        });
        if (!has) {
          list.innerHTML = '<span class="usage-empty">暂无使用记录</span>';
        }
        usagePanel.appendChild(list);
      }).catch(function () {
        usagePanel.innerHTML = '<span class="usage-empty">加载失败</span>';
      });
    }
  }
})();
</script>
