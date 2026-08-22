<?php
/**
 * Listeners
 *
 * @package GamiPress\BookingPress\Listeners
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Book appointment listener
 *
 * @since 1.0.0
 *
 * @param int $appointment_id
 */
function gamipress_bookingpress_book_appointment( $appointment_id ) {

    $customer_id = gamipress_bookingpress_get_customer_by_appointment_id( $appointment_id );
    $user_id = gamipress_bookingpress_get_wp_user_id_from_customer_id( $customer_id );

    // Bail if not user ID
    if ( empty( $user_id ) ) {
        return;
    }

    $service_id = gamipress_bookingpress_get_service_by_appointment_id( $appointment_id );

    // Book any service
    do_action( 'gamipress_bookingpress_user_books_appointment', $service_id, $user_id );

    // Book specific service
    do_action( 'gamipress_bookingpress_user_books_appointment_service', $service_id, $user_id );

}
add_action( 'bookingpress_after_insert_appointment', 'gamipress_bookingpress_book_appointment' );

/**
 * Status appointment listener
 *
 * @since 1.0.0
 *
 * @param int $appointment_id
 * @param string $appointment_status
 */
function gamipress_bookingpress_status_appointment( $appointment_id, $appointment_status ) {

    $customer_id = gamipress_bookingpress_get_customer_by_appointment_id( $appointment_id );
    $user_id = gamipress_bookingpress_get_wp_user_id_from_customer_id( $customer_id );

    // Bail if not user ID
    if ( empty( $user_id ) ) {
        return;
    }

    $service_id = gamipress_bookingpress_get_service_by_appointment_id( $appointment_id );

    switch( $appointment_status ){
        case '1':
            // Approve an appointment
            do_action( 'gamipress_bookingpress_user_approves_appointment', $service_id, $user_id );

            // Approve an appointment for specific service
            do_action( 'gamipress_bookingpress_user_approves_appointment_service', $service_id, $user_id );
            break;
        case '2':
            // Set to pending an appointment
            do_action( 'gamipress_bookingpress_user_pending_appointment', $service_id, $user_id );

            // Set to pending an appointment for specific service
            do_action( 'gamipress_bookingpress_user_pending_appointment_service', $service_id, $user_id );
            break;
        case '3':
            // Cancel an appointment
            do_action( 'gamipress_bookingpress_user_cancels_appointment', $service_id, $user_id );

            // Cancel an appointment for specific service
            do_action( 'gamipress_bookingpress_user_cancels_appointment_service', $service_id, $user_id );
            break;
        case '4':
            // Reject an appointment
            do_action( 'gamipress_bookingpress_user_rejects_appointment', $service_id, $user_id );

            // Reject an appointment for specific service
            do_action( 'gamipress_bookingpress_user_rejects_appointment_service', $service_id, $user_id );
            break;
    }

}
add_action( 'bookingpress_after_change_appointment_status', 'gamipress_bookingpress_status_appointment', 10, 2 );

/**
 * Register customer listener
 *
 * @since 1.0.0
 *
 * @param int $customer_id BookingPress customer ID
 */
function gamipress_bookingpress_user_register_customer( $customer_id ) {

    $user_id = automatorwp_bookingpress_get_wp_user_id_from_customer_id( $customer_id );

    if( empty( $user_id ) ) {
        return;
    }

    // User registered as a customer
    do_action( 'gamipress_bookingpress_user_register_customer', $user_id );

}
add_action( 'bookingpress_after_create_customer', 'gamipress_bookingpress_user_register_customer' );
