<?php
/**
 * AI 代码助手功能页模板
 * Template Name: AI 代码助手
 */
get_header();
$section = 'code';
$title = 'CODE 代码助手';
$icon = '&#60;/&#62;';
$color = '#1668dc';
$desc = '写代码、解释、调试，调用中国大模型 DeepSeek';
$welcome = '你好，我是 CODE 代码助手。可以帮你编写、解释、审查和调试代码。开始吧！';
$placeholder = '描述你的代码需求，例如：用 Python 写一个爬虫抓取网页标题...（Enter 发送，Shift+Enter 换行）';
$footer_text = 'didi AI · CODE 代码助手（DeepSeek）';
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
