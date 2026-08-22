<?php
/**
 * AI 写作功能页模板
 * Template Name: AI 写作
 */
get_header();
$section = 'write';
$title = '写作';
$icon = '&#9997;';
$color = '#e8663c';
$desc = '各类文章创作：新闻、文案、散文、小说、公文';
$welcome = '你好！我是 didi AI 的写作助手。告诉我你想写什么，我为你完成。';
$placeholder = '输入写作需求，如：帮我写一篇新品发布会的新闻稿...（Enter 发送）';
$footer_text = 'didi AI · AI 写作（DeepSeek）';
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
