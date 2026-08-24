<?php
/**
 * Listeners
 *
 * @package GamiPress\FluentCart\Listeners
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Purchase listener
 *
 * @since 1.0.0
 *
 * @param array     $data
 * 
 */
function gamipress_fluentcart_complete_purchase( $data ) {

    $user_id = get_current_user_id();

    // Make sure subscriber has a user ID assigned
    if ( $user_id === 0 ) 
        return;

    $order = $data['order'];
    $order_id = $order->id;

    // Complete a purchase
    do_action( 'gamipress_fluentcart_new_purchase', $order_id, $user_id );

    $items = $order->order_items;

    // Loop all items to trigger events on each one purchased
    foreach ( $items as $item ) {

        $product_id = $item->post_id;
        $quantity = $item->quantity;

        // Skip items not assigned to a product
        if( $product_id === 0 )
            continue;

        // Trigger events same times as item quantity
        for ( $i = 0; $i < $quantity; $i++ ) {
            
            // Purchase any product
            do_action( 'gamipress_fluentcart_product_purchase', $product_id, $user_id, $order_id );

            // Purchase a specific product
            do_action( 'gamipress_fluentcart_specific_product_purchase', $product_id, $user_id, $order_id );
        }
    }

}
add_action( 'fluent_cart/order_status_changed_to_completed', 'gamipress_fluentcart_complete_purchase' );

/**
 * Full refund listener
 *
 * @since 1.0.0
 *
 * @param array     $data
 * 
 */
function gamipress_fluentcart_full_refund( $data ) {

    // Shorthand
    $order = $data['order'];
    $order_id = $order->id;
    $customer = $data['customer'];
    $user_id = $customer->user_id;

    // Make sure customer has a user ID assigned
    if ( $user_id === 0 )
        return;

    // Full refund
    do_action( 'gamipress_fluentcart_purchase_full_refund', $order_id, $user_id );

}
add_action( 'fluent_cart/order_fully_refunded', 'gamipress_fluentcart_full_refund' );

/**
 * Partial refund listener
 *
 * @since 1.0.0
 *
 * @param array     $data
 * 
 */
function gamipress_fluentcart_partial_refund( $data ) {

    // Shorthand
    $order = $data['order'];
    $order_id = $order->id;
    $customer = $data['customer'];
    $user_id = $customer->user_id;

    // Make sure customer has a user ID assigned
    if ( $user_id === 0 )
        return;

    // Partial refund
    do_action( 'gamipress_fluentcart_purchase_partial_refund', $order_id, $user_id );

}
add_action( 'fluent_cart/order_partially_refunded', 'gamipress_fluentcart_partial_refund' );

/**
 * Subscription activated listener
 *
 * @since 1.0.0
 *
 * @param array     $data
 * 
 */
function gamipress_fluentcart_subscription_activated( $data ) {

    // Shorthand
    $order = $data['order'];
    $order_id = $order->id;
    $customer = $data['customer'];
    $user_id = $customer->user_id;
    $items = $order->order_items;

    // Make sure customer has a user ID assigned
    if ( $user_id === 0 ) 
        return;

    // Loop all items to trigger events on each one purchased
    foreach ( $items as $item ) {
        
        if ( $item->payment_type !== 'subscription' )
            continue;

        $product_id = $item->post_id;
            
        // Skip items not assigned to a product
        if( $product_id === 0 )
            continue;

        // Subscription activated for any product
        do_action( 'gamipress_fluentcart_subscription_activated', $product_id, $user_id, $order_id );

        // Subscription activated for specific product
        do_action( 'gamipress_fluentcart_specific_subscription_activated', $product_id, $user_id, $order_id );
            
    }

}
add_action( 'fluent_cart/subscription_activated', 'gamipress_fluentcart_subscription_activated' );

/**
 * Subscription canceled listener
 *
 * @since 1.0.0
 *
 * @param array     $data
 * 
 */
function gamipress_fluentcart_subscription_canceled( $data ) {

    // Shorthand
    $order = $data['order'];
    $order_id = $order->id;
    $customer = $data['customer'];
    $user_id = $customer->user_id;
    $items = $order->order_items;

    // Make sure customer has a user ID assigned
    if ( $user_id === 0 ) 
        return;

    // Loop all items to trigger events on each one purchased
    foreach ( $items as $item ) {
        
        if ( $item->payment_type !== 'subscription' )
            continue;

        $product_id = $item->post_id;
            
        // Skip items not assigned to a product
        if( $product_id === 0 )
            continue;

        // Subscription canceled for any product
        do_action( 'gamipress_fluentcart_subscription_canceled', $product_id, $user_id, $order_id );

        // Subscription canceled for specific product
        do_action( 'gamipress_fluentcart_specific_subscription_canceled', $product_id, $user_id, $order_id );
            
    }

}
add_action( 'fluent_cart/subscription_canceled', 'gamipress_fluentcart_subscription_canceled' );

/**
 * Subscription renewed listener
 *
 * @since 1.0.0
 *
 * @param array     $data
 * 
 */
function gamipress_fluentcart_subscription_renewed( $data ) {

    // Shorthand
    $order = $data['order'];
    $order_id = $order->id;
    $customer = $data['customer'];
    $user_id = $customer->user_id;
    $items = $order->order_items;

    // Make sure customer has a user ID assigned
    if ( $user_id === 0 ) 
        return;

    // Loop all items to trigger events on each one purchased
    foreach ( $items as $item ) {
        
        if ( $item->payment_type !== 'subscription' )
            continue;

        $product_id = $item->post_id;
            
        // Skip items not assigned to a product
        if( $product_id === 0 )
            continue;

        // Subscription renewed for any product
        do_action( 'gamipress_fluentcart_subscription_renewed', $product_id, $user_id, $order_id );

        // Subscription renewed for specific product
        do_action( 'gamipress_fluentcart_specific_subscription_renewed', $product_id, $user_id, $order_id );
            
    }

}
add_action( 'fluent_cart/subscription_renewed', 'gamipress_fluentcart_subscription_renewed' );