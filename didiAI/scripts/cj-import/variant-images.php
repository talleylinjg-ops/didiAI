<?php
/** 变体图/详情图外链迁移 R2：收集 CJ CDN 外链 → 分 shard → 压缩上传 R2 → 替换 URL（断点=队列文件，处理完即出队） */
if (PHP_SAPI !== 'cli') { exit("CLI only\n"); }

$_SERVER['HTTP_HOST'] = 'localhost:8080';
$_SERVER['REQUEST_URI'] = '/';
require dirname(__DIR__, 2) . '/wp-load.php';
require __DIR__ . '/import-tasks.php';

$_CJ = array(
    'r2' => array(
        'bucket'     => 'didi-ai-mirror',
        'accountId'  => getenv('CF_ACCOUNT_ID') ?: '2e33f078edc00ade4b25e61526d6f544',
        'token'      => getenv('CF_API_TOKEN') ?: '',
        'publicBase' => 'https://didi-ai-mirror.talley-linjg.workers.dev',
    ),
);

if (empty($_CJ['r2']['token'])) { fwrite(STDERR, "缺少 CF_API_TOKEN\n"); exit(1); }

$cmd = isset($argv[1]) ? $argv[1] : 'collect';
$limit = isset($argv[2]) ? intval($argv[2]) : 40;
$shardIdx = isset($argv[3]) ? intval($argv[3]) : 0;
$shardTotal = isset($argv[4]) ? intval($argv[4]) : 1;

$LIKE_DOMAINS = array('https://cf.cjdropshipping%', 'https://oss-cf.cjdropshipping%', 'https://cj-product-center%', 'https://cbu01.alicdn%');

if ($cmd === 'collect') {
    global $wpdb;
    $where = array();
    $params = array();
    foreach ($LIKE_DOMAINS as $d) { $where[] = 'meta_value LIKE %s'; $params[] = $d; }
    $sql = "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key='_wp_attached_file' AND (" . implode(' OR ', $where) . ')';
    $ids = $wpdb->get_col(call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $params)));
    $shardIds = array_values(array_filter($ids, function ($id) use ($shardIdx, $shardTotal) { return ((int)$id % $shardTotal) === $shardIdx; }));
    $file = "/tmp/opencode/variant-pending-$shardIdx";
    file_put_contents($file, implode("\n", $shardIds));
    echo "total=" . count($ids) . " shard$shardIdx=" . count($shardIds) . " -> $file\n";
    exit(0);
}

if ($cmd === 'migrate') {
    global $wpdb;
    $listFile = "/tmp/opencode/variant-pending-$shardIdx";
    if (!file_exists($listFile)) { fwrite(STDERR, "先跑 collect\n"); exit(1); }
    $ids = array_filter(explode("\n", trim(file_get_contents($listFile))));
    $batch = array_slice($ids, 0, $limit);
    if (!$batch) { echo "shard$shardIdx 队列空\n"; exit(0); }
    $processed = 0; $ok = 0; $fail = 0;
    foreach ($batch as $aid) {
        $aid = intval($aid);
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT p.post_parent pp, m.meta_value url FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON m.post_id=p.ID AND m.meta_key='_wp_attached_file' WHERE p.ID=%d", $aid), ARRAY_A);
        if (!$row || empty($row['url']) || strpos($row['url'], 'https://didi-ai-mirror') === 0) { cjPendingRemove($listFile, $aid); continue; }
        $wpid = intval($row['pp']) > 0 ? intval($row['pp']) : $aid;
        $map = cjBatchUploadToR2($_CJ, array($row['url']), $wpid);
        if (!empty($map[$row['url']])) {
            $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->postmeta} SET meta_value=%s WHERE meta_key='_wp_attached_file' AND meta_value=%s",
                $map[$row['url']], $row['url']));
            $ok++;
        } else { $fail++; }
        cjPendingRemove($listFile, $aid);
        $processed++;
        // 防 WP 对象缓存内存泄漏：每批退出，由 shell 循环重启
        if ($processed >= 40) { echo "shard$shardIdx 批量退出(ok=$ok fail=$fail)\n"; exit(0); }
    }
    $left = count($ids) - $processed;
    echo "shard$shardIdx 批完成 processed=$processed ok=$ok fail=$fail 剩余=$left\n";
    exit(0);
}

fwrite(STDERR, "用法: variant-images.php [collect|migrate] [limit] [shardIdx] [shardTotal]\n");
exit(1);
