<?php
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$file = __DIR__ . $path;
if ( $path !== '/' && file_exists( $file ) && ! is_dir( $file ) ) {
	return false;
}
if ( preg_match( '#^/wp-admin(/|$)#', $path ) ) {
	require __DIR__ . '/wp-admin/index.php';
	return true;
}
require __DIR__ . '/index.php';
