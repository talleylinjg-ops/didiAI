<?php
/**
 * AI 聊天通用布局（对话/代码/工作/音频/写作/PPT/数字人/语音/文件 共用）
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
$section_json = wp_json_encode($section);
$icon_json = wp_json_encode($icon);
$color_json = wp_json_encode($color);

/* 每个 AI 页一组各自不同的模型列表：section => (default, domestic, intl)
 * 每页保留各自主题侧重，同时恢复丰富数量（国内 5-7 + 海外 3-4） */
$section_models = array(
  'llm' => array(
    'default' => 'kimi-k3',
    'domestic' => array(
      'kimi-k3'        => 'Kimi K3（国内最强）',
      'deepseek-chat'  => 'DeepSeek',
      'qwen-plus'      => '通义千问',
      'ernie-4.0-turbo'=> '文心 ERNIE',
      'moonshot-v1'    => 'Kimi',
      'glm-4'          => '智谱 GLM',
      'doubao-pro'     => '豆包',
      'hunyuan-turbo'  => '腾讯混元',
    ),
    'intl' => array(
      'gpt-6-astra' => 'GPT-6 Astra（全球最强）',
      'claude-fable-5.1' => 'Claude Fable 5.1',
      'gpt-4o'      => 'GPT-4o',
      'claude-3-5-sonnet' => 'Claude 3.5 Sonnet',
      'gemini-1.5-pro'    => 'Gemini 1.5 Pro',
      'gpt-4o-mini'       => 'GPT-4o mini',
    ),
  ),
  'code' => array(
    'default' => 'qwen3.8-max',
    'domestic' => array(
      'qwen3.8-max'    => '通义千问 Qwen3.8-Max（国内最强）',
      'deepseek-chat'  => 'DeepSeek',
      'qwen-plus'      => '通义千问',
      'glm-4'          => '智谱 GLM',
      'doubao-pro'     => '豆包',
      'hunyuan-turbo'  => '腾讯混元',
      'ernie-4.0-turbo'=> '文心 ERNIE',
    ),
    'intl' => array(
      'claude-fable-5.1' => 'Claude Fable 5.1（全球最强）',
      'gpt-6-astra'      => 'GPT-6 Astra',
      'gpt-4o'      => 'GPT-4o',
      'claude-3-5-sonnet' => 'Claude 3.5 Sonnet',
      'gemini-1.5-pro'    => 'Gemini 1.5 Pro',
      'gpt-4o-mini'       => 'GPT-4o mini',
    ),
  ),
  'work' => array(
    'default' => 'qwen3.8-max',
    'domestic' => array(
      'qwen3.8-max'    => '通义千问 Qwen3.8-Max（国内最强）',
      'qwen-plus'      => '通义千问',
      'deepseek-chat'  => 'DeepSeek',
      'glm-4'          => '智谱 GLM',
      'moonshot-v1'    => 'Kimi',
      'doubao-pro'     => '豆包',
      'hunyuan-turbo'  => '腾讯混元',
    ),
    'intl' => array(
      'gpt-6-astra' => 'GPT-6 Astra（全球最强）',
      'gpt-4o'      => 'GPT-4o',
      'claude-3-5-sonnet' => 'Claude 3.5 Sonnet',
      'gemini-1.5-pro'    => 'Gemini 1.5 Pro',
    ),
  ),
  'music' => array(
    'default' => 'yinchao-v4',
    'domestic' => array(
      'yinchao-v4'     => '音潮 V4.0（国内最强）',
      'didi-media'     => 'didi Media（免费）',
      'ernie-4.0-turbo'=> '文心 ERNIE',
      'deepseek-chat'  => 'DeepSeek',
      'qwen-plus'      => '通义千问',
      'moonshot-v1'    => 'Kimi',
      'doubao-pro'     => '豆包',
    ),
    'intl' => array(
      'suno-v5.5'   => 'Suno V5.5（全球最强）',
      'gpt-4o'      => 'GPT-4o',
      'claude-3-5-sonnet' => 'Claude 3.5 Sonnet',
      'gemini-1.5-pro'    => 'Gemini 1.5 Pro',
    ),
  ),
  'write' => array(
    'default' => 'kimi-k3',
    'domestic' => array(
      'kimi-k3'        => 'Kimi K3（国内最强）',
      'moonshot-v1'    => 'Kimi',
      'ernie-4.0-turbo'=> '文心 ERNIE',
      'deepseek-chat'  => 'DeepSeek',
      'qwen-plus'      => '通义千问',
      'glm-4'          => '智谱 GLM',
      'doubao-pro'     => '豆包',
    ),
    'intl' => array(
      'claude-fable-5.1' => 'Claude Fable 5.1（全球最强）',
      'claude-3-5-sonnet' => 'Claude 3.5 Sonnet',
      'gpt-4o'            => 'GPT-4o',
      'gemini-1.5-pro'    => 'Gemini 1.5 Pro',
    ),
  ),
  'ppt' => array(
    'default' => 'kimi-k3',
    'domestic' => array(
      'kimi-k3'        => 'Kimi K3（国内最强）',
      'glm-4'          => '智谱 GLM',
      'moonshot-v1'    => 'Kimi',
      'deepseek-chat'  => 'DeepSeek',
      'qwen-plus'      => '通义千问',
      'ernie-4.0-turbo'=> '文心 ERNIE',
    ),
    'intl' => array(
      'gpt-6-astra' => 'GPT-6 Astra（全球最强）',
      'gpt-4o'      => 'GPT-4o',
      'claude-3-5-sonnet' => 'Claude 3.5 Sonnet',
      'gemini-1.5-pro'    => 'Gemini 1.5 Pro',
    ),
  ),
  'avatar' => array(
    'default' => 'guiji',
    'domestic' => array(
      'guiji'      => '硅基智能（国内最强）',
      'doubao-pro' => '豆包',
      'huoshan'    => '火山引擎',
      'tencent'    => '腾讯智影',
      'shengshuo'  => '晟诺科讯达',
      'iflytek'    => '讯飞虚拟人',
      'baidu'      => '百度文心 2.0',
    ),
    'intl' => array(
      'heygen'     => 'HeyGen（全球最强）',
      'did'        => 'D-ID',
      'synthesia'  => 'Synthesia',
    ),
  ),
  'voice' => array(
    'default' => 'hunyuan-turbo',
    'domestic' => array(
      'hunyuan-turbo'  => '腾讯混元',
      'deepseek-chat'  => 'DeepSeek',
      'qwen-plus'      => '通义千问',
      'ernie-4.0-turbo'=> '文心 ERNIE',
      'doubao-pro'     => '豆包',
    ),
    'intl' => array(
      'gpt-4o'      => 'GPT-4o',
      'claude-3-5-sonnet' => 'Claude 3.5 Sonnet',
      'gemini-1.5-pro'    => 'Gemini 1.5 Pro',
    ),
  ),
  'file' => array(
    'default' => 'moonshot-v1',
    'domestic' => array(
      'deepseek-chat'  => 'DeepSeek',
      'qwen-plus'      => '通义千问',
      'ernie-4.0-turbo'=> '文心 ERNIE',
      'moonshot-v1'    => 'Kimi',
      'doubao-pro'     => '豆包',
    ),
    'intl' => array(
      'gpt-4o'      => 'GPT-4o',
      'gemini-1.5-pro' => 'Gemini 1.5 Pro',
      'claude-3-5-sonnet' => 'Claude 3.5 Sonnet',
      'gpt-4o-mini'       => 'GPT-4o mini',
    ),
  ),
);
if (!isset($section_models[$section])) $section = 'llm';
$sm = $section_models[$section];
$page_default = $sm['default'];
$domestic_models = $sm['domestic'];
$intl_models = $sm['intl'];

