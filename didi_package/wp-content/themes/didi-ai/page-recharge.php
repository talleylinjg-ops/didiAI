<?php
/**
 * 充值中心（未登录时为登录页面，登录后显示充值）
 * Template Name: 充值中心
 */
$msg = '';
$err = '';

// 登录/注册处理（与 page-login 一致）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !is_user_logged_in()) {
  if (isset($_POST['didi_register_nonce']) && wp_verify_nonce($_POST['didi_register_nonce'], 'didi_register')) {
    $u = sanitize_user($_POST['reg_user']);
    $p = $_POST['reg_pass'];
    $e = sanitize_email($_POST['reg_email']);
    if (strlen($u) < 2) {
      $err = '用户名至少 2 个字符';
    } elseif (!preg_match('/^[a-zA-Z0-9_\-\x{4e00}-\x{9fa5}]+$/u', $u)) {
      $err = '用户名只能包含字母、数字、下划线、短横线或中文';
    } elseif (strlen($p) < 6) {
      $err = '密码至少 6 位';
    } elseif (!is_email($e)) {
      $err = '请输入有效的邮箱地址';
    } elseif (username_exists($u) || email_exists($e)) {
      $err = '用户名或邮箱已被注册';
    } else {
      $uid = wp_create_user($u, $p, $e);
      if (is_wp_error($uid)) {
        $err = '注册失败：' . $uid->get_error_message();
      } else {
        $user = wp_signon(array('user_login' => $u, 'user_password' => $p), false);
        if (!is_wp_error($user)) {
          wp_redirect(home_url('/recharge'));
          exit;
        }
        $msg = '注册成功，请登录';
      }
    }
  }
  if (isset($_POST['didi_login_nonce']) && wp_verify_nonce($_POST['didi_login_nonce'], 'didi_login')) {
    $creds = array(
      'user_login'    => sanitize_text_field($_POST['log']),
      'user_password' => $_POST['pwd'],
      'remember'      => true,
    );
    $user = wp_signon($creds, false);
    if (is_wp_error($user)) {
      $err = '用户名或密码错误';
    } else {
      wp_redirect(home_url('/recharge'));
      exit;
    }
  }
}

get_header();
$logged_in = is_user_logged_in();
?>
<?php if (!$logged_in): ?>
<main style="flex-direction:column;align-items:center;padding:80px 20px 40px;">
  <div style="width:100%;max-width:420px;">
    <h1 style="font-size:28px;margin-bottom:6px;text-align:center;">充值中心 · 登录</h1>
    <p style="font-size:14px;color:#4e5969;margin-bottom:28px;text-align:center;">登录 / 注册后即可进入充值中心与会员中心</p>

    <?php if ($err) : ?>
      <p style="color:#dc2626;font-size:13px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 12px;margin-bottom:16px;"><?php echo esc_html($err); ?></p>
    <?php endif; ?>
    <?php if ($msg) : ?>
      <p style="color:#16a34a;font-size:13px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 12px;margin-bottom:16px;"><?php echo esc_html($msg); ?></p>
    <?php endif; ?>

    <div style="display:flex;gap:12px;margin-bottom:28px;">
      <button type="button" id="tabLogin" class="auth-tab active" style="flex:1;padding:18px 0;font-size:22px;color:#fff;font-weight:700;border:none;background:#1668dc;border-radius:12px;cursor:pointer;letter-spacing:6px;box-shadow:0 4px 14px rgba(22,104,220,0.3);">登 录</button>
      <button type="button" id="tabReg" class="auth-tab" style="flex:1;padding:18px 0;font-size:22px;color:#4e5969;font-weight:700;border:1px solid #e5e6eb;background:#fff;border-radius:12px;cursor:pointer;letter-spacing:6px;">注 册</button>
    </div>

    <div id="paneLogin" style="background:#fff;">
      <form method="post">
        <?php wp_nonce_field('didi_login', 'didi_login_nonce'); ?>
        <div style="margin-bottom:16px;">
          <label for="log" style="display:block;font-size:13px;color:#4e5969;margin-bottom:6px;">用户名</label>
          <input class="search-input" id="log" type="text" name="log" required autofocus style="width:100%;box-sizing:border-box;padding:12px 14px;font-size:15px;">
        </div>
        <div style="margin-bottom:22px;">
          <label for="pwd" style="display:block;font-size:13px;color:#4e5969;margin-bottom:6px;">密码</label>
          <input class="search-input" id="pwd" type="password" name="pwd" required style="width:100%;box-sizing:border-box;padding:12px 14px;font-size:15px;">
        </div>
        <button class="btn-primary" type="submit" style="width:100%;padding:12px;font-size:15px;">登 录</button>
      </form>
      <p style="font-size:13px;color:#4e5969;margin-top:18px;text-align:center;line-height:1.8;">不注册 · 全球共用演示账号<br><b style="color:#1668dc;">admin / admin123</b></p>
    </div>

    <div id="paneReg" style="background:#fff;display:none;">
      <form method="post">
        <?php wp_nonce_field('didi_register', 'didi_register_nonce'); ?>
        <div style="margin-bottom:16px;">
          <label for="reg_user" style="display:block;font-size:13px;color:#4e5969;margin-bottom:6px;">用户名</label>
          <input class="search-input" id="reg_user" type="text" name="reg_user" required style="width:100%;box-sizing:border-box;padding:12px 14px;font-size:15px;">
        </div>
        <div style="margin-bottom:16px;">
          <label for="reg_email" style="display:block;font-size:13px;color:#4e5969;margin-bottom:6px;">邮箱</label>
          <input class="search-input" id="reg_email" type="email" name="reg_email" required style="width:100%;box-sizing:border-box;padding:12px 14px;font-size:15px;">
        </div>
        <div style="margin-bottom:22px;">
          <label for="reg_pass" style="display:block;font-size:13px;color:#4e5969;margin-bottom:6px;">密码（至少 6 位）</label>
          <input class="search-input" id="reg_pass" type="password" name="reg_pass" required minlength="6" style="width:100%;box-sizing:border-box;padding:12px 14px;font-size:15px;">
        </div>
        <button class="btn-primary" type="submit" style="width:100%;padding:12px;font-size:15px;">注 册</button>
      </form>
    </div>
  </div>
