<?php
/**
 * AI 数字人功能页模板
 * Template Name: AI 数字人
 */
get_header();
$section = 'avatar';
$title = '数字人';
$icon = '&#128100;';
$color = '#0aa1a1';
$desc = '我是数字人助手。告诉我口播主题，为你生成完整视频脚本。';
$desc_side = true;
$welcome = '';
$placeholder = '输入数字人视频需求，如：生成一段 30 秒产品介绍的直播口播脚本...（Enter 发送）';
$hide_footnote = true;
$hide_price_table = true;
include __DIR__ . '/parts/ai-chat-layout.php';
get_footer();
