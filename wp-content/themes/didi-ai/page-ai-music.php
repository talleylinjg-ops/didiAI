<?php
/**
 * AI 音乐功能页模板
 * Template Name: AI 音乐
 */
get_header();
$section = 'music';
$title = '音乐';
$icon = '&#127925;';
$color = '#1db954';
$desc = '歌词创作、旋律与编曲建议';
$welcome = '你好！我是 didi AI 的音乐助手。告诉我你的需求，为你创作歌词与音乐建议。';
$placeholder = '输入音乐需求，如：写一首关于夏天的流行歌词...（Enter 发送）';
$footer_text = 'didi AI · AI 音乐（DeepSeek）';
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
