<?php
/** CJ 导入各阶段实现（由 cj-import.php 引入） */

function cjStagingAppend($_CJ, $products) {
    $line = '';
    foreach ($products as $p) $line .= json_encode($p, JSON_UNESCAPED_UNICODE) . "\n";
    file_put_contents($_CJ['staging'], $line, FILE_APPEND | LOCK_EX);
}

function cjStagingCount($_CJ) {
    if (!file_exists($_CJ['staging'])) return 0;
    $n = 0; $fp = fopen($_CJ['staging'], 'r');
    while (fgets($fp) !== false) $n++;
    fclose($fp);
    return $n;
}

/** 阶段1：抓 CJT 产品列表（pageSize=100，断点续抓） */
function cjStageList($_CJ) {
    $s = stateLoad($_CJ);
    $pageSize = 100;
    echo "== 阶段1：抓取 CJT 产品列表（前缀 {$_CJ['skuPrefix']}）\n";
    while (true) {
        $res = cjGet($_CJ, '/v2/product/list', array(
            'pageNum' => $s['listPage'] + 1,
            'pageSize' => $pageSize,
            'productSku' => $_CJ['skuPrefix'],
        ));
        if (empty($res['data']['list'])) {
            if ($s['listPage'] === 0) { fwrite(STDERR, "列表为空或接口失败: " . (isset($res['message']) ? $res['message'] : '') . "\n"); exit(1); }
            $s['listDone'] = true;
            stateSave($_CJ, $s);
            echo "列表抓取完成，共 " . cjStagingCount($_CJ) . " 条\n";
            return;
        }
        cjStagingAppend($_CJ, $res['data']['list']);
        $s['listPage']++;
        stateSave($_CJ, $s);
        $total = isset($res['data']['total']) ? $res['data']['total'] : '?';
        echo "页 {$s['listPage']}: +" . count($res['data']['list']) . "（累计 " . cjStagingCount($_CJ) . "/$total）\n";
        if (count($res['data']['list']) < $pageSize) {
            $s['listDone'] = true;
            stateSave($_CJ, $s);
            echo "列表抓取完成，共 " . cjStagingCount($_CJ) . " 条\n";
            return;
        }
    }
}

/** 分类树构建/获取：categoryName 形如 "A > B > C" */
function cjEnsureCategory($namePath) {
    static $cache = array();
    $parts = array_values(array_filter(array_map('trim', explode('>', $namePath))));
    if (!$parts) return 0;
    $parent = 0;
    $pathKey = '';
    foreach ($parts as $i => $name) {
        $pathKey .= '/' . $name;
        if (isset($cache[$pathKey])) { $parent = $cache[$pathKey]; continue; }
        $slug = sanitize_title($name);
        if ($i > 0) $slug = sanitize_title($parts[$i-1]) . '-' . $slug;
        $exists = get_term_by('slug', $slug, 'product_cat');
        if ($exists && intval($exists->parent) === intval($parent)) {
            $cache[$pathKey] = intval($exists->term_id);
        } else {
            $r = wp_insert_term($name, 'product_cat', array('slug' => $slug, 'parent' => $parent));
            if (is_wp_error($r)) {
                if ($r->get_error_code() === 'term_exists') {
                    $cache[$pathKey] = intval($r->get_error_data()['term_id']);
                } else {
                    fwrite(STDERR, "  分类[$namePath]失败: " . $r->get_error_message() . "\n");
                    return 0;
                }
            } else {
                $cache[$pathKey] = intval($r['term_id']);
            }
        }
        $parent = $cache[$pathKey];
    }
    return $parent;
}

