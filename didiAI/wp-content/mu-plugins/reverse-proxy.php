<?php
/**
 * 动态站点地址：跟随当前 HTTP_HOST
 * 解决通过平台预览域名访问时，站点链接仍指向 localhost:8080 导致登录后台打不开的问题
 * 本地访问（HTTP_HOST=localhost:8080）行为与原 siteurl 完全一致
 */

function didi_dyn_host_scheme() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') return '';
    $https = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
        || substr($host, -21) === '.monkeycode-ai.online';
    return ($https ? 'https' : 'http') . '://' . $host;
}

function didi_dyn_url($v) {
    $u = didi_dyn_host_scheme();
    return $u !== '' ? $u : $v;
}
add_filter('pre_option_siteurl', 'didi_dyn_url');
add_filter('pre_option_home', 'didi_dyn_url');
