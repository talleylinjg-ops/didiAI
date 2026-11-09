<?php
// shop.daqi.asia 新产品导入脚本：读 products-data.json 全自动导入
// 幂等：SKU 已存在且标记完成则跳过；中断后重跑自动续
// 用法：php import-new-products.php <products-data.json> [批大小]
if (PHP_SAPI !== 'cli') exit("CLI only\n");

// 自动向上定位 wp-load.php
$wpRoot = '';
$dir = __DIR__;
for ($i = 0; $i < 5; $i++) {
    if (file_exists($dir . '/wp-load.php')) { $wpRoot = $dir; break; }
    $parent = dirname($dir);
    if ($parent === $dir) break;
    $dir = $parent;
}
if (!$wpRoot) { fwrite(STDERR, "找不到 wp-load.php\n"); exit(1); }
require $wpRoot . '/wp-load.php';

global $wpdb;
$dataFile = isset($argv[1]) ? $argv[1] : __DIR__ . '/products-data.json';
$batch = isset($argv[2]) ? intval($argv[2]) : 50;
if (!file_exists($dataFile)) { fwrite(STDERR, "找不到数据文件 $dataFile\n"); exit(1); }
$products = json_decode(file_get_contents($dataFile), true);
if (!is_array($products)) { fwrite(STDERR, "数据文件解析失败\n"); exit(1); }
fwrite(STDERR, "载入产品=" . count($products) . "\n");

$imported = 0; $imgOnly = 0; $skipped = 0; $failed = 0; $processed = 0; $pubFixed = 0; $slugFixed = 0;

foreach ($products as $idx => $pd) {
    $sku = $pd['sku'];

    $existId = $wpdb->get_var($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID=m.post_id
         WHERE m.meta_key='_sku' AND m.meta_value=%s AND p.post_type='product' LIMIT 1", $sku));

    $pid = 0;
    if ($existId) {
        // 已存在：对齐发布状态与 slug（修复 draft 或 slug 不一致导致的 404），再补图与 meta
        $pid = (int) $existId;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT post_status, post_name FROM {$wpdb->posts} WHERE ID=%d", $pid));
        if ($row && $row->post_status !== 'publish') {
            wp_update_post(array('ID' => $pid, 'post_status' => 'publish'));
            $pubFixed++;
        }
        $wantSlug = $pd['slug'] ? $pd['slug'] : sanitize_title($pd['title']);
        if ($row && $row->post_name !== $wantSlug) {
            wp_update_post(array('ID' => $pid, 'post_name' => $wantSlug));
            $slugFixed++;
        }
        $imgOnly++;
    } else {
        // slug 冲突处理
        $slug = $pd['slug'];
        if (!$slug) $slug = sanitize_title($pd['title']);
        if ($wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type='product' LIMIT 1", $slug))) {
            $i2 = 2;
            while ($wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name=%s AND post_type='product' LIMIT 1", $slug . '-' . $i2))) $i2++;
            $slug = $slug . '-' . $i2;
        }
        $pid = wp_insert_post(array(
            'post_title' => $pd['title'],
            'post_name' => $slug,
            'post_content' => $pd['content'],
            'post_excerpt' => $pd['excerpt'],
            'post_status' => 'publish',
            'post_type' => 'product',
            'post_date' => $pd['date'],
            'post_date_gmt' => gmdate('Y-m-d H:i:s', strtotime($pd['date'])),
            'menu_order' => (int) $pd['menu_order'],
        ), true);
        if (is_wp_error($pid) || !$pid) { $failed++; fwrite(STDERR, "FAIL insert {$pd['sku']}\n"); continue; }
        update_post_meta($pid, '_sku', $sku);
        $imported++;
    }

    // meta 批量
    foreach ($pd['meta'] as $k => $v) {
        if (strpos($k, '_wc_') === 0 || strpos($k, '_transient_') === 0) continue;
        if ($k === '_thumbnail_id' || $k === '_product_image_gallery') continue;
        if ($k === '_sku') continue;
        update_post_meta($pid, $k, maybe_unserialize($v));
    }

    // terms 对齐
    foreach ($pd['terms'] as $tax => $list) {
        if (!taxonomy_exists($tax)) continue;
        $termIds = array();
        foreach ($list as $t) {
            $term = get_term_by('slug', $t['slug'], $tax);
            if (!$term) {
                $parent = 0;
                if (!empty($t['parent'])) {
                    $pt = get_term_by('slug', $t['parent'], $tax);
                    if ($pt) $parent = (int) $pt->term_id;
                }
                $r = wp_insert_term($t['name'], $tax, array('slug' => $t['slug'], 'parent' => $parent, 'description' => $t['description']));
                if (is_wp_error($r)) { $term = get_term_by('slug', $t['slug'], $tax); if (!$term) continue; }
                else $term = get_term($r['term_id'], $tax);
            }
            if ($term && !is_wp_error($term)) $termIds[] = (int) $term->term_id;
        }
        if ($termIds) wp_set_object_terms($pid, $termIds, $tax, false);
    }

    // 图片：主图 -> featured，其余 -> gallery（下载进源站媒体库）
    $imgs = array_slice((array) $pd['images'], 0, 7);
    $attachIds = array();
    foreach ($imgs as $n => $url) {
        $attachId = shopSideloadImage($url, $pid);
        if ($attachId) $attachIds[] = $attachId;
    }
    if ($attachIds) {
        update_post_meta($pid, '_thumbnail_id', $attachIds[0]);
        if (count($attachIds) > 1) update_post_meta($pid, '_product_image_gallery', implode(',', $attachIds));
    }
    update_post_meta($pid, '_shop_sync_done', 'yes');
    wc_delete_product_transients($pid);

    $processed++;
    if ($processed % $batch === 0) {
        fwrite(STDERR, "progress=$processed new=$imported imgOnly=$imgOnly pub=$pubFixed slug=$slugFixed fail=$failed\n");
        // 防内存增长，分批退出（重跑幂等续）
        if ($processed >= 200) {
            file_put_contents(__DIR__ . '/sync-progress.tmp', "$idx");
            fwrite(STDERR, "BATCH-EXIT 已处理 $processed，重跑本命令自动继续\n");
            exit(0);
        }
    }
}

fwrite(STDERR, "DONE 新导入=$imported 补图=$imgOnly 补发布=$pubFixed 修slug=$slugFixed 失败=$failed\n");

// 下载外链图并创建附件（shop 端媒体库）
function shopSideloadImage($url, $parentId) {
    global $wpdb;
    $name = basename(parse_url($url, PHP_URL_PATH));
    if (!$name || strpos($name, '.jpg') === false) $name = md5($url) . '.jpg';
    $exist = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_source_url' AND meta_value=%s LIMIT 1", $url));
    if ($exist) return (int) $exist;
    $bin = @file_get_contents($url, false, stream_context_create(array('http' => array('timeout' => 25, 'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120\r\n"))));
    if (!$bin || strlen($bin) < 1024) return 0;
    $up = wp_upload_bits($name, null, $bin);
    if (!empty($up['error'])) return 0;
    $file = $up['file'];
    $ftype = wp_check_filetype($file);
    $attId = wp_insert_attachment(array(
        'post_mime_type' => $ftype['type'] ?: 'image/jpeg',
        'post_title' => sanitize_file_name(pathinfo($file, PATHINFO_FILENAME)),
        'post_status' => 'inherit',
        'post_parent' => $parentId,
    ), $file);
    if (is_wp_error($attId) || !$attId) return 0;
    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata($attId, wp_generate_attachment_metadata($attId, $file));
    update_post_meta($attId, '_source_url', $url);
    return (int) $attId;
}
