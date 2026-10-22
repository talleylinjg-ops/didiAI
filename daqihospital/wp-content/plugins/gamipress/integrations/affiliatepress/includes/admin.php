<?php
/**
 * Admin
 *
 * @package GamiPress\AffiliatePress\Admin
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Plugin automatic updates
 *
 * @since  1.0.0
 *
 * @param array $automatic_updates_plugins
 *
 * @return array
 */
function gamipress_affiliatepress_automatic_updates( $automatic_updates_plugins ) {

    $automatic_updates_plugins['gamipress'] = __( 'AffiliatePress integration', 'gamipress' );

    return $automatic_updates_plugins;
}
add_filter( 'gamipress_automatic_updates_plugins', 'gamipress_affiliatepress_automatic_updates' );