/* 数字人厂商名 → 实际调用模型映射（沿用原版：全部映射 DeepSeek 后端） */
$page_model_map = array();
if ($section === 'avatar') {
  foreach (array_keys($domestic_models) as $mv) { if ($mv !== 'doubao-pro') $page_model_map[$mv] = 'deepseek-chat'; }
  foreach (array_keys($intl_models) as $mv) { $page_model_map[$mv] = 'deepseek-chat'; }
}
$model_map_json = wp_json_encode($page_model_map);
?>
<div class="ai-page ai-chat-page">
  <div class="ai-chat-header">
    <div class="ai-chat-heading<?php echo !empty($desc_side) ? ' desc-side' : ''; ?>">
      <h1><span style="color:<?php echo esc_attr($color); ?>;"><?php echo $icon; ?></span> <?php echo esc_html($title); ?></h1>
      <?php if (!empty($desc)) : ?><p><?php echo esc_html($desc); ?></p><?php endif; ?>
    </div>
    <div class="ai-model-group"><span class="ai-model-label">选择模型</span>
    <select id="modelSelect" class="ai-model-select" title="选择模型">
      <optgroup label="国内模型">
        <?php foreach ($domestic_models as $mv => $ml) : ?>
          <option value="<?php echo esc_attr($mv); ?>" <?php selected($page_default, $mv); ?>><?php echo esc_html($ml); ?></option>
        <?php endforeach; ?>
      </optgroup>
      <optgroup label="海外模型">
        <?php foreach ($intl_models as $mv => $ml) : ?>
          <option value="<?php echo esc_attr($mv); ?>" <?php selected($page_default, $mv); ?>><?php echo esc_html($ml); ?></option>
        <?php endforeach; ?>
      </optgroup>
      <optgroup label="定制模型">
        <option value="__custom__">自定义模型</option>
      </optgroup>
    </select>
    </div>
  </div>
  <div class="ai-chat-messages" id="chatMessages">
    <?php if (!empty($welcome)) : ?>
    <div class="msg assistant">
      <div class="avatar" style="color:<?php echo esc_attr($color); ?>;"><?php echo $icon; ?></div>
      <div class="bubble"><?php echo esc_html($welcome); ?></div>
    </div>
    <?php endif; ?>
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
    <button class="btn ai-btn" id="<?php echo esc_attr($submit_id); ?>">didi</button>
  </div>
  <?php if (empty($hide_footnote)) : ?>
  <p class="ai-footnote" id="aiFootnote"><?php echo esc_html($footer_text); ?></p>
  <?php endif; ?>
