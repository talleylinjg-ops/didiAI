<?php
/**
 * AI 代码助手功能页模板（项目工作台：创建项目 → 下发任务 → 自动生成/续写多文件）
 * Template Name: AI 代码助手
 */
get_header();
$icon = '&#60;/&#62;';
$color = '#1668dc';
$welcome = '你好，我是 CODE 代码助手。新建一个项目，然后描述你想要的功能，我会帮你规划并自动编写多文件项目代码。';
$placeholder = '描述任务，例如：帮我创建一个 Python 待办事项网页应用，含增删改查与 SQLite 存储…（Enter 发送，Shift+Enter 换行）';
?>
<div class="ai-page codex-page">
  <div class="page-header">
    <div class="codex-hd-title">
      <h1><span style="color:<?php echo esc_attr($color); ?>;"><?php echo $icon; ?></span> CODE 代码助手</h1>
    </div>
    <div class="codex-hd-model">
      <span class="hd-label">选择模型</span>
      <select id="codexModel" class="ai-model-select" title="选择模型">
        <optgroup label="国内模型">
          <option value="qwen3.8-max" selected>通义千问 Qwen3.8-Max（国内最强）</option>
          <option value="deepseek-chat">DeepSeek</option>
          <option value="qwen-plus">通义千问</option>
          <option value="glm-4">智谱 GLM</option>
          <option value="doubao-pro">豆包</option>
          <option value="hunyuan-turbo">腾讯混元</option>
          <option value="ernie-4.0-turbo">文心 ERNIE</option>
        </optgroup>
        <optgroup label="海外模型">
          <option value="claude-fable-5.1">Claude Fable 5.1（全球最强）</option>
          <option value="gpt-6-astra">GPT-6 Astra</option>
          <option value="gpt-4o">GPT-4o</option>
          <option value="claude-3-5-sonnet">Claude 3.5 Sonnet</option>
          <option value="gemini-1.5-pro">Gemini 1.5 Pro</option>
        </optgroup>
        <optgroup label="定制模型">
          <option value="__custom__">自定义模型</option>
        </optgroup>
      </select>
      <input type="text" id="codexModelCustom" class="input" placeholder="输入自定义模型名称" style="display:none;margin-left:8px;min-width:150px;max-width:200px;flex-shrink:0;padding:8px 10px;border:1px solid #e5e6eb;border-radius:10px;font-size:13px;outline:none;">
    </div>
    <div class="codex-new">
      <input type="text" id="newProjName" class="input" placeholder="项目名称，如：待办应用" maxlength="40">
      <button class="btn ai-btn" id="newProjBtn">新建项目</button>
    </div>
  </div>

  <div class="codex-layout">
    <!-- 左：项目列表 -->
    <aside class="codex-pane projects">
      <div class="codex-pane-head">
        <span>我的项目</span>
        <span class="codex-hint" id="projCount"></span>
      </div>
      <div class="codex-project-list" id="projectList">
        <div class="empty">加载中…</div>
      </div>
    </aside>
    <div class="codex-resizer"></div>

    <!-- 中：任务对话 -->
    <section class="codex-pane chat">
      <div class="codex-pane-head" id="chatHead">
        <span>任务对话</span>
        <span class="codex-hint">选择左侧项目后开始</span>
      </div>
      <div class="codex-msgs" id="codexMsgs">
        <div class="msg assistant">
          <div class="avatar" style="color:#1668dc;font-weight:700;">&#60;/&#62;</div>
          <div class="bubble"><?php echo esc_html($welcome); ?></div>
        </div>
      </div>
      <div class="codex-chat-input">
        <div class="row1">
          <textarea id="codexPrompt" placeholder="<?php echo esc_attr($placeholder); ?>" rows="2"></textarea>
          <div class="send-col">
            <div class="codex-tools" id="codexTools">
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
            <button class="btn ai-btn send-btn" id="codexSend">didi</button>
          </div>
        </div>
      </div>
    </section>
    <div class="codex-resizer"></div>

    <!-- 右：项目文件 -->
    <aside class="codex-pane files">
      <div class="codex-pane-head">
        <span>项目文件</span>
      </div>
      <div class="codex-files-body">
        <div class="codex-file-tree" id="fileTree">
          <div class="empty">尚未选择项目<br>创建或选择项目后在此生成文件</div>
        </div>
      </div>
    </aside>
    <div class="codex-resizer"></div>

    <!-- 最右：代码编辑器 -->
    <aside class="codex-pane editor">
      <div class="codex-pane-head">
        <span>代码编辑器</span>
      </div>
      <div class="codex-editor">
        <div class="codex-editor-empty" id="editorEmpty">
          <div style="font-size:40px;">&#128193;</div>
          <div>点击左侧文件查看 / 编辑代码</div>
        </div>
        <div style="display:none;flex:1;flex-direction:column;min-width:0;" id="editorWrap">
          <div class="codex-editor-head">
            <span class="f-title" id="editTitle"></span>
            <button type="button" id="copyBtn">复制</button>
            <button type="button" id="downloadBtn">下载</button>
            <button type="button" id="saveBtn">保存</button>
          </div>
          <textarea id="codexEditor" spellcheck="false"></textarea>
          <div class="codex-editbar">
            <span id="editLang"></span>
            <span class="save-hint" id="saveHint"></span>
            <span class="edit-actions"></span>
          </div>
        </div>
      </div>
    </aside>
  </div>
