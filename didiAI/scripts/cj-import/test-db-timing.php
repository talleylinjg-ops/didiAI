<?php
$_CJ = array(
    'r2' => array(
        'bucket'   => 'didi-ai-mirror',
        'accountId'=> getenv('CF_ACCOUNT_ID') ?: '2e33f078edc00ade4b25e61526d6f544',
        'token'    => getenv('CF_API_TOKEN') ?: '',
        'publicBase' => 'https://didi-ai-mirror.talley-linjg.workers.dev',
    ),
);
require dirname(__DIR__, 2) . '/wp-load.php';
require __DIR__ . '/import-tasks.php';

echo "GD: ", var_export(function_exists('imagecreatefromstring'), true), "\n";

$wpid = intval($argv[1] ?? 0);
$images = get_post_meta($wpid, '_cj_images', true);
if (!is_array($images) || !$images) { echo "no images\n"; exit(1); }
$images = array_slice($images, 0, 10);

$t0 = microtime(true);
$map = cjBatchUploadToR2($_CJ, $images, $wpid);
$t1 = microtime(true);
echo "cjBatchUploadToR2: " . round($t1 - $t0, 2) . "s，成功 " . count($map) . "/" . count($images) . "\n";

if ($map) {
    $t2 = microtime(true);
    $atts = get_posts(array('post_type' => 'attachment', 'posts_per_page' => -1, 'fields' => 'ids',
        'post_status' => 'inherit',
        'meta_query' => array(array('key' => '_wp_attached_file', 'value' => array_keys($map), 'compare' => 'IN'))));
    $t3 = microtime(true);
    echo "get_posts: " . round($t3 - $t2, 2) . "s，命中 " . count($atts) . " attachment\n";
    foreach ($atts as $aid) {
        $cur = get_post_meta($aid, '_wp_attached_file', true);
        if (isset($map[$cur])) {
            update_post_meta($aid, '_wp_attached_file', $map[$cur]);
            update_post_meta($aid, '_cj_r2_url', $map[$cur]);
        }
    }
    $t4 = microtime(true);
    echo "update attachments: " . round($t4 - $t3, 2) . "s\n";
}