</div>
<script>
(function () {
  var section = <?php echo $section_json; ?>;
  var MODEL_MAP = <?php echo $model_map_json; ?>;
  var textarea = document.getElementById('<?php echo esc_js($prompt_id); ?>');
  var submitBtn = document.getElementById('<?php echo esc_js($submit_id); ?>');
  var modelSel = document.getElementById('modelSelect');
  var msgBox = document.getElementById('chatMessages');
  var footnote = document.getElementById('aiFootnote');
  var customInput = null;

  function ensureCustomInput() {
    if (customInput) return customInput;
    customInput = document.createElement('input');
    customInput.type = 'text';
    customInput.placeholder = '输入自定义模型名称';
    customInput.className = 'input';
    customInput.style.cssText = 'flex:1 1 120px;width:auto;min-width:120px;max-width:240px;padding:8px 10px;border:1px solid #e5e6eb;border-radius:10px;font-size:13px;outline:none;';
    modelSel.parentNode.insertBefore(customInput, modelSel.nextSibling);
    return customInput;
  }

  function updateFootnote() {
    var isCustom = modelSel.value === '__custom__';
    var group = modelSel.parentNode;
    if (isCustom) {
      modelSel.style.minWidth = '0';
      modelSel.style.maxWidth = '130px';
      if (group) { group.style.flexShrink = '1'; group.style.minWidth = '0'; group.style.justifyContent = 'flex-end'; }
    } else {
      modelSel.style.minWidth = '';
      modelSel.style.maxWidth = '';
      if (group) { group.style.flexShrink = ''; group.style.minWidth = ''; group.style.justifyContent = ''; }
    }
    if (!footnote) return;
    var label = modelSel.selectedOptions[0] ? modelSel.selectedOptions[0].textContent.trim() : '自定义模型';
    var base = '<?php echo esc_js($title); ?>';
    footnote.textContent = 'didi AI · ' + base + '（' + label + '）';
  }

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
    if (textarea.dispatchEvent) textarea.dispatchEvent(new Event('input'));
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
    updateFootnote();
    if (modelSel.value === '__custom__') {
      ensureCustomInput().focus();
    }
  });

  updateFootnote();

  // 记住当前 TAB 的模型选择（含自定义模型名）
  if (typeof didiRememberModel === 'function') {
    didiRememberModel(section, modelSel, {
      customValue: '__custom__',
      ensureCustomInput: ensureCustomInput,
      getCustomInput: function () { return customInput; },
      onChange: updateFootnote
    });
  }

  // 语音 / 文件 / 图片工具接线（复用 ai-common.js）
  if (typeof didiWireTools === 'function') {
    didiWireTools(document.querySelector('.ai-chat-input'), textarea, submitBtn, false);
  }
})();
</script>
