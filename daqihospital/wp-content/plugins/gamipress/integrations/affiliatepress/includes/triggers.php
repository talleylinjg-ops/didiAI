<?php
/**
 * Triggers
 *
 * @package GamiPress\AffiliatePress\Triggers
 * @since 1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin triggers
 *
 * @since  1.0.0
 *
 * @param array $triggers
 * @return mixed
 */
function gamipress_affiliatepress_activity_triggers( $triggers ) {

    $triggers[__( 'AffiliatePress', 'gamipress' )] = array(

        'gamipress_affiliatepress_become_affiliate' => __( 'Become as affiliate', 'gamipress' ),
        'gamipress_affiliatepress_earn_commission'  => __( 'Earn a commission', 'gamipress' ),
        'gamipress_affiliatepress_get_payment'      => __( 'Get a generated payment', 'gamipress' ),
        
    );

    return $triggers;

}
add_filter( 'gamipress_activity_triggers', 'gamipress_affiliatepress_activity_triggers' );

/**
 * Get user for a given trigger action.
 *
 * @since  1.0.0
 *
 * @param  integer $user_id user ID to override.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 * @return integer          User ID.
 */
function gamipress_affiliatepress_trigger_get_user_id( $user_id, $trigger, $args ) {

    switch ( $trigger ) {
        case 'gamipress_affiliatepress_become_affiliate':
        case 'gamipress_affiliatepress_earn_commission':
        case 'gamipress_affiliatepress_get_payment':
            $user_id = $args[0];
            break;
    }

    return $user_id;

}
add_filter( 'gamipress_trigger_get_user_id', 'gamipress_affiliatepress_trigger_get_user_id', 10, 3 );
