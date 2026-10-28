<?php
/**
 * @package CJdropshipping
 * @version 1.0.0
 */
/*
Plugin Name: CJdropshipping 
Plugin URI: https://wordpress.org/plugins/cjdropshipping
Description: A simple jump plugin.
Author: cjdropshipping
Author URI: https://cjdropshipping.com/
Version: 1.0.0
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

/** 第1步：创建自定义菜单的函数**/
function cjdsp_add_plugin_menu()
{
    add_menu_page('CJDropshipping', 'CJDropshipping', 'manage_options', 'cjdsp_plugin_manager', 'cjdsp_plugin_options', '', 10);

}

/** 第2步：将函数注册到钩子中 */
add_action('admin_menu', 'cjdsp_add_plugin_menu');

register_activation_hook(__FILE__, 'cjdsp_plugin_activate');
add_action('admin_init', 'cjdsp_plugin_redirect');

function cjdsp_plugin_activate() {
    add_option('my_plugin_do_activation_redirect', true);
}

function cjdsp_plugin_redirect() {
    if (get_option('my_plugin_do_activation_redirect', false)) {
        delete_option('my_plugin_do_activation_redirect');
        wp_redirect(('admin.php?page=cjdsp_plugin_manager'));
    }
}

/** 第3步：定义选项被点击时打开的页面 */
function cjdsp_plugin_options()
{
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    //include_once(plugin_dir_path(__FILE__) . 'detail/index.php');

    //也可以直接返回HTML，不过我建议是额外放一个文件，这样以后维护起来好处理
    //你可以直接 echo "hello world"
    echo '<div class="cj-content">
            <div class="cj-header">
                <img src="' . esc_url( plugins_url('/images',__FILE__) ) . '/logo.png" />
            </div>
            <div class="cj-body">
                <h1 class="text-h1">About CJdropshipping</h1>
                <h2 class="text-h2">CJdropshipping Features</h2>
                <div class="text-body">
                    <div class="item-content">· Import products to your WooCommerce stores with just a few clicks </div>
                    <div class="item-content">· Free product sourcing service, and thousands of POD products</div>
                    <div class="item-content">· Sourcing from 1688 and Taobao directly</div>
                </div>
                <h2 class="text-h2">CJdropshipping Service</h2>
                <div class="text-body">
                    <div class="item-content">· Easily list and source any products into your WooCommerce stores, normally price lower than AliExpress, and thousands of POD
                    products available</div>
                    <div class="item-content">· Convenient order fulfillment service, everything automatically syncs with your WooCommerce stores, CSV orders available too</div>
                    <div class="item-content">· As fast as same day processing, shipping from US warehouse directly to your US customers</div>
                    <div class="item-content">· Brand packing and insert available, quality control before dispatching, almost one by one checking</div>
                    <div class="item-content">· Track your orders anytime and make the delivery analysis</div>
                    <div class="item-content">· The team working with your team and almost 24/7 online support</div>
                </div>
                <h2 class="text-h2">Free to install</h2>
                <div class="text-body">
                    <div class="item-content">No app usage fee required, free to install. Additional charges may apply.</div>
                </div>
                <div class="text-bottom">Connect WooCommerce with CJdropshipping now!</div>
                <div class="text-footer">
                    <div class="footer-link">
                        <a href="https://cjdropshipping.com" target="_blank">Go to CJdropshipping</a>
                    </div>
                    <div class="footer-link">
                        <a href="https://m.cjdropshipping.com/mycj/add-authorization?type=woocommerce" target="_blank">Connect</a>
                    </div>
                </div>
                <div class="grey-discribe">No app usage fee required, free to install. Additional charges may apply.</div>
            </div>
        </div>';
    wp_die();
}

function cjdsp_add_css(){
    echo "
	<style type='text/css'>
    .cj-content{
        padding: 24px 56px 0 56px;
    }
    .cj-content .cj-header{
        height: 40px;
        width: 100%;
    }
    .cj-content .cj-header .header-img{
        width: 200px;
        height: 40px;
        border: none;
    }
    .cj-content .cj-body{
        width: 816px;
        height: auto;
        margin: 0 auto;
    }
    .cj-content .cj-body .text-h1{
        width: 100%;
        height: 45px;
        font-size: 32px;
        color: #333;
        line-height: 45px;
        margin: 36px 0 8px 0;
    }
    .cj-content .cj-body .text-h2{
        width: 100%;
        height: 32px;
        font-size: 24px;
        color: #333;
        line-height: 32px;
        margin: 32px 0;
    }
    .cj-content .cj-body .text-body{
        width: 100%;
        height: auto;
    }
    .cj-content .cj-body .text-body .item-content{
        width: 100%;
        height: auto;
        font-size: 16px;
        line-height: 32px;
        color: #333;
    }
    .cj-content .cj-body .text-bottom{
        width: 100%;
        height: 32px;
        line-height: 32px;
        font-size: 18px;
        color: #333;
        text-align: center;
        margin-top: 40px;
    }
    .cj-content .cj-body .text-footer{
        width: 100%;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 12px;
    }
    .cj-content .cj-body .text-footer .footer-link{
        width: auto;
        height: 40px;
        border-radius: 4px;
        background: #FF7701;
        color: #fff;
        font-size: 20px;
        line-height: 40px;
        text-align: center;
        cursor: pointer;
        margin-right: 20px;
        padding: 0 16px;
    }
    .cj-content .cj-body .text-footer .footer-link a{
        color: #fff;
        text-decoration: none;
        outline: none;
    }
    .cj-content .cj-body .text-footer .footer-link:hover{
        background: #E56A00;
    }
    .cj-content .cj-body .grey-discribe{
        width: 100%;
        height: 32px;
        line-height: 32px;
        text-align: center;
        margin-top: 22px;
        color: #999;
        font-size: 14px;
    }
	</style>
	";
}

add_action( 'admin_head', 'cjdsp_add_css' );


