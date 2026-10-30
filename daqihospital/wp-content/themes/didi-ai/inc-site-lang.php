<?php
/**
 * 大气AI医院 双语层：域名定语言
 * ai.daqihospital.com → 英文站；其他（ai.daqihospital.net 等）→ 中文站
 * ?lang=en|zh 可临时覆盖（测试用）
 */

function daqi_site_lang() {
  static $lang = null;
  if ($lang !== null) return $lang;
  if (isset($_GET['lang']) && in_array($_GET['lang'], array('zh', 'en'), true)) {
    $lang = $_GET['lang'];
    return $lang;
  }
  $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
  $lang = (strpos($host, 'daqihospital.com') !== false && strpos($host, 'daqihospital.net') === false) ? 'en' : 'zh';
  return $lang;
}

/* 双语文案助手 */
function daqi_t($zh, $en) {
  return daqi_site_lang() === 'en' ? $en : $zh;
}

/* 英文站名称 */
function daqi_site_name() {
  return daqi_site_lang() === 'en' ? 'DaQi AI Hospital' : '大气AI医院';
}

/* html lang 属性（zh-CN / en） */
add_filter('language_attributes', function ($out) {
  $out = daqi_site_lang() === 'en' ? 'lang="en"' : 'lang="zh-CN"';
  return $out;
});

/* hreflang：两站互指（消除跨域名重复内容问题） */
add_action('wp_head', function () {
  if (is_admin() || is_feed()) return;
  $scheme = is_ssl() ? 'https' : 'http';
  $host = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
  // 规范域名：zh 站固定 .net，en 站固定 .com；预览域名下也输出规范 alternate
  $zh = $scheme . '://ai.daqihospital.net' . (isset($_SERVER['REQUEST_URI']) ? esc_url_raw(str_replace(array('&amp;', '&#038;'), '&', $_SERVER['REQUEST_URI'])) : '/');
  $en = $scheme . '://ai.daqihospital.com' . (isset($_SERVER['REQUEST_URI']) ? esc_url_raw(str_replace(array('&amp;', '&#038;'), '&', $_SERVER['REQUEST_URI'])) : '/');
  echo '<link rel="alternate" hreflang="zh" href="' . esc_url($zh) . '">' . "\n";
  echo '<link rel="alternate" hreflang="en" href="' . esc_url($en) . '">' . "\n";
  echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($zh) . '">' . "\n";
}, 1);

/* ===== 内容层：英文站读取 _en_* meta（由翻译脚本写入） ===== */

add_filter('the_title', function ($title, $post_id = 0) {
  if (daqi_site_lang() !== 'en' || !$post_id) return $title;
  $en = get_post_meta($post_id, '_en_title', true);
  return ($en !== '' && $en !== null) ? $en : $title;
}, 10, 2);

add_filter('the_content', function ($content) {
  if (daqi_site_lang() !== 'en') return $content;
  $pid = get_the_ID();
  if (!$pid) return $content;
  $en = get_post_meta($pid, '_en_content', true);
  return ($en !== '' && $en !== null) ? $en : $content;
}, 5);

add_filter('get_the_excerpt', function ($excerpt, $post = null) {
  if (daqi_site_lang() !== 'en') return $excerpt;
  $pid = $post instanceof WP_Post ? $post->ID : (is_numeric($post) ? (int) $post : get_the_ID());
  if (!$pid) return $excerpt;
  $en = get_post_meta($pid, '_en_excerpt', true);
  return ($en !== '' && $en !== null) ? $en : $excerpt;
}, 5, 2);

/* 分类/分区名双语（term meta _en_name） */
function daqi_term_name($name, $term_id) {
  if (daqi_site_lang() !== 'en' || !$term_id) return $name;
  $en = get_term_meta($term_id, '_en_name', true);
  return ($en !== '' && $en !== null) ? $en : $name;
}

/* 英文站可见的站点标题（header/footer/logo） */
add_filter('pre_option_blogname', function ($value) {
  if (daqi_site_lang() === 'en') return 'DaQi AI Hospital';
  return $value;
});

/* 文档标题分隔符下站点名（Rank Math title %sitename%） */
add_filter('document_title_parts', function ($parts) {
  if (daqi_site_lang() === 'en' && isset($parts['site'])) $parts['site'] = 'DaQi AI Hospital';
  if (daqi_site_lang() === 'en' && isset($parts['title'])) {
    if ((is_singular() || is_page()) && !is_front_page()) {
      $t = get_the_title();
      if ($t !== '') $parts['title'] = $t;
    } else {
      $parts['title'] = strtr((string) $parts['title'], array('首页' => 'Home', '大气AI医院' => 'DaQi AI Hospital', '论坛主题' => 'Health Community', '健康社区' => 'Health Community'));
    }
  }
  return $parts;
});

