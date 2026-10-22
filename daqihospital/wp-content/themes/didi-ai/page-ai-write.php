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
$desc = '我是写作助手。告诉我你想写什么，我为你完成。';
$desc_side = true;
$welcome = '';
$placeholder = '输入写作需求，如：帮我写一篇新品发布会的新闻稿...（Enter 发送）';
$hide_footnote = true;
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
