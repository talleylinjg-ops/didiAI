<?php
/**
 * AI 音频功能页模板
 * Template Name: AI 音频
 */
get_header();
$section = 'music';
$title = '音频';
$icon = '&#127925;';
$color = '#1db954';
$desc = '输入文字，免费合成语音（didi Media）；也可切换智谱模型创作歌词与文案。';
$desc_side = true;
$welcome = '';
$placeholder = '输入要转成语音的文字，如：欢迎来到 didi AI...（Enter 发送）';
$hide_footnote = true;
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
