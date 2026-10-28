<?php
/**
 * Listeners
 *
 * @package GamiPress\MetForm\Listeners
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Form submission listener
 *
 * @since 1.0.0
 *
 * @param int $form_id
 * @param array $form_data
 * @param array $form_settings
 * @param array $attributes
 */
function gamipress_metform_submission_listener( $form_id, $form_data, $form_settings, $attributes ) {

    $user_id = get_current_user_id();

    // Login is required
    if ( $user_id === 0 ) {
        return;
    }

    // Trigger event for submit a new form
    do_action( 'gamipress_metform_new_form_submission', $form_id, $user_id );

    // Trigger event for submit a specific form
    do_action( 'gamipress_metform_specific_new_form_submission', $form_id, $user_id );

}
add_action( 'metform_after_store_form_data', 'gamipress_metform_submission_listener', 10, 4 );

/**
 * Field submission listener
 *
 * @since 1.0.0
 *
 * @param int $form_id
 * @param array $form_data
 * @param array $form_settings
 * @param array $attributes
 */
function gamipress_metform_field_submission_listener( $form_id, $form_data, $form_settings, $attributes ) {

    $user_id = get_current_user_id();

    // Login is required
    if ( $user_id === 0 ) {
        return;
    }

    // To get form fields
    array_splice( $form_data, 0, 3);

    $form_fields = $form_data;
    
    foreach( $form_fields as $field_name => $field_value ) {

        // Used for hook
        $field = array( $field_name => $field_value );
    
        /**
         * Excluded fields event by filter
         *
         * @since 1.0.0
         *
         * @param bool      $exclude        Whatever to exclude or not, by default false
         * @param string    $field_name     Field name
         * @param mixed     $field_value    Field value
         * @param array     $field          Field setup array
         */
        if( apply_filters( 'gamipress_metform_exclude_field', false, $field_name, $field_value, $field ) )
            continue;

        // Trigger event for submit a specific field value
        do_action( 'gamipress_metform_field_value_submission', $form_id, $user_id, $field_name, $field_value );

        // Trigger event for submit a specific field value of a specific form
        do_action( 'gamipress_metform_specific_field_value_submission', $form_id, $user_id, $field_name, $field_value );

    }
}
add_action( 'metform_after_store_form_data', 'gamipress_metform_field_submission_listener', 10, 4 );