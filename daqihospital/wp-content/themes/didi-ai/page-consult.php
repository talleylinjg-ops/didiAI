<?php
/**
 * Template Name: AI 问诊通道
 * 11 条就诊通道共用模板：slug 为 consult-{key}，配置来自 inc-medical-presets.php
 */
get_header();

$slug = get_post_field('post_name', get_the_ID());
$key = preg_replace('/^consult-/', '', $slug);
$ch = didi_ai_medical_preset($key);
if (!$ch) { wp_redirect(home_url('/')); exit; }
$home = home_url();
?>
<style>
  .cq-wrap{max-width:880px;margin:0 auto;padding:24px 16px 60px;}
  .cq-head{display:flex;align-items:center;gap:12px;margin-bottom:6px;}
  .cq-head .cq-emoji{font-size:40px;}
  .cq-head h1{font-size:26px;margin:0;}
  .cq-desc{color:#4e5969;margin:0 0 14px;font-size:15px;}
  .cq-disclaimer{background:#fff7e8;border:1px solid #ffd591;border-radius:12px;padding:10px 14px;font-size:13px;color:#874d00;margin-bottom:18px;}
  .cq-card{background:#fff;border:1px solid #e5e6eb;border-radius:14px;padding:18px;}
  .cq-field{margin-bottom:12px;}
  .cq-field label{display:block;font-size:14px;font-weight:600;margin-bottom:6px;}
  .cq-field input{width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #e5e6eb;border-radius:10px;font-size:14px;}
  .cq-start{width:100%;padding:12px;border:none;border-radius:12px;background:linear-gradient(180deg,#0e7c86,#12a5b0);color:#fff;font-size:16px;font-weight:600;cursor:pointer;}
  .cq-chat{display:none;}
  .cq-log{max-height:52vh;overflow-y:auto;display:flex;flex-direction:column;gap:12px;margin-bottom:14px;}
  .cq-msg{max-width:82%;padding:10px 14px;border-radius:12px;font-size:14px;line-height:1.7;white-space:pre-wrap;word-break:break-word;}
  .cq-msg.user{align-self:flex-end;background:#0e7c86;color:#fff;}
  .cq-msg.ai{align-self:flex-start;background:#f2f3f5;color:#1d2129;}
  .cq-inputrow{display:flex;gap:10px;}
  .cq-inputrow textarea{flex:1;box-sizing:border-box;padding:10px 12px;border:1px solid #e5e6eb;border-radius:10px;font-size:14px;min-height:64px;resize:vertical;}
  .cq-send{width:96px;border:none;border-radius:10px;background:#0e7c86;color:#fff;font-size:15px;cursor:pointer;}
  .cq-send:disabled{opacity:.5;cursor:not-allowed;}
  .cq-note{font-size:12px;color:#86909c;margin-top:10px;text-align:center;}
</style>
<div class="cq-wrap">
  <div class="cq-head"><span class="cq-emoji"><?php echo $ch['emoji']; ?></span><h1><?php echo esc_html(didi_ai_channel_name($ch)); ?></h1></div>
  <p class="cq-desc"><?php echo esc_html(didi_ai_channel_desc($ch)); ?></p>
  <div class="cq-disclaimer"><?php echo daqi_t('本服务为 AI 辅助预问诊，输出仅供参考，不能替代执业医师的诊断与治疗。如出现胸痛、呼吸困难、意识改变、大出血等急危情况，请立即拨打 <strong>120</strong> 或前往急诊。', 'This is an AI-assisted pre-consultation service. Output is for reference only and cannot replace the diagnosis or treatment of a licensed physician. For emergencies such as chest pain, breathing difficulty, altered consciousness or heavy bleeding, call <strong>911/120</strong> or go to the emergency room immediately.'); ?></div>

  <div class="cq-card" id="cqFormCard">
    <form id="cqForm">
      <?php foreach ($ch['fields'] as $fk => $f): ?>
        <div class="cq-field">
          <label><?php echo esc_html(didi_ai_channel_field($ch, $fk, 'label')); ?></label>
          <input type="text" name="<?php echo esc_attr(didi_ai_channel_field($ch, $fk, 'label')); ?>" placeholder="<?php echo esc_attr(didi_ai_channel_field($ch, $fk, 'ph')); ?>" autocomplete="off">
        </div>
      <?php endforeach; ?>
      <button class="cq-start" type="submit"><?php echo esc_html(daqi_t('开始 AI 问诊', 'Start AI consultation')); ?></button>
    </form>
  </div>

  <div class="cq-card cq-chat" id="cqChat">
    <div class="cq-log" id="cqLog"></div>
    <div class="cq-inputrow">
      <textarea id="cqInput" placeholder="<?php echo esc_attr(daqi_t('描述你的症状或继续追问…', 'Describe your symptoms or ask a follow-up question…')); ?>"></textarea>
      <button class="cq-send" id="cqSend"><?php echo esc_html(daqi_t('发送', 'Send')); ?></button>
    </div>
    <div class="cq-note"><?php echo daqi_t('AI 输出仅供参考 · 急危情况请拨打 120 · ', 'AI output is for reference only · For emergencies call 911/120 · ') . (is_user_logged_in() ? daqi_t('会员问诊中', 'member consultation') : daqi_t('游客每日 5 次问诊额度', '5 free consultations per day for guests')); ?></div>
  </div>
</div>
<script>
(function () {
  var preset = <?php echo wp_json_encode($key); ?>;
  var channelName = <?php echo wp_json_encode(didi_ai_channel_name($ch)); ?>;
  var L = {
    history: <?php echo wp_json_encode(daqi_t('病史信息', 'Medical history')); ?>,
    notProvided: <?php echo wp_json_encode(daqi_t('未提供', 'not provided')); ?>,
    analyze: <?php echo wp_json_encode(daqi_t('请根据以上信息进行预问诊分析。', 'Please perform a pre-consultation analysis based on the information above.')); ?>,
    errorPrefix: <?php echo wp_json_encode(daqi_t('错误：', 'Error: ')); ?>,
    failMsg: <?php echo wp_json_encode(daqi_t('请求失败：', 'Request failed: ') . '' . daqi_t('（游客请稍后再试，或注册会员使用）', ' (Guests please try again later, or register as a member.)')); ?>
  };
  var form = document.getElementById('cqForm');
  var formCard = document.getElementById('cqFormCard');
  var chatCard = document.getElementById('cqChat');
  var log = document.getElementById('cqLog');
  var input = document.getElementById('cqInput');
  var sendBtn = document.getElementById('cqSend');
  var history = [];

  function addMsg(role, text) {
    var d = document.createElement('div');
    d.className = 'cq-msg ' + (role === 'user' ? 'user' : 'ai');
    d.textContent = text;
    log.appendChild(d);
    log.scrollTop = log.scrollHeight;
    return d;
  }

  function stream(messages, onDone) {
    sendBtn.disabled = true;
    var aiEl = addMsg('ai', '');
    var acc = '';
    fetch('<?php echo esc_url_raw(rest_url('didi/v1/chat/stream')); ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ preset: preset, section: 'llm', messages: messages })
    }).then(function (r) {
      if (!r.ok) return r.json().then(function (e) { throw new Error(e.message || ('HTTP ' + r.status)); });
      var reader = r.body.getReader();
      var dec = new TextDecoder();
      function pump() {
        return reader.read().then(function (v) {
          if (v.done) { finish(); return; }
          dec.decode(v.value, { stream: true }).split('\n\n').forEach(function (blk) {
            blk.split('\n').forEach(function (line) {
              if (line.indexOf('data: ') !== 0) return;
              var payload = line.slice(6);
              if (payload === '[DONE]') { finish(); return; }
              try {
                var j = JSON.parse(payload);
                if (j.error) { acc += (acc ? '\n' : '') + L.errorPrefix + j.error; aiEl.textContent = acc; }
                else if (j.content) { acc += j.content; aiEl.textContent = acc; log.scrollTop = log.scrollHeight; }
              } catch (e) {}
            });
          });
          return pump();
        });
      }
      function finish() {
        if (acc) history.push({ role: 'assistant', content: acc });
        sendBtn.disabled = false;
        if (onDone) onDone();
      }
      return pump();
    }).catch(function (e) {
      aiEl.textContent = L.failMsg + ' ' + e.message;
      sendBtn.disabled = false;
    });
  }

  function ask(text) {
    if (!text.trim()) return;
    addMsg('user', text);
    history.push({ role: 'user', content: text });
    stream(history);
  }

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    var parts = ['【' + channelName + ' · ' + L.history + '】'];
    form.querySelectorAll('input').forEach(function (inp) {
      var label = inp.previousElementSibling ? inp.previousElementSibling.textContent : '';
      parts.push(label + '：' + (inp.value.trim() || L.notProvided));
    });
    parts.push('');
    parts.push(L.analyze);
    var first = parts.join('\n');
    formCard.style.display = 'none';
    chatCard.style.display = 'block';
    ask(first);
  });

  sendBtn.addEventListener('click', function () {
    var t = input.value.trim();
    if (!t) return;
    input.value = '';
    ask(t);
  });

  input.addEventListener('keydown', function (ev) {
    if (ev.key === 'Enter' && (ev.ctrlKey || ev.metaKey)) { ev.preventDefault(); sendBtn.click(); }
  });
})();
</script>
<?php get_footer(); ?>
