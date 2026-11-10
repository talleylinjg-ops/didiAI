<?php
/**
 * Plugin Name: Disable XML-RPC
 * Description: 彻底关闭 XML-RPC：HTTP 层 403 + 清空方法表，关闭暴力破解与 pingback 攻击面。
 */

// HTTP 层直接 403，不加载 WP 主体逻辑
add_action('init', function () {
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
        http_response_code(403);
        exit('XML-RPC disabled');
    }
}, 1);

// 清空方法表（双保险：即使 403 被绕过也无可调用方法）
add_filter('xmlrpc_methods', '__return_empty_array');

// 禁用需要认证的 XML-RPC 方法
add_filter('xmlrpc_enabled', '__return_false');
