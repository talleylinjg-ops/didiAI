<?php
/**
 * AI 视频生成功能页模板
 * Template Name: AI 视频生成
 */
get_header();
?>
<div class="ai-page gen">
  <div class="gen-shell">
    <div class="gen-controls">
      <div class="card">
        <label class="field"><span>提示词</span>
          <div class="prompt-row" data-submit="generate-btn">
            <textarea class="input" id="prompt" style="min-height:120px;" placeholder="描述你想生成的视频画面，例如：一只猫在夕阳下的海边散步，电影质感"></textarea>
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
        <label class="field"><span>时长（秒）</span>
          <select class="input" id="duration">
            <option value="5">5 秒</option>
            <option value="10">10 秒</option>
            <option value="20">20 秒</option>
            <option value="28">28 秒（最高）</option>
          </select>
        </label>
        <label class="field"><span>视频模型</span>
          <select class="input" id="provider">
            <option value="jimeng">标准 · 即梦 Dreamina API</option>
            <option value="kling">高端 · 默认 可灵（推荐）</option>
            <option value="openai">高端 · 备选 OPEN AI</option>
            <option value="runway">高端 · 备选 RUNWAY</option>
            <option value="modelscope">经济 · ModelScope（便宜）5 秒</option>
            <option value="custom">自定义模型</option>
          </select>
        </label>
        <label class="field" id="customModelField" style="display:none;"><span>自定义模型名称</span>
          <input class="input" type="text" id="customModel" placeholder="输入自定义模型标识，例如 my-video-model">
        </label>
        <div class="form-actions">
          <button class="btn primary ai-btn" id="generate-btn" style="flex:1;">didi</button>
        </div>
        <p class="hint" style="color:var(--text-faint); font-size:12px; margin-top:10px;">接口配置可在后台「didi AI 配置」页填写，也可用 USER_VIDEO_* / USER_VIDEO_INTL_* 环境变量</p>
      </div>
    </div>
    <div>
      <div class="card gen-output" id="output">
        <div class="placeholder" id="placeholder">
          <div class="big">&#9654;</div>
          <p>输入提示词，点击生成<br>视频生成通常需要 1~5 分钟</p>
        </div>
      </div>
    </div>
  </div>
  <div class="card" style="margin-top:20px; overflow-x:auto;">
    <table style="width:100%; border-collapse:collapse; font-size:13px; min-width:640px;">
      <thead>
        <tr style="background:var(--bg-soft); text-align:left;">
          <th style="padding:10px 12px;">模型</th>
          <th style="padding:10px 12px;">厂商 / 定位</th>
          <th style="padding:10px 12px;">地区</th>
          <th style="padding:10px 12px;">擅长</th>
          <th style="padding:10px 12px;">档位</th>
          <th style="padding:10px 12px;">价格</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="padding:10px 12px;"><b>即梦 Jimeng</b></td>
          <td style="padding:10px 12px;">字节即梦 · Dreamina API</td>
          <td style="padding:10px 12px;">中国</td>
          <td style="padding:10px 12px;">图生视频 / 视频编辑，艺术风格与特效丰富</td>
          <td style="padding:10px 12px;"><span class="badge">标准档</span></td>
          <td style="padding:10px 12px; font-weight:700;">30 点/条</td>
        </tr>
        <tr style="background:var(--bg-soft);">
          <td style="padding:10px 12px;"><b>SEEDANCE</b></td>
          <td style="padding:10px 12px;">字节豆包 · 标准档可选</td>
          <td style="padding:10px 12px;">中国</td>
          <td style="padding:10px 12px;">文生视频，中文理解好、画质细腻、响应快</td>
          <td style="padding:10px 12px;"><span class="badge">标准档</span></td>
          <td style="padding:10px 12px; font-weight:700;">30 点/条</td>
        </tr>
        <tr>
          <td style="padding:10px 12px;"><b>可灵 Kling 1.6</b></td>
          <td style="padding:10px 12px;">快手 · 影视级质感</td>
          <td style="padding:10px 12px;">中国</td>
          <td style="padding:10px 12px;">文生视频 / 图生视频，镜头语言与光影真实</td>
          <td style="padding:10px 12px;"><span class="badge accent">高端档</span></td>
          <td style="padding:10px 12px; font-weight:700;">30 点/条</td>
        </tr>
        <tr>
          <td style="padding:10px 12px;"><b>OpenAI</b></td>
          <td style="padding:10px 12px;">OpenAI · 高端备选</td>
          <td style="padding:10px 12px;">国际</td>
          <td style="padding:10px 12px;">文生视频，语义理解强、画面连贯</td>
          <td style="padding:10px 12px;"><span class="badge accent">高端备选</span></td>
          <td style="padding:10px 12px; font-weight:700;">80 点/条</td>
        </tr>
        <tr>
          <td style="padding:10px 12px;"><b>ModelScope</b></td>
          <td style="padding:10px 12px;">阿里 · 开源模型托管</td>
          <td style="padding:10px 12px;">中国</td>
          <td style="padding:10px 12px;">文生视频 / 图生视频，开源社区模型，价格便宜</td>
          <td style="padding:10px 12px;"><span class="badge">经济档</span></td>
          <td style="padding:10px 12px; font-weight:700;">超低价</td>
        </tr>
        <tr style="background:var(--bg-soft);">
          <td style="padding:10px 12px;"><b>Runway Gen-3</b></td>
          <td style="padding:10px 12px;">Runway · 国际电影级</td>
          <td style="padding:10px 12px;">国际</td>
          <td style="padding:10px 12px;">文生视频，电影级动态、转场与镜头控制</td>
          <td style="padding:10px 12px;"><span class="badge accent">国际高端档</span></td>
          <td style="padding:10px 12px; font-weight:700;">80 点/条</td>
        </tr>
      </tbody>
    </table>
  </div>
  <p class="ai-footnote">didi AI · 视频生成（标准：即梦 / 高端：可灵 / 经济：ModelScope）</p>