/* 会员档位名双语（didi_ai_memberships / didi_ai_user_quota 输出为中文名） */
function daqi_membership_name($name) {
  if (daqi_site_lang() !== 'en') return $name;
  $map = array('免费会员' => 'Free Member', '月卡会员' => 'Monthly Member', '年卡会员' => 'Yearly Member');
  return isset($map[$name]) ? $map[$name] : $name;
}

/* EN 站点副标题（blogdescription，用于 title 标签与 meta） */
add_filter('pre_option_blogdescription', function ($value) {
  if (daqi_site_lang() === 'en') return 'AI pre-consultation, report reading and online health services';
  return $value;
});

/* forum_topic 标签动态翻译（feed 标题、后台、archive title） */
add_filter('register_post_type_args', function ($args, $post_type) {
  if (daqi_site_lang() !== 'en' || $post_type !== 'forum_topic') return $args;
  $args['labels']['name'] = 'Community Topics';
  $args['labels']['singular_name'] = 'Community Topic';
  $args['labels']['add_new_item'] = 'New Topic';
  return $args;
}, 10, 2);

/* 定价表动态字段双语（unit / model 中文值映射） */
function daqi_pricing_unit($unit) {
  if (daqi_site_lang() !== 'en') return $unit;
  return strtr($unit, array('点数/千token' => 'points / 1K tokens', '点数/次' => 'points / use', '点数/张' => 'points / image', '点数/条' => 'points / clip'));
}
function daqi_pricing_model($model) {
  if (daqi_site_lang() !== 'en') return $model;
  return strtr($model, array('通义千问' => 'Qwen', '智谱 GLM' => 'Zhipu GLM', '豆包' => 'Doubao', '腾讯混元' => 'Tencent Hunyuan', '文心 ERNIE' => 'ERNIE Bot', '智谱 CogView-3-Flash' => 'Zhipu CogView-3-Flash'));
}

/* Rank Math meta / schema / 面包屑双语（EN 站输出整体替换） */
if (function_exists('rank_math')) {
  add_filter('rank_math/frontend/description', function ($desc) {
    if (daqi_site_lang() !== 'en') return $desc;
    return strtr($desc, array(
      '大气AI医院 一站式 AI 工具台' => 'DaQi AI Hospital one-stop AI medical consultation platform',
      'AI 预问诊、报告解读与在线健康服务' => 'AI pre-consultation, report reading and online health services',
      '大气AI医院' => 'DaQi AI Hospital',
    ));
  });
  add_filter('rank_math/frontend/keywords', function ($kw) {
    if (daqi_site_lang() !== 'en') return $kw;
    return 'DaQi AI Hospital,AI hospital,AI consultation,AI doctor online,AI health advice,AI report reading,TCM consultation,eye care consultation,oral care consultation,medical aesthetics,mental health counseling,maternal and child care,GLM,DeepSeek,GPT-4o,Claude,Gemini,free AI consultation';
  });
  add_filter('rank_math/json_ld', function ($ld) {
    if (daqi_site_lang() !== 'en') return $ld;
    array_walk_recursive($ld, function (&$v) {
      if (is_string($v)) {
        $v = strtr($v, array(
          '大气AI医院 是聚合国内与全球最强 AI 模型的一站式在线工具台。' => 'DaQi AI Hospital is a one-stop online AI medical consultation platform powered by leading AI models at home and abroad.',
          '大气AI医院' => 'DaQi AI Hospital',
          '首页' => 'Home',
        ));
      }
    });
    return $ld;
  });
  add_filter('rank_math/frontend/breadcrumb/items', function ($items) {
    if (daqi_site_lang() !== 'en') return $items;
    array_walk_recursive($items, function (&$v) {
      if (is_string($v)) $v = strtr($v, array('首页' => 'Home', '大气AI医院' => 'DaQi AI Hospital'));
    });
    return $items;
  });
  /* Rank Math 标题：EN 时按 _en_title / 通道名重建（%title% 模板可能直读原始 post_title） */
  add_filter('rank_math/frontend/title', function ($title) {
    if (daqi_site_lang() !== 'en') return $title;
    $site = 'DaQi AI Hospital';
    if (is_front_page()) {
      return $site . ' – One-Stop AI Medical Consultation Platform (11 Online Channels)';
    }
    if (is_singular() || is_page()) {
      $t = get_the_title();
      if ($t !== '') return $t . ' – ' . $site;
    }
    return $title;
  });
  /* Rank Math og/twitter 标题里的页面名（经 get_the_title 过滤已双语，这里兜底站点名后缀已由 document_title_parts 处理） */
  add_filter('rank_math/frontend/breadcrumb/home_label', function ($label) {
    if (daqi_site_lang() !== 'en') return $label;
    return 'Home';
  });
}
