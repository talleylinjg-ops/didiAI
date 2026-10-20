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

$wpid = intval($argv[1] ?? 0);
$images = get_post_meta($wpid, '_cj_images', true);
if (!is_array($images) || !$images) { echo "no images for $wpid\n"; exit(1); }
$images = array_slice($images, 0, 10);
echo "产品 $wpid，图片 " . count($images) . " 张，首图 " . substr($images[0], 0, 60) . "...\n";

$t0 = microtime(true);
// 1) 并发下载计时
$mh = curl_multi_init();
$chs = array();
foreach ($images as $u) {
    $ch = curl_init($u);
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60, CURLOPT_FOLLOWLOCATION => true));
    curl_multi_add_handle($mh, $ch);
    $chs[$u] = $ch;
}
do { $status = curl_multi_exec($mh, $active); if ($active) curl_multi_select($mh, 1); } while ($active && $status === CURLM_OK);
$bins = array();
$sizes = array();
foreach ($chs as $u => $ch) {
    $bins[$u] = curl_multi_getcontent($ch);
    $sizes[$u] = strlen((string)$bins[$u]);
    $info = curl_getinfo($ch);
    echo "  下载 " . round($info['total_time'], 2) . "s " . $info['http_code'] . " " . $sizes[$u] . "B " . substr($u, 0, 60) . "\n";
    curl_multi_remove_handle($mh, $ch); curl_close($ch);
}
curl_multi_close($mh);
$t1 = microtime(true);
echo "下载合计 " . round($t1 - $t0, 2) . "s\n";

// 2) 压缩计时
foreach ($bins as $u => $bin) {
    if (!$bin || strlen($bin) < 1024) { $bins[$u] = null; continue; }
    $c0 = microtime(true);
    $bins[$u] = cjCompressImage($bin);
    echo "  压缩 " . round(microtime(true) - $c0, 2) . "s " . strlen($bins[$u]) . "B\n";
}
$t2 = microtime(true);
echo "压缩合计 " . round($t2 - $t1, 2) . "s\n";

// 3) 上传计时
foreach ($bins as $u => $bin) {
    if ($bin === null) continue;
    $u0 = microtime(true);
    $key = 'wp-content/uploads/cj/' . $wpid . '/' . md5($u) . '.jpg';
    $acc = $_CJ['r2']['accountId'];
    $api = "https://api.cloudflare.com/client/v4/accounts/$acc/r2/buckets/{$_CJ['r2']['bucket']}/objects/" . rawurlencode($key);
    $ch = curl_init($api);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 90, CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $bin,
        CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $_CJ['r2']['token'], 'Content-Type: image/jpeg'),
    ));
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "  上传 " . round(microtime(true) - $u0, 2) . "s HTTP$code\n";
}
$t3 = microtime(true);
echo "上传合计 " . round($t3 - $t2, 2) . "s，总计 " . round($t3 - $t0, 2) . "s\n";
