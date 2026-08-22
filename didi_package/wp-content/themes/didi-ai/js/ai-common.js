function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[c]));
}

function escapeCode(s) {
  return escapeHtml(s).replace(/`/g, '&#96;');
}

function renderMarkdown(md) {
  if (!md) return '';
  let s = escapeHtml(md);
  s = s.replace(/```(\w*)\n([\s\S]*?)```/g, (_, lang, code) =>
    `<pre><code class="lang-${lang || 'text'}">${code}</code></pre>`);
  s = s.replace(/`([^`\n]+)`/g, (_, c) => `<code>${c}</code>`);
  s = s.replace(/^### (.*)$/gm, '<h3>$1</h3>');
  s = s.replace(/^## (.*)$/gm, '<h2>$1</h2>');
  s = s.replace(/^# (.*)$/gm, '<h1>$1</h1>');
  s = s.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
  s = s.replace(/\*([^*\n]+)\*/g, '<em>$1</em>');
  s = s.replace(/^&gt; (.*)$/gm, '<blockquote>$1</blockquote>');
  s = s.replace(/^- (.*)$/gm, '<li>$1</li>');
  s = s.replace(/(<li>[\s\S]*?<\/li>)(\n)?/g, '<ul>$1</ul>');
  s = s.replace(/<\/ul>\n<ul>/g, '');
  s = s.replace(/\[([^\]]+)\]\((https?:\/\/[^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');
  s = s.replace(/\n{2,}/g, '</p><p>');
  s = s.replace(/\n/g, '<br>');
  return `<p>${s}</p>`;
}

function toast(msg, type = 'info', ms = 3000) {
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.textContent = msg;
  document.body.appendChild(el);
  setTimeout(() => el.remove(), ms);
}

async function didiPost(path, body) {
  const res = await fetch(path, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': (window.didiRestNonce || '')
    },
    body: JSON.stringify(body)
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data.message || `请求失败 (${res.status})`);
    err.status = res.status;
    throw err;
  }
  return data;
}

function didiInitPromptTools(cfg) {
  if (!cfg) {
    document.querySelectorAll('.prompt-row').forEach(function (row) {
      var textarea = row.querySelector('textarea');
      var submitId = row.getAttribute('data-submit');
      var submitBtn = submitId ? document.getElementById(submitId) : null;
      if (!textarea) return;
      var auto = submitBtn ? true : false;
      didiWireTools(row, textarea, submitBtn, auto);
    });
    return;
  }
  const textarea = document.getElementById(cfg.textareaId || 'prompt');
  const submitBtn = document.getElementById(cfg.submitId || 'generate-btn');
  if (!textarea) return;
  const autoSubmit = cfg.autoSubmit !== false;
  const toolsRoot = cfg.toolsRoot ? document.querySelector(cfg.toolsRoot) : document;
  didiWireTools(toolsRoot, textarea, submitBtn, autoSubmit);
}

function didiWireTools(root, textarea, submitBtn, autoSubmit) {
  const run = (fn) => { if (fn) fn(); if (autoSubmit && submitBtn) submitBtn.click(); };

  const tools = root.querySelectorAll('[data-tool]');
  tools.forEach((btn) => {
    const kind = btn.getAttribute('data-tool');
    if (kind === 'voice') {
      btn.addEventListener('click', () => {
        const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SR) { if (window.toast) toast('当前浏览器不支持语音输入', 'error'); return; }
        const recog = new SR();
        recog.lang = 'zh-CN';
        recog.interimResults = false;
        recog.maxAlternatives = 1;
        btn.disabled = true;
        recog.onresult = (e) => {
          let t = '';
          for (let i = 0; i < e.results.length; i++) t += e.results[i][0].transcript;
          textarea.value = t;
          run();
        };
        recog.onerror = (e) => { if (window.toast) toast('语音识别失败：' + (e.error || ''), 'error'); };
        recog.onend = () => { btn.disabled = false; };
        recog.start();
      });
    } else if (kind === 'file') {
      btn.addEventListener('click', () => {
        const inputEl = document.createElement('input');
        inputEl.type = 'file';
        inputEl.accept = '.txt,.md,.csv,.json,.log,.js,.py,.html,.css,.xml,.yml,.yaml';
        inputEl.style.display = 'none';
        document.body.appendChild(inputEl);
        inputEl.addEventListener('change', () => {
          const f = inputEl.files[0];
          if (!f) return;
          const reader = new FileReader();
          reader.onload = () => {
            textarea.value = '请分析以下文件内容（文件：' + f.name + '）：\n\n' + String(reader.result || '').slice(0, 12000);
            run();
          };
          reader.readAsText(f);
          inputEl.remove();
        });
        inputEl.click();
      });
    } else if (kind === 'image') {
      const imgInput = document.createElement('input');
      imgInput.type = 'file';
      imgInput.accept = 'image/*';
      imgInput.style.display = 'none';
      document.body.appendChild(imgInput);
      btn.addEventListener('click', () => imgInput.click());
      imgInput.addEventListener('change', () => {
        const f = imgInput.files[0];
        if (!f) return;
        const fd = new FormData();
        fd.append('file', f);
        btn.disabled = true;
        fetch('/wp-json/didi/v1/upload', {
          method: 'POST',
          headers: { 'X-WP-Nonce': (window.didiRestNonce || '') },
          body: fd,
          credentials: 'same-origin'
        }).then((r) => r.json())
        .then((d) => {
          if (d.ok && d.url) {
            textarea.value = '图片参考：' + d.url + '\n请根据这张图片处理：' + (textarea.value ? '\n' + textarea.value : '');
            run();
          } else {
            throw new Error((d && d.message) || '上传失败');
          }
        }).catch((err) => {
          if (window.toast) toast(err.message, 'error');
        }).finally(() => {
          btn.disabled = false;
          imgInput.value = '';
        });
      });
    }
  });
}

document.addEventListener('DOMContentLoaded', function () {
  didiInitPromptTools();
});
