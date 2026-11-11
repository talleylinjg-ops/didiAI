<?php
/**
 * GEO 优化页模板
 * Template Name: GEO 优化
 */
get_header();
$tools = array(
  array('AI 写作', '/ai-write', '生成长文、公文与营销文案，为 GEO 与 SEO 持续产出优质内容。'),
  array('AI 提问', '/ai-chat', '多模型对话，快速生成 FAQ、摘要与结构化答案。'),
  array('AI 文件', '/ai-file', '上传站点内容文档，AI 提取要点并改写为 AI 友好格式。'),
  array('AI PPT', '/ai-ppt', '一键生成演示文稿，沉淀可复用的内容主题。'),
);
$faqs = array(
  array('什么是 GEO？', 'GEO（Generative Engine Optimization，生成式引擎优化）是让内容更容易被 ChatGPT、Perplexity、豆包等 AI 引擎理解、引用和推荐的优化方法。'),
  array('didi AI 如何实践 GEO？', '本站提供 llms.txt 站点摘要、面向 GPTBot / ClaudeBot / PerplexityBot 等 AI 爬虫的抓取白名单、JSON-LD 结构化数据，以及高频更新的原创工具内容。'),
  array('GEO 与 SEO 的关系？', 'SEO 面向传统搜索引擎排名，GEO 面向 AI 生成式答案的引用。两者共享高质量内容基础，GEO 更强调清晰的语义结构、事实准确与可引用性。'),
  array('如何开始优化我的站点？', '第一步添加 llms.txt 与 AI 爬虫白名单；第二步为页面补充结构化数据；第三步持续产出回答真实问题的原创内容，可借助本站 AI 写作工具提效。'),
);
?>
<main class="page-simple">
  <section class="ps-hero">
    <h1>GEO 优化 - 让 AI 引擎更愿意引用你</h1>
    <p class="ps-lead">GEO（生成式引擎优化）帮助你的内容在 ChatGPT、Perplexity、豆包等 AI 回答中获得引用与推荐。didi AI 的工具链与站点实践可直接复用。</p>
    <div class="ps-badges"><span>llms.txt</span><span>AI 爬虫白名单</span><span>JSON-LD 结构化数据</span><span>原创内容工厂</span></div>
  </section>
  <section class="ps-section">
    <h2>didi AI 的 GEO 实践清单</h2>
    <div class="ps-grid">
      <div class="ps-card"><h3>llms.txt 站点摘要</h3><p>面向生成式引擎输出结构化站点说明与工具清单，AI 可第一时间理解站点能力。</p></div>
      <div class="ps-card"><h3>AI 爬虫欢迎策略</h3><p>robots.txt 明确放行 GPTBot、OAI-SearchBot、ClaudeBot、PerplexityBot 等，并对高频爬虫设置抓取延迟。</p></div>
      <div class="ps-card"><h3>结构化数据</h3><p>全站输出 JSON-LD（ItemList / FAQPage / WebPage），让 AI 与搜索引擎都能准确解析页面语义。</p></div>
      <div class="ps-card"><h3>可引用的内容结构</h3><p>工具页采用"是什么 / 怎么用 / 常见问题"结构，段落短、事实清楚，便于 AI 直接摘录。</p></div>
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
