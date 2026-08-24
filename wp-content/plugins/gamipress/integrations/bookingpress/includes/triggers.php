<?php
/**
 * Triggers
 *
 * @package GamiPress\BookingPress\Triggers
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register plugin activity triggers
 *
 * @param array $triggers
 * @return mixed
 */
function gamipress_bookingpress_activity_triggers( $triggers ) {

    // BookingPress
    $triggers[__( 'BookingPress', 'gamipress' )] = array(
        // Appointments
        'gamipress_bookingpress_user_register_customer'             => __( 'Register as a customer', 'gamipress' ),
        'gamipress_bookingpress_user_books_appointment'             => __( 'Book an appointment for a service', 'gamipress' ),
        'gamipress_bookingpress_user_books_appointment_service'     => __( 'Book an appointment for a specific service', 'gamipress' ),
        'gamipress_bookingpress_user_approves_appointment'          => __( 'Approve an appointment for a service', 'gamipress' ),
        'gamipress_bookingpress_user_approves_appointment_service'  => __( 'Approve an appointment for a specific service', 'gamipress' ),
        'gamipress_bookingpress_user_rejects_appointment'            => __( 'Reject an appointment for a service', 'gamipress' ),
        'gamipress_bookingpress_user_rejects_appointment_service'    => __( 'Reject an appointment for for a specific service', 'gamipress' ),
        'gamipress_bookingpress_user_cancels_appointment'           => __( 'Cancel an appointment for a service', 'gamipress' ),
        'gamipress_bookingpress_user_cancels_appointment_service'   => __( 'Cancel an appointment for a specific service', 'gamipress' ),
        'gamipress_bookingpress_user_pending_appointment'           => __( 'An appointment is set to pending for a service', 'gamipress' ),
        'gamipress_bookingpress_user_pending_appointment_service'   => __( 'An appointment is set to pending for a specific service', 'gamipress' ),
        
    );

    return $triggers;

}
add_filter( 'gamipress_activity_triggers', 'gamipress_bookingpress_activity_triggers' );

/**
 * Register specific activity triggers
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_triggers
 * @return array
 */
function gamipress_bookingpress_specific_activity_triggers( $specific_activity_triggers ) {
    
    $specific_activity_triggers['gamipress_bookingpress_user_books_appointment_service'] = array( 'bookingpress_service' );
    $specific_activity_triggers['gamipress_bookingpress_user_approves_appointment_service'] = array( 'bookingpress_service' );
    $specific_activity_triggers['gamipress_bookingpress_user_rejects_appointment_service'] = array( 'bookingpress_service' );
    $specific_activity_triggers['gamipress_bookingpress_user_cancels_appointment_service'] = array( 'bookingpress_service' );
    $specific_activity_triggers['gamipress_bookingpress_user_pending_appointment_service'] = array( 'bookingpress_service' );
    
    return $specific_activity_triggers;
}
add_filter( 'gamipress_specific_activity_triggers', 'gamipress_bookingpress_specific_activity_triggers' );

/**
 * Register specific activity triggers labels
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_trigger_labels
 * @return array
 */
function gamipress_bookingpress_specific_activity_trigger_label( $specific_activity_trigger_labels ) {

    $specific_activity_trigger_labels['gamipress_bookingpress_user_books_appointment_service'] = __( 'Book an appointment for %s service', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bookingpress_user_approves_appointment_service'] = __( 'Approves an appointment for %s service', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bookingpress_user_rejects_appointment_service'] = __( 'Reject an appointment for %s service', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bookingpress_user_cancels_appointment_service'] = __( 'Cancel an appointment for %s service', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bookingpress_user_pending_appointment_service'] = __( 'Set to pending an appointment for %s service', 'gamipress' );
    
    return $specific_activity_trigger_labels;
}
add_filter( 'gamipress_specific_activity_trigger_label', 'gamipress_bookingpress_specific_activity_trigger_label' );

/**
 * Get plugin specific activity trigger post title
 *
 * @since  1.0.0
 *
 * @param  string   $post_title
 * @param  integer  $specific_id
 * @param  string   $trigger_type
 *
 * @return string
 */
