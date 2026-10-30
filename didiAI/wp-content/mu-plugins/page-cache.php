<?php
/**
 * didi AI 轻量页面缓存（匿名 GET 请求）
 * 条件：GET + 无 cookie + 200 响应 + text/html + 非后台/接口/购物页
 * TTL 10 分钟；内容变动自动清空
 */

define('DIDI_PAGE_CACHE_DIR', WP_CONTENT_DIR . '/cache/pages');
define('DIDI_PAGE_CACHE_TTL', 600);

function didi_page_cache_key() {
    return md5((isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''));
}

function didi_page_cache_purge_all() {
    if (!is_dir(DIDI_PAGE_CACHE_DIR)) return;
    foreach (glob(DIDI_PAGE_CACHE_DIR . '/*.html*') as $f) @unlink($f);
}

function didi_page_cache_skip() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return true;
    if (!empty($_COOKIE)) return true;
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (preg_match('#^/(wp-admin|wp-login\.php|wp-json|xmlrpc\.php|wp-cron\.php|cart|checkout|my-account|feed|robots\.txt|llms\.txt|wp-sitemap|sitemap)#', $uri)) return true;
    return false;
}

function didi_page_cache_hit() {
    if (didi_page_cache_skip()) return false;
    if (isset($_GET['s'], $_GET['q'], $_GET['paged'], $_GET['cat'], $_GET['tag'])) return false;
    $file = DIDI_PAGE_CACHE_DIR . '/' . didi_page_cache_key() . '.html';
    if (!is_readable($file)) return false;
    $meta = json_decode((string) @file_get_contents($file . '.meta'), true);
    if (!$meta || (time() - $meta['ts']) > DIDI_PAGE_CACHE_TTL) { @unlink($file); @unlink($file . '.meta'); return false; }
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Cache: HIT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $meta['ts']) . ' GMT');
    readfile($file);
    exit;
}

function didi_page_cache_store($html) {
    $ct = function_exists('headers_list') ? '' : '';
    if (http_response_code() !== 200 || strlen($html) < 1024 || stripos($html, '<html') === false) return $html;
    $ctype = '';
    foreach (headers_list() as $h) { if (stripos($h, 'Content-Type:') === 0) $ctype = $h; }
    if ($ctype && stripos($ctype, 'text/html') === false) return $html;
    if (!is_dir(DIDI_PAGE_CACHE_DIR)) wp_mkdir_p(DIDI_PAGE_CACHE_DIR);
    $file = DIDI_PAGE_CACHE_DIR . '/' . didi_page_cache_key() . '.html';
    @file_put_contents($file, $html, LOCK_EX);
    @file_put_contents($file . '.meta', json_encode(array('ts' => time())));
    header('X-Cache: MISS');
    return $html;
}

if (!is_admin() && !defined('WP_CLI') && php_sapi_name() !== 'cli') {
    if (!didi_page_cache_hit()) {
        ob_start('didi_page_cache_store');
    }
}

add_action('transition_post_status', 'didi_page_cache_purge_all', 10, 0);
add_action('deleted_post', 'didi_page_cache_purge_all', 10, 0);
add_action('wp_insert_comment', 'didi_page_cache_purge_all', 10, 0);
add_action('profile_update', 'didi_page_cache_purge_all', 10, 0);