/** 伪 attachment：不落盘文件，_wp_attached_file 存外链（CJ/R2）→ wp_get_attachment_url 原样返回 */
function cjFakeAttachment($url, $title) {
    global $wpdb;
    $aid = intval($wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND post_status='inherit' AND guid=%s LIMIT 1", $url)));
    if ($aid) return $aid;
    $aid = wp_insert_attachment(array(
        'post_title' => $title,
        'post_status' => 'inherit',
        'post_type' => 'attachment',
        'guid' => $url,
    ));
    if (is_wp_error($aid) || !$aid) return 0;
    update_post_meta($aid, '_wp_attached_file', $url);
    return intval($aid);
}

function cjPrice($p) { return round(floatval($p) * 1.5, 2); }

/** 阶段2：详情抓取 + WC 导入 */
function cjStageImport($_CJ, $limit) {
    $s = stateLoad($_CJ);
    $staging = cjStagingCount($_CJ);
    echo "== 阶段2：详情导入（staging $staging 条，起始游标 {$s['importCursor']}，limit $limit)\n";
    // 批量延迟 term 计数重算（COUNT(*) 全量扫描过慢），批尾统一刷新
    wp_defer_term_counting(true);
    wp_defer_comment_counting(true);
    $fp = fopen($_CJ['staging'], 'r');
    $idx = -1;
    $processed = 0;
    while (($line = fgets($fp)) !== false) {
        $idx++;
        if ($idx < $s['importCursor']) continue;
        if ($limit > 0 && $processed >= $limit) break;
        $item = json_decode($line, true);
        if (!$item || empty($item['pid'])) continue;
        $pid = $item['pid'];

        $res = cjGet($_CJ, '/v1/product/query', array('pid' => $pid));
        if (empty($res['data'])) {
            fwrite(STDERR, "  [$pid] 详情失败: " . (isset($res['message']) ? $res['message'] : '') . "\n");
            $s['failed'][] = $pid;
            $s['importCursor'] = $idx + 1;
            stateSave($_CJ, $s);
            continue;
        }
        $d = $res['data'];
        $sku = isset($d['productSku']) ? $d['productSku'] : '';
        if ($sku && wc_get_product_id_by_sku($sku)) {
            $s['skipped']++;
            $s['importCursor'] = $idx + 1;
            stateSave($_CJ, $s);
            continue;
        }
        $err = cjImportProduct($_CJ, $d);
        if ($err) {
            fwrite(STDERR, "  [$pid] 导入失败: $err\n");
            $s['failed'][] = $pid;
        } else {
            $s['imported']++;
        }
        $s['importCursor'] = $idx + 1;
        if ($s['imported'] % 20 === 0 || ($limit > 0 && $processed >= $limit - 1)) stateSave($_CJ, $s);
        $processed++;
        if ($processed % 25 === 0) {
            stateSave($_CJ, $s);
            echo "  进度 $idx/$staging 导入 {$s['imported']} 跳过 {$s['skipped']} 失败 " . count($s['failed']) . "\n";
        }
        // 防 WP 对象缓存内存泄漏：每 500 个退出，由 shell 循环重启续跑
        if ($processed >= 500) {
            stateSave($_CJ, $s);
            echo "  分批退出（processed $processed，游标 {$s['importCursor']}）\n";
            exit(0);
        }
    }
    fclose($fp);
    stateSave($_CJ, $s);
    if ($s['importCursor'] >= $staging) {
        wp_defer_term_counting(false); // 全部完成时统一刷新 term 计数
        echo "导入全部完成\n";
    }
    echo "导入结束：导入 {$s['imported']} 跳过 {$s['skipped']} 失败 " . count($s['failed']) . "\n";
}

/** 阶段2b：重试 failed 里的 pid（详情导入直接查 CJ API，无需 staging 行） */
function cjStageRetry($_CJ, $limit) {
    $s = stateLoad($_CJ);
    $pids = $s['failed'];
    echo "== 阶段2b：重试失败产品（待重试 " . count($pids) . " 个，limit $limit)\n";
    wp_defer_term_counting(true);
    wp_defer_comment_counting(true);
    $ok = 0; $stillFailed = array(); $processed = 0;
    foreach ($pids as $pid) {
        if ($limit > 0 && $processed >= $limit) { $stillFailed[] = $pid; continue; }
        $processed++;
        $res = cjGet($_CJ, '/v1/product/query', array('pid' => $pid));
        if (empty($res['data'])) {
            fwrite(STDERR, "  [$pid] 详情失败: " . (isset($res['message']) ? $res['message'] : '') . "\n");
            $stillFailed[] = $pid;
            continue;
        }
        $d = $res['data'];
        $sku = isset($d['productSku']) ? $d['productSku'] : '';
        if ($sku && wc_get_product_id_by_sku($sku)) { $s['skipped']++; continue; }
        $err = cjImportProduct($_CJ, $d);
        if ($err) {
            fwrite(STDERR, "  [$pid] 导入失败: $err\n");
            $stillFailed[] = $pid;
        } else {
            $s['imported']++; $ok++;
        }
        if ($ok % 20 === 0) {
            $s['failed'] = array_merge($stillFailed, array_slice($pids, $processed));
            stateSave($_CJ, $s);
        }
        if ($ok % 500 === 0 && $ok > 0) {
            $s['failed'] = array_merge($stillFailed, array_slice($pids, $processed));
            stateSave($_CJ, $s);
            echo "  重试进度 $processed/" . count($pids) . " 成功 $ok\n";
            wp_defer_term_counting(false);
            exit(0); // 防内存泄漏，由 shell 循环重启
        }
    }
    $s['failed'] = $stillFailed;
    stateSave($_CJ, $s);
    wp_defer_term_counting(false);
    echo "重试完成：本次成功 {$ok}，仍失败 " . count($stillFailed) . "\n";
    echo "导入结束：导入 {$s['imported']} 跳过 {$s['skipped']} 失败 " . count($s['failed']) . "\n";
}

function cjImportProduct($_CJ, $d) {
    $pid = $d['pid'];
    $sku = isset($d['productSku']) ? $d['productSku'] : '';
    if ($sku && wc_get_product_id_by_sku($sku)) return null; // 已存在
    $name = !empty($d['productNameEn']) ? $d['productNameEn'] : (!empty($d['productName']) ? $d['productName'] : 'CJ Product ' . $pid);
    $variants = isset($d['variants']) && is_array($d['variants']) ? $d['variants'] : array();
    $images = cjCollectImages($d);

    $catId = !empty($d['categoryName']) ? cjEnsureCategory($d['categoryName']) : 0;

    try {
        if ($variants) {
            $product = new WC_Product_Variable();
        } else {
            $product = new WC_Product_Simple();
        }
        $product->set_name($name);
        $product->set_status('draft');
        if ($sku) $product->set_sku($sku);
        if ($catId) $product->set_category_ids(array($catId));
        $desc = isset($d['description']) ? wp_kses_post($d['description']) : '';
        if ($desc) $product->set_description($desc);
        $short = '';
        if (!empty($d['productProEn'])) {
            $pro = $d['productProEn'];
            $decoded = is_string($pro) ? json_decode($pro, true) : $pro;
            $lines = is_array($decoded) ? $decoded : (is_array($pro) ? $pro : array($pro));
            $short = wp_kses_post(implode("\n", array_map('strval', $lines)));
        }
        if ($short) $product->set_short_description($short);

        $mainImg = !empty($images[0]) ? $images[0] : '';
        if ($mainImg) {
            $aid = cjFakeAttachment($mainImg, $name . ' - main');
            if ($aid) $product->set_image_id($aid);
        }
        if (count($images) > 1) {
            $gids = array();
            foreach (array_slice($images, 1, 8) as $i => $u) {
                $gid = cjFakeAttachment($u, $name . " - g$i");
                if ($gid) $gids[] = $gid;
            }
            $product->set_gallery_image_ids($gids);
        }
        $product->set_weight(cjNum(isset($d['productWeight']) ? $d['productWeight'] : 0));

        $pidCreated = $product->save();
        if (!$pidCreated) return 'save failed';

        update_post_meta($pidCreated, '_cj_pid', (string)$pid);
        update_post_meta($pidCreated, '_cj_warehouse', $_CJ['warehouse']);
        update_post_meta($pidCreated, '_cj_warehouse_label', 'CJT · Thailand Warehouse');
        update_post_meta($pidCreated, '_cj_images', $images);
        update_post_meta($pidCreated, '_cj_images_migrated', 'no');
        if (!empty($d['productVideo'])) update_post_meta($pidCreated, '_cj_video', $d['productVideo']);
        if (!empty($d['supplierName'])) update_post_meta($pidCreated, '_cj_supplier', $d['supplierName']);

        if ($variants) {
            cjAttachVariants($_CJ, $pidCreated, $d, $images);
        } else {
            $price = cjPrice(!empty($d['sellPrice']) ? $d['sellPrice'] : 0);
            $product->set_regular_price($price);
            $product->save();
        }
        return null;
    } catch (Throwable $e) {
        return $e->getMessage();
    }
}

function cjCollectImages($d) {
    $urls = array();
    $main = isset($d['productImage']) ? $d['productImage'] : '';
    foreach (explode(',', (string)$main) as $u) { $u = trim($u); if ($u && filter_var($u, FILTER_VALIDATE_URL)) $urls[] = $u; }
    if (!empty($d['productImageSet'])) {
        $set = is_array($d['productImageSet']) ? $d['productImageSet'] : explode(',', (string)$d['productImageSet']);
        foreach ($set as $u) { $u = trim($u); if ($u && filter_var($u, FILTER_VALIDATE_URL)) $urls[] = $u; }
    }
    if (!empty($d['bigImage']) && filter_var($d['bigImage'], FILTER_VALIDATE_URL)) $urls[] = $d['bigImage'];
    if (!empty($d['variants'])) {
        foreach ($d['variants'] as $v) {
            if (!empty($v['variantImage']) && filter_var($v['variantImage'], FILTER_VALIDATE_URL)) $urls[] = $v['variantImage'];
        }
    }
    return array_values(array_unique($urls));
}

/** 变体：统一挂到自定义属性 pa_cj-variant，variantKey 为值 */
function cjAttachVariants($_CJ, $productId, $d, $images) {
    $attrLabel = 'CJ Variant';
    $attrSlug = 'pa_cj-variant';
    $taxonomy = $attrSlug;
    if (!taxonomy_exists($taxonomy)) {
        register_taxonomy($taxonomy, 'product', array('hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false));
    }
    $product = wc_get_product($productId);
    if (!$product) return;
    $attr = new WC_Product_Attribute();
    $attr->set_id(0);
    $attr->set_name($attrLabel);
    $attr->set_options(array());
    $attr->set_position(0);
    $attr->set_visible(true);
    $attr->set_variation(true);
    // 逐变体 term 化便于筛选
    $keys = array();
    foreach ($d['variants'] as $v) {
        $key = !empty($v['variantKey']) ? $v['variantKey'] : (!empty($v['variantNameEn']) ? $v['variantNameEn'] : $v['variantSku']);
        $key = trim($key);
        if ($key === '') $key = $v['variantSku'];
        $keys[] = $key;
    }
    $attr->set_options(array_values(array_unique($keys)));
    $product->set_attributes(array($attr));
    $product->save();

    foreach ($d['variants'] as $v) {
        $vsku = isset($v['variantSku']) ? $v['variantSku'] : '';
        if (!$vsku || wc_get_product_id_by_sku($vsku)) continue;
        $key = !empty($v['variantKey']) ? $v['variantKey'] : (!empty($v['variantNameEn']) ? $v['variantNameEn'] : $vsku);
        $key = trim($key);
        if ($key === '') $key = $vsku;
        try {
            $variation = new WC_Product_Variation();
            $variation->set_parent_id($productId);
            $variation->set_status('publish');
            $variation->set_sku($vsku);
            $variation->set_attributes(array('cj-variant' => $key));
            $variation->set_regular_price(cjPrice(!empty($v['variantSellPrice']) ? $v['variantSellPrice'] : (!empty($d['sellPrice']) ? $d['sellPrice'] : 0)));
            if (!empty($v['variantWeight'])) $variation->set_weight(cjNum($v['variantWeight']));
            if (!empty($v['variantImage'])) {
                $aid = cjFakeAttachment($v['variantImage'], $vsku);
                if ($aid) $variation->set_image_id($aid);
            }
            $inventory = 0;
            if (!empty($v['inventories']) && is_array($v['inventories'])) {
                foreach ($v['inventories'] as $inv) {
                    if (isset($inv['inventoryNum'])) $inventory += intval($inv['inventoryNum']);
                }
            }
            $variation->set_manage_stock(true);
            $variation->set_stock_quantity(max($inventory, 0));
            $variation->set_stock_status($inventory > 0 ? 'instock' : 'outofstock');
            $vid = $variation->save();
            if ($vid) {
                update_post_meta($vid, '_cj_vid', (string)$v['vid']);
                update_post_meta($vid, '_cj_warehouse', $_CJ['warehouse']);
            }
        } catch (Throwable $e) {
            fwrite(STDERR, "    变体[$vsku]失败: " . $e->getMessage() . "\n");
        }
    }
    wc_delete_product_transients($productId);
}

function cjNum($v) { $f = floatval($v); return $f > 0 ? (string)$f : ''; }

/** 阶段3：图片迁移 R2 —— 基于待迁移 ID 列表文件，避免 wp_postmeta 大表重复扫描 */
function cjStageImages($_CJ, $limit, $shardIdx = 0, $shardTotal = 1) {
    if (empty($_CJ['r2']['token'])) { fwrite(STDERR, "缺少 CF_API_TOKEN\n"); exit(1); }
    global $wpdb;
    $listFile = $_CJ['pendingList'] . ($shardTotal > 1 ? "-$shardIdx" : '');
    if (!file_exists($listFile)) {
        $ids = $wpdb->get_col($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_cj_images_migrated' AND meta_value='no' AND post_id %% %d = %d ORDER BY post_id ASC",
            $shardTotal, $shardIdx));
        file_put_contents($listFile, implode("\n", $ids));
        echo "待迁移列表已生成($listFile)：" . count($ids) . " 个\n";
    }
    $st = file_exists($_CJ['imgState']) ? json_decode(file_get_contents($_CJ['imgState']), true) : array('products' => array(), 'done' => 0);
    $processed = 0;
    while ($limit <= 0 || $processed < $limit) {
        $ids = file($listFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$ids) break;
        $batch = array_slice($ids, 0, 40);
        foreach ($batch as $wpid) {
            if ($limit > 0 && $processed >= $limit) break;
            $wpid = intval($wpid);
            $images = get_post_meta($wpid, '_cj_images', true);
            if (!is_array($images) || !$images) {
                update_post_meta($wpid, '_cj_images_migrated', 'empty');
            } else {
                $images = array_slice($images, 0, 10); // 每产品最多 10 张
                $map = cjBatchUploadToR2($_CJ, $images, $wpid);
                if ($map) {
                    // 单条 UPDATE ... CASE 批量替换 attachment 外链（走 k_mv 索引）
                    $caseParts = array();
                    $params = array();
                    foreach ($map as $cjUrl => $r2Url) {
                        $caseParts[] = 'WHEN %s THEN %s';
                        $params[] = $cjUrl;
                        $params[] = $r2Url;
                    }
                    $params = array_merge($params, array_keys($map));
                    $sql = "UPDATE {$wpdb->postmeta} SET meta_value = CASE meta_value "
                        . implode(' ', $caseParts)
                        . ' ELSE meta_value END'
                        . " WHERE meta_key='_wp_attached_file' AND meta_value IN ("
                        . implode(',', array_fill(0, count($map), '%s')) . ')';
                    $wpdb->query(call_user_func_array(array($wpdb, 'prepare'), array_merge(array($sql), $params)));
                    update_post_meta($wpid, '_cj_images', array_values($map));
                }
                update_post_meta($wpid, '_cj_images_migrated', 'yes');
                $st['done']++;
                file_put_contents($_CJ['imgState'], json_encode($st));
            }
            $processed++;
            cjPendingRemove($listFile, $wpid);
            if ($processed % 10 === 0) echo "  图片迁移(shard$shardIdx) $processed\n";
            // 防 WP 对象缓存内存泄漏：每 40 个退出，由 shell 循环重启续跑
            if ($processed >= 40) {
                echo "  分批退出(shard{$shardIdx}，done {$st['done']})\n";
                exit(0);
            }
        }
        if ($limit > 0 && $processed >= $limit) break;
    }
    echo "图片迁移批完成(shard$shardIdx)：累计 {$st['done']} 个产品\n";
}

/** 从待迁移列表文件中移除指定 ID（处理完成即出队，文件即断点） */
function cjPendingRemove($file, $id) {
    $fp = fopen($file, 'c+');
    if (!$fp) return;
    flock($fp, LOCK_EX);
    $out = '';
    $removed = false;
    while (($line = fgets($fp)) !== false) {
        $l = trim($line);
        if ($l === '') continue;
        if (!$removed && intval($l) === intval($id)) { $removed = true; continue; }
        $out .= $l . "\n";
    }
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $out);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

/** 并发下载（6 路）→ GD 压缩 → 并发上传 R2（6 路）；返回 [cjUrl => r2Url] */
function cjBatchUploadToR2($_CJ, $urls, $wpid) {
    $keys = array();
    foreach ($urls as $u) $keys[$u] = 'wp-content/uploads/cj/' . $wpid . '/' . md5($u) . '.jpg';
    // 1) 并发下载（timeout 20s，超时/失败重试 1 次）
    $mh = curl_multi_init();
    $chs = array();
    foreach ($urls as $u) {
        $ch = curl_init($u);
        curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_FOLLOWLOCATION => true));
        curl_multi_add_handle($mh, $ch);
        $chs[$u] = $ch;
    }
    do { $status = curl_multi_exec($mh, $active); if ($active) curl_multi_select($mh, 1); } while ($active && $status === CURLM_OK);
    $bins = array();
    $dlFailed = array();
    foreach ($chs as $u => $ch) {
        $bin = curl_multi_getcontent($ch);
        if ($bin && strlen($bin) >= 1024) $bins[$u] = $bin;
        else $dlFailed[] = $u;
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);
    foreach ($dlFailed as $u) { // 快速重试一次
        $ch = curl_init($u);
        curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_CONNECTTIMEOUT => 8, CURLOPT_FOLLOWLOCATION => true));
        $bin = curl_exec($ch);
        curl_close($ch);
        if ($bin && strlen($bin) >= 1024) $bins[$u] = $bin;
    }
    // 2) 压缩（长边 1200，jpeg q78）
    foreach ($bins as $u => $bin) {
        if (!$bin || strlen($bin) < 1024) { $bins[$u] = null; continue; }
        $bins[$u] = cjCompressImage($bin);
    }
    // 3) 并发上传 R2（分批 6 路，失败自动重试 1 次）
    $map = array();
    $batch = array_chunk(array_keys($bins), 6);
    foreach ($batch as $group) {
        $mh = curl_multi_init();
        $chs = array();
        foreach ($group as $u) {
            if ($bins[$u] === null) continue;
            $acc = $_CJ['r2']['accountId'];
            $api = "https://api.cloudflare.com/client/v4/accounts/$acc/r2/buckets/{$_CJ['r2']['bucket']}/objects/" . rawurlencode($keys[$u]);
            $ch = curl_init($api);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 90, CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $bins[$u],
                CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $_CJ['r2']['token'], 'Content-Type: image/jpeg'),
            ));
            curl_multi_add_handle($mh, $ch);
            $chs[$u] = $ch;
        }
        do { $status = curl_multi_exec($mh, $active); if ($active) curl_multi_select($mh, 1); } while ($active && $status === CURLM_OK);
        $failed = array();
        foreach ($chs as $u => $ch) {
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code >= 200 && $code < 300) $map[$u] = $_CJ['r2']['publicBase'] . '/' . $keys[$u];
            else $failed[] = $u;
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
        foreach ($failed as $u) { // 重试一次（串行）
            $acc = $_CJ['r2']['accountId'];
            $api = "https://api.cloudflare.com/client/v4/accounts/$acc/r2/buckets/{$_CJ['r2']['bucket']}/objects/" . rawurlencode($keys[$u]);
            $ch = curl_init($api);
            curl_setopt_array($ch, array(
                CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 90, CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => $bins[$u],
                CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $_CJ['r2']['token'], 'Content-Type: image/jpeg'),
            ));
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code >= 200 && $code < 300) $map[$u] = $_CJ['r2']['publicBase'] . '/' . $keys[$u];
            else fwrite(STDERR, "    R2 put 失败($code): {$keys[$u]}\n");
        }
    }
    return $map;
}

