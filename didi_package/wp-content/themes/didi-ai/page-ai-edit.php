<?php
/**
 * AI 剪辑工作台（视频 / 动漫 / 图片 / 音频 / 轻剪辑）
 * 支持上传原始素材 + 选择编辑模型 + 写编辑指令 + 预览结果
 * 视频/动漫走本站服务；图片/音频/轻剪辑对接用户自部署的 VPS 剪辑服务（USER_VPS_BASE_URL）
 * Template Name: AI 剪辑工作台
 */
get_header();
?>
<div class="ai-page gen">
  <div class="edit-tabs" style="display:flex;gap:10px;margin-bottom:20px;margin-top:24px;flex-wrap:wrap;">
    <button type="button" class="edit-tab active" data-type="video" style="flex:1;min-width:120px;padding:16px 0;font-size:18px;font-weight:700;border:none;border-radius:12px;cursor:pointer;letter-spacing:4px;background:#1668dc;color:#fff;box-shadow:0 4px 14px rgba(22,104,220,0.3);">视频剪辑</button>
    <button type="button" class="edit-tab" data-type="animate" style="flex:1;min-width:120px;padding:16px 0;font-size:18px;font-weight:700;border:none;border-radius:12px;cursor:pointer;letter-spacing:4px;background:#fff;color:#4e5969;border:1px solid #e5e6eb;">动画剪辑</button>
    <button type="button" class="edit-tab" data-type="image" style="flex:1;min-width:120px;padding:16px 0;font-size:18px;font-weight:700;border:none;border-radius:12px;cursor:pointer;letter-spacing:4px;background:#fff;color:#4e5969;border:1px solid #e5e6eb;">图片剪辑</button>
    <button type="button" class="edit-tab" data-type="audio" style="flex:1;min-width:120px;padding:16px 0;font-size:18px;font-weight:700;border:none;border-radius:12px;cursor:pointer;letter-spacing:4px;background:#fff;color:#4e5969;border:1px solid #e5e6eb;">音频剪辑</button>
    <button type="button" class="edit-tab" data-type="lite" style="flex:1;min-width:120px;padding:9px 0;font-size:18px;font-weight:700;border:none;border-radius:12px;cursor:pointer;letter-spacing:4px;background:#fff;color:#4e5969;border:1px solid #e5e6eb;display:flex;flex-direction:column;align-items:center;justify-content:center;">
      <span>轻剪辑</span>
      <span style="font-size:11px;font-weight:400;letter-spacing:1px;color:#86909c;margin-top:4px;">图片生视频</span>
    </button>
  </div>
  <div class="gen-shell">
    <div class="gen-controls">
      <div class="card">
        <label class="field"><span>剪辑指令</span>
          <div class="prompt-row" data-submit="edit-btn">
            <textarea class="input" id="prompt" style="min-height:140px;" placeholder="描述你想怎么处理素材，例如：去头去尾，把中间片段切成 3 段并加字幕"></textarea>
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
        <label class="field" id="providerField"><span>编辑模型</span>
          <select class="input" id="provider">
          </select>
        </label>
        <div id="tierNote" style="display:none;margin-top:8px;padding:8px 10px;background:var(--bg-soft);border-radius:10px;font-size:12.5px;color:#4e5969;line-height:1.7;"></div>
        <div id="vpsNote" style="display:none;margin-top:8px;padding:8px 10px;background:linear-gradient(90deg,rgba(22,104,220,.08),rgba(123,104,238,.08));border:1px dashed #c8dbff;border-radius:10px;font-size:12.5px;color:#1668dc;line-height:1.7;"></div>
        <label class="field"><span id="mediaLabel">上传素材（图片或视频）</span>
          <div style="display:flex;gap:10px;">
            <input class="input" type="text" id="mediaUrl" placeholder="粘贴素材 URL，或上传本地文件">
            <button class="btn primary" id="pickMedia" style="white-space:nowrap;">上传</button>
          </div>
          <input type="file" id="mediaFile" accept="image/*,video/*" style="display:none;">
        </label>
        <div id="mediaPreview" style="margin-top:10px;display:none;">
          <div style="position:relative;display:inline-block;">
            <img id="previewImg" style="max-width:100%;max-height:200px;border-radius:10px;border:1px solid #e5e6eb;" alt="素材预览">
            <video id="previewVid" style="max-width:100%;max-height:200px;border-radius:10px;border:1px solid #e5e6eb;display:none;" controls></video>
            <audio id="previewAud" style="max-width:100%;margin-top:6px;display:none;" controls></audio>
            <button id="removeMedia" style="position:absolute;top:-8px;right:-8px;width:24px;height:24px;border-radius:50%;border:none;background:#f5222d;color:#fff;cursor:pointer;font-size:14px;line-height:1;">&times;</button>
          </div>
        </div>
        <label class="field" id="customModelField" style="display:none;"><span>自定义模型名称</span>
          <input class="input" type="text" id="customModel" placeholder="输入自定义模型标识，例如 my-edit-model">
        </label>
        <div id="chatcutHint" class="hint" style="color:var(--text-faint); font-size:12px; margin-bottom:10px;">已加载 ChatCut 剪辑规则库（10 条：转写/拆分/删减/重组/字幕/节奏/转场/声音/风格/校验），将随指令注入剪辑模型</div>
        <div class="form-actions">
          <button class="btn primary ai-btn" id="edit-btn" style="flex:1;">didi</button>
        </div>
        <p class="hint" style="color:var(--text-faint); font-size:12px; margin-top:10px;">视频/动漫走本站服务（USER_EDIT_* / USER_ANIMATE_*）；图片/音频/轻剪辑对接你的剪辑服务（USER_VPS_BASE_URL）</p>
      </div>
    </div>
    <div>
      <div class="card gen-output" id="output">
        <div class="placeholder" id="placeholder">
          <div class="big">&#9998;</div>
          <p>上传素材，输入剪辑指令<br>AI 将按提示词完成剪辑，通常需要 1~5 分钟</p>
        </div>
      </div>
    </div>
  </div>
  <p class="ai-footnote">didi AI · 剪辑工作台（视频：即梦/可灵/OpenAI/Runway/ModelScope · 动画：AnimateDiff · 图片/音频/轻剪辑：VPS）</p>
