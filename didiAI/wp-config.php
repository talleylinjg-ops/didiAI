<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_db' );

/** Database username */
define( 'DB_USER', 'wp_user' );

/** Database password */
define( 'DB_PASSWORD', 'wp_pass_2026' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'p_=9^&!z<nf?=oe1ZN!bAdNn-!1B`@eSw(IWnx_Gg^%GyG</w_+k(ZhT=|xM4@P8');
define('SECURE_AUTH_KEY', 'Ihx1B&lF}aYhV;-<|@-}vzz-<?Gk- 3Ln4UV}~,+s*azMQH[!2Z_+@}eUcHadZPk');
define('LOGGED_IN_KEY', 'U()6?:tdyn0SY|0unS:HFk|#u6S+}K8+EHL;qy_c$do8P||P1t}X]<P?swFv:gj)');
define('NONCE_KEY', ';x} MMLC>M+i%p;={|RFJs-GR5Xc;F(CoXm.[$!{-*S_D9A2Dto_frxomJ-5<7dd');
define('AUTH_SALT', '92d>fC*1_o&(NG*YV},B!*6MvsIvK,X`vdiO&_Z~L0s)BxI9-,mDEA7fJUKJnAO6');
define('SECURE_AUTH_SALT', 'Ux5:cmPT*Bidb6{MEx0++wRR>ce2Q1aIz;)Hsvr7]Fhf[iBI2eB$EDw4zF1q~{u)');
define('LOGGED_IN_SALT', 'd8-hwY;$=J}4nHBAj3+:3wK-Qm6XLJ#!MCk7^Ot xY(4Y>!%s&o^Yk`|iE(.A_;u');
define('NONCE_SALT', 'n(%9Tu};iptnyIn)vyvANsh(>ecF:y*vF.KZr|{2[jp=<(^EY]g#N1]:_y|B9y~]');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

// 预览环境：外部经 https 代理访问，php -S 后端收到的为 http，需让 WP 识别 https
if ( isset( $_SERVER['HTTP_HOST'] ) && strpos( $_SERVER['HTTP_HOST'], '.monkeycode-ai.online' ) !== false ) {
	$_SERVER['HTTPS'] = 'on';
	$_SERVER['SERVER_PORT'] = 443;
	if ( ! defined( 'WP_HOME' ) ) {
		define( 'WP_HOME', 'https://' . $_SERVER['HTTP_HOST'] );
		define( 'WP_SITEURL', 'https://' . $_SERVER['HTTP_HOST'] );
	}
}


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
