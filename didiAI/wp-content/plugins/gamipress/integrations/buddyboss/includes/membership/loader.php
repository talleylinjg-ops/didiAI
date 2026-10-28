<?php
/**
 * Loader
 *
 * @package GamiPress\Membership\Loader
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Plugin version
define( 'GAMIPRESS_BBMEMBERSHIP_VER', '1.3.7' );

// Plugin file
define( 'GAMIPRESS_BBMEMBERSHIP_FILE', __FILE__ );

// Plugin path
define( 'GAMIPRESS_BBMEMBERSHIP_DIR', plugin_dir_path( __FILE__ ) );

// Plugin URL
define( 'GAMIPRESS_BBMEMBERSHIP_URL', plugin_dir_url( __FILE__ ) );

require_once GAMIPRESS_BBMEMBERSHIP_DIR . 'includes/functions.php';
require_once GAMIPRESS_BBMEMBERSHIP_DIR . 'includes/listeners.php';
require_once GAMIPRESS_BBMEMBERSHIP_DIR . 'includes/triggers.php';
