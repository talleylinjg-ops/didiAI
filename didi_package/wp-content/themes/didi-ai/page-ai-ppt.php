<?php
/**
 * AI PPT 功能页模板
 * Template Name: AI PPT
 */
get_header();
$section = 'ppt';
$title = 'PPT';
$icon = '&#128209;';
$color = '#7c3aed';
$desc = '一键生成 PPT 大纲与内容，可导出为 Markdown';
$welcome = '你好！我是 didi AI 的 PPT 助手。告诉我主题，为你生成完整大纲。';
$placeholder = '输入 PPT 主题，如：生成一份人工智能发展趋势的 PPT...（Enter 发送）';
$footer_text = 'didi AI · AI PPT（DeepSeek）';
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