</div>
<script>
(function () {
  var REST = <?php echo wp_json_encode(esc_url_raw(rest_url('didi/v1/codex/'))); ?>;
  var cur = null; // {slug,name}
  var curFile = null; // {path, content}
  var editorDirty = false;

  function $id(s) { return document.getElementById(s); }
  var listEl = $id('projectList');
  var msgsEl = $id('codexMsgs');
  var treeEl = $id('fileTree');
  var promptEl = $id('codexPrompt');
  var sendBtn = $id('codexSend');
  var modelSel = $id('codexModel');
  var modelSelCustom = $id('codexModelCustom');
  function syncModelCustom() { modelSelCustom.style.display = modelSel.value === '__custom__' ? '' : 'none'; }
  modelSel.addEventListener('change', syncModelCustom);
  if (typeof didiRememberModel === 'function') {
    didiRememberModel('code', modelSel, { customValue: '__custom__', customInput: modelSelCustom, onChange: syncModelCustom });
  }
  var editor = $id('codexEditor');
  var editorWrap = $id('editorWrap');
  var editorEmpty = $id('editorEmpty');
  var editTitle = $id('editTitle');
  var editLang = $id('editLang');
  var saveHint = $id('saveHint');
  var saveBtn = $id('saveBtn');
  var chatHead = $id('chatHead');

  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]); }); }

  function api(method, path, body) {
    var opt = { method: method, headers: { 'X-WP-Nonce': (window.didiRestNonce || '') } };
    if (body !== undefined) { opt.headers['Content-Type'] = 'application/json'; opt.body = JSON.stringify(body); }
    return fetch(REST + path, opt).then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { if (!r.ok) { var e = new Error((d && (d.message || d.code)) || ('请求失败 (' + r.status + ')')); e.data = d; throw e; } return d; }); });
  }

  function updateNavBalance(v) {
    var el = $id('nav-balance');
    if (el && typeof v === 'number') el.textContent = v.toLocaleString() + ' 点';
  }

  function loadProjects() {
    listEl.innerHTML = '<div class="empty">加载中…</div>';
    return api('GET', 'projects').then(function (d) {
      var list = (d && d.projects) || [];
      $id('projCount').textContent = list.length ? list.length + ' 个' : '';
      if (!list.length) {
        listEl.innerHTML = '<div class="empty">还没有项目<br>在上方输入名称并点击「新建项目」开始</div>';
        return;
      }
      listEl.innerHTML = '';
      list.forEach(function (p) {
        var el = document.createElement('button');
        el.type = 'button';
        el.className = 'codex-project' + (cur && cur.slug === p.slug ? ' active' : '');
        el.dataset.slug = p.slug;
        el.innerHTML = '<span class="p-name">' + esc(p.name)
          + '<span class="p-del" role="button" title="删除项目">&#10005;</span></span>'
          + (p.desc ? '<span class="p-desc">' + esc(p.desc) + '</span>' : '')
          + '<span class="p-meta"><span>' + p.files + ' 个文件</span><span>' + (p.updated || '').replace('T', ' ').slice(0, 16) + '</span></span>';
        el.addEventListener('click', function (e) {
          if (e.target.classList.contains('p-del')) {
            e.stopPropagation();
            if (confirm('确定删除项目「' + p.name + '」？该操作不可恢复。')) {
              api('DELETE', 'project?slug=' + encodeURIComponent(p.slug)).then(function () {
                if (cur && cur.slug === p.slug) { cur = null; renderWelcome(); resetFilePanel(); }
                loadProjects();
              }).catch(function (err) { if (window.toast) toast(err.message, 'error'); });
            }
            return;
          }
          selectProject(p.slug, p.name);
        });
        listEl.appendChild(el);
      });
    }).catch(function (err) {
      listEl.innerHTML = '<div class="empty">加载失败</div>';
      if (window.toast) toast(err.message, 'error');
    });
  }

  function renderWelcome() {
    chatHead.innerHTML = '<span>任务对话</span><span class="codex-hint">选择左侧项目后开始</span>';
    msgsEl.innerHTML = '<div class="msg assistant"><div class="avatar" style="color:#1668dc;font-weight:700;">&#60;/&#62;</div><div class="bubble"><?php echo esc_js($welcome); ?></div></div>';
  }

  function selectProject(slug, name) {
    cur = { slug: slug, name: name };
    chatHead.innerHTML = '<span>任务对话</span><span class="codex-hint" style="color:#1668dc;font-weight:600;">' + esc(name) + '</span>';
    // 高亮当前项目（基于 data-slug 精确匹配）
    Array.prototype.forEach.call(listEl.querySelectorAll('.codex-project'), function (el) {
      el.classList.toggle('active', el.dataset.slug === slug);
    });
    loadProjectDetail();
  }

  function addMsg(cls, icon, html) {
    var wrap = document.createElement('div');
    wrap.className = 'msg ' + cls;
    wrap.innerHTML = '<div class="avatar"' + (cls === 'user' ? '' : ' style="color:#1668dc;"') + '>' + (cls === 'user' ? '我' : icon) + '</div><div class="bubble">' + html + '</div>';
    msgsEl.appendChild(wrap);
    msgsEl.scrollTop = msgsEl.scrollHeight;
    return wrap;
  }

  function loadProjectDetail() {
    msgsEl.innerHTML = '<div class="msg assistant"><div class="avatar" style="color:#1668dc;font-weight:700;">&#60;/&#62;</div><div class="bubble">加载项目…</div></div>';
    return api('GET', 'project?slug=' + encodeURIComponent(cur.slug)).then(function (d) {
      msgsEl.innerHTML = '';
      var hist = (d && d.history) || [];
      if (!hist.length) {
        addMsg('assistant', '&#60;/&#62;', esc('项目「' + (d.name || cur.name) + '」已创建。在下方输入任务，我将自动编写代码。'));
      } else {
        hist.forEach(function (h) {
          if (h.role === 'user') {
            addMsg('user', '我', esc(h.content));
          } else {
            var html = '';
            if (h.content) html += esc(h.content);
            addMsg('assistant', '&#60;/&#62;', html);
          }
        });
      }
      renderFileTree((d && d.files) || []);
      chatHead.innerHTML = '<span>任务对话</span><span class="codex-hint" style="color:#1668dc;font-weight:600;">' + esc(d.name || cur.name) + '</span>';
    }).catch(function (err) {
      msgsEl.innerHTML = '';
      addMsg('assistant', '&#60;/&#62;', esc('加载失败：' + err.message));
    });
  }

  function renderFileTree(files) {
    treeEl.innerHTML = '';
    if (!files.length) {
      treeEl.innerHTML = '<div class="empty">暂无文件<br>输入任务后自动生成</div>';
      resetFilePanel();
      return;
    }
    files.forEach(function (f) {
      var lang = (f.path.indexOf('.') > -1 ? f.path.split('.').pop() : '').toLowerCase();
      var lname = lang || 'txt';
      var ico = '&#128196;';
      var el = document.createElement('div');
      el.className = 'codex-fitem';
      el.dataset.path = f.path;
      el.innerHTML = '<span class="f-ico">' + ico + '</span><span class="f-path">' + esc(f.path) + '</span><span class="f-lang">' + esc(lname) + '</span>';
      el.addEventListener('click', function () {
        selectFile(f.path);
      });
      treeEl.appendChild(el);
    });
  }

  function resetFilePanel() {
    editorWrap.style.display = 'none';
    editorEmpty.style.display = 'flex';
    curFile = null;
    editorDirty = false;
    saveHint.textContent = '';
  }

  function selectFile(path) {
    if (editorDirty && !confirm('当前文件有未保存的修改，放弃并切换文件？')) return;
    Array.prototype.forEach.call(treeEl.querySelectorAll('.codex-fitem'), function (el) {
      el.classList.toggle('active', el.dataset.path === path);
    });
    api('GET', 'file?slug=' + encodeURIComponent(cur.slug) + '&path=' + encodeURIComponent(path)).then(function (d) {
      curFile = { path: path, content: d.content || '' };
      editor.value = d.content || '';
      editTitle.textContent = path;
      editLang.textContent = '语言：' + (d.language || 'plaintext');
      saveHint.textContent = '';
      editorDirty = false;
      editorEmpty.style.display = 'none';
      editorWrap.style.display = 'flex';
    }).catch(function (err) { if (window.toast) toast(err.message, 'error'); });
  }

  function saveFile() {
    if (!curFile) return;
    var btnLabel = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.textContent = '保存中…';
    api('POST', 'file', { slug: cur.slug, path: curFile.path, content: editor.value }).then(function () {
      curFile.content = editor.value;
      editorDirty = false;
      saveHint.textContent = '已保存 ' + new Date().toLocaleTimeString();
      if (window.toast) toast('文件已保存', 'success');
    }).catch(function (err) {
      if (window.toast) toast(err.message, 'error');
    }).finally(function () {
      saveBtn.disabled = false;
      saveBtn.textContent = btnLabel;
    });
  }

  function sendTask() {
    var text = promptEl.value.trim();
    if (!cur) { if (window.toast) toast('请先选择或新建项目', 'error'); return; }
    if (!text) return;
    var model = modelSel.value;
    if (model === '__custom__') {
      model = (modelSelCustom.value || '').trim();
      if (!model) { if (window.toast) toast('请输入自定义模型名称', 'error'); modelSelCustom.focus(); return; }
    }
    promptEl.value = '';
    if (promptEl.dispatchEvent) promptEl.dispatchEvent(new Event('input'));
    addMsg('user', '我', esc(text));
    var loading = addMsg('assistant', '&#60;/&#62;', '<span class="codex-loading"><span class="spinner"></span> 正在分析并编写代码…</span>');
    sendBtn.disabled = true;
    sendBtn.textContent = '生成中…';
    api('POST', 'task', { slug: cur.slug, message: text, model: model }).then(function (d) {
      loading.remove();
      var html = '';
      if (d.plan) html += '<div class="codex-plan">' + esc(d.plan) + '</div>';
      if (d.summary) html += esc(d.summary);
      var written = (d && d.written) || [];
      if (written.length) {
        html += '<br>' + written.map(function (w) { return '<span class="codex-file-chip">' + esc(w) + '</span>'; }).join('');
      }
      if (typeof d.cost === 'number') html += '<span class="codex-cost">本次消耗 ' + d.cost + ' 点</span>';
      addMsg('assistant', '&#60;/&#62;', html);
      renderFileTree((d && d.files) || []);
      if (typeof d.balance === 'number') updateNavBalance(d.balance);
      loadProjects();
    }).catch(function (err) {
      loading.remove();
      var msg = err.message || '任务失败';
      if (err.data && err.data.error && err.data.message) msg = err.data.message;
      addMsg('assistant', '&#60;/&#62;', esc('任务执行失败：' + msg));
    }).finally(function () {
      sendBtn.disabled = false;
      sendBtn.textContent = 'didi';
    });
  }

  // 新建项目
  $id('newProjBtn').addEventListener('click', function () {
    var name = $id('newProjName').value.trim();
    if (!name) { if (window.toast) toast('请填写项目名称', 'error'); return; }
    $id('newProjBtn').disabled = true;
    $id('newProjBtn').textContent = '创建中…';
    api('POST', 'projects', { name: name, desc: '' }).then(function (d) {
      $id('newProjName').value = '';
      cur = { slug: d.slug, name: d.name };
      loadProjects();
      selectProject(d.slug, d.name);
      if (window.toast) toast('项目「' + d.name + '」已创建', 'success');
    }).catch(function (err) { if (window.toast) toast(err.message, 'error'); }).finally(function () {
      $id('newProjBtn').disabled = false;
      $id('newProjBtn').textContent = '新建项目';
    });
  });

  // 事件绑定
  sendBtn.addEventListener('click', sendTask);
  promptEl.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendTask(); }
  });
  saveBtn.addEventListener('click', saveFile);
  $id('copyBtn').addEventListener('click', function () {
    if (!curFile) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(editor.value).then(function () { if (window.toast) toast('已复制到剪贴板', 'success'); }, function () { if (window.toast) toast('复制失败', 'error'); });
    } else {
      editor.select(); document.execCommand('copy'); if (window.toast) toast('已复制到剪贴板', 'success');
    }
  });
  $id('downloadBtn').addEventListener('click', function () {
    if (!curFile) return;
    var blob = new Blob([editor.value], { type: 'text/plain;charset=utf-8' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = curFile.path.split('/').pop();
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(function () { URL.revokeObjectURL(url); }, 800);
  });
  editor.addEventListener('input', function () { editorDirty = true; });

  // 模型下拉事件留空（模型名由后端直传）

  // 工具图标接线（voice/file/image，复用 ai-common.js）
  if (typeof didiWireTools === 'function') {
    didiWireTools(document.getElementById('codexTools'), promptEl, sendBtn, false);
  }

  // 分隔条拖拽调整列宽（我的项目 / 任务对话 / 项目文件 / 代码编辑器）
  function initCodexResizers() {
    var layout = document.querySelector('.codex-layout');
    if (!layout) return;
    var resizers = layout.querySelectorAll('.codex-resizer');
    Array.prototype.forEach.call(resizers, function (rz) {
      rz.addEventListener('mousedown', function (e) {
        e.preventDefault();
        var paneA = rz.previousElementSibling;
        var paneB = rz.nextElementSibling;
        if (!paneA || !paneB || !paneA.classList.contains('codex-pane') || !paneB.classList.contains('codex-pane')) return;
        var startX = e.clientX;
        var startWA = paneA.offsetWidth;
        var startWB = paneB.offsetWidth;
        var minW = 130;
        rz.classList.add('active');
        document.body.style.userSelect = 'none';
        document.body.style.cursor = 'col-resize';
        function onMove(ev) {
          var dx = ev.clientX - startX;
          var total = startWA + startWB;
          var wa = Math.max(minW, Math.min(total - minW, startWA + dx));
          var wb = total - wa;
          paneA.style.flex = '0 0 ' + wa + 'px';
          paneB.style.flex = '0 0 ' + wb + 'px';
        }
        function onUp() {
          rz.classList.remove('active');
          document.body.style.userSelect = '';
          document.body.style.cursor = '';
          document.removeEventListener('mousemove', onMove);
          document.removeEventListener('mouseup', onUp);
        }
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
      });
    });
  }

  // 启动
  if (window.toast) window.toast = window.toast;
  initCodexResizers();
  var bootP = new URLSearchParams(location.search).get('p');
  var bootQ = new URLSearchParams(location.search).get('q');
  loadProjects().then(function () {
    if (bootP) {
      var hit = Array.prototype.filter.call(listEl.querySelectorAll('.codex-project'), function (el) { return el.dataset.slug === bootP; });
      if (hit.length) {
        var nm = String((hit[0].querySelector('.p-name').textContent || '')).replace(/\u2715/g, '').trim() || bootP;
        selectProject(bootP, nm);
        if (bootQ) { promptEl.value = bootQ; promptEl.focus(); }
        return;
      }
    }
    if (bootQ) { promptEl.value = bootQ; promptEl.focus(); }
  });
  if (window.didiRestNonce) {
    fetch(<?php echo wp_json_encode(esc_url_raw(rest_url('didi/v1/quota'))); ?>, { headers: { 'X-WP-Nonce': (window.didiRestNonce || '') } })
      .then(function (r) { return r.json(); })
      .then(function (q) { if (q && typeof q.balance === 'number') updateNavBalance(q.balance); }).catch(function () {});
  }
})();
</script>
<?php
get_footer();