function cjCompressImage($bin) {
    if (!function_exists('imagecreatefromstring')) return $bin;
    $img = @imagecreatefromstring($bin);
    if (!$img) return $bin;
    $w = imagesx($img); $h = imagesy($img);
    $max = 1200;
    if (max($w, $h) > $max) {
        $scale = $max / max($w, $h);
        $nw = (int)round($w * $scale); $nh = (int)round($h * $scale);
        $dst = imagecreatetruecolor($nw, $nh);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $dst;
    }
    ob_start();
    imagejpeg($img, null, 78);
    $out = ob_get_clean();
    imagedestroy($img);
    return $out && strlen($out) > 1024 ? $out : $bin;
}

function cjPutToR2($_CJ, $key, $bin) {
    $acc = $_CJ['r2']['accountId'];
    $api = "https://api.cloudflare.com/client/v4/accounts/$acc/r2/buckets/{$_CJ['r2']['bucket']}/objects/" . rawurlencode($key);
    $ch = curl_init($api);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 90, CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $bin,
        CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $_CJ['r2']['token'], 'Content-Type: image/jpeg'),
    ));
    curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300) return $_CJ['r2']['publicBase'] . '/' . $key;
    fwrite(STDERR, "    R2 put 失败($code): $key\n");
    return '';
}

