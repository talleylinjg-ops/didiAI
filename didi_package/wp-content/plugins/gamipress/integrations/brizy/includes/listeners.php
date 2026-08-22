<?php
/**
 * Listeners
 *
 * @package GamiPress\Brizy\Listeners
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Form submission listener
 *
 * @since 1.0.0
 *
 * @param array $fields
 * @param stdClass $form
 */
function gamipress_brizy_submission_listener( $fields, $form ) {

    // Login is required
    if ( ! is_user_logged_in() )  {
        return;
    }

    $user_id = get_current_user_id();

    $form_id = $form->getId();

    // Trigger event for submit a new form
    do_action( 'gamipress_brizy_new_form_submission', $form_id, $user_id );

    // Trigger event for submit a specific form
    do_action( 'gamipress_brizy_specific_new_form_submission', $form_id, $user_id );

    return $fields;

}
add_action( 'brizy_form_submit_data', 'gamipress_brizy_submission_listener', 10, 2 );

/**
 * Field submission listener
 *
 * @since 1.0.9
 *
 * @param array $fields
 * @param stdClass $form
 */
function gamipress_brizy_field_submission_listener( $fields, $form ) {

    // Login is required
    if ( ! is_user_logged_in() ) return;

    $user_id = get_current_user_id();

    $form_id = $form->getId();
    $form_fields = gamipress_brizy_get_form_fields_values( $fields );

    // Loop all fields to trigger events per field value
    foreach ( $form_fields as $field_name => $field_value ) {

        // Trigger event for submit a specific field value
        do_action( 'gamipress_brizy_field_value_submission', $form_id, $user_id, $field_name, $field_value );

        // Trigger event for submit a specific field value of a specific form
        do_action( 'gamipress_brizy_specific_field_value_submission', $form_id, $user_id, $field_name, $field_value );
    }

    return $fields;
}
add_action( 'brizy_form_submit_data', 'gamipress_brizy_field_submission_listener', 10, 2 );
