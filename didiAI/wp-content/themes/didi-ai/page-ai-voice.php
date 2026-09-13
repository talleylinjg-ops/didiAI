<?php
/**
 * AI 语音功能页模板
 * Template Name: AI 语音
 */
get_header();
$section = 'voice';
$title = 'AI 语音';
$icon = '&#127897;';
$color = '#1668dc';
$desc = '语音输入转文字，由国产大模型回答';
$welcome = '你好！点击麦克风按钮说话，或直接输入文字，我会用语音和文字一起回复。';
$placeholder = '点击麦克风说话，或输入文字...';
$footer_text = 'didi AI · AI 语音（腾讯混元 + 浏览器语音识别）';
?>
<script>
window.didi_tts_speak = function(text) {
  if (!('speechSynthesis' in window)) return;
  var clean = String(text || '').replace(/[\n\r]+/g, '。').replace(/[#*`>-]/g, '');
  var u = new SpeechSynthesisUtterance(clean);
  u.lang = 'zh-CN';
  u.rate = 1.0;
  window.speechSynthesis.cancel();
  window.speechSynthesis.speak(u);
};
</script>
<?php
include __DIR__ . '/parts/ai-chat-layout.php';
?>
<?php get_footer(); ?>
