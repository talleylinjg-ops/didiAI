<?php
/**
 * Listeners
 *
 * @package GamiPress\AffiliatePress\Listeners
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Become affiliate listener
 *
 * @since 1.0.0
 *
 * @param array $affiliate_id
 *
 * @return mixed
 */
function gamipress_affiliatepress_become_affiliate_listener( $affiliate_id ) {

    $user_id = gamipress_affiliatepress_get_user_id( $affiliate_id );

    // Bail if no user id
    if ( empty( $user_id ) ) {
        return; 
    }

    // Trigger become affiliate
    do_action( 'gamipress_affiliatepress_become_affiliate', $user_id );

}
add_action( 'affiliatepress_after_signup_affiliate', 'gamipress_affiliatepress_become_affiliate_listener' );


/**
 * Earn commission listener
 *
 * @since 1.0.0
 *
 * @param int $commission_id
 * @param array $commission_data
 *
 * @return mixed
 */
function gamipress_affiliatepress_earn_commission_listener( $commission_id, $commission_data ) {

    $affiliate_id = $commission_data['ap_affiliates_id'];
    
    $user_id = gamipress_affiliatepress_get_user_id( $affiliate_id );

    // Bail if no user id
    if ( empty( $user_id ) ) {
            return; 
    }

    // Trigger become affiliate
    do_action( 'gamipress_affiliatepress_earn_commission', $user_id );

}
add_action( 'affiliatepress_after_commission_created', 'gamipress_affiliatepress_earn_commission_listener', 10, 2 );

/**
 * Get payment listener
 *
 * @since 1.0.0
 *
 * @param array $affiliatepress_payout_id
 *
 * @return mixed
 */
function gamipress_affiliatepress_get_payment_listener( $affiliatepress_payout_id ) {

    $affiliate_id = gamipress_affiliatepress_get_affiliate_id_by_payout( $affiliatepress_payout_id );
    $user_id = gamipress_affiliatepress_get_user_id( $affiliate_id );

    // Bail if no user id
    if ( empty( $user_id ) ) {
        return; 
    }

    // Trigger become affiliate
    do_action( 'gamipress_affiliatepress_get_payment', $user_id );

}
add_action( 'affiliatepress_after_payout_generate', 'gamipress_affiliatepress_get_payment_listener' );
