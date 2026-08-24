<?php
/**
 * AI 动画创作功能页模板
 * Template Name: AI 动画创作
 */
get_header();
?>
<div class="ai-page gen">
  <div class="gen-shell">
    <div class="gen-controls">
      <div class="card">
        <label class="field"><span>提示词</span>
          <div class="prompt-row" data-submit="generate-btn">
            <textarea class="input" id="prompt" style="min-height:120px;" placeholder="描述动画内容，例如：一只橘猫在草地上打滚，卡通风格，角色运动流畅"></textarea>
            <div class="prompt-tools">
              <button type="button" class="tool-icon" data-tool="voice" title="语音输入">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
              </button>
              <button type="button" class="tool-icon" data-tool="file" title="上传文件">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              </button>
              <button type="button" class="tool-icon" data-tool="image" title="上传图片">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              </button>
            </div>
          </div>
        </label>
        <label class="field"><span>动画模型</span>
          <select class="input" id="provider">
            <option value="animatediff">AnimateDiff</option>
            <option value="animatediff-lite">AnimateDiff-Lite</option>
            <option value="custom">自定义模型</option>
          </select>
        </label>
        <label class="field" id="customModelField" style="display:none;"><span>自定义模型名称</span>
          <input class="input" type="text" id="customModel" placeholder="输入自定义模型标识，例如 my-animate-model">
        </label>
        <label class="field"><span>帧数</span>
          <select class="input" id="frames">
            <option value="16">16 帧</option>
            <option value="24">24 帧</option>
            <option value="32">32 帧</option>
          </select>
        </label>
        <label class="field"><span>分辨率</span>
          <select class="input" id="resolution">
            <option value="512x512">512 × 512</option>
            <option value="512x768">512 × 768（竖屏）</option>
            <option value="768x512">768 × 512（横屏）</option>
          </select>
        </label>
        <div class="form-actions">
          <button class="btn primary ai-btn" id="generate-btn" style="flex:1;">didi</button>
        </div>
        <p class="hint" style="color:var(--text-faint); font-size:12px; margin-top:10px;">需要服务端配置 USER_ANIMATE_* 环境变量接入 AnimateDiff 推理服务（ComfyUI / Replicate / 自建）</p>
      </div>
    </div>
    <div>
      <div class="card gen-output" id="output">
        <div class="placeholder" id="placeholder">
          <div class="big">&#127916;</div>
          <p>输入提示词，点击生成 AnimateDiff 动画<br>动画生成通常需要 1~3 分钟</p>
        </div>
      </div>
    </div>
  </div>
  <p class="ai-footnote">didi AI · 动画创作（AnimateDiff 视频模型）</p>
</div>
<script>
(function(){
  var providerSel = document.getElementById('provider');
  var customModelField = document.getElementById('customModelField');
  var customModelInput = document.getElementById('customModel');
  function syncProvider() {
    var isCustom = providerSel.value === 'custom';
    customModelField.style.display = isCustom ? '' : 'none';
    if (isCustom) customModelInput.focus();
  }
  providerSel.addEventListener('change', syncProvider);
  document.getElementById('generate-btn').addEventListener('click', async function() {
    var prompt = document.getElementById('prompt').value.trim();
    if (!prompt) return toast('请输入动画提示词', 'error');
    var provider = providerSel.value;
    var customModel = '';
    if (provider === 'custom') {
      customModel = customModelInput.value.trim();
      if (!customModel) return toast('请输入自定义模型名称', 'error');
    }
    var frames = Number(document.getElementById('frames').value);
    var res = document.getElementById('resolution').value.split('x');
    var width = Number(res[0]), height = Number(res[1]);
    var btn = document.getElementById('generate-btn');
    btn.disabled = true;
    var output = document.getElementById('output');
    output.innerHTML = '<div style="text-align:center; color:var(--text-dim); padding:40px;"><div class="loading-spinner" style="margin:0 auto 16px;"></div><p>动画生成任务已提交，请稍候...</p><p style="font-size:12px; margin-top:8px;">模型：' + (customModel || provider) + ' · 帧数：' + frames + ' · ' + width + '×' + height + '</p></div>';
    try {
      var data = await didiPost('/wp-json/didi/v1/animate', { prompt: prompt, frames: frames, width: width, height: height, provider: provider, model: customModel });
      output.innerHTML = '<div style="width:100%; padding:20px;">'
        + '<h3 style="margin-bottom:10px;">动画任务已提交</h3>'
        + '<p style="font-size:13.5px; color:var(--text-dim); margin-bottom:14px;">提示词：' + escapeHtml(prompt) + '</p>'
        + '<div class="card" style="background:var(--bg-soft);">'
        + '<p><b>任务 ID：</b>' + escapeHtml(data.result.taskId || data.taskId || 'N/A') + '</p>'
        + '<p><b>状态：</b><span class="badge accent">' + escapeHtml(data.result.status || data.status || 'submitted') + '</span></p>'
        + '<p style="font-size:12px; color:var(--text-faint); margin-top:8px;">AnimateDiff 动画完成后，帧序列或视频 URL 会出现在接口返回中。</p></div>'
        + '</div>';
      toast('动画任务已提交', 'success');
    } catch (err) {
      output.innerHTML = '<div class="placeholder"><div class="big">&#9888;</div><p style="color:var(--danger)">' + escapeHtml(err.message) + '</p></div>';
      toast(err.message, 'error');
    }
    btn.disabled = false;
  });
  var preset = new URLSearchParams(location.search).get('q');
  if (preset) {
    document.getElementById('prompt').value = preset;
    document.getElementById('generate-btn').click();
  }
  if (new URLSearchParams(location.search).get('embed') === '1') {
    window.addEventListener('message', function (e) {
      var data = e.data;
      if (!data || (data.type !== 'didi-submit' && data.type !== 'didi-focus')) return;
      var promptEl = document.getElementById('prompt');
      if (data.type === 'didi-focus') {
        if (promptEl) { promptEl.focus(); promptEl.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
        return;
      }
      promptEl.value = String(data.text || '').trim();
      if (promptEl.value) document.getElementById('generate-btn').click();
    });
  }
})();
</script>
<?php get_footer(); ?>
