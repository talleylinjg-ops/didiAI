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
        <label class="field prompt-embedded"><span>提示词</span>
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
        <label class="field"><span>视频模型</span>
          <select class="input" id="provider">
            <optgroup label="国内模型">
              <option value="didi-mpt" data-provider="mpt" data-tier="economy">免费 · MPT 主题成片（素材/配音/字幕）</option>
              <option value="doubao-seedance-1.0-pro" data-provider="seedance" data-tier="standard">标准 · 豆包 Seedance 1.0 Pro</option>
              <option value="jimeng-video-3.0" data-provider="jimeng" data-tier="standard">标准 · 即梦 Dreamina 3.0</option>
              <option value="kling-3.0" data-provider="kling" data-tier="premium" selected>高端 · 可灵 3.0（国内最强）</option>
              <option value="vidu-q2" data-provider="vidu" data-tier="premium">高端 · 生数 Vidu Q2</option>
              <option value="hailuo-2.3" data-provider="minimax" data-tier="premium">高端 · MiniMax 海螺 2.3</option>
              <option value="wan2.5-i2v" data-provider="wan" data-tier="standard">标准 · 通义万相 2.5</option>
            </optgroup>
            <optgroup label="海外模型">
              <option value="veo-3.1" data-provider="veo" data-tier="intl">高端 · Veo 3.1（全球最强）</option>
              <option value="sora-2" data-provider="sora" data-tier="intl">高端 · Sora 2</option>
              <option value="runway-gen4" data-provider="runway" data-tier="intl">高端 · Runway Gen-4</option>
              <option value="pika-2.5" data-provider="pika" data-tier="intl">标准 · Pika 2.5</option>
            </optgroup>
            <optgroup label="定制模型">
              <option value="custom" data-provider="custom">自定义模型</option>
            </optgroup>
          </select>
        </label>
        <label class="field" id="customModelField" style="display:none;"><span>自定义模型名称</span>
          <input class="input" type="text" id="customModel" placeholder="输入自定义模型标识，例如 my-video-model">
        </label>
        <label class="field" id="aspectField" style="display:none;"><span>画幅</span>
          <select class="input" id="videoAspect">
            <option value="9:16" selected>9:16 竖屏（短视频）</option>
            <option value="16:9">16:9 横屏</option>
            <option value="1:1">1:1 方形</option>
          </select>
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
</div>
<script>
(function(){
  var providerSel = document.getElementById('provider');
  var customModelField = document.getElementById('customModelField');
  var aspectField = document.getElementById('aspectField');
  function syncProvider() {
    customModelField.style.display = providerSel.value === 'custom' ? '' : 'none';
    aspectField.style.display = providerSel.value === 'didi-mpt' ? '' : 'none';
  }
  providerSel.addEventListener('change', syncProvider);
  if (typeof didiRememberModel === 'function') {
    didiRememberModel('video_v2', providerSel, { customValue: 'custom', customInput: document.getElementById('customModel'), onChange: syncProvider });
  }
  function currentChoice() {
    var opt = providerSel.options[providerSel.selectedIndex];
    var provider = opt ? (opt.getAttribute('data-provider') || opt.value) : 'kling';
    var tier = opt ? (opt.getAttribute('data-tier') || 'standard') : 'standard';
    var label = opt ? opt.textContent.trim() : provider;
    var model = providerSel.value;
    var custom = provider === 'custom';
    if (custom) {
      model = (document.getElementById('customModel').value || '').trim();
      if (!model) { toast('请输入自定义模型名称', 'error'); return null; }
    }
    var aspectEl = document.getElementById('videoAspect');
    var aspect = aspectEl ? aspectEl.value : '';
    return { provider: provider, tier: tier, model: model, custom: custom, label: label, aspect: aspect };
  }
  var tierNames = { standard: '标准', premium: '高端', economy: '免费', intl: '国际' };
  function sleep(ms){ return new Promise(function(r){ setTimeout(r, ms); }); }
  function errCard(msg){ return '<div class="placeholder"><div class="big">&#9888;</div><p style="color:var(--danger)">' + escapeHtml(msg) + '</p></div>'; }
  function loadingHtml(choice, text){
    return '<div style="text-align:center; color:var(--text-dim); padding:40px;"><div class="loading-spinner" style="margin:0 auto 16px;"></div><p>' + escapeHtml(text) + '</p><p style="font-size:12px; margin-top:8px;">档位：' + (tierNames[choice.tier] || choice.tier) + ' · 模型：' + escapeHtml(choice.label) + '</p></div>';
  }
  function videoResult(url, taskId){
    return '<div style="width:100%; padding:20px;">'
      + '<h3 style="margin-bottom:10px;">视频生成完成</h3>'
      + '<video src="' + escapeHtml(url) + '" controls style="max-width:100%; border-radius:10px;"></video>'
      + '<p style="font-size:12px; color:var(--text-faint); margin-top:8px;">任务 ID：' + escapeHtml(taskId) + '</p></div>';
  }
  // 免费异步渠道（didi Media / MPT）：前端轮询直到拿到结果 URL；容忍偶发网络波动
  async function pollTask(taskId, endpoint){
    var output = document.getElementById('output');
    var deadline = Date.now() + 15 * 60 * 1000;
    var statusNames = { pending: '排队中', queued: '排队中', running: '生成中', processing: '生成中' };
    var errStreak = 0;
    while (Date.now() < deadline) {
      await sleep(5000);
      var d;
      try {
        d = await didiPost(endpoint, { taskId: taskId });
        errStreak = 0;
      } catch (e) {
        errStreak++;
        if (errStreak >= 6) {
          output.innerHTML = errCard(e.message);
          toast(e.message, 'error');
          return;
        }
        await sleep(3000);
        continue;
      }
      if (d.url) {
        output.innerHTML = videoResult(d.url, taskId);
        toast('视频生成完成', 'success');
        return;
      }
      if (d.ok === false) {
        output.innerHTML = errCard(d.message || '任务失败');
        toast(d.message || '任务失败', 'error');
        return;
      }
      var st = String(d.status || 'queued').toLowerCase();
      var stage = d.stage ? (' · ' + escapeHtml(d.stage)) : '';
      var progress = (typeof d.progress === 'number' && d.progress > 0) ? (' ' + d.progress + '%') : '';
      output.innerHTML = '<div style="text-align:center; color:var(--text-dim); padding:40px;"><div class="loading-spinner" style="margin:0 auto 16px;"></div><p>' + (statusNames[st] || '处理中') + progress + stage + '…</p><p style="font-size:12px; margin-top:8px;">任务 ID：' + escapeHtml(taskId) + '</p></div>';
    }
    output.innerHTML = '<div style="width:100%; padding:20px;"><h3>任务仍在处理</h3><p style="font-size:13px; color:var(--text-dim);">任务 ID：' + escapeHtml(taskId) + '，请稍后重试查询。</p></div>';
  }
  document.getElementById('generate-btn').addEventListener('click', async function() {
    var prompt = document.getElementById('prompt').value.trim();
    if (!prompt) return toast('请输入视频提示词', 'error');
    var choice = currentChoice();
    if (!choice) return;
    var btn = document.getElementById('generate-btn');
    btn.disabled = true;
    var output = document.getElementById('output');
    var useMediaCut = choice.provider === 'didi-media';
    var useMpt = choice.provider === 'mpt';
    var isFreeAsync = useMediaCut || useMpt;
    output.innerHTML = loadingHtml(choice, useMediaCut ? '正在提交 didi Media 任务...' : (useMpt ? '正在提交 MPT 成片任务...' : '视频生成任务已提交，请稍候...'));
    try {
      var payload = { prompt: prompt, provider: choice.provider, tier: choice.tier, model: choice.model, custom: choice.custom };
      if (choice.aspect) payload.aspect = choice.aspect;
      var data = await didiPost('/wp-json/didi/v1/video', payload);
      var taskId = (data.result && data.result.taskId) || data.taskId || '';
      var shouldPoll = isFreeAsync || (data.result && data.result.poll) || data.poll;
      if (shouldPoll && taskId) {
        await pollTask(taskId, useMpt ? '/wp-json/didi/v1/mpt/task' : '/wp-json/didi/v1/mc/task');
      } else {
        output.innerHTML = '<div style="width:100%; padding:20px;">'
          + '<h3 style="margin-bottom:10px;">任务已提交</h3>'
          + '<p style="font-size:13.5px; color:var(--text-dim); margin-bottom:14px;">提示词：' + escapeHtml(prompt) + '</p>'
          + '<div class="card" style="background:var(--bg-soft);">'
          + '<p><b>任务 ID：</b>' + escapeHtml(taskId || 'N/A') + '</p>'
          + '<p><b>状态：</b><span class="badge accent">' + escapeHtml((data.result && data.result.status) || data.status || 'submitted') + '</span></p>'
          + '<p style="font-size:12px; color:var(--text-faint); margin-top:8px;">视频生成完成后，视频 URL 会出现在接口返回中。</p></div>'
          + '</div>';
        toast('视频任务已提交', 'success');
      }
    } catch (err) {
      output.innerHTML = errCard(err.message);
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
