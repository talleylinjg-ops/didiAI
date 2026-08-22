<?php
/**
 * AI 工作台功能页模板
 * Template Name: AI 工作台
 */
get_header();
$section = 'work';
$title = 'WORK 工作台';
$icon = '&#9881;';
$color = '#34c77b';
$desc = '通用工作台：文档撰写、数据整理、方案策划，调用中国大模型 DeepSeek';
$welcome = '你好，我是 WORK 工作台助手。可以帮你写文档、做总结、列方案、整理数据。开始你的任务吧！';
$placeholder = '描述你的任务，例如：帮我写一份关于 AI 落地的商业计划书大纲...';
$footer_text = 'didi AI · WORK 工作台（DeepSeek）';
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
