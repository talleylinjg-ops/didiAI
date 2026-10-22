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
$desc = '我是PPT 助手。告诉我主题，为你生成完整大纲。';
$desc_side = true;
$welcome = '';
$placeholder = '输入 PPT 主题，如：生成一份人工智能发展趋势的 PPT...（Enter 发送）';
$hide_footnote = true;
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
