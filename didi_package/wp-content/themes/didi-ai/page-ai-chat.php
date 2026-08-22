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
$desc = '与国产大模型自由对话：问答、写作、翻译、总结';
$welcome = '你好！我是 didi AI 的对话助手，接入中国大模型。有什么可以帮你的？';
$placeholder = '输入你的问题...（Enter 发送，Shift+Enter 换行）';
$footer_text = 'didi AI · AI 对话（DeepSeek）';
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
