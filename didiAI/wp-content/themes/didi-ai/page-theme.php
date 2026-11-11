<?php
/**
 * 主题中心页模板
 * Template Name: 主题中心
 */
get_header();
$groups = array(
  array('写作主题', array(
    array('公众号长文', '/ai-write', '热点解读与深度长文模板'),
    array('公文与通知', '/ai-write', '标准公文格式一键成文'),
    array('营销文案', '/ai-write', '促销、种草与品牌故事模板'),
    array('技术博客', '/ai-write', '教程、复盘与最佳实践模板'),
  )),
  array('演示主题', array(
    array('行业分析 PPT', '/ai-ppt', '市场趋势与竞品对比大纲'),
    array('产品发布 PPT', '/ai-ppt', '产品卖点与发布节奏大纲'),
    array('教学课件 PPT', '/ai-ppt', '课程知识点结构化呈现'),
    array('年终汇报 PPT', '/ai-ppt', '业绩总结与来年规划框架'),
  )),
  array('视觉主题', array(
    array('插画风格', '/ai-image', '扁平、手绘与 3D 插画模板'),
    array('电商海报', '/ai-image', '产品主图与促销海报模板'),
    array('社交封面', '/ai-image', '公众号与视频封面模板'),
    array('LOGO 草案', '/ai-image', '品牌标志概念生成'),
  )),
  array('开发主题', array(
    array('工程脚手架', '/ai-code', '主流框架项目初始化代码'),
    array('API 模块', '/ai-code', 'REST 与接口层代码模板'),
    array('算法实现', '/ai-code', '常用算法与数据结构模板'),
    array('单元测试', '/ai-code', '测试用例生成与覆盖模板'),
  )),
  array('视频主题', array(
    array('产品宣传片', '/ai-video', '产品展示与场景演绎脚本'),
    array('知识科普', '/ai-video', '知识点动画与讲解模板'),
    array('口播视频', '/ai-voice', '语音合成与口播稿模板'),
    array('短视频剪辑', '/ai-edit', '节奏卡点与字幕模板'),
  )),
  array('社区主题', array(
    array('经验分享', '/forum', '论坛经验与踩坑交流板块'),
    array('作品展示', '/forum', '用 AI 工具完成的作品展示'),
    array('求助问答', '/forum', '使用问题互助解答'),
    array('功能建议', '/forum', '产品改进与需求反馈'),
  )),
);
?>
<main class="page-simple">
  <section class="ps-hero">
    <h1>主题中心 - 场景化模板一站直达</h1>
    <p class="ps-lead">按写作、演示、视觉、开发、视频与社区六大方向整理的 AI 创作主题，点击即可进入对应工具，套用模板快速产出。</p>
    <div class="ps-badges"><span>写作</span><span>演示</span><span>视觉</span><span>开发</span><span>视频</span><span>社区</span></div>
  </section>
  <?php foreach ($groups as $g) { ?>
  <section class="ps-section">
    <h2><?php echo esc_html($g[0]); ?></h2>
    <div class="ps-grid ps-grid-4">
      <?php foreach ($g[1] as $item) { ?>
      <a class="ps-card ps-link" href="<?php echo esc_url(home_url($item[1])); ?>">
        <h3><?php echo esc_html($item[0]); ?></h3><p><?php echo esc_html($item[2]); ?></p>
      </a>
      <?php } ?>
    </div>
  </section>
  <?php } ?>
</main>
<script type="application/ld+json">
<?php
$items = array();
$i = 1;
foreach ($groups as $g) {
  foreach ($g[1] as $item) {
    $items[] = array('@type' => 'ListItem', 'position' => $i++, 'name' => $g[0] . ' - ' . $item[0], 'url' => home_url($item[1]));
  }
}
echo wp_json_encode(array('@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => $items), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
</script>
<?php get_footer();
