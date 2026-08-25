<?php
/**
 * AI 聊天通用布局（对话/代码/工作/音乐/写作/PPT/数字人/语音/文件 共用）
 * 依赖页面传入变量：$section / $title / $icon / $color / $desc / $welcome / $placeholder / $footer_text
 * 可选变量：$model_options / $model_map（数字人厂商下拉用）
 */
if (empty($section)) $section = 'llm';
if (empty($title)) $title = 'AI';
if (empty($icon)) $icon = '&#128172;';
if (empty($color)) $color = '#1668dc';
if (empty($desc)) $desc = '';
if (empty($welcome)) $welcome = '';
if (empty($placeholder)) $placeholder = '输入你的问题...（Enter 发送，Shift+Enter 换行）';
if (empty($footer_text)) $footer_text = 'didi AI';
$model_options = isset($model_options) ? $model_options : array();
$model_map = isset($model_map) ? $model_map : array();

$prompt_id = 'chat-prompt-' . $section;
$submit_id = 'chat-submit-' . $section;
$model_json = empty($model_map) ? array() : $model_map;
$section_json = wp_json_encode($section);
$icon_json = wp_json_encode($icon);
$color_json = wp_json_encode($color);
$model_map_json = wp_json_encode($model_json);
?>
<div class="ai-page ai-chat-page">
  <div class="ai-chat-header">
    <div class="ai-chat-heading">
      <h1><span style="color:<?php echo esc_attr($color); ?>;"><?php echo $icon; ?></span> <?php echo esc_html($title); ?></h1>
      <p><?php echo esc_html($desc); ?></p>
    </div>
    <select id="modelSelect" class="ai-model-select" title="选择模型">
      <?php if (empty($model_options)) : ?>
        <optgroup label="国内模型">
          <option value="deepseek-chat">DeepSeek（默认）</option>
        </optgroup>
        <optgroup label="海外模型">
          <option value="gpt-4o-mini">GPT-4o mini</option>
        </optgroup>
        <optgroup label="其他">
          <option value="__custom__">自定义模型（需会员）</option>
        </optgroup>
      <?php else : ?>
        <?php foreach ($model_options as $opt_val => $opt_label) : ?>
          <option value="<?php echo esc_attr($opt_val); ?>"><?php echo esc_html($opt_label); ?></option>
        <?php endforeach; ?>
      <?php endif; ?>
    </select>
  </div>
  <div class="ai-chat-messages" id="chatMessages">
    <div class="msg assistant">
      <div class="avatar" style="color:<?php echo esc_attr($color); ?>;"><?php echo $icon; ?></div>
      <div class="bubble"><?php echo esc_html($welcome); ?></div>
    </div>
  </div>
  <div class="ai-chat-input">
    <textarea id="<?php echo esc_attr($prompt_id); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"></textarea>
    <div class="ai-input-tools">
      <button type="button" class="ai-tool" data-tool="voice" title="语音输入">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
      </button>
      <button type="button" class="ai-tool" data-tool="file" title="上传文件">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
      </button>
      <button type="button" class="ai-tool" data-tool="image" title="上传图片">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
      </button>
    </div>
    <button class="btn ai-btn" id="<?php echo esc_attr($submit_id); ?>">发送</button>
  </div>
  <p class="ai-footnote"><?php echo esc_html($footer_text); ?></p>
