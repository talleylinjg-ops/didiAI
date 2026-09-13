<?php
/**
 * AI 对话功能页模板
 * Template Name: AI 对话
 */
get_header();
$section = 'llm';
$title = '对话';
$icon = '&#128172;';
$color = '#1668dc';
$desc = '我是对话助手，接入大模型。有什么可以帮你的？';
$desc_side = true;
$welcome = '';
$placeholder = '输入你的问题...（Enter 发送，Shift+Enter 换行）';
$hide_footnote = true;
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
