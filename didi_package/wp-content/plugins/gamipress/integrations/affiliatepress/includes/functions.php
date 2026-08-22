<?php
/**
 * Functions
 *
 * @package     GamiPress\Integrations\AffilatePress
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get WordPress user ID by affiliate ID
 *
 * @since 1.0.0
 * 
 * @param int $affiliate_id 
 */
function gamipress_affiliatepress_get_user_id ( $affiliate_id ) {

    global $wpdb;
    
    $table_name = $wpdb->prefix . 'affiliatepress_affiliates'; 
    $user_id = $wpdb->get_var( $wpdb->prepare( "SELECT ap_affiliates_user_id FROM $table_name WHERE ap_affiliates_id = %d", $affiliate_id ) );

    return $user_id;

}

/**
 * Get affiliate ID by payout ID
 *
 * @since 1.0.0
 * 
 * @param int $payout_id 
 */
function gamipress_affiliatepress_get_affiliate_id_by_payout ( $payout_id ) {

    global $wpdb;
    
    $table_name = $wpdb->prefix . 'affiliatepress_payouts'; 
    $affiliate_id = $wpdb->get_var( $wpdb->prepare( "SELECT ap_payout_selected_affiliate FROM $table_name WHERE ap_payout_id = %d", $payout_id ) );

    return $affiliate_id;

}