</div>
<script>
(function () {
  var section = <?php echo $section_json; ?>;
  var MODEL_MAP = <?php echo $model_map_json; ?>;
  var textarea = document.getElementById('<?php echo esc_js($prompt_id); ?>');
  var submitBtn = document.getElementById('<?php echo esc_js($submit_id); ?>');
  var modelSel = document.getElementById('modelSelect');
  var msgBox = document.getElementById('chatMessages');
  var customInput = null;

  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]); }); }

  function bubble(cls, html) {
    var el = document.createElement('div');
    el.className = 'msg ' + cls;
    var ico = <?php echo $icon_json; ?>;
    el.innerHTML = '<div class="avatar">' + ico + '</div><div class="bubble">' + html + '</div>';
    msgBox.appendChild(el);
    msgBox.scrollTop = msgBox.scrollHeight;
    return el.querySelector('.bubble');
  }

  function mdHtml(text) {
    return (typeof renderMarkdown === 'function') ? renderMarkdown(text) : '<p>' + esc(text).replace(/\n/g, '<br>') + '</p>';
  }

  function appendStream(text) {
    var last = msgBox.querySelector('.msg.assistant:last-of-type');
    if (last) {
      var b = last.querySelector('.bubble');
      if (b) { b.innerHTML = mdHtml(text); msgBox.scrollTop = msgBox.scrollHeight; return; }
    }
    bubble('assistant', mdHtml(text));
  }

  function currentModel() {
    var v = modelSel.value;
    if (v !== '__custom__') return { model: (MODEL_MAP && MODEL_MAP[v]) || v, custom: false };
    var name = (customInput && customInput.value.trim()) || '';
    if (!name) { toast('请先输入自定义模型名称', 'error'); return null; }
    return { model: name, custom: true };
  }

  function send() {
    var text = textarea.value.trim();
    if (!text) return;
    var m = currentModel();
    if (!m) return;
    textarea.value = '';
    bubble('user', esc(text));
    var aBubble = bubble('assistant', '<div class="loading-spinner" style="margin:6px;"></div>');
    aBubble.classList.add('typing');
    var acc = '';
    submitBtn.disabled = true;
    var body = { section: section, messages: [{ role: 'user', content: text }], model: m.model, custom: m.custom };

    fetch('/wp-json/didi/v1/chat/stream', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': (window.didiRestNonce || '') },
      credentials: 'same-origin',
      body: JSON.stringify(body)
    }).then(function (res) {
      if (!res.ok) {
        return res.json().catch(function () { return { code: 'error', message: '请求失败 (' + res.status + ')' }; }).then(function (d) {
          aBubble.classList.remove('typing');
          aBubble.innerHTML = mdHtml((d && d.message) || '请求失败，请稍后重试');
          throw new Error('stop');
        });
      }
      var reader = res.body.getReader();
      var decoder = new TextDecoder();
      var buf = '';
      function pump() {
        return reader.read().then(function (r) {
          if (r.done) return;
          buf += decoder.decode(r.value, { stream: true });
          var lines = buf.split('\n');
          buf = lines.pop();
          lines.forEach(function (line) {
            line = line.trim();
            if (line.indexOf('data:') !== 0) return;
            var payload = line.slice(5).trim();
            if (!payload || payload === '[DONE]') return;
            var o;
            try { o = JSON.parse(payload); } catch (e) { return; }
            if (o.delta) { acc += o.delta; appendStream(acc); }
            if (o.meta && window.didiRestNonce && document.getElementById('nav-balance')) {
              document.getElementById('nav-balance').textContent = o.meta.balance;
            }
            if (o.error) { toast(o.error, 'error'); }
          });
          return pump();
        });
      }
      return pump();
    }).catch(function (e) {
      if (e && e.message === 'stop') return;
      aBubble.classList.remove('typing');
      aBubble.innerHTML = mdHtml('网络错误，请重试');
    }).finally(function () {
      submitBtn.disabled = false;
      aBubble.classList.remove('typing');
      if (acc && window.didi_tts_speak) { try { window.didi_tts_speak(acc); } catch (e) {} }
    });
  }

  submitBtn.addEventListener('click', send);
  textarea.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
  });

  modelSel.addEventListener('change', function () {
    if (modelSel.value === '__custom__') {
      if (!customInput) {
        customInput = document.createElement('input');
        customInput.type = 'text';
        customInput.placeholder = '输入自定义模型名称（需会员）';
        customInput.className = 'input';
        customInput.style.cssText = 'min-width:160px;max-width:200px;flex-shrink:0;padding:8px 10px;border:1px solid #e5e6eb;border-radius:10px;font-size:13px;outline:none;';
        modelSel.parentNode.insertBefore(customInput, modelSel.nextSibling);
      }
      customInput.style.display = '';
      customInput.focus();
    } else if (customInput) {
      customInput.style.display = 'none';
    }
  });

  // 语音 / 文件 / 图片工具接线（复用 ai-common.js）
  if (typeof didiWireTools === 'function') {
    didiWireTools(document.querySelector('.ai-chat-input'), textarea, submitBtn, false);
  }
})();
</script>
