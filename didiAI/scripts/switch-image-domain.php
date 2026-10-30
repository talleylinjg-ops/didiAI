<?php
/**
 * didiAI 图片域名一键切换：把 R2 图片 URL 的域名从 workers.dev 换成自定义域名
 *
 * 背景：workers.dev 在大陆被间歇性 SNI 干扰，绑定自定义域名（R2 自定义域或 Worker route）后
 *       跑一次本脚本即可全站切换，无需重新迁移。
 *
 * 用法：php switch-image-domain.php <new-domain> [dry-run]
 *   例：php switch-image-domain.php img.chacha.asia dry-run
 *       php switch-image-domain.php img.chacha.asia
 */
if (PHP_SAPI !== 'cli') { exit("CLI only\n"); }

if (PHP_SAPI === 'cli' && !isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'localhost:8080';
    $_SERVER['REQUEST_URI'] = '/';
}
require dirname(__DIR__) . '/wp-load.php';

$OLD = 'https://didi-ai-mirror.talley-linjg.workers.dev';
$newDomain = isset($argv[1]) ? preg_replace('#^https?://#', '', rtrim($argv[1], '/')) : '';
$dryRun = (isset($argv[2]) && $argv[2] === 'dry-run');
if ($newDomain === '') { fwrite(STDERR, "用法: php switch-image-domain.php <new-domain> [dry-run]\n"); exit(1); }
$NEW = 'https://' . $newDomain;

global $wpdb;

/* 1) 图片外链：_wp_attached_file（伪 attachment 主数据） */
$c1 = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s", $OLD . '%'));

/* 2) 产品 gallery 字符串里嵌的完整 URL（_cj_images 历史字段） */
$c2 = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", '%' . $OLD . '%'));

echo "待替换: _wp_attached_file 以旧域开头 {$c1} 行；任意 meta 含旧域 {$c2} 行\n";

if ($dryRun) {
    $sample = $wpdb->get_col($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s LIMIT 3", $OLD . '%'));
    foreach ($sample as $s) echo "  样例: $s\n";
    echo "dry-run 结束，未修改数据。去掉 dry-run 参数执行真实替换。\n";
    exit(0);
}

/* 批量替换（每次 5000 行，防长事务） */
$total = 0;
while (true) {
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s LIMIT 5000", $OLD . '%'), ARRAY_A);
    if (!$rows) break;
    foreach ($rows as $r) {
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->postmeta} SET meta_value=%s WHERE post_id=%d AND meta_key='_wp_attached_file' AND meta_value=%s",
            $NEW . substr($r['meta_value'], strlen($OLD)), $r['post_id'], $r['meta_value']));
        $total++;
    }
    echo "  已替换 $total\n";
}
echo "完成：共替换 $total 行 _wp_attached_file\n";

/* 3) 其他 meta 中的完整 URL（_cj_images 值等），小概率存在 */
$others = $wpdb->query($wpdb->prepare(
    "UPDATE {$wpdb->postmeta} SET meta_value=REPLACE(meta_value, %s, %s) WHERE meta_value LIKE %s AND meta_key<>'_wp_attached_file'",
    $OLD, $NEW, '%' . $OLD . '%'));
echo "其他 meta 含旧域的行已替换（影响行数 $others）\n";

/* 4) 验证 */
$left = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s", '%' . $OLD . '%'));
echo "残留旧域引用: $left\n";
echo "提示：如配置了页面静态缓存（page-cache mu-plugin），清空 wp-content/cache 生效。\n";
