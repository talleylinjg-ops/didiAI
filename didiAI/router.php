<?php
$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
$file = __DIR__ . $path;
if ( $path !== '/' && file_exists( $file ) && ! is_dir( $file ) ) {
	// 静态文件自管：php -S 内置处理器会丢弃自定义头，这里自己输出以提供长缓存与 304
	if ( !preg_match( '#\.php$#i', $path ) && preg_match( '#\.(css|js|mjs|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|eot|mp3|wav|m4a|aac|mp4|webm|json|txt|xml)$#i', $path ) ) {
		$mimeMap = array(
			'css' => 'text/css; charset=utf-8', 'js' => 'text/javascript; charset=utf-8', 'mjs' => 'text/javascript; charset=utf-8',
			'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
			'woff2' => 'font/woff2', 'woff' => 'font/woff', 'ttf' => 'font/ttf', 'eot' => 'application/vnd.ms-fontobject',
			'mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'm4a' => 'audio/mp4', 'aac' => 'audio/aac',
			'mp4' => 'video/mp4', 'webm' => 'video/webm',
			'json' => 'application/json; charset=utf-8', 'txt' => 'text/plain; charset=utf-8', 'xml' => 'application/xml',
		);
		$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
		$mtime = filemtime( $file );
		$maxAge = ( strpos( $path, '/wp-content/uploads/' ) === 0 ) ? 2592000 : 31536000;
		$ims = isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ? strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) : false;
		header( 'Content-Type: ' . ( isset( $mimeMap[$ext] ) ? $mimeMap[$ext] : 'application/octet-stream' ) );
		header( 'Cache-Control: public, max-age=' . $maxAge . ', immutable' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $mtime ) . ' GMT' );
		if ( $ims !== false && $ims >= $mtime ) {
			http_response_code( 304 );
			exit;
		}
		header( 'Content-Length: ' . filesize( $file ) );
		readfile( $file );
		exit;
	}
	return false;
}
if ( preg_match( '#^/wp-admin(/|$)#', $path ) ) {
	require __DIR__ . '/wp-admin/index.php';
	return true;
}
require __DIR__ . '/index.php';
