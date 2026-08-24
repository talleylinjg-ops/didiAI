<?php
/**
 * 会员中心页面
 * Template Name: 会员中心
 * 访客可见：展示权益卡与定价表；账户信息区仅登录用户显示
 */
get_header();
$logged_in = is_user_logged_in();
$u = $logged_in ? wp_get_current_user() : null;
$q = $logged_in ? didi_ai_user_quota() : array('membership' => 'free', 'membership_name' => '普通会员', 'membership_color' => '#86909c', 'balance' => 0);
$ms = didi_ai_memberships();
$pricing = didi_ai_pricing();
$billing = $logged_in ? didi_ai_billing_items(20) : array();
$expire = $logged_in ? get_user_meta($u->ID, 'didi_membership_expire', true) : '';
?>
<main style="flex-direction:column;align-items:center;padding:90px 20px 60px;">
  <h1 style="font-size:30px;margin-bottom:8px;">会员中心</h1>
  <p style="font-size:14px;color:#86909c;margin-bottom:28px;">一个钱包 · 所有模型统一计费</p>

  <?php if ($logged_in): ?>
  <div style="width:100%;max-width:880px;display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
    <div style="border:1px solid #e5e6eb;border-radius:16px;padding:24px;">
      <h3 style="font-size:16px;margin-bottom:14px;">账户信息</h3>
      <p style="font-size:14px;color:#4e5969;margin-bottom:8px;">用户名：<b style="color:#1f2329;"><?php echo esc_html($u->display_name ? $u->display_name : $u->user_login); ?></b></p>
      <p style="font-size:14px;color:#4e5969;margin-bottom:8px;">邮箱：<?php echo esc_html($u->user_email); ?></p>
      <p style="font-size:14px;color:#4e5969;">注册时间：<?php echo esc_html(date('Y-m-d', strtotime($u->user_registered))); ?></p>
      <?php if ($q['membership'] !== 'free' && $expire): ?>
      <p style="font-size:13px;color:#16a34a;margin-top:10px;">会员有效期至：<?php echo esc_html($expire); ?></p>
      <?php endif; ?>
    </div>
    <div style="border:1px solid #e5e6eb;border-radius:16px;padding:24px;background:#f7f9ff;">
      <h3 style="font-size:16px;margin-bottom:14px;">我的资产</h3>
      <p style="font-size:14px;color:#4e5969;margin-bottom:8px;">当前等级：<b style="color:<?php echo esc_attr($q['membership_color']); ?>;"><?php echo esc_html($q['membership_name']); ?></b></p>
      <p style="font-size:24px;color:#1668dc;font-weight:700;margin:10px 0;"><?php echo esc_html(number_format($q['balance'])); ?> <span style="font-size:14px;color:#86909c;font-weight:400;">点数</span></p>
      <p style="font-size:12px;color:#86909c;margin-bottom:16px;">¥1 = 100 点数 · 各模型按定价表扣费</p>
      <a href="<?php echo esc_url(home_url('/recharge')); ?>" class="btn-primary" style="display:inline-block;margin-right:8px;text-decoration:none;">去充值</a>
      <a href="<?php echo esc_url(home_url('/ai-edit')); ?>" style="display:inline-block;padding:10px 20px;border:1px solid #1668dc;border-radius:8px;font-size:14px;color:#1668dc;text-decoration:none;">去使用</a>
    </div>
  </div>
  <?php else: ?>
  <div style="width:100%;max-width:880px;border:1px solid #e5e6eb;border-radius:16px;padding:24px;text-align:center;background:#f7f9ff;">
    <p style="font-size:14px;color:#4e5969;margin-bottom:12px;">登录后可查看账户信息、点数余额、消费记录与会员开通入口</p>
    <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn-primary" style="display:inline-block;text-decoration:none;">登录 / 注册</a>
  </div>
  <?php endif; ?>

  <div style="width:100%;max-width:880px;margin-top:24px;">
    <h3 style="font-size:16px;margin-bottom:12px;">会员方案（解锁全部自定义模型 · 不限每日额度）</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px;">
      <div style="border:1px solid #5eead4;border-radius:12px;padding:18px;background:#f0fdfa;position:relative;overflow:hidden;">
        <div style="position:absolute;top:10px;right:-28px;background:#14b8a6;color:#fff;font-size:11px;padding:3px 32px;transform:rotate(45deg);">试用</div>
        <p style="font-size:14px;color:#0d9488;">试用会员（最低档）</p>
        <p style="font-size:28px;font-weight:700;color:#1f2329;margin:6px 0;">¥0.01<span style="font-size:13px;color:#86909c;font-weight:400;"> /7天</span></p>
        <p style="font-size:12px;color:#16a34a;">解锁自定义模型 · 每日额度翻倍</p>
        <p style="font-size:12px;color:#86909c;margin-top:8px;">适合先体验全部功能</p>
        <?php if ($logged_in && $q['membership'] === 'free'): ?>
          <button id="btn-join-trial" style="width:100%;margin-top:14px;padding:10px;border:none;border-radius:10px;background:#14b8a6;color:#fff;font-size:14px;font-weight:700;cursor:pointer;">¥0.01 开通试用</button>
        <?php elseif ($logged_in): ?>
          <p style="width:100%;margin-top:14px;text-align:center;font-size:12px;color:#0d9488;">已开通更高级会员</p>
        <?php else: ?>
          <a href="<?php echo esc_url(home_url('/login')); ?>" style="display:block;text-align:center;margin-top:14px;padding:10px;border-radius:10px;background:#14b8a6;color:#fff;font-size:14px;font-weight:700;text-decoration:none;">登录后开通试用</a>
        <?php endif; ?>
      </div>
      <div style="border:1px solid #e5e6eb;border-radius:12px;padding:18px;background:#fff;position:relative;overflow:hidden;">
        <div style="position:absolute;top:10px;right:-28px;background:#1668dc;color:#fff;font-size:11px;padding:3px 32px;transform:rotate(45deg);">月付</div>
        <p style="font-size:14px;color:#86909c;">月卡会员</p>
        <p style="font-size:28px;font-weight:700;color:#1f2329;margin:6px 0;">¥9.99<span style="font-size:13px;color:#86909c;font-weight:400;"> /月</span></p>
        <p style="font-size:12px;color:#16a34a;">促销期仅需 <b>¥0.01</b> 开通</p>
        <p style="font-size:12px;color:#86909c;margin-top:8px;">自定义模型 · 更高每日额度</p>
        <?php if ($logged_in && in_array($q['membership'], array('free', 'trial'), true)): ?>
          <button id="btn-join-member" style="width:100%;margin-top:14px;padding:10px;border:none;border-radius:10px;background:linear-gradient(180deg,#1668dc,#6a3de8);color:#fff;font-size:14px;font-weight:700;cursor:pointer;">¥0.01 开通月卡</button>
        <?php elseif ($logged_in): ?>
          <p style="width:100%;margin-top:14px;text-align:center;font-size:12px;color:#86909c;">已开通</p>
        <?php else: ?>
          <a href="<?php echo esc_url(home_url('/login')); ?>" style="display:block;text-align:center;margin-top:14px;padding:10px;border-radius:10px;background:linear-gradient(180deg,#1668dc,#6a3de8);color:#fff;font-size:14px;font-weight:700;text-decoration:none;">登录后开通月卡</a>
        <?php endif; ?>
      </div>
      <div style="border:1px solid #f7ba1e;border-radius:12px;padding:18px;background:#fffdf5;position:relative;overflow:hidden;">
        <div style="position:absolute;top:10px;right:-28px;background:#f7ba1e;color:#fff;font-size:11px;padding:3px 32px;transform:rotate(45deg);">年付</div>
        <p style="font-size:14px;color:#86909c;">年卡会员</p>
        <p style="font-size:28px;font-weight:700;color:#1f2329;margin:6px 0;">¥99.99<span style="font-size:13px;color:#86909c;font-weight:400;"> /年</span></p>
        <p style="font-size:12px;color:#16a34a;">促销期仅需 <b>¥0.01</b> 开通</p>
        <p style="font-size:12px;color:#86909c;margin-top:8px;">推荐 · 约 8 折 · 全部权益</p>
        <?php if ($logged_in && in_array($q['membership'], array('free', 'trial'), true)): ?>
          <button id="btn-join-year" style="width:100%;margin-top:14px;padding:10px;border:none;border-radius:10px;background:#f7ba1e;color:#fff;font-size:14px;font-weight:700;cursor:pointer;">¥0.01 开通年卡</button>
        <?php elseif ($logged_in): ?>
          <p style="width:100%;margin-top:14px;text-align:center;font-size:12px;color:#86909c;">已开通</p>
        <?php else: ?>
          <a href="<?php echo esc_url(home_url('/login')); ?>" style="display:block;text-align:center;margin-top:14px;padding:10px;border-radius:10px;background:#f7ba1e;color:#fff;font-size:14px;font-weight:700;text-decoration:none;">登录后开通年卡</a>
        <?php endif; ?>
      </div>
    </div>
    <p id="member-tip" style="font-size:13px;color:#86909c;text-align:center;"></p>
    <?php if ($logged_in && in_array($q['membership'], array('silver', 'gold'), true)): ?>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;color:#16a34a;">您已是 <b><?php echo esc_html($q['membership_name']); ?></b>，全部自定义模型与会员权益已解锁。</div>
    <?php elseif ($logged_in && $q['membership'] === 'trial'): ?>
      <div style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:10px;padding:12px 16px;font-size:13px;color:#0d9488;">试用会员体验中，自定义模型已解锁。到期后可升级月卡 / 年卡，享受完整权益。</div>
    <?php endif; ?>
  </div>

  <div style="width:100%;max-width:880px;margin-top:24px;">
    <h3 style="font-size:16px;margin-bottom:12px;">会员权益</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
      <?php foreach (array('trial', 'silver', 'gold') as $key): $m = $ms[$key]; $dl = $m['daily']; ?>
      <div style="border:1px solid <?php echo $key === $q['membership'] ? '#1668dc' : '#e5e6eb'; ?>;border-radius:12px;padding:16px;background:<?php echo $key === $q['membership'] ? '#f0f6ff' : '#fff'; ?>;">
        <p style="font-size:15px;font-weight:600;color:#1f2329;"><?php echo esc_html($m['name']); ?></p>
        <p style="font-size:12px;color:#86909c;margin:4px 0 10px;">每月 <?php echo esc_html(number_format($m['monthly'])); ?> 点数额度</p>
        <p style="font-size:13px;color:#4e5969;margin-bottom:4px;">提问 / 代码 / 工作：<?php echo esc_html($dl['chat']); ?> 次/日</p>
        <p style="font-size:13px;color:#4e5969;margin-bottom:4px;">图片生成：<?php echo esc_html($dl['image']); ?> 张/日</p>
        <p style="font-size:13px;color:#4e5969;margin-bottom:4px;">视频国内档：<?php echo esc_html($dl['video']); ?> 条/日</p>
        <p style="font-size:13px;color:#4e5969;margin-bottom:4px;">视频国际档：<?php echo esc_html($dl['video_intl']); ?> 条/日</p>
        <p style="font-size:13px;color:#4e5969;margin-bottom:4px;">动漫：<?php echo esc_html($dl['animate']); ?> 条/日</p>
        <p style="font-size:13px;color:#4e5969;">剪辑：<?php echo esc_html($dl['edit']); ?> 次/日</p>
      </div>
      <?php endforeach; ?>
    </div>
    <ul style="margin-top:14px;padding-left:20px;font-size:13px;color:#4e5969;line-height:2;">
      <li>解锁全部 <b>自定义模型</b>（国内 / 海外 / 自定义网页版）</li>
      <li>会员不受每日额度限制，按点数余额自由使用全部功能</li>
      <li>专属客服优先响应 · 新模型抢先体验</li>
      <li>试用会员体验期 7 天，到期自动降为普通会员（余额保留）</li>
    </ul>
  </div>

  <?php if ($logged_in): ?>
  <div style="width:100%;max-width:880px;margin-top:24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
    <div style="border:1px solid #e5e6eb;border-radius:12px;padding:20px;">
      <h3 style="font-size:16px;margin-bottom:14px;">修改密码</h3>
      <div class="field"><span>当前密码</span><input type="password" id="pw-old" class="input" autocomplete="current-password" placeholder="输入当前密码"></div>
      <div class="field"><span>新密码</span><input type="password" id="pw-new" class="input" autocomplete="new-password" placeholder="至少 6 位"></div>
      <div class="field"><span>确认新密码</span><input type="password" id="pw-confirm" class="input" autocomplete="new-password" placeholder="再次输入新密码"></div>
      <div class="form-actions">
        <button type="button" id="btn-change-password" class="btn primary">确认修改</button>
      </div>
      <p id="pw-tip" class="hint" style="margin-top:10px;"></p>
    </div>
    <div style="border:1px solid #e5e6eb;border-radius:12px;padding:20px;">
      <h3 style="font-size:16px;margin-bottom:14px;">消费记录</h3>
      <div id="billing-list">
        <div style="text-align:center;padding:24px 0;color:var(--text-faint);">加载中...</div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div style="width:100%;max-width:880px;margin-top:24px;">
    <h3 style="font-size:16px;margin-bottom:12px;">模型定价表</h3>
    <table style="width:100%;border-collapse:collapse;background:#fff;">
      <thead>
        <tr>
          <th style="text-align:left;padding:10px 12px;font-size:13px;color:#86909c;border-bottom:1px solid #f2f3f5;">功能</th>
          <th style="text-align:left;padding:10px 12px;font-size:13px;color:#86909c;border-bottom:1px solid #f2f3f5;">模型</th>
          <th style="text-align:left;padding:10px 12px;font-size:13px;color:#86909c;border-bottom:1px solid #f2f3f5;">计费单位</th>
          <th style="text-align:right;padding:10px 12px;font-size:13px;color:#86909c;border-bottom:1px solid #f2f3f5;">单价</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pricing as $p): ?>
        <tr>
          <td style="padding:10px 12px;font-size:14px;color:#1f2329;border-bottom:1px solid #f7f8fa;"><?php echo esc_html($p['name']); ?></td>
          <td style="padding:10px 12px;font-size:13px;color:#4e5969;border-bottom:1px solid #f7f8fa;"><?php echo esc_html($p['model']); ?></td>
          <td style="padding:10px 12px;font-size:13px;color:#4e5969;border-bottom:1px solid #f7f8fa;"><?php echo esc_html($p['unit']); ?></td>
          <td style="padding:10px 12px;font-size:14px;color:#1668dc;font-weight:600;text-align:right;border-bottom:1px solid #f7f8fa;"><?php echo esc_html($p['price']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="width:100%;max-width:880px;margin-top:24px;border:1px solid #e5e6eb;border-radius:12px;padding:16px;background:#fff;">
    <p style="font-size:13px;color:#86909c;">如需帮助或退款，请联系客服。当前所有功能已按定价表统一扣费，余额不足时会引导充值。</p>
  </div>
</main>

<?php if ($logged_in): ?>
<script>
(function () {
  var tip = document.getElementById('member-tip');

  function join(plan, label, btn) {
    if (!btn) return;
    btn.addEventListener('click', function () {
      btn.disabled = true;
      btn.textContent = '开通中...';
      fetch('/wp-json/didi/v1/recharge', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': (window.didiRestNonce || '') },
        credentials: 'same-origin',
        body: JSON.stringify({ plan: plan })
      }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        if (res.ok && res.d.ok) {
          tip.style.color = '#16a34a';
          tip.textContent = (res.d.note || label) + ' 开通成功！当前余额 ' + res.d.balance + ' 点。';
          setTimeout(function () { location.reload(); }, 1200);
        } else {
          tip.style.color = '#f53f3f';
          tip.textContent = (res.d && res.d.message) ? res.d.message : '开通失败';
        }
      }).catch(function () {
        tip.style.color = '#f53f3f';
        tip.textContent = '网络错误，请重试';
      }).finally(function () {
        btn.disabled = false;
        btn.textContent = btn.getAttribute('data-label') || label;
      });
    });
    if (btn) btn.setAttribute('data-label', btn.textContent);
  }
  join('trial', '¥0.01 开通试用', document.getElementById('btn-join-trial'));
  join('membership', '¥0.01 开通月卡', document.getElementById('btn-join-member'));
  join('year', '¥0.01 开通年卡', document.getElementById('btn-join-year'));

  // 修改密码
  var pwTip = document.getElementById('pw-tip');
  var pwBtn = document.getElementById('btn-change-password');
  if (pwBtn) {
    pwBtn.addEventListener('click', function () {
      var oldP = document.getElementById('pw-old').value;
      var newP = document.getElementById('pw-new').value;
      var conf = document.getElementById('pw-confirm').value;
      if (!oldP || !newP) { pwTip.style.color = '#f53f3f'; pwTip.textContent = '请填写当前密码与新密码'; return; }
      if (newP.length < 6) { pwTip.style.color = '#f53f3f'; pwTip.textContent = '新密码至少 6 位'; return; }
      if (newP !== conf) { pwTip.style.color = '#f53f3f'; pwTip.textContent = '两次输入的新密码不一致'; return; }
      pwBtn.disabled = true;
      fetch('/wp-json/didi/v1/change-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': (window.didiRestNonce || '') },
        credentials: 'same-origin',
        body: JSON.stringify({ old_password: oldP, new_password: newP })
      }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        if (res.ok && res.d.ok) {
          pwTip.style.color = '#16a34a';
          pwTip.textContent = '密码修改成功，正在跳转重新登录...';
          setTimeout(function () { location.href = '/login'; }, 1200);
        } else {
          pwTip.style.color = '#f53f3f';
          pwTip.textContent = (res.d && res.d.message) ? res.d.message : '修改失败';
        }
      }).catch(function () {
        pwTip.style.color = '#f53f3f';
        pwTip.textContent = '网络错误，请重试';
      }).finally(function () {
        pwBtn.disabled = false;
      });
    });
  }

  // 消费记录
  var billingBox = document.getElementById('billing-list');
  var TYPE_MAP = { spend: '消费', recharge: '充值', membership: '开通会员', refund: '退款', bonus: '奖励' };
  var COLOR_MAP = { spend: '#1f2329', recharge: '#1668dc', membership: '#14b8a6', refund: '#00a36c', bonus: '#f7ba1e' };
  if (billingBox) {
    fetch('/wp-json/didi/v1/billing?limit=20', {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'X-WP-Nonce': (window.didiRestNonce || '') }
    }).then(function (r) { return r.json(); })
    .then(function (d) {
      var items = (d && d.ok && d.items) ? d.items : [];
      if (!items.length) {
        billingBox.innerHTML = '<div style="text-align:center;padding:24px 0;color:var(--text-faint);">暂无消费记录</div>';
        return;
      }
      var rows = items.map(function (it) {
        var ts = new Date(it.t * 1000);
        var pad = function (n) { return (n < 10 ? '0' : '') + n; };
        var time = ts.getFullYear() + '-' + pad(ts.getMonth() + 1) + '-' + pad(ts.getDate()) + ' ' + pad(ts.getHours()) + ':' + pad(ts.getMinutes());
        var typeLabel = TYPE_MAP[it.type] || it.type;
        var color = COLOR_MAP[it.type] || '#4e5969';
        var sign = it.cost >= 0 ? '+' : '';
        return '<div style="display:flex;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid #f7f8fa;">'
          + '<div style="min-width:0;"><div style="font-size:13px;color:#1f2329;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">[' + typeLabel + '] ' + (it.note ? it.note : it.feature) + '</div>'
          + '<div style="font-size:12px;color:#c9cdd4;">' + time + '</div></div>'
          + '<div style="text-align:right;white-space:nowrap;"><span style="font-size:13px;font-weight:600;color:' + color + ';">' + sign + it.cost + ' 点</span>'
          + '<div style="font-size:12px;color:#c9cdd4;">余额 ' + it.balance + '</div></div></div>';
      }).join('');
      billingBox.innerHTML = '<div style="max-height:320px;overflow:auto;">' + rows + '</div>';
    }).catch(function () {
      billingBox.innerHTML = '<div style="text-align:center;padding:24px 0;color:var(--text-faint);">消费记录加载失败</div>';
    });
  }
})();
</script>
<?php endif; ?>
<?php get_footer(); ?>