</div>
<script>
(function(){
  var providerSel = document.getElementById('provider');
  var customModelField = document.getElementById('customModelField');
  function syncProvider() {
    customModelField.style.display = providerSel.value === 'custom' ? '' : 'none';
  }
  providerSel.addEventListener('change', syncProvider);
  var providerTier = { jimeng: 'standard', kling: 'premium', openai: 'premium', runway: 'premium', modelscope: 'economy', custom: 'standard' };
  var tierNames = { standard: '标准', premium: '高端', economy: '经济' };
  document.getElementById('generate-btn').addEventListener('click', async function() {
    var prompt = document.getElementById('prompt').value.trim();
    if (!prompt) return toast('请输入视频提示词', 'error');
    var provider = providerSel.value;
    var customModel = '';
    if (provider === 'custom') {
      customModel = document.getElementById('customModel').value.trim();
      if (!customModel) return toast('请输入自定义模型名称', 'error');
    }
    var tier = providerTier[provider] || 'standard';
    var duration = Number(document.getElementById('duration').value);
    var btn = document.getElementById('generate-btn');
    btn.disabled = true;
    var output = document.getElementById('output');
    output.innerHTML = '<div style="text-align:center; color:var(--text-dim); padding:40px;"><div class="loading-spinner" style="margin:0 auto 16px;"></div><p>视频生成任务已提交，请稍候...</p><p style="font-size:12px; margin-top:8px;">档位：' + (tierNames[tier] || tier) + ' · 厂商：' + (customModel || provider) + ' · 时长：' + duration + 's</p></div>';
    try {
      var data = await didiPost('/wp-json/didi/v1/video', { prompt: prompt, duration: duration, provider: provider, tier: tier, model: customModel });
      output.innerHTML = '<div style="width:100%; padding:20px;">'
        + '<h3 style="margin-bottom:10px;">任务已提交</h3>'
        + '<p style="font-size:13.5px; color:var(--text-dim); margin-bottom:14px;">提示词：' + escapeHtml(prompt) + '</p>'
        + '<div class="card" style="background:var(--bg-soft);">'
        + '<p><b>任务 ID：</b>' + escapeHtml(data.result.taskId || data.taskId || 'N/A') + '</p>'
        + '<p><b>状态：</b><span class="badge accent">' + escapeHtml(data.result.status || data.status || 'submitted') + '</span></p>'
        + '<p style="font-size:12px; color:var(--text-faint); margin-top:8px;">视频生成完成后，视频 URL 会出现在接口返回中。</p></div>'
        + '</div>';
      toast('视频任务已提交', 'success');
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
