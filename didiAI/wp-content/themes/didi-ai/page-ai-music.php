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
$desc = '歌词创作、旋律与编曲建议，告诉我你的需求，为你创作歌词与音频建议。';
$desc_side = true;
$welcome = '';
$placeholder = '输入音频需求，如：写一首关于夏天的流行歌词...（Enter 发送）';
$hide_footnote = true;
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
