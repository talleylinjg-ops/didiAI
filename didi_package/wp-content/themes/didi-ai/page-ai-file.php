<?php
/**
 * AI 文件功能页模板
 * Template Name: AI 文件
 */
get_header();
$section = 'llm';
$title = 'AI 文件';
$icon = '&#128193;';
$color = '#1668dc';
$desc = '上传文档，AI 提取并分析内容';
$welcome = '你好！上传一个文本类文件（txt / md / csv / json），我会读取内容并帮你总结、分析或提取要点。';
$placeholder = '文件内容将自动填入，你也可以补充要求...';
$footer_text = 'didi AI · AI 文件（DeepSeek + 本地文件读取）';
include __DIR__ . '/parts/ai-chat-layout.php';
?>
<?php get_footer(); ?>
