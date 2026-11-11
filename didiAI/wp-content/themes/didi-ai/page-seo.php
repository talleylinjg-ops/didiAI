<?php
/**
 * SEO 优化页模板
 * Template Name: SEO 优化
 */
get_header();
$tools = array(
  array('AI 写作', '/ai-write', '围绕关键词产生长文与营销文案，覆盖搜索意图的原创内容。'),
  array('AI 提问', '/ai-chat', '挖掘长尾关键词、生成标题与描述，优化点击率。'),
  array('AI 图片生成', '/ai-image', '为文章生成配图与替代文本，提升内容完整度。'),
  array('AI 文件', '/ai-file', '分析已有文档，批量改写为搜索引擎友好的结构。'),
);
$faqs = array(
  array('didi AI 站点的 SEO 基础设施有哪些？', 'Rank Math 全站优化（sitemap_index.xml、元信息、面包屑）、robots.txt 精细化规则、JSON-LD 结构化数据、语义化 HTML 模板与移动端适配。'),
  array('sitemap 在哪里？', '访问 /sitemap_index.xml 可获取全部子 sitemap，包含文章、页面、产品与论坛主题，robots.txt 已同步声明。'),
  array('AI 生成内容会影响 SEO 吗？', '搜索引擎关注内容质量与实用性。用 AI 提效、由人审核修订、持续更新真实有用的内容，是当前被验证的可持续策略。'),
  array('新页面多久被收录？', '取决于站点权重与抓取频率。保持 sitemap 更新、内链清晰、页面秒开（本站已接入整页缓存与 CDN），有助于加快收录。'),
);
?>
<main class="page-simple">
  <section class="ps-hero">
    <h1>SEO 优化 - 站点基础设施与实践指南</h1>
    <p class="ps-lead">didi AI 基于 Rank Math 与自研模板体系，实现结构化数据、站点地图、整页缓存与语义化页面的一体化 SEO 底座，并提供 AI 工具持续产出优质内容。</p>
    <div class="ps-badges"><span>sitemap_index.xml</span><span>JSON-LD</span><span>Rank Math</span><span>整页缓存</span></div>
  </section>
  <section class="ps-section">
    <h2>本站 SEO 基础设施</h2>
    <div class="ps-grid">
      <div class="ps-card"><h3>站点地图</h3><p>Rank Math 自动生成 sitemap_index.xml，覆盖文章、页面、产品与论坛主题，随内容发布实时更新。</p></div>
      <div class="ps-card"><h3>结构化数据</h3><p>工具页与内容页输出 JSON-LD（WebPage / FAQPage / ItemList），帮助搜索引擎精准理解页面类型。</p></div>
      <div class="ps-card"><h3>性能优化</h3><p>整页缓存 + 边缘 CDN + 图片就近分发，页面秒开，Core Web Vitals 友好。</p></div>
      <div class="ps-card"><h3>语义化与内链</h3><p>统一的标题层级、面包屑与相关工具推荐，站内权重流动顺畅。</p></div>
    </div>
  </section>
  <section class="ps-section">
    <h2>用 didi AI 工具提效</h2>
    <div class="ps-grid">
      <?php foreach ($tools as $t) { ?>
      <a class="ps-card ps-link" href="<?php echo esc_url(home_url($t[1])); ?>">
        <h3><?php echo esc_html($t[0]); ?></h3><p><?php echo esc_html($t[2]); ?></p>
      </a>
      <?php } ?>
    </div>
  </section>
  <section class="ps-section">
    <h2>常见问题</h2>
    <div class="ps-faq">
      <?php foreach ($faqs as $f) { ?>
      <details><summary><?php echo esc_html($f[0]); ?></summary><p><?php echo esc_html($f[1]); ?></p></details>
      <?php } ?>
    </div>
  </section>
</main>
<script type="application/ld+json">
<?php
$schema = array('@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array());
foreach ($faqs as $f) {
  $schema['mainEntity'][] = array('@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array('@type' => 'Answer', 'text' => $f[1]));
}
echo wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
</script>
<?php get_footer();