</main>
<script>
(function(){
  var tabLogin = document.getElementById('tabLogin');
  var tabReg = document.getElementById('tabReg');
  var paneLogin = document.getElementById('paneLogin');
  var paneReg = document.getElementById('paneReg');
  function switchTo(login){
    var on = 'flex:1;padding:18px 0;font-size:22px;color:#fff;font-weight:700;border:none;background:#1668dc;border-radius:12px;cursor:pointer;letter-spacing:6px;box-shadow:0 4px 14px rgba(22,104,220,0.3);';
    var off = 'flex:1;padding:18px 0;font-size:22px;color:#4e5969;font-weight:700;border:1px solid #e5e6eb;background:#fff;border-radius:12px;cursor:pointer;letter-spacing:6px;';
    tabLogin.style.cssText = login ? on : off;
    tabReg.style.cssText = login ? off : on;
    paneLogin.style.display = login ? '' : 'none';
    paneReg.style.display = login ? 'none' : '';
  }
  tabLogin.addEventListener('click', function(){ switchTo(true); });
  tabReg.addEventListener('click', function(){ switchTo(false); });
})();
</script>
<?php get_footer(); ?>
<?php return; endif; ?>
<?php
$balance = 0;
$q = didi_ai_user_quota();
$balance = $q['balance'];
$points = $q['balance'];
$presets = array(10, 30, 50, 100, 300, 500);
?>
<main style="flex-direction:column;align-items:center;padding:80px 20px 40px;">
  <h1 style="font-size:30px;margin-bottom:8px;">充值中心</h1>
  <p style="font-size:14px;color:#4e5969;margin-bottom:24px;">为你的 didi AI 账户充值点数（¥1 = 100 点数），余额统一供所有模型使用</p>
  <div style="width:100%;max-width:720px;border:1px solid #e5e6eb;border-radius:16px;padding:28px;background:#fff;">
    <div style="background:#f2f3f5;border-radius:12px;padding:20px;text-align:center;margin-bottom:18px;">
      <div style="font-size:13px;color:#86909c;">当前余额</div>
      <div style="font-size:36px;font-weight:700;color:#1668dc;margin-top:6px;">
        <?php echo $logged_in ? esc_html(number_format($points)) : '--'; ?>
        <span style="font-size:14px;color:#86909c;font-weight:400;"> 点数</span>
      </div>
      <?php if ($logged_in): ?><div style="font-size:12px;color:#86909c;margin-top:4px;">≈ ¥ <?php echo esc_html(number_format($points / 100, 2)); ?></div><?php endif; ?>
    </div>
    <div style="font-size:14px;color:#4e5969;margin-bottom:10px;">充值金额</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
      <?php foreach ($presets as $amt) : ?>
        <div data-amount="<?php echo esc_attr($amt); ?>" class="recharge-amount" style="border:1px solid #e5e6eb;border-radius:10px;padding:14px;text-align:center;cursor:pointer;">
          <div style="font-size:20px;font-weight:700;color:#1668dc;">¥<?php echo esc_html($amt); ?></div>
          <div style="font-size:12px;color:#86909c;margin-top:4px;"><?php echo esc_html($amt * 100); ?> 点数</div>
        </div>
      <?php endforeach; ?>
      <div class="recharge-amount" data-amount="0" style="border:1px dashed #1668dc;border-radius:10px;padding:14px;text-align:center;cursor:pointer;">
        <input type="number" id="custom-amount" min="1" placeholder="自定义金额" style="width:100%;border:none;outline:none;text-align:center;font-size:16px;font-weight:700;color:#1668dc;background:transparent;" />
      </div>
    </div>
    <div style="font-size:14px;color:#4e5969;margin-bottom:10px;">支付方式</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
      <?php foreach (array('支付宝' => '支', '微信支付' => '微', '银闪付' => '闪') as $name => $icon) : ?>
        <div class="recharge-pay" style="border:1px solid #e5e6eb;border-radius:10px;padding:18px 14px;text-align:center;cursor:pointer;">
          <div style="font-size:26px;"><?php echo esc_html($icon); ?></div>
          <div style="font-size:14px;margin-top:6px;font-weight:600;"><?php echo esc_html($name); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;">
      <?php foreach (array('抖音支付', '京东支付', '多多支付', '美团支付', '华为支付', '苹果支付') as $name) : ?>
        <div style="border:1px solid #e5e6eb;border-radius:8px;padding:12px 4px;text-align:center;">
          <div style="font-size:13px;"><?php echo esc_html($name); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if (!$logged_in) : ?>
      <p style="font-size:13px;color:#4e5969;margin-top:24px;text-align:center;">登录后即可充值并使用 AI 创作服务
        <a href="<?php echo esc_url(home_url('/login')); ?>" class="btn-primary" style="display:inline-block;margin-left:8px;padding:8px 20px;text-decoration:none;">登录 / 注册</a>
      </p>
    <?php else : ?>
      <div style="display:flex;gap:10px;margin-top:24px;">
        <button id="btn-confirm-recharge" class="btn-primary" style="flex:1;text-align:center;padding:12px;cursor:pointer;border:none;font-size:15px;">确认充值</button>
      </div>
      <p id="recharge-tip" style="font-size:12px;color:#86909c;margin-top:10px;text-align:center;"></p>
    <?php endif; ?>
  </div>
