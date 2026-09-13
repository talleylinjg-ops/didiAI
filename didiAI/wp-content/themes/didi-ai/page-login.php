<?php
/**
 * 登录 / 注册页面
 * 支持自助注册
 * Template Name: 登录 / 注册
 */
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // ===== 注册 =====
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
          $msg = '注册成功，正在进入...';
          wp_redirect(home_url('/'));
          exit;
        }
        $msg = '注册成功，请登录';
      }
    }
  }

  // ===== 登录 =====
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
      wp_redirect(home_url('/'));
      exit;
    }
  }
}

if (is_user_logged_in()) {
  wp_redirect(home_url('/'));
  exit;
}
get_header();
?>
<main style="flex-direction:column;align-items:center;padding:80px 20px 40px;">
  <div style="width:100%;max-width:420px;">
    <h1 style="font-size:28px;margin-bottom:6px;text-align:center;">登录 didi AI</h1>
    <p style="font-size:14px;color:#4e5969;margin-bottom:28px;text-align:center;">登录后即可使用博客、论坛、AI 创作等全部功能</p>

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
      <p style="font-size:13px;color:#4e5969;margin-top:18px;text-align:center;line-height:1.8;">不注册 · 全球共用演示账号<br><b style="color:#1668dc;">admin / admin123</b><br><span style="color:#86909c;">无隐私 · 全球大数据共享</span></p>
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
    var f = login ? document.getElementById('log') : document.getElementById('reg_user');
    if (f) f.focus();
  }
  tabLogin.addEventListener('click', function(){ switchTo(true); });
  tabReg.addEventListener('click', function(){ switchTo(false); });
})();
</script>
<?php get_footer(); ?>
