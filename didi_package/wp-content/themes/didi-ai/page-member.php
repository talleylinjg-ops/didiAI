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
    <p style="font-size:14px;color:#4e5969;margin-bottom:12px;">登录后可查看账户信息、点数余额与充值入口</p>
    <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn-primary" style="display:inline-block;text-decoration:none;">登录 / 注册</a>
  </div>
  <?php endif; ?>

  <div style="width:100%;max-width:880px;margin-top:24px;">
    <h3 style="font-size:16px;margin-bottom:12px;">会员方案（解锁全部自定义模型）</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
      <div style="border:1px solid #e5e6eb;border-radius:12px;padding:18px;background:#fff;position:relative;overflow:hidden;">
        <div style="position:absolute;top:10px;right:-28px;background:#1668dc;color:#fff;font-size:11px;padding:3px 32px;transform:rotate(45deg);">月付</div>
        <p style="font-size:14px;color:#86909c;">月卡会员</p>
        <p style="font-size:28px;font-weight:700;color:#1f2329;margin:6px 0;">¥9.99<span style="font-size:13px;color:#86909c;font-weight:400;"> /月</span></p>
        <p style="font-size:12px;color:#16a34a;">促销期仅需 <b>¥0.01</b> 开通</p>
        <p style="font-size:12px;color:#86909c;margin-top:8px;">自定义模型 · 更高每日额度</p>
      </div>
      <div style="border:1px solid #1668dc;border-radius:12px;padding:18px;background:#f0f6ff;position:relative;overflow:hidden;">
        <div style="position:absolute;top:10px;right:-28px;background:#f7ba1e;color:#fff;font-size:11px;padding:3px 32px;transform:rotate(45deg);">年付</div>
        <p style="font-size:14px;color:#86909c;">年卡会员</p>
        <p style="font-size:28px;font-weight:700;color:#1f2329;margin:6px 0;">¥99.99<span style="font-size:13px;color:#86909c;font-weight:400;"> /年</span></p>
        <p style="font-size:12px;color:#16a34a;">促销期仅需 <b>¥0.01</b> 开通</p>
        <p style="font-size:12px;color:#86909c;margin-top:8px;">推荐 · 约 8 折 · 全部权益</p>
      </div>
    </div>
    <?php if ($logged_in): ?>
      <?php if ($q['membership'] !== 'free'): ?>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;color:#16a34a;">您已是 <b><?php echo esc_html($q['membership_name']); ?></b>，自定义模型已解锁。</div>
      <?php else: ?>
        <button id="btn-join-member" style="width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(180deg,#1668dc,#6a3de8);color:#fff;font-size:16px;font-weight:700;cursor:pointer;">¥0.01 开通会员（促销价，原价 ¥9.99/月）</button>
        <p id="member-tip" style="font-size:13px;color:#86909c;margin-top:10px;text-align:center;"></p>
      <?php endif; ?>
    <?php else: ?>
      <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn-primary" style="display:block;text-align:center;text-decoration:none;padding:14px;">登录后 ¥0.01 开通会员</a>
    <?php endif; ?>
  </div>

  <div style="width:100%;max-width:880px;margin-top:24px;">
    <h3 style="font-size:16px;margin-bottom:12px;">会员权益</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
      <?php foreach ($ms as $key => $m): $dl = $m['daily']; ?>
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
  </div>

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
    <p style="font-size:13px;color:#86909c;">会员权益与高级功能即将上线，敬请期待。当前所有功能已按定价表统一扣费，余额不足时会引导充值。</p>
  </div>
</main>
<?php if ($logged_in && $q['membership'] === 'free'): ?>
<script>
(function () {
  var btn = document.getElementById('btn-join-member');
  var tip = document.getElementById('member-tip');
  if (!btn) return;
  btn.addEventListener('click', function () {
    btn.disabled = true;
    btn.textContent = '开通中...';
    fetch('/wp-json/didi/v1/recharge', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': (window.didiRestNonce || '') },
      credentials: 'same-origin',
      body: JSON.stringify({ plan: 'membership' })
    }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
    .then(function (res) {
      if (res.ok && res.d.ok) {
        tip.style.color = '#16a34a';
        tip.textContent = '开通成功！促销价 ¥0.01，已解锁自定义模型，当前余额 ' + res.d.balance + ' 点。';
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
      btn.textContent = '¥0.01 开通会员（促销价，原价 ¥9.99/月）';
    });
  });
})();
</script>
<?php endif; ?>
<?php get_footer(); ?>
