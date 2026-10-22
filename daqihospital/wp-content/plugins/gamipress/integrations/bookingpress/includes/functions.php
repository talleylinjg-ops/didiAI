<?php
/**
 * Functions
 *
 * @package GamiPress\BookingPress\Functions
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Overrides GamiPress AJAX Helper for selecting posts
 *
 * @since 1.0.0
 */
function gamipress_bookingpress_ajax_get_posts() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check if user can manage GamiPress
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'gamipress' ) );
    }

    global $wpdb;

    $results = array();

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? sanitize_text_field( $_REQUEST['q'] ) : '';
    $search = $wpdb->esc_like( $search );

    if( isset( $_REQUEST['post_type'] ) && in_array( 'bookingpress_service', $_REQUEST['post_type'] ) ) {

        // Get the services
        $services = $wpdb->get_results( $wpdb->prepare(
            "SELECT a.bookingpress_service_id, a.bookingpress_service_name
            FROM {$wpdb->prefix}bookingpress_services AS a
            WHERE a.bookingpress_service_name LIKE %s",
            "%%{$search}%%"
        ) );

        foreach ( $services as $service ) {

            // Results should meet same structure like posts
            $results[] = array(
                'ID' => $service->bookingpress_service_id,
                'post_title' => $service->bookingpress_service_name,
            );

        }

        // Return our results
        wp_send_json_success( $results );
        die;

    } 

}
add_action( 'wp_ajax_gamipress_get_posts', 'gamipress_bookingpress_ajax_get_posts', 5 );

// Get the service title
function gamipress_bookingpress_get_service_title( $service_id ) {

    if( absint( $service_id ) === 0 ) return '';

    global $wpdb;

    return $wpdb->get_var( $wpdb->prepare(
        "SELECT a.bookingpress_service_name FROM {$wpdb->prefix}bookingpress_services AS a WHERE a.bookingpress_service_id = %d",
        absint( $service_id )
    ) );

}

/**
 * Get the related WordPress user ID from a BookingPress customer ID.
 *
 * @since 1.0.0
 *
 * @param int $customer_id BookingPress customer ID.
 *
 * @return int WordPress user ID
 */
function gamipress_bookingpress_get_wp_user_id_from_customer_id( $customer_id ) {

    global $wpdb;

    $customer_id = absint( $customer_id );

    if( empty( $customer_id ) ) {
        return 0;
    }

    $customer_table = $wpdb->prefix . 'bookingpress_customers';
    $wp_user_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT bookingpress_wpuser_id FROM {$customer_table} WHERE bookingpress_customer_id = %d",
            $customer_id
        )
    );

    return $wp_user_id;

}


/**
 * Get Service by appointment ID
 *
 * @since 1.0.0
 *
 * @param int    $appointment_id         ID appointment
 * 
 */
function gamipress_bookingpress_get_service_by_appointment_id( $appointment_id ) {

    global $wpdb;

    // Empty if no ID provided
    if( absint( $appointment_id ) === 0 ) {
        return '';
    }

    $service_id = $wpdb->get_var( "SELECT bookingpress_service_id FROM {$wpdb->prefix}bookingpress_appointment_bookings WHERE bookingpress_appointment_booking_id = {$appointment_id}" );
    
    return $service_id;

}

/**
 * Get Customer by appointment ID
 *
 * @since 1.0.0
 *
 * @param int    $appointment_id         ID appointment
 * 
 */
function gamipress_bookingpress_get_customer_by_appointment_id( $appointment_id ) {

    global $wpdb;

    // Empty if no ID provided
    if( absint( $appointment_id ) === 0 ) {
        return '';
    }

    $customer_id = $wpdb->get_var( "SELECT bookingpress_customer_id FROM {$wpdb->prefix}bookingpress_appointment_bookings WHERE bookingpress_appointment_booking_id = {$appointment_id}" );
    
    return $customer_id;

}
