<?php
/**
 * CJ Dropshipping 泰国仓产品导入 WooCommerce
 *
 * 用法（CLI，需在 WP 根目录执行）：
 *   php scripts/cj-import/cj-import.php list                 阶段1：抓 CJT 产品列表到 staging
 *   php scripts/cj-import/cj-import.php import [LIMIT]       阶段2：详情抓取 + WC 导入（默认全量）
 *   php scripts/cj-import/cj-import.php retry [LIMIT]        阶段2b：重试 state.failed 里的 pid
 *   php scripts/cj-import/cj-import.php images [LIMIT]       阶段3：产品图片搬运到 R2
 *   php scripts/cj-import/cj-import.php status               查看进度
 *
 * 断点续跑：state.json 记录游标；SKU 查重防重复。
 * 价格规则：CJ 价 × 1.5；发布状态：draft；仓库：CJT（泰国仓）。
 */

if (PHP_SAPI !== 'cli') { exit("CLI only\n"); }

$_CJ = array(
    'email'     => getenv('CJ_EMAIL') ?: 'talleylin45@icloud.com',
    'password'  => getenv('CJ_PASSWORD') ?: 'CJ5868700@api@3b77f49531ab4ce8be95165d80719cc1',
    'api'       => 'https://developers.cjdropshipping.com/api2.0',
    'skuPrefix' => 'CJT',
    'markup'    => 1.5,
    'warehouse' => 'CJT',
    'staging'   => __DIR__ . '/staging.jsonl',
    'state'     => __DIR__ . '/state.json',
    'tokenFile' => __DIR__ . '/.cj_token',
    'imgState'  => __DIR__ . '/images-state.json',
    'pendingList' => __DIR__ . '/images-pending',
    'r2'        => array(
        'bucket'   => 'didi-ai-mirror',
        'accountId'=> getenv('CF_ACCOUNT_ID') ?: '2e33f078edc00ade4b25e61526d6f544',
        'token'    => getenv('CF_API_TOKEN') ?: '',
        'publicBase' => 'https://img.chacha.asia',
    ),
);

function stateLoad($_CJ) {
    if (file_exists($_CJ['state'])) return json_decode(file_get_contents($_CJ['state']), true);
    return array('listPage' => 0, 'listDone' => false, 'importCursor' => 0, 'imported' => 0, 'skipped' => 0, 'failed' => array());
}
function stateSave($_CJ, $s) { file_put_contents($_CJ['state'], json_encode($s, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)); }

/** CJ 认证：缓存 token，401 自动刷新 */
function cjToken($_CJ, $force = false) {
    if (!$force && file_exists($_CJ['tokenFile'])) {
        $t = trim(file_get_contents($_CJ['tokenFile']));
        if ($t) return $t;
    }
    $res = cjHttp($_CJ, '/v1/authentication/getAccessToken', array(
        'method' => 'POST',
        'json'   => array('email' => $_CJ['email'], 'password' => $_CJ['password']),
        'auth'   => false,
    ));
    if (empty($res['data']['accessToken'])) {
        fwrite(STDERR, "CJ 认证失败: " . json_encode($res, JSON_UNESCAPED_UNICODE) . "\n");
        exit(1);
    }
    file_put_contents($_CJ['tokenFile'], $res['data']['accessToken']);
    return $res['data']['accessToken'];
}

function cjHttp($_CJ, $path, $opt = array()) {
    $method = isset($opt['method']) ? $opt['method'] : 'GET';
    $url = $_CJ['api'] . $path;
    if (!empty($opt['query'])) $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($opt['query']);
    $ch = curl_init($url);
    $headers = array('Content-Type: application/json');
    if (!isset($opt['auth']) || $opt['auth']) $headers[] = 'CJ-Access-Token: ' . cjToken($_CJ, !empty($opt['forceToken']));
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 40,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true,
    ));
    if (!empty($opt['json'])) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($opt['json']));
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false) return array('success' => false, 'message' => 'curl: ' . $err, 'curlErrno' => 1);
    $data = json_decode($body, true);
    if (!is_array($data)) return array('success' => false, 'message' => 'bad json', 'http' => $code);
    $data['_http'] = $code;
    return $data;
}

/** 带限速/重试/429退避的 CJ 请求 */
function cjGet($_CJ, $path, $query, $tries = 4) {
    static $lastReq = 0;
    $gap = 0.35;
    for ($i = 1; $i <= $tries; $i++) {
        $wait = ($lastReq + $gap) - microtime(true);
        if ($wait > 0) usleep((int)($wait * 1e6));
        $lastReq = microtime(true);
        $res = cjHttp($_CJ, $path, array('query' => $query));
        $msg = isset($res['message']) ? $res['message'] : '';
        if (isset($res['_http']) && $res['_http'] === 429) {
            fwrite(STDERR, "  429 限流，等待 30s（第 $i 次）\n");
            sleep(30);
            continue;
        }
        if (strpos($msg, 'token') !== false && (strpos($msg, 'expire') !== false || strpos($msg, 'invalid') !== false || strpos($msg, 'empty') !== false)) {
            cjToken($_CJ, true);
            continue;
        }
        if (!empty($res['result']) || (isset($res['code']) && $res['code'] === 200)) return $res;
        if ($i < $tries) { usleep(800000 * $i); continue; }
        return $res;
    }
    return array('success' => false, 'message' => 'exhausted');
}

require dirname(__DIR__, 2) . '/wp-load.php';
if (!class_exists('WC_Product_Simple')) {
    fwrite(STDERR, "WooCommerce 未加载，中止\n");
    exit(1);
}

require __DIR__ . '/import-tasks.php';
cjImportMain($argv, $_CJ);
