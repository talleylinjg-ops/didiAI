<?php
/**
 * Triggers
 *
 * @package GamiPress\FluentCart\Triggers
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin specific triggers
 *
 * @since  1.0.0
 *
 * @param array $triggers
 * @return mixed
 */
function gamipress_fluentcart_activity_triggers( $triggers ) {

    $triggers[__( 'FluentCart', 'gamipress' )] = array(
        
        'gamipress_fluentcart_new_purchase'                     => __( 'Make a new purchase', 'gamipress' ),
        'gamipress_fluentcart_product_purchase'                 => __( 'Purchase a product', 'gamipress' ),
        'gamipress_fluentcart_specific_product_purchase'        => __( 'Purchase a specific product', 'gamipress' ),
        'gamipress_fluentcart_purchase_full_refund'             => __( 'Refund fully a purchase', 'gamipress' ),
        'gamipress_fluentcart_purchase_partial_refund'          => __( 'Refund partially a purchase', 'gamipress' ),
        'gamipress_fluentcart_subscription_activated'           => __( 'Activate a subscription', 'gamipress' ),
        'gamipress_fluentcart_specific_subscription_activated'  => __( 'Activate a subscription of a specific product', 'gamipress' ),
        'gamipress_fluentcart_subscription_canceled'            => __( 'Cancel a subscription', 'gamipress' ),
        'gamipress_fluentcart_specific_subscription_canceled'   => __( 'Cancel a subscription of a specific product', 'gamipress' ),
        'gamipress_fluentcart_subscription_renewed'             => __( 'Renew a subscription', 'gamipress' ),
        'gamipress_fluentcart_specific_subscription_renewed'    => __( 'Renew a subscription of a specific product', 'gamipress' ),
    );

    return $triggers;

}
add_filter( 'gamipress_activity_triggers', 'gamipress_fluentcart_activity_triggers' );

/**
 * Register plugin specific activity triggers
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_triggers
 * @return array
 */
function gamipress_fluentcart_specific_activity_triggers( $specific_activity_triggers ) {

    $specific_activity_triggers['gamipress_fluentcart_specific_product_purchase'] = array( 'fluent-products' );
    $specific_activity_triggers['gamipress_fluentcart_specific_subscription_activated'] = array( 'fluent-products' );
    $specific_activity_triggers['gamipress_fluentcart_specific_subscription_canceled'] = array( 'fluent-products' );
    $specific_activity_triggers['gamipress_fluentcart_specific_subscription_renewed'] = array( 'fluent-products' );

    return $specific_activity_triggers;

}
add_filter( 'gamipress_specific_activity_triggers', 'gamipress_fluentcart_specific_activity_triggers' );

/**
 * Register plugin specific activity triggers labels
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_trigger_labels
 * @return array
 */
