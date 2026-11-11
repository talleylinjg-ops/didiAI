<?php
require '/workspace/didiAI/wp-load.php';
$pages = array(
  'geo'   => array('GEO 优化', 'page-geo.php'),
  'seo'   => array('SEO 优化', 'page-seo.php'),
  'theme' => array('主题中心', 'page-theme.php'),
);
foreach ($pages as $slug => $p) {
  $exists = get_page_by_path($slug);
  if ($exists && $exists->post_status === 'publish') {
    update_post_meta($exists->ID, '_wp_page_template', $p[1]);
    echo "exists: $slug #{$exists->ID} (template set)\n";
    continue;
  }
  $id = wp_insert_post(array(
    'post_title'   => $p[0],
    'post_name'    => $slug,
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_content' => '',
  ));
  if (is_wp_error($id)) { echo "FAIL $slug: " . $id->get_error_message() . "\n"; continue; }
  update_post_meta($id, '_wp_page_template', $p[1]);
  echo "created: $slug #$id\n";
}
