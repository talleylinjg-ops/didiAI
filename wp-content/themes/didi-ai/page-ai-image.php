<?php
/**
 * AI 图片生成功能页模板
 * Template Name: AI 图片生成
 */
get_header();
?>
<div class="ai-page gen">
  <div class="gen-shell">
    <div class="gen-controls">
      <div class="card">
        <label class="field"><span>提示词</span>
          <div class="prompt-row" data-submit="generate-btn">
            <textarea class="input" id="prompt" style="min-height:120px;" placeholder="描述你想生成的图片，例如：赛博朋克风格的城市夜景，霓虹灯，高清，4K"></textarea>
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
        <label class="field"><span>图片模型</span>
          <select class="input" id="provider">
            <option value="modelscope">基础 · ModelScope</option>
            <option value="jimeng">标准 · 即梦 Dreamina</option>
            <option value="gemini">高端 · Gemini Banna</option>
            <option value="custom">自定义模型</option>
          </select>
        </label>
        <label class="field" id="customModelField" style="display:none;"><span>自定义模型名称</span>
          <input class="input" type="text" id="customModel" placeholder="输入自定义模型标识，例如 my-image-model">
        </label>
        <label class="field"><span>尺寸</span>
          <select class="input" id="size">
            <option value="1024x1024">1024 × 1024（方形）</option>
            <option value="1024x1792">1024 × 1792（竖版）</option>
            <option value="1792x1024">1792 × 1024（横版）</option>
            <option value="512x512">512 × 512（快速）</option>
          </select>
        </label>
        <div class="form-actions">
          <button class="btn primary ai-btn" id="generate-btn" style="flex:1;">didi</button>
        </div>
        <p class="hint" style="color:var(--text-faint); font-size:12px; margin-top:10px;">需要服务端配置 USER_IMAGE_* 环境变量接入你的图片 API Key</p>
      </div>
      <div class="card">
        <h3 style="margin-bottom:14px;">上传图片</h3>
        <label class="field"><span>选择本地图片上传</span>
          <div style="display:flex;gap:10px;">
            <button class="btn primary ai-btn" id="pickImage" type="button" style="flex:1;">上传图片</button>
          </div>
          <input type="file" id="imageFile" accept="image/*" style="display:none;">
        </label>
        <div id="uploadPreview" style="margin-top:10px;display:none;">
          <img id="uploadedImg" style="max-width:100%;max-height:200px;border-radius:10px;border:1px solid #e5e6eb;" alt="已上传图片">
          <p style="font-size:12px;color:var(--text-faint);margin-top:6px;">图片已上传，将作为图生图参考图使用</p>
        </div>
      </div>
    </div>
    <div>
      <div class="card gen-output" id="output">
        <div class="placeholder" id="placeholder">
          <div class="big">&#127912;</div>
          <p>输入提示词，点击生成图片<br>通常需要 10~60 秒</p>
        </div>
      </div>
    </div>
  </div>
  <p class="ai-footnote">didi AI · 图片生成（即梦 / OpenAI）</p>
</div>
<script>
(function(){
  var pickBtn = document.getElementById('pickImage');
  var fileInput = document.getElementById('imageFile');
  var uploadPreview = document.getElementById('uploadPreview');
  var uploadedImg = document.getElementById('uploadedImg');
  var uploadedUrl = '';
  var providerSel = document.getElementById('provider');
  var customModelField = document.getElementById('customModelField');
  function syncProvider() {
    customModelField.style.display = providerSel.value === 'custom' ? '' : 'none';
  }
  providerSel.addEventListener('change', syncProvider);
  if (pickBtn && fileInput) {
    pickBtn.addEventListener('click', function () { fileInput.click(); });
    fileInput.addEventListener('change', function () {
      var f = fileInput.files[0];
      if (!f) return;
      var fd = new FormData();
      fd.append('file', f);
      pickBtn.disabled = true;
      pickBtn.textContent = '上传中...';
      fetch('/wp-json/didi/v1/upload', { method: 'POST', headers: { 'X-WP-Nonce': (window.didiRestNonce || '') }, body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
          if (res.ok && res.d.ok) {
            uploadPreview.style.display = 'block';
            uploadedImg.src = res.d.url;
            uploadedUrl = res.d.url;
            toast('图片上传成功', 'success');
          } else {
            throw new Error((res.d && res.d.message) || '上传失败');
          }
        })
        .catch(function (err) {
          toast(err.message || '上传失败', 'error');
        })
        .finally(function () {
          pickBtn.disabled = false;
          pickBtn.textContent = '上传图片';
          fileInput.value = '';
        });
    });
  }
  document.getElementById('generate-btn').addEventListener('click', async function() {
    var prompt = document.getElementById('prompt').value.trim();
    if (!prompt) return toast('请输入图片提示词', 'error');
    var provider = document.getElementById('provider').value;
    var customModel = '';
    if (provider === 'custom') {
      customModel = document.getElementById('customModel').value.trim();
      if (!customModel) return toast('请输入自定义模型名称', 'error');
    }
    var size = document.getElementById('size').value;
    var ref = uploadedUrl;
    var btn = document.getElementById('generate-btn');
    btn.disabled = true;
    var output = document.getElementById('output');
    output.innerHTML = '<div style="text-align:center; color:var(--text-dim); padding:40px;"><div class="loading-spinner" style="margin:0 auto 16px;"></div><p>正在生成图片，请稍候...</p></div>';
    try {
      var data = await didiPost('/wp-json/didi/v1/image', { prompt: prompt, size: size, provider: provider, imageUrl: ref, model: customModel });
      var urls = data.result && data.result.urls ? data.result.urls : (data.urls || []);
      output.innerHTML = urls.map(function(u) {
        return '<img src="' + escapeHtml(u) + '" alt="' + escapeHtml(prompt) + '" style="width:100%; margin-bottom:12px;">';
      }).join('') || '<div class="placeholder"><p>生成完成，但接口未返回图片 URL</p></div>';
      toast('图片生成成功', 'success');
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
    // 顶部图片图标已覆盖上传，隐藏页内上传图片卡片
    var pickWrap = document.getElementById('pickImage');
    if (pickWrap && pickWrap.closest('.card')) pickWrap.closest('.card').style.display = 'none';
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
