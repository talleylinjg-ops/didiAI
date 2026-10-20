<?php
/** CJ 抓取公共库（deep-fetch.php 使用）：bootstrap WP 非必需，纯 API 抓取 */

$_CJ = array(
    'email'     => getenv('CJ_EMAIL') ?: 'talleylin45@icloud.com',
    'password'  => getenv('CJ_PASSWORD') ?: 'CJ5868700@api@3b77f49531ab4ce8be95165d80719cc1',
    'api'       => 'https://developers.cjdropshipping.com/api2.0',
    'staging'   => __DIR__ . '/staging.jsonl',
    'tokenFile' => __DIR__ . '/.cj_token',
    'prefixState' => __DIR__ . '/prefixes-done.json',
);

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
    if (empty($res['data']['accessToken'])) { fwrite(STDERR, "认证失败\n"); exit(1); }
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
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 40,
        CURLOPT_CUSTOMREQUEST => $method, CURLOPT_HTTPHEADER => $headers,
    ));
    if (!empty($opt['json'])) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($opt['json']));
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($body === false) return array('success' => false, 'message' => 'curl err');
    $data = json_decode($body, true);
    if (!is_array($data)) return array('success' => false, 'message' => 'bad json', 'http' => $code);
    $data['_http'] = $code;
    return $data;
}

function cjGet($_CJ, $path, $query, $tries = 4) {
    static $lastReq = 0;
    $gap = 0.3;
    for ($i = 1; $i <= $tries; $i++) {
        $wait = ($lastReq + $gap) - microtime(true);
        if ($wait > 0) usleep((int)($wait * 1e6));
        $lastReq = microtime(true);
        $res = cjHttp($_CJ, $path, array('query' => $query));
        $msg = isset($res['message']) ? $res['message'] : '';
        if (isset($res['_http']) && $res['_http'] === 429) { fwrite(STDERR, "  429, sleep 30\n"); sleep(30); continue; }
        if (strpos($msg, 'token') !== false && (strpos($msg, 'expire') !== false || strpos($msg, 'invalid') !== false || strpos($msg, 'empty') !== false)) { cjToken($_CJ, true); continue; }
        if (!empty($res['result']) || (isset($res['code']) && $res['code'] === 200)) return $res;
        if ($i < $tries) { usleep(700000 * $i); continue; }
        return $res;
    }
    return array('success' => false, 'message' => 'exhausted');
}

function cjPrefixTotal($_CJ, $prefix) {
    $res = cjGet($_CJ, '/v2/product/list', array('pageNum' => 1, 'pageSize' => 1, 'productSku' => $prefix));
    if (empty($res['data']['total']) && !empty($res['message']) && stripos($res['message'], 'max offset') !== false) return -1;
    return isset($res['data']['total']) ? intval($res['data']['total']) : -2;
}

function cjPrefixDone($_CJ) {
    if (file_exists($_CJ['prefixState'])) return json_decode(file_get_contents($_CJ['prefixState']), true) ?: array();
    return array();
}
function cjMarkDone($_CJ, $p, &$done) {
    $done[] = $p;
    file_put_contents($_CJ['prefixState'], json_encode($done));
}

function cjStagingPids($_CJ) {
    $pids = array();
    if (file_exists($_CJ['staging'])) {
        foreach (file($_CJ['staging'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
            $o = json_decode($l, true);
            if ($o && !empty($o['pid'])) $pids[$o['pid']] = 1;
        }
    }
    return $pids;
}

function cjAppend($_CJ, $list, &$pids) {
    $line = '';
    $n = 0;
    foreach ($list as $p) {
        if (empty($p['pid']) || isset($pids[$p['pid']])) continue;
        $pids[$p['pid']] = 1;
        $line .= json_encode($p, JSON_UNESCAPED_UNICODE) . "\n";
        $n++;
    }
    if ($line) file_put_contents($_CJ['staging'], $line, FILE_APPEND | LOCK_EX);
    return $n;
}

function cjStagingCount($_CJ) {
    if (!file_exists($_CJ['staging'])) return 0;
    $n = 0; $fp = fopen($_CJ['staging'], 'r');
    while (fgets($fp) !== false) $n++;
    fclose($fp);
    return $n;
}

function cjDeepFetch($startPrefix) {
    global $_CJ;
    $done = cjPrefixDone($_CJ);
    $pids = cjStagingPids($_CJ);
    $stack = array($startPrefix);
    $chars = array_merge(range('A', 'Z'), range('0', '9'));
    $pageSize = 100;
    while ($stack) {
        $prefix = array_shift($stack);
        if (in_array($prefix, $done, true)) continue;
        $total = cjPrefixTotal($_CJ, $prefix);
        if ($total === -2) { fwrite(STDERR, "[$prefix] 计数失败，跳过\n"); continue; }
        if ($total === 0) { cjMarkDone($_CJ, $prefix, $done); continue; }
        if ($total === -1 || $total > 6000) {
            if ($total === -1) $total = -1;
            // 需要细分：枚举下一字符
            $subs = 0;
            foreach ($chars as $ch) {
                $sub = $prefix . $ch;
                $t2 = cjPrefixTotal($_CJ, $sub);
                if ($t2 > 0) {
                    $subs += $t2;
                    array_push($stack, $sub);
                    echo "[$prefix] 子前缀 $sub => $t2\n";
                } else if ($t2 === -1) {
                    fwrite(STDERR, "[$sub] 仍超 6000，将在后续递归细分\n");
                    array_push($stack, $sub);
                }
            }
            cjMarkDone($_CJ, $prefix, $done);
            echo "[$prefix] total=$total 细分完成（子合计 $subs）\n";
            continue;
        }
        // 可整段抓取
        $pages = (int)ceil($total / $pageSize);
        $got = 0;
        for ($pg = 1; $pg <= $pages; $pg++) {
            $res = cjGet($_CJ, '/v2/product/list', array('pageNum' => $pg, 'pageSize' => $pageSize, 'productSku' => $prefix));
            if (empty($res['data']['list'])) break;
            $got += cjAppend($_CJ, $res['data']['list'], $pids);
        }
        cjMarkDone($_CJ, $prefix, $done);
        echo "[$prefix] 抓取 $got/$total（staging 累计 " . cjStagingCount($_CJ) . "）\n";
;
    }
}