function gamipress_fluentcart_specific_activity_trigger_label( $specific_activity_trigger_labels ) {

    $specific_activity_trigger_labels['gamipress_fluentcart_specific_product_purchase'] = __( 'Purchase %s', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_fluentcart_specific_subscription_activated'] = __( 'Activate subscription for product %s', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_fluentcart_specific_subscription_canceled'] = __( 'Cancel subscription for product %s', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_fluentcart_specific_subscription_renewed'] = __( 'Renew subscription for product %s', 'gamipress' );

    return $specific_activity_trigger_labels;
}
add_filter( 'gamipress_specific_activity_trigger_label', 'gamipress_fluentcart_specific_activity_trigger_label' );

/**
 * Get plugin specific activity trigger permalink
 *
 * @since  1.0.0
 *
 * @param  string   $permalink
 * @param  integer  $specific_id
 * @param  string   $trigger_type
 * @param  integer  $site_id
 *
 * @return string
 */
function gamipress_fluentcart_specific_activity_trigger_permalink( $permalink, $specific_id, $trigger_type, $site_id ) {

    switch( $trigger_type ) {
        case 'gamipress_fluentcart_specific_product_purchase':
        case 'gamipress_fluentcart_specific_subscription_activated':
        case 'gamipress_fluentcart_specific_subscription_canceled':
        case 'gamipress_fluentcart_specific_subscription_renewed':
            $permalink = '';
            break;
    }

    return $permalink;

}
add_filter( 'gamipress_specific_activity_trigger_permalink', 'gamipress_fluentcart_specific_activity_trigger_permalink', 10, 4 );

/**
 * Get user for a fluentcart trigger action.
 *
 * @since  1.0.0
 *
 * @param  integer $user_id user ID to override.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 * @return integer          User ID.
 */
function gamipress_fluentcart_trigger_get_user_id( $user_id, $trigger, $args ) {

    switch ( $trigger ) {
        
        case 'gamipress_fluentcart_new_purchase':
        case 'gamipress_fluentcart_product_purchase':
        case 'gamipress_fluentcart_specific_product_purchase':
        case 'gamipress_fluentcart_purchase_full_refund':
        case 'gamipress_fluentcart_purchase_partial_refund':
        case 'gamipress_fluentcart_subscription_activated':
        case 'gamipress_fluentcart_specific_subscription_activated':
        case 'gamipress_fluentcart_subscription_canceled':
        case 'gamipress_fluentcart_specific_subscription_canceled':
        case 'gamipress_fluentcart_subscription_renewed':
        case 'gamipress_fluentcart_specific_subscription_renewed':
            $user_id = $args[1];
            break;
    }

    return $user_id;

}
add_filter( 'gamipress_trigger_get_user_id', 'gamipress_fluentcart_trigger_get_user_id', 10, 3 );

/**
 * Get the id for a fluentcart specific trigger action.
 *
 * @since  1.0.0
 *
 * @param  integer  $specific_id Specific ID.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 *
 * @return integer          Specific ID.
 */
function gamipress_fluentcart_specific_trigger_get_id( $specific_id, $trigger = '', $args = array() ) {

    switch ( $trigger ) {
        
        case 'gamipress_fluentcart_specific_product_purchase':
        case 'gamipress_fluentcart_specific_subscription_activated':
        case 'gamipress_fluentcart_specific_subscription_canceled':
        case 'gamipress_fluentcart_specific_subscription_renewed':
            $specific_id = $args[0];
            break;
    }

    return $specific_id;
}
add_filter( 'gamipress_specific_trigger_get_id', 'gamipress_fluentcart_specific_trigger_get_id', 10, 3 );

/**
 * Extended meta data for event trigger logging
 *
 * @since 1.0.0
 *
 * @param array 	$log_meta
 * @param integer 	$user_id
 * @param string 	$trigger
 * @param integer 	$site_id
 * @param array 	$args
 *
 * @return array
 */
function gamipress_fluentcart_log_event_trigger_meta_data( $log_meta, $user_id, $trigger, $site_id, $args ) {

    switch ( $trigger ) {
        case 'gamipress_fluentcart_product_purchase':
        case 'gamipress_fluentcart_specific_product_purchase':
        case 'gamipress_fluentcart_subscription_activated':
        case 'gamipress_fluentcart_specific_subscription_activated':
        case 'gamipress_fluentcart_subscription_canceled':
        case 'gamipress_fluentcart_specific_subscription_canceled':
        case 'gamipress_fluentcart_subscription_renewed':
        case 'gamipress_fluentcart_specific_subscription_renewed':
            // Add the product and order ID
            $log_meta['product_id'] = $args[0];
            $log_meta['order_id'] = $args[2];
            break;
        case 'gamipress_fluentcart_new_purchase':
        case 'gamipress_fluentcart_purchase_full_refund':
        case 'gamipress_fluentcart_purchase_partial_refund':
            // Add the order ID
            $log_meta['order_id'] = $args[0];
            break;
    }

    return $log_meta;
}
add_filter( 'gamipress_log_event_trigger_meta_data', 'gamipress_fluentcart_log_event_trigger_meta_data', 10, 5 );