</main>
<script>
<?php if ($logged_in): ?>
(function () {
  var selectedAmount = 0;
  var selectedMethod = '支付宝';
  var amounts = document.querySelectorAll('.recharge-amount');
  var pays = document.querySelectorAll('.recharge-pay');
  function clearSel(elms) { elms.forEach(function (e) { e.style.borderColor = '#e5e6eb'; e.style.background = '#fff'; }); }
  amounts.forEach(function (el) {
    el.addEventListener('click', function () {
      clearSel(amounts);
      el.style.borderColor = '#1668dc';
      el.style.background = '#f0f6ff';
      var input = document.getElementById('custom-amount');
      if (input) { input.value = ''; }
      selectedAmount = parseInt(el.getAttribute('data-amount'), 10) || 0;
    });
  });
  pays.forEach(function (el) {
    el.addEventListener('click', function () {
      clearSel(pays);
      el.style.borderColor = '#1668dc';
      el.style.background = '#f0f6ff';
      selectedMethod = el.textContent.trim();
    });
  });
  var btn = document.getElementById('btn-confirm-recharge');
  var tip = document.getElementById('recharge-tip');
  btn.addEventListener('click', function () {
    var custom = document.getElementById('custom-amount');
    var amt = selectedAmount;
    if (custom && custom.value) {
      amt = parseFloat(custom.value);
      if (!amt || amt <= 0) { tip.textContent = '请输入有效金额'; return; }
    }
    if (amt <= 0) { tip.textContent = '请选择或输入充值金额'; return; }
    btn.disabled = true;
    btn.textContent = '充值中...';
    fetch('/wp-json/didi/v1/recharge', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': (window.didiRestNonce || '') },
      credentials: 'same-origin',
      body: JSON.stringify({ amount: amt, method: selectedMethod })
    }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
    .then(function (res) {
      if (res.ok && res.d.ok) {
        tip.style.color = '#16b28e';
        tip.textContent = '充值成功！到账 ' + res.d.added + ' 点数，当前余额 ' + res.d.balance + ' 点。';
        setTimeout(function () { location.href = '/member'; }, 1200);
      } else {
        tip.style.color = '#f53f3f';
        tip.textContent = (res.d && res.d.message) ? res.d.message : '充值失败';
      }
    }).catch(function () {
      tip.style.color = '#f53f3f';
      tip.textContent = '网络错误，请重试';
    }).finally(function () {
      btn.disabled = false;
      btn.textContent = '确认充值';
    });
  });
})();
<?php endif; ?>
</script>
<?php get_footer(); ?>