function gamipress_bookingpress_specific_activity_trigger_post_title( $post_title, $specific_id, $trigger_type ) {

    switch( $trigger_type ) {
        case 'gamipress_bookingpress_user_books_appointment_service':
            if( absint( $specific_id ) !== 0 ) {
                // Get the service title
                $service_title = gamipress_bookingpress_get_service_title( $specific_id );

                $post_title = $service_title;
            }
            break;
        case 'gamipress_bookingpress_user_approves_appointment_service':
            if( absint( $specific_id ) !== 0 ) {
                // Get the service title
                $service_title = gamipress_bookingpress_get_service_title( $specific_id );

                $post_title = $service_title;
            }
            break;
        case 'gamipress_bookingpress_user_rejects_appointment_service':
            if( absint( $specific_id ) !== 0 ) {
                // Get the service title
                $service_title = gamipress_bookingpress_get_service_title( $specific_id );
    
                $post_title = $service_title;
            }
            break;
        case 'gamipress_bookingpress_user_cancels_appointment_service':
            if( absint( $specific_id ) !== 0 ) {
                // Get the service title
                $service_title = gamipress_bookingpress_get_service_title( $specific_id );
        
                $post_title = $service_title;
            }
            break;
        case 'gamipress_bookingpress_user_pending_appointment_service':
            if( absint( $specific_id ) !== 0 ) {
                // Get the service title
                $service_title = gamipress_bookingpress_get_service_title( $specific_id );
        
                $post_title = $service_title;
            }
            break;
    }

    return $post_title;

}
add_filter( 'gamipress_specific_activity_trigger_post_title', 'gamipress_bookingpress_specific_activity_trigger_post_title', 10, 3 );

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
function gamipress_bookingpress_specific_activity_trigger_permalink( $permalink, $specific_id, $trigger_type, $site_id ) {

    switch( $trigger_type ) {
        case 'gamipress_bookingpress_user_books_appointment_service':
        case 'gamipress_bookingpress_user_approves_appointment_service':
        case 'gamipress_bookingpress_user_rejects_appointment_service':
        case 'gamipress_bookingpress_user_cancels_appointment_service':
        case 'gamipress_bookingpress_user_pending_appointment_service':
            $permalink = '';
            break;
    }

    return $permalink;

}
add_filter( 'gamipress_specific_activity_trigger_permalink', 'gamipress_bookingpress_specific_activity_trigger_permalink', 10, 4 );

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
function gamipress_bookingpress_trigger_get_user_id( $user_id, $trigger, $args ) {

    switch ( $trigger ) {
        // Appointments
        case 'gamipress_bookingpress_user_register_customer':
        case 'gamipress_bookingpress_user_books_appointment':
        case 'gamipress_bookingpress_user_books_appointment_service':
        case 'gamipress_bookingpress_user_approves_appointment':
        case 'gamipress_bookingpress_user_approves_appointment_service':
        case 'gamipress_bookingpress_user_rejects_appointment':
        case 'gamipress_bookingpress_user_rejects_appointment_service':
        case 'gamipress_bookingpress_user_cancels_appointment':
        case 'gamipress_bookingpress_user_cancels_appointment_service':
        case 'gamipress_bookingpress_user_pending_appointment':
        case 'gamipress_bookingpress_user_pending_appointment_service':
            $user_id = $args[1];
            break;
    }

    return $user_id;

}
add_filter( 'gamipress_trigger_get_user_id', 'gamipress_bookingpress_trigger_get_user_id', 10, 3 );

/**
 * Get the id for a given specific trigger action.
 *
 * @since  1.0.0
 *
 * @param  integer $specific_id Specific ID to override.
 * @param  string  $trigger     Trigger name.
 * @param  array   $args        Passed trigger args.
 *
 * @return integer              Specific ID.
 */
function gamipress_bookingpress_specific_trigger_get_id( $specific_id, $trigger, $args ) {

    switch ( $trigger ) {
        case 'gamipress_bookingpress_user_books_appointment_service':
        case 'gamipress_bookingpress_user_approves_appointment_service':
        case 'gamipress_bookingpress_user_rejects_appointment_service':
        case 'gamipress_bookingpress_user_cancels_appointment_service':
        case 'gamipress_bookingpress_user_pending_appointment_service':
            $specific_id = $args[0];
            break;
    }

    return $specific_id;

}

add_filter( 'gamipress_specific_trigger_get_id', 'gamipress_bookingpress_specific_trigger_get_id', 10, 3 );

/**
 * Extended meta data for event trigger logging
 *
 * @since 1.0.2
 *
 * @param array 	$log_meta
 * @param integer 	$user_id
 * @param string 	$trigger
 * @param integer 	$site_id
 * @param array 	$args
 *
 * @return array
 */
function gamipress_bookingpress_log_event_trigger_meta_data( $log_meta, $user_id, $trigger, $site_id, $args ) {

    switch ( $trigger ) {

        // Appointments
        case 'gamipress_bookingpress_user_books_appointment':
        case 'gamipress_bookingpress_user_books_appointment_service':
        case 'gamipress_bookingpress_user_approves_appointment':
        case 'gamipress_bookingpress_user_approves_appointment_service':
        case 'gamipress_bookingpress_user_rejects_appointment':
        case 'gamipress_bookingpress_user_rejects_appointment_service':
        case 'gamipress_bookingpress_user_cancels_appointment':
        case 'gamipress_bookingpress_user_cancels_appointment_service':
        case 'gamipress_bookingpress_user_pending_appointment':
        case 'gamipress_bookingpress_user_pending_appointment_service':
            // Add the service ID
            $log_meta['service_id'] = $args[0];
            break;
    }

    return $log_meta;
}
add_filter( 'gamipress_log_event_trigger_meta_data', 'gamipress_bookingpress_log_event_trigger_meta_data', 10, 5 );