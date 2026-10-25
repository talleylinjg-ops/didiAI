<?php
/**
 * 会员中心页面
 * Template Name: 会员中心
 * 访客可见：展示权益卡与定价表；账户信息区仅登录用户显示
 */
get_header();
$logged_in = is_user_logged_in();
$u = $logged_in ? wp_get_current_user() : null;
$q = $logged_in ? didi_ai_user_quota() : array('membership' => 'free', 'membership_name' => daqi_t('免费会员', 'Free Member'), 'membership_color' => '#14b8a6', 'balance' => 0);
$ms = didi_ai_memberships();
$pricing = didi_ai_pricing();
$billing = $logged_in ? didi_ai_billing_items(20) : array();
$expire = $logged_in ? get_user_meta($u->ID, 'didi_membership_expire', true) : '';
?>
<style>
.account-card{background:#fff;border:1px solid #eef0f4;border-radius:18px;padding:24px;box-shadow:0 6px 24px rgba(31,35,41,.05);transition:box-shadow .25s,transform .25s;}
.account-card:hover{box-shadow:0 12px 32px rgba(31,35,41,.09);transform:translateY(-2px);}
.account-card .card-head{display:flex;align-items:center;gap:12px;margin-bottom:20px;}
.account-card .card-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.account-card .card-head h3{font-size:16px;color:#1f2329;margin:0;}
.account-card .card-head p{font-size:12px;color:#86909c;margin:2px 0 0;}
.acct-field{margin-bottom:16px;}
.acct-field span{display:block;font-size:13px;color:#4e5969;margin-bottom:6px;font-weight:500;}
.acct-field input{width:100%;box-sizing:border-box;padding:11px 14px;border:1px solid #e5e6eb;border-radius:10px;background:#fbfbfd;font-size:14px;color:#1f2329;font-family:inherit;outline:none;transition:border-color .2s,background .2s,box-shadow .2s;}
.acct-field input:focus{background:#fff;border-color:#1668dc;box-shadow:0 0 0 3px rgba(22,104,220,.12);}
.acct-submit{width:100%;padding:12px;border:none;border-radius:10px;background:linear-gradient(135deg,#1668dc,#6a3de8);color:#fff;font-size:15px;font-weight:600;cursor:pointer;transition:opacity .2s,transform .1s;letter-spacing:1px;}
.acct-submit:hover{opacity:.9;}
.acct-submit:active{transform:scale(.985);}
.acct-submit:disabled{opacity:.55;cursor:not-allowed;}
.acct-tip{display:flex;align-items:center;gap:6px;font-size:13px;margin-top:12px;min-height:18px;}
</style>
<main style="flex-direction:column;align-items:center;padding:90px 20px 60px;">
  <h1 style="font-size:30px;margin-bottom:8px;"><?php echo daqi_t('会员中心', 'Member Center'); ?></h1>

  <?php if ($logged_in): ?>
  <div style="width:100%;max-width:880px;display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
    <div style="border:1px solid #e5e6eb;border-radius:16px;padding:24px;">
      <h3 style="font-size:16px;margin-bottom:14px;"><?php echo daqi_t('账户信息', 'Account Info'); ?></h3>
      <p style="font-size:14px;color:#4e5969;margin-bottom:8px;"><?php echo daqi_t('用户名', 'Username'); ?>：<b style="color:#1f2329;"><?php echo esc_html($u->display_name ? $u->display_name : $u->user_login); ?></b></p>
      <p style="font-size:14px;color:#4e5969;margin-bottom:8px;"><?php echo daqi_t('邮箱', 'Email'); ?>：<?php echo esc_html($u->user_email); ?></p>
      <p style="font-size:14px;color:#4e5969;"><?php echo daqi_t('注册时间', 'Registered'); ?>：<?php echo esc_html(date('Y-m-d', strtotime($u->user_registered))); ?></p>
      <?php if ($q['membership'] !== 'free' && $expire): ?>
      <p style="font-size:13px;color:#16a34a;margin-top:10px;"><?php echo daqi_t('会员有效期至', 'Membership valid until'); ?>：<?php echo esc_html($expire); ?></p>
      <?php endif; ?>
    </div>
    <div style="border:1px solid #e5e6eb;border-radius:16px;padding:24px;background:#f7f9ff;">
      <h3 style="font-size:16px;margin-bottom:14px;"><?php echo daqi_t('我的资产', 'My Assets'); ?></h3>
      <p style="font-size:14px;color:#4e5969;margin-bottom:8px;"><?php echo daqi_t('当前等级', 'Current Plan'); ?>：<b style="color:<?php echo esc_attr($q['membership_color']); ?>;"><?php echo esc_html(daqi_membership_name($q['membership_name'])); ?></b></p>
      <p style="font-size:24px;color:#1668dc;font-weight:700;margin:10px 0;"><?php echo esc_html(number_format($q['balance'])); ?> <span style="font-size:14px;color:#86909c;font-weight:400;"><?php echo daqi_t('点数', 'points'); ?></span></p>
      <p style="font-size:12px;color:#86909c;margin-bottom:16px;"><?php echo daqi_t('¥1 = 100 点数 · 各模型按定价表扣费', 'CNY 1 = 100 points · usage billed per the pricing table'); ?></p>
      <a href="<?php echo esc_url(home_url('/recharge')); ?>" class="btn-primary" style="display:inline-block;margin-right:8px;text-decoration:none;"><?php echo daqi_t('去充值', 'Top Up'); ?></a>
      <a href="<?php echo esc_url(home_url('/')); ?>" style="display:inline-block;padding:10px 20px;border:1px solid #1668dc;border-radius:8px;font-size:14px;color:#1668dc;text-decoration:none;"><?php echo daqi_t('去使用', 'Start Now'); ?></a>
    </div>
  </div>
  <?php else: ?>
  <div style="width:100%;max-width:880px;border:1px solid #e5e6eb;border-radius:16px;padding:24px;text-align:center;background:#f7f9ff;">
    <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn-primary" style="display:inline-block;text-decoration:none;"><?php echo daqi_t('登录 / 注册', 'Log In / Sign Up'); ?></a>
  </div>
  <?php endif; ?>

  <div style="width:100%;max-width:880px;margin-top:24px;">
    <h3 style="font-size:16px;margin-bottom:12px;"><?php echo daqi_t('会员方案（免费会员注册即用 · 月卡/年卡解锁更多权益）', 'Plans (Free tier on signup · Monthly/Yearly unlock more benefits)'); ?></h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px;">
      <div style="border:1px solid #5eead4;border-radius:12px;padding:18px;background:#f0fdfa;position:relative;overflow:hidden;">
        <div style="position:absolute;top:10px;right:-28px;background:#14b8a6;color:#fff;font-size:11px;padding:3px 32px;transform:rotate(45deg);"><?php echo daqi_t('免费', 'Free'); ?></div>
        <p style="font-size:14px;color:#0d9488;"><?php echo daqi_t('免费会员（最低档）', 'Free Member (starter tier)'); ?></p>
        <p style="font-size:28px;font-weight:700;color:#1f2329;margin:6px 0;"><?php echo daqi_t('免费', 'Free'); ?><span style="font-size:13px;color:#86909c;font-weight:400;"> / <?php echo daqi_t('无需充值', 'no payment needed'); ?></span></p>
        <p style="font-size:12px;color:#16a34a;"><?php echo daqi_t('注册即可使用全部功能 · 基础每日额度', 'All features on signup · basic daily quota'); ?></p>
        <p style="font-size:12px;color:#86909c;margin-top:8px;"><?php echo daqi_t('无需充值 · 即刻开始使用', 'No payment needed · start right away'); ?></p>
        <?php if ($logged_in && $q['membership'] === 'free'): ?>
          <p style="width:100%;margin-top:14px;text-align:center;font-size:12px;color:#0d9488;"><?php echo daqi_t('当前等级', 'Current plan'); ?></p>
        <?php else: ?>
          <a href="<?php echo esc_url(home_url('/login')); ?>" style="display:block;text-align:center;margin-top:14px;padding:10px;border-radius:10px;background:#14b8a6;color:#fff;font-size:14px;font-weight:700;text-decoration:none;"><?php echo daqi_t('注册即免费使用', 'Sign up — it is free'); ?></a>
        <?php endif; ?>
      </div>
      <div style="border:1px solid #e5e6eb;border-radius:12px;padding:18px;background:#fff;position:relative;overflow:hidden;">
        <div style="position:absolute;top:10px;right:-28px;background:#1668dc;color:#fff;font-size:11px;padding:3px 32px;transform:rotate(45deg);"><?php echo daqi_t('月付', 'Monthly'); ?></div>
        <p style="font-size:14px;color:#86909c;"><?php echo daqi_t('月卡会员', 'Monthly Member'); ?></p>
        <p style="font-size:28px;font-weight:700;color:#1f2329;margin:6px 0;">¥9.99<span style="font-size:13px;color:#86909c;font-weight:400;"> /<?php echo daqi_t('月', 'mo'); ?></span></p>
        <p style="font-size:12px;color:#16a34a;"><?php echo daqi_t('原价充值 · 更高每日额度', 'Billed at list price · higher daily quota'); ?></p>
        <p style="font-size:12px;color:#86909c;margin-top:8px;"><?php echo daqi_t('自定义模型 · 更多次数', 'Custom models · more uses'); ?></p>
        <?php if ($logged_in && $q['membership'] === 'free'): ?>
          <button id="btn-join-member" style="width:100%;margin-top:14px;padding:10px;border:none;border-radius:10px;background:linear-gradient(180deg,#1668dc,#6a3de8);color:#fff;font-size:14px;font-weight:700;cursor:pointer;"><?php echo daqi_t('¥9.99 开通月卡', '¥9.99 — Get Monthly'); ?></button>
        <?php elseif ($logged_in): ?>
          <p style="width:100%;margin-top:14px;text-align:center;font-size:12px;color:#86909c;"><?php echo daqi_t('已开通', 'Active'); ?></p>
        <?php else: ?>
          <a href="<?php echo esc_url(home_url('/login')); ?>" style="display:block;text-align:center;margin-top:14px;padding:10px;border-radius:10px;background:linear-gradient(180deg,#1668dc,#6a3de8);color:#fff;font-size:14px;font-weight:700;text-decoration:none;"><?php echo daqi_t('登录后开通月卡', 'Log in to get Monthly'); ?></a>
        <?php endif; ?>
      </div>
      <div style="border:1px solid #f7ba1e;border-radius:12px;padding:18px;background:#fffdf5;position:relative;overflow:hidden;">
        <div style="position:absolute;top:10px;right:-28px;background:#f7ba1e;color:#fff;font-size:11px;padding:3px 32px;transform:rotate(45deg);"><?php echo daqi_t('年付', 'Yearly'); ?></div>
        <p style="font-size:14px;color:#86909c;"><?php echo daqi_t('年卡会员', 'Yearly Member'); ?></p>
        <p style="font-size:28px;font-weight:700;color:#1f2329;margin:6px 0;">¥99.99<span style="font-size:13px;color:#86909c;font-weight:400;"> /<?php echo daqi_t('年', 'yr'); ?></span></p>
        <p style="font-size:12px;color:#16a34a;"><?php echo daqi_t('原价充值 · 全部权益', 'Billed at list price · all benefits'); ?></p>
        <p style="font-size:12px;color:#86909c;margin-top:8px;"><?php echo daqi_t('推荐 · 约 8 折 · 不限每日额度', 'Recommended · ~20% off · no daily cap'); ?></p>
        <?php if ($logged_in && $q['membership'] === 'free'): ?>
          <button id="btn-join-year" style="width:100%;margin-top:14px;padding:10px;border:none;border-radius:10px;background:#f7ba1e;color:#fff;font-size:14px;font-weight:700;cursor:pointer;"><?php echo daqi_t('¥99.99 开通年卡', '¥99.99 — Get Yearly'); ?></button>
        <?php elseif ($logged_in): ?>
          <p style="width:100%;margin-top:14px;text-align:center;font-size:12px;color:#86909c;"><?php echo daqi_t('已开通', 'Active'); ?></p>
        <?php else: ?>
          <a href="<?php echo esc_url(home_url('/login')); ?>" style="display:block;text-align:center;margin-top:14px;padding:10px;border-radius:10px;background:#f7ba1e;color:#fff;font-size:14px;font-weight:700;text-decoration:none;"><?php echo daqi_t('登录后开通年卡', 'Log in to get Yearly'); ?></a>
        <?php endif; ?>
      </div>
    </div>
    <p id="member-tip" style="font-size:13px;color:#86909c;text-align:center;"></p>
    <?php if ($logged_in && in_array($q['membership'], array('month', 'year'), true)): ?>
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;color:#16a34a;"><?php echo daqi_t('您已是', 'You are a'); ?> <b><?php echo esc_html(daqi_membership_name($q['membership_name'])); ?></b><?php echo daqi_t('，全部自定义模型与会员权益已解锁。', ' — all custom models and member benefits unlocked.'); ?></div>
    <?php elseif ($logged_in && $q['membership'] === 'free'): ?>
      <div style="background:#f0fdfa;border:1px solid #99f6e4;border-radius:10px;padding:12px 16px;font-size:13px;color:#0d9488;"><?php echo daqi_t('免费会员无需充值，注册即可使用全部功能。升级月卡 / 年卡可解锁更高每日额度与更多权益。', 'The Free tier needs no payment — all features are available on signup. Upgrade to Monthly/Yearly for higher daily quotas and more benefits.'); ?></div>
    <?php endif; ?>
  </div>

  <div style="width:100%;max-width:880px;margin-top:24px;">
    <h3 style="font-size:16px;margin-bottom:12px;"><?php echo daqi_t('会员权益', 'Membership Benefits'); ?></h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
      <?php foreach (array('free', 'month', 'year') as $key): $m = $ms[$key]; $dl = $m['daily']; ?>
      <div style="border:1px solid <?php echo $key === $q['membership'] ? '#1668dc' : '#e5e6eb'; ?>;border-radius:12px;padding:16px;background:<?php echo $key === $q['membership'] ? '#f0f6ff' : '#fff'; ?>;">
        <p style="font-size:15px;font-weight:600;color:#1f2329;"><?php echo esc_html(daqi_membership_name($m['name'])); ?></p>
        <p style="font-size:12px;color:#86909c;margin:4px 0 10px;"><?php echo daqi_t('每月', 'Monthly'); ?> <?php echo esc_html(number_format($m['monthly'])); ?> <?php echo daqi_t('点数额度', 'points allowance'); ?></p>
        <p style="font-size:13px;color:#4e5969;margin-bottom:4px;"><?php echo daqi_t('AI 问诊', 'AI consultations'); ?>：<?php echo esc_html($dl['chat']); ?> <?php echo daqi_t('次/日', '/day'); ?></p>
        <p style="font-size:13px;color:#4e5969;margin-bottom:4px;"><?php echo daqi_t('报告解读', 'Report reading'); ?>：<?php echo esc_html($dl['chat']); ?> <?php echo daqi_t('次/日', '/day'); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <ul style="margin-top:14px;padding-left:20px;font-size:13px;color:#4e5969;line-height:2;">
      <li><?php echo daqi_t('免费会员注册即用，无需充值，包含全部基础功能', 'The Free tier works on signup with no payment and includes all basic features'); ?></li>
      <li><?php echo daqi_t('月卡 / 年卡会员解锁全部 <b>自定义模型</b>（国内 / 海外 / 自定义网页版）', 'Monthly/Yearly members unlock all <b>custom models</b> (domestic / overseas / custom web endpoints)'); ?></li>
      <li><?php echo daqi_t('月卡 / 年卡会员不受每日额度限制，按点数余额自由使用全部功能', 'Monthly/Yearly members have no daily quota cap — use everything freely against your points balance'); ?></li>
      <li><?php echo daqi_t('专属客服优先响应 · 新模型抢先体验', 'Priority support · early access to new models'); ?></li>
      <li><?php echo daqi_t('月卡 / 年卡到期自动降为免费会员（余额保留）', 'On expiry, Monthly/Yearly plans revert to Free automatically (balance is kept)'); ?></li>
    </ul>
  </div>

  <?php if ($logged_in): ?>
  <div style="width:100%;max-width:880px;margin-top:24px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;align-items:start;">
    <div class="account-card">
      <div class="card-head">
        <div class="card-icon" style="background:linear-gradient(135deg,#16a34a,#22c55e);">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <div>
          <h3><?php echo daqi_t('修改用户资料', 'Edit Profile'); ?></h3>
          <p><?php echo daqi_t('更新你的昵称与登录邮箱', 'Update your nickname and login email'); ?></p>
        </div>
      </div>
      <div class="acct-field">
        <span><?php echo daqi_t('昵称', 'Nickname'); ?></span>
        <input type="text" id="pf-nickname" value="<?php echo esc_attr($u->display_name ? $u->display_name : $u->user_login); ?>" placeholder="<?php echo esc_attr(daqi_t('输入昵称', 'Enter nickname')); ?>">
      </div>
      <div class="acct-field">
        <span><?php echo daqi_t('邮箱', 'Email'); ?></span>
        <input type="email" id="pf-email" value="<?php echo esc_attr($u->user_email); ?>" placeholder="<?php echo esc_attr(daqi_t('输入邮箱', 'Enter email')); ?>">
      </div>
      <button type="button" id="btn-save-profile" class="acct-submit"><?php echo daqi_t('保存资料', 'Save Profile'); ?></button>
      <p id="pf-tip" class="acct-tip"></p>
    </div>
    <div class="account-card">
      <div class="card-head">
        <div class="card-icon" style="background:linear-gradient(135deg,#1668dc,#6a3de8);">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div>
          <h3><?php echo daqi_t('修改密码', 'Change Password'); ?></h3>
          <p><?php echo daqi_t('定期更换密码更安全', 'Changing it regularly keeps you safer'); ?></p>
        </div>
      </div>
      <div class="acct-field">
        <span><?php echo daqi_t('当前密码', 'Current password'); ?></span>
        <input type="password" id="pw-old" autocomplete="current-password" placeholder="<?php echo esc_attr(daqi_t('输入当前密码', 'Enter current password')); ?>">
      </div>
      <div class="acct-field">
        <span><?php echo daqi_t('新密码', 'New password'); ?></span>
        <input type="password" id="pw-new" autocomplete="new-password" placeholder="<?php echo esc_attr(daqi_t('至少 6 位', 'At least 6 characters')); ?>">
      </div>
      <div class="acct-field">
        <span><?php echo daqi_t('确认新密码', 'Confirm new password'); ?></span>
        <input type="password" id="pw-confirm" autocomplete="new-password" placeholder="<?php echo esc_attr(daqi_t('再次输入新密码', 'Re-enter new password')); ?>">
      </div>
      <button type="button" id="btn-change-password" class="acct-submit"><?php echo daqi_t('确认修改', 'Confirm Change'); ?></button>
      <p id="pw-tip" class="acct-tip"></p>
    </div>
    <div class="account-card">
      <div class="card-head">
        <div class="card-icon" style="background:linear-gradient(135deg,#f59e0b,#f7ba1e);">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </div>
        <div>
          <h3><?php echo daqi_t('消费记录', 'Usage & Billing'); ?></h3>
          <p><?php echo daqi_t('点数收支与余额明细', 'Points transactions and balance details'); ?></p>
        </div>
      </div>
      <div id="billing-list">
        <div style="text-align:center;padding:24px 0;color:#c9cdd4;"><?php echo daqi_t('加载中...', 'Loading...'); ?></div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div style="width:100%;max-width:880px;margin-top:24px;">
    <h3 style="font-size:16px;margin-bottom:12px;"><?php echo daqi_t('模型定价表（全模型统一公示）', 'Model Pricing (uniform, published)'); ?></h3>
    <p style="font-size:12px;color:#86909c;margin-bottom:12px;"><?php echo daqi_t('以下为各功能全部可用模型的统一定价。价格从低到高排序 · ¥1 = 100 点数 · 免费会员即可使用全部模型。', 'Uniform pricing for all available models, sorted low to high · CNY 1 = 100 points · every model is available to Free members.'); ?></p>
    <div class="card" style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;background:#fff;min-width:640px;">
        <thead>
          <tr>
            <th style="text-align:left;padding:10px 12px;font-size:13px;color:#86909c;border-bottom:1px solid #f2f3f5;"><?php echo daqi_t('功能', 'Feature'); ?></th>
            <th style="text-align:left;padding:10px 12px;font-size:13px;color:#86909c;border-bottom:1px solid #f2f3f5;"><?php echo daqi_t('模型', 'Model'); ?></th>
            <th style="text-align:left;padding:10px 12px;font-size:13px;color:#86909c;border-bottom:1px solid #f2f3f5;"><?php echo daqi_t('计费单位', 'Unit'); ?></th>
            <th style="text-align:right;padding:10px 12px;font-size:13px;color:#86909c;border-bottom:1px solid #f2f3f5;"><?php echo daqi_t('单价', 'Price'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php
          $chat_group_names = array('提问', '提问 · 国内', '提问 · 海外');
          $cur_group = '';
          foreach ($pricing as $p):
            if (!in_array($p['name'], $chat_group_names, true)) continue;
            $g = daqi_t('AI 问诊', 'AI Consultation');
            $new_group = ($g !== $cur_group);
            $cur_group = $g;
          ?>
          <?php if ($new_group): ?>
          <tr><td colspan="4" style="padding:8px 12px;font-size:12px;font-weight:700;color:#1668dc;background:#f0f6ff;border-bottom:1px solid #f2f3f5;"><?php echo esc_html($g); ?></td></tr>
          <?php endif; ?>
          <tr>
            <td style="padding:10px 12px;font-size:14px;color:#1f2329;border-bottom:1px solid #f7f8fa;"><?php echo esc_html(daqi_t($p['name'] === '提问' ? '提问' : '问诊', daqi_pricing_model($p['name'] === '提问' ? 'Chat (built-in)' : ($p['name'] === '提问 · 国内' ? 'Domestic' : 'Overseas') . ' LLM'))); ?></td>
            <td style="padding:10px 12px;font-size:13px;color:#4e5969;border-bottom:1px solid #f7f8fa;"><?php echo esc_html(daqi_pricing_model($p['model'])); ?></td>
            <td style="padding:10px 12px;font-size:13px;color:#4e5969;border-bottom:1px solid #f7f8fa;"><?php echo esc_html(daqi_pricing_unit($p['unit'])); ?></td>
            <td style="padding:10px 12px;font-size:14px;color:#1668dc;font-weight:600;text-align:right;border-bottom:1px solid #f7f8fa;"><?php echo esc_html($p['price']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="width:100%;max-width:880px;margin-top:24px;border:1px solid #e5e6eb;border-radius:12px;padding:16px;background:#fff;">
    <p style="font-size:13px;color:#86909c;"><?php echo daqi_t('如需帮助或退款，请联系客服。当前所有功能已按定价表统一扣费，余额不足时会引导充值。', 'Contact support for help or refunds. All usage is billed per the pricing table; you will be prompted to top up when your balance runs low.'); ?></p>
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
  join('month', '¥9.99 开通月卡', document.getElementById('btn-join-member'));
  join('year', '¥99.99 开通年卡', document.getElementById('btn-join-year'));

  // 修改密码
  var pwTip = document.getElementById('pw-tip');
  var pwBtn = document.getElementById('btn-change-password');
  if (pwBtn) {
    pwBtn.addEventListener('click', function () {
      var oldP = document.getElementById('pw-old').value;
      var newP = document.getElementById('pw-new').value;
      var conf = document.getElementById('pw-confirm').value;
      if (!oldP || !newP) { pwTip.style.color = '#f53f3f'; pwTip.textContent = EN ? 'Please fill in the current and new password' : '请填写当前密码与新密码'; return; }
      if (newP.length < 6) { pwTip.style.color = '#f53f3f'; pwTip.textContent = EN ? 'The new password needs at least 6 characters' : '新密码至少 6 位'; return; }
      if (newP !== conf) { pwTip.style.color = '#f53f3f'; pwTip.textContent = EN ? 'The two new passwords do not match' : '两次输入的新密码不一致'; return; }
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
          pwTip.textContent = EN ? 'Password changed — redirecting to sign in again...' : '密码修改成功，正在跳转重新登录...';
          setTimeout(function () { location.href = '/login'; }, 1200);
        } else {
          pwTip.style.color = '#f53f3f';
          pwTip.textContent = (res.d && res.d.message) ? res.d.message : (EN ? 'Change failed' : '修改失败');
        }
      }).catch(function () {
        pwTip.style.color = '#f53f3f';
        pwTip.textContent = '网络错误，请重试';
      }).finally(function () {
        pwBtn.disabled = false;
      });
    });
  }

  // 修改用户资料
  var pfTip = document.getElementById('pf-tip');
  var pfBtn = document.getElementById('btn-save-profile');
  if (pfBtn) {
    pfBtn.addEventListener('click', function () {
      var nickname = document.getElementById('pf-nickname').value.trim();
      var email = document.getElementById('pf-email').value.trim();
      if (!nickname) { pfTip.style.color = '#f53f3f'; pfTip.textContent = EN ? 'Nickname cannot be empty' : '昵称不能为空'; return; }
      if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { pfTip.style.color = '#f53f3f'; pfTip.textContent = EN ? 'Invalid email format' : '邮箱格式不正确'; return; }
      pfBtn.disabled = true;
      fetch('/wp-json/didi/v1/update-profile', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': (window.didiRestNonce || '') },
        credentials: 'same-origin',
        body: JSON.stringify({ nickname: nickname, email: email })
      }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (res) {
        if (res.ok && res.d.ok) {
          pfTip.style.color = '#16a34a';
          pfTip.textContent = EN ? 'Profile saved' : '资料修改成功';
        } else {
          pfTip.style.color = '#f53f3f';
          pfTip.textContent = (res.d && res.d.message) ? res.d.message : (EN ? 'Save failed' : '保存失败');
        }
      }).catch(function () {
        pfTip.style.color = '#f53f3f';
        pfTip.textContent = '网络错误，请重试';
      }).finally(function () {
        pfBtn.disabled = false;
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
        var amount = Math.abs(parseFloat(it.cost) || 0);
        var sign = it.type === 'spend' ? '-' : '+';
        return '<div style="display:flex;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid #f7f8fa;">'
          + '<div style="min-width:0;"><div style="font-size:13px;color:#1f2329;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">[' + typeLabel + '] ' + (it.note ? it.note : it.feature) + '</div>'
          + '<div style="font-size:12px;color:#c9cdd4;">' + time + '</div></div>'
          + '<div style="text-align:right;white-space:nowrap;"><span style="font-size:13px;font-weight:600;color:' + color + ';">' + sign + amount + ' 点</span>'
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