</div>
<script>
(function(){
  var type = 'video';
  var mediaUrl = '';

  var PROVIDERS = {
    video: {
      label: '视频模型 / 档位',
      options: [
        { value: 'jimeng', text: '标准 · 即梦 Dreamina API' },
        { value: 'kling', text: '高端 · 默认 可灵（推荐）' },
        { value: 'openai', text: '高端 · 备选 OPEN AI' },
        { value: 'runway', text: '高端 · 备选 RUNWAY' },
        { value: 'modelscope', text: '经济 · ModelScope（便宜）5 秒' },
        { value: 'custom', text: '自定义模型' }
      ],
      tier: '标准：即梦 Dreamina API　｜　高端：默认可灵，备选 OPEN AI / RUNWAY　｜　经济：ModelScope（便宜）5 秒',
      vps: '',
      media: 'image/*,video/*',
      mediaLabel: '上传素材（图片或视频）',
      api: 'edit'
    },
    animate: {
      label: '动画模型',
      options: [
        { value: 'animatediff', text: 'AnimateDiff（恢复原配置）' },
        { value: 'animatediff-lite', text: 'AnimateDiff-Lite' },
        { value: 'custom', text: '自定义模型' }
      ],
      tier: '动画剪辑恢复 AnimateDiff 视频模型，文生动画 / 图生动画',
      vps: '',
      media: 'image/*,video/*',
      mediaLabel: '上传素材（可选，图生动画）',
      api: 'animate'
    },
    image: {
      label: '图片剪辑工具',
      options: [
        { value: 'pillow', text: '基础 · Pillow（完全免费）' },
        { value: 'opencv', text: '高级 · OpenCV（完全免费）' },
        { value: 'modelscope', text: 'AI · ModelScope（收费）' },
        { value: 'jimeng', text: 'AI · 即梦 Dreamina' },
        { value: 'custom', text: '自定义模型' }
      ],
      tier: '基础 Pillow（完全免费）　｜　高级 OpenCV（完全免费）　｜　AI ModelScope（收费）　｜　AI 即梦 Dreamina',
      vps: '图片剪辑走你自部署的剪辑服务（Pillow / OpenCV / ModelScope 由该服务执行）',
      media: 'image/*',
      mediaLabel: '上传图片素材',
      api: 'vps',
      vpsPath: '/api/image/edit'
    },
    audio: {
      label: '音频处理模型',
      options: [
        { value: 'modelscope', text: 'ModelScope（AI 模型：语音识别 / 合成）' },
        { value: 'custom', text: '自定义模型' }
      ],
      tier: 'ModelScope 提供 AI 模型（语音识别 / 合成），由你的 VPS 剪辑服务执行音频剪辑',
      vps: '音频剪辑对接你自部署的剪辑服务（ModelScope 提供 AI 模型）',
      media: 'audio/*',
      mediaLabel: '上传音频素材',
      api: 'vps',
      vpsPath: '/api/audio/edit'
    },
    lite: {
      label: '图片生视频工具',
      options: [
        { value: 'pillow', text: '基础 · Pillow（完全免费 · 无限 · 编程剪辑 · 自动化）' },
        { value: 'opencv', text: '高级 · OpenCV（完全免费 · 无限 · 编程处理 · 高级开发）' },
        { value: 'custom', text: '自定义模型' }
      ],
      tier: '基础 Pillow（完全免费·无限·编程剪辑·自动化）　｜　高级 OpenCV（完全免费·无限·编程处理·高级开发）',
      vps: '轻剪辑·图片生视频对接你自部署的图片剪辑软件（Pillow / OpenCV）',
      media: 'image/*',
      mediaLabel: '上传图片素材',
      api: 'vps',
      vpsPath: '/api/lite/video'
    }
  };

  function setTab(t){
    type = t;
    document.querySelectorAll('.edit-tab').forEach(function(btn){
      var on = btn.getAttribute('data-type') === t;
      btn.style.background = on ? '#1668dc' : '#fff';
      btn.style.color = on ? '#fff' : '#4e5969';
      btn.style.border = on ? 'none' : '1px solid #e5e6eb';
      btn.style.boxShadow = on ? '0 4px 14px rgba(22,104,220,0.3)' : 'none';
    });
    var cfg = PROVIDERS[t] || PROVIDERS.video;
    var prov = document.getElementById('provider');
    prov.innerHTML = '';
    cfg.options.forEach(function(o){
      var op = document.createElement('option');
      op.value = o.value; op.textContent = o.text;
      prov.appendChild(op);
    });
    document.getElementById('tierNote').style.display = cfg.tier ? '' : 'none';
    document.getElementById('tierNote').textContent = cfg.tier || '';
    document.getElementById('vpsNote').style.display = cfg.vps ? '' : 'none';
    document.getElementById('vpsNote').innerHTML = cfg.vps ? '<b>VPS 对接：</b>' + cfg.vps : '';
    document.getElementById('mediaLabel').textContent = cfg.mediaLabel;
    document.getElementById('mediaFile').accept = cfg.media;
    document.getElementById('chatcutHint').style.display = (t === 'video' || t === 'animate') ? '' : 'none';
    var customField = document.getElementById('customModelField');
    if (customField) {
      customField.style.display = (prov.value === 'custom') ? '' : 'none';
    }
    mediaUrl = '';
    document.getElementById('mediaPreview').style.display = 'none';
    document.getElementById('mediaUrl').value = '';
  }

  var provSel = document.getElementById('provider');
  provSel.addEventListener('change', function(){
    var customField = document.getElementById('customModelField');
    if (customField) customField.style.display = (provSel.value === 'custom') ? '' : 'none';
  });

  document.querySelectorAll('.edit-tab').forEach(function(btn){
    btn.addEventListener('click', function(){ setTab(btn.getAttribute('data-type')); });
  });

  document.getElementById('pickMedia').addEventListener('click', function(){
    document.getElementById('mediaFile').click();
  });

  document.getElementById('mediaFile').addEventListener('change', function(){
    var file = this.files[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) return toast('素材不能超过 10MB', 'error');
    var fd = new FormData();
    fd.append('file', file);
    fetch('/wp-json/didi/v1/upload', {
      method: 'POST',
      headers: { 'X-WP-Nonce': (window.didiRestNonce || '') },
      body: fd,
      credentials: 'same-origin'
    }).then(function (r) { return r.json(); })
    .then(function (d) {
      if (d.ok && d.url) {
        mediaUrl = d.url;
        showMedia(d.url, file.type);
        toast('素材已上传，可直接处理', 'success');
      } else {
        throw new Error((d && d.message) || '上传失败');
      }
    }).catch(function (err) {
      toast(err.message, 'error');
    });
  });

  function showMedia(url, mime){
    var box = document.getElementById('mediaPreview');
    var img = document.getElementById('previewImg');
    var vid = document.getElementById('previewVid');
    var aud = document.getElementById('previewAud');
    box.style.display = '';
    if (mime && mime.indexOf('video/') === 0){
      img.style.display = 'none'; vid.style.display = ''; aud.style.display = 'none';
      vid.src = url;
    } else if (mime && mime.indexOf('audio/') === 0){
      img.style.display = 'none'; vid.style.display = 'none'; aud.style.display = '';
      aud.src = url;
    } else {
      vid.style.display = 'none'; aud.style.display = 'none'; img.style.display = '';
      img.src = url;
    }
  }

  document.getElementById('mediaUrl').addEventListener('input', function(){
    var v = this.value.trim();
    if (/^https?:\/\//i.test(v)) {
      mediaUrl = v;
      showMedia(v, v.match(/\.(mp4|webm|mov)/i) ? 'video/mp4' : (v.match(/\.(mp3|wav|m4a|aac|flac)/i) ? 'audio/mpeg' : 'image/*'));
    }
  });

  document.getElementById('removeMedia').addEventListener('click', function(){
    mediaUrl = '';
    document.getElementById('mediaPreview').style.display = 'none';
    document.getElementById('mediaUrl').value = '';
    document.getElementById('mediaFile').value = '';
  });

  document.getElementById('edit-btn').addEventListener('click', async function() {
    var prompt = document.getElementById('prompt').value.trim();
    if (!prompt) return toast('请输入剪辑指令', 'error');
    if (!mediaUrl) return toast('请先上传素材', 'error');
    var provider = document.getElementById('provider').value;
    var cfg = PROVIDERS[type] || PROVIDERS.video;
    var customModel = '';
    if (provider === 'custom') {
      customModel = document.getElementById('customModel').value.trim();
      if (!customModel) return toast('请输入自定义模型名称', 'error');
    }
    var btn = document.getElementById('edit-btn');
    btn.disabled = true;
    var output = document.getElementById('output');
    output.innerHTML = '<div style="text-align:center; color:var(--text-dim); padding:40px;"><div class="loading-spinner" style="margin:0 auto 16px;"></div><p>任务已提交，请稍候...</p><p style="font-size:12px; margin-top:8px;">模式：' + type + ' · 模型：' + (customModel || provider) + '</p></div>';
    try {
      if (cfg.api === 'vps') {
        var data = await didiPost('/wp-json/didi/v1/vps', {
          feature: type,
          action: 'generate',
          path: cfg.vpsPath,
          data: { type: type, provider: provider, prompt: prompt, mediaUrl: mediaUrl, model: customModel }
        });
        renderVpsResult(data, prompt, type);
      } else if (cfg.api === 'animate') {
        var data = await didiPost('/wp-json/didi/v1/animate', {
          prompt: prompt,
          provider: provider,
          frames: 16,
          width: 512,
          height: 512,
          imageUrl: mediaUrl || '',
          model: customModel
        });
        var html = '<div style="width:100%; padding:20px;">'
          + '<h3 style="margin-bottom:10px;">动画任务已提交</h3>'
          + '<p style="font-size:13.5px; color:var(--text-dim); margin-bottom:14px;">指令：' + escapeHtml(prompt) + '</p>'
          + '<div class="card" style="background:var(--bg-soft);">'
          + '<p><b>任务 ID：</b>' + escapeHtml(data.result.taskId || data.taskId || 'N/A') + '</p>'
          + '<p><b>状态：</b><span class="badge accent">' + escapeHtml(data.result.status || 'submitted') + '</span></p>'
          + '<p style="font-size:12px; color:var(--text-faint); margin-top:8px;">AnimateDiff 动画完成后，帧序列或视频 URL 会出现在接口返回中。</p></div>'
          + '</div>';
        output.innerHTML = html;
        toast('动画任务已提交', 'success');
      } else {
        var data = await didiPost('/wp-json/didi/v1/edit', {
          type: type,
          prompt: prompt,
          provider: provider,
          imageUrl: mediaUrl,
          model: customModel
        });
        var urls = data.result && data.result.urls ? data.result.urls : [];
        var html = '<div style="width:100%; padding:20px;">'
          + '<h3 style="margin-bottom:10px;">剪辑结果</h3>'
          + '<p style="font-size:13.5px; color:var(--text-dim); margin-bottom:14px;">指令：' + escapeHtml(prompt) + '</p>';
        if (urls.length){
          urls.forEach(function(u){
            html += '<img src="' + escapeHtml(u) + '" style="max-width:100%; border-radius:10px; margin-bottom:10px;"/>';
          });
        } else if (data.result && data.result.taskId){
          html += '<div class="card" style="background:var(--bg-soft);">'
            + '<p><b>任务 ID：</b>' + escapeHtml(data.result.taskId) + '</p>'
            + '<p><b>状态：</b><span class="badge accent">' + escapeHtml(data.result.status || 'submitted') + '</span></p>'
            + '<p style="font-size:12px; color:var(--text-faint); margin-top:8px;">剪辑完成后，结果 URL 会出现在接口返回中。</p></div>';
        }
        html += '</div>';
        output.innerHTML = html;
        toast('剪辑任务已提交', 'success');
      }
    } catch (err) {
      output.innerHTML = '<div class="placeholder"><div class="big">&#9888;</div><p style="color:var(--danger)">' + escapeHtml(err.message) + '</p></div>';
      toast(err.message, 'error');
    }
    btn.disabled = false;
  });

  function renderVpsResult(data, prompt, t){
    var output = document.getElementById('output');
    var res = data.result || {};
    var urls = res.urls || [];
    var videoUrl = res.videoUrl || res.url || '';
    var taskId = res.taskId || data.taskId || '';
    var html = '<div style="width:100%; padding:20px;">'
      + '<h3 style="margin-bottom:10px;">' + (t === 'audio' ? '音频剪辑结果' : '处理结果') + '</h3>'
      + '<p style="font-size:13.5px; color:var(--text-dim); margin-bottom:14px;">指令：' + escapeHtml(prompt) + '</p>';
    if (videoUrl){
      html += '<video src="' + escapeHtml(videoUrl) + '" controls style="max-width:100%; border-radius:10px;"></video>';
    } else if (urls.length){
      urls.forEach(function(u){
        html += '<img src="' + escapeHtml(u) + '" style="max-width:100%; border-radius:10px; margin-bottom:10px;"/>';
      });
    } else if (taskId){
      html += '<div class="card" style="background:var(--bg-soft);">'
        + '<p><b>任务 ID：</b>' + escapeHtml(taskId) + '</p>'
        + '<p><b>状态：</b><span class="badge accent">' + escapeHtml(res.status || 'submitted') + '</span></p>'
        + '<p style="font-size:12px; color:var(--text-faint); margin-top:8px;">你的 VPS 剪辑服务将异步处理，结果 URL 会出现在返回中。</p></div>';
    } else {
      html += '<div class="card" style="background:var(--bg-soft);"><pre style="white-space:pre-wrap;word-break:break-all;margin:0;font-size:12px;">' + escapeHtml(JSON.stringify(res, null, 2)) + '</pre></div>';
    }
    html += '</div>';
    output.innerHTML = html;
    toast('任务已提交', 'success');
  }

  setTab('video');
  var preset = new URLSearchParams(location.search).get('q');
  if (preset) {
    document.getElementById('prompt').value = preset;
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
      var genBtn = document.getElementById('edit-btn');
      if (genBtn) genBtn.click();
    });
  }
})();
</script>
<?php get_footer(); ?>