function cjUploadToR2($_CJ, $url, $wpid, &$map) {
    $key = 'wp-content/uploads/cj/' . $wpid . '/' . md5($url) . '.jpg';
    $acc = $_CJ['r2']['accountId'];
    $api = "https://api.cloudflare.com/client/v4/accounts/$acc/r2/buckets/{$_CJ['r2']['bucket']}/objects/" . rawurlencode($key);
    $ch = curl_init($url);
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 60, CURLOPT_FOLLOWLOCATION => true));
    $bin = curl_exec($ch);
    curl_close($ch);
    if (!$bin || strlen($bin) < 1024) return '';
    $ch = curl_init($api);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 90, CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => $bin,
        CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $_CJ['r2']['token'], 'Content-Type: image/jpeg'),
    ));
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300) return $_CJ['r2']['publicBase'] . '/' . $key;
    fwrite(STDERR, "    R2 put 失败($code): $url\n");
    return '';
}

function cjImportMain($argv, $_CJ) {
    $cmd = isset($argv[1]) ? $argv[1] : 'status';
    $limit = isset($argv[2]) ? intval($argv[2]) : 0;
    switch ($cmd) {
        case 'list':
            cjStageList($_CJ); break;
        case 'import':
            cjStageImport($_CJ, $limit); break;
        case 'retry':
            cjStageRetry($_CJ, $limit); break;
        case 'images':
            $shardIdx = isset($argv[3]) ? intval($argv[3]) : 0;
            $shardTotal = isset($argv[4]) ? intval($argv[4]) : 1;
            cjStageImages($_CJ, $limit, $shardIdx, $shardTotal); break;
        case 'status':
            $s = stateLoad($_CJ);
            echo "staging: " . cjStagingCount($_CJ) . " 条（listDone=" . var_export($s['listDone'], true) . "）\n";
            echo "导入: 已导入 {$s['imported']}，跳过 {$s['skipped']}，失败 " . count($s['failed']) . "，游标 {$s['importCursor']}\n";
            break;
        default:
            echo "未知命令: $cmd\n";
    }
}
