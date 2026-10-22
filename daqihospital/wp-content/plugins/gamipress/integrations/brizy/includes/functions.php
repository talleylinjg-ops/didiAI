<?php
/**
 * Functions
 *
 * @package GamiPress\Brizy\Functions
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Overrides GamiPress AJAX Helper for selecting posts
 *
 * @since 1.0.0
 */
function gamipress_brizy_ajax_get_posts() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check if user can manage GamiPress
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'gamipress' ) );
    }

    global $wpdb;

    if( isset( $_REQUEST['post_type'] ) && in_array( 'brizy_form', $_REQUEST['post_type'] ) ) {

        // Pull back the search string
        $results = array();

        $forms = gamipress_brizy_get_forms();

        foreach ( $forms as $form ) {

            // Results should meet same structure like posts
            $results[] = array(
                'ID' => $form['id'],
                'post_title' => $form['name'],
            );

        }

        // Return our results
        wp_send_json_success( $results );
        die;
    }

}
add_action( 'wp_ajax_gamipress_get_posts', 'gamipress_brizy_ajax_get_posts', 5 );

/**
 * Get all forms
 *
 * @since 1.0.0
 *
 * @return string|null
 */
function gamipress_brizy_get_forms( ) {

    global $wpdb;

    $all_forms = array();

    $forms = $wpdb->get_results( 
        $wpdb->prepare(
            "SELECT ID, post_title, post_content 
            FROM {$wpdb->posts} 
            WHERE post_content LIKE %s 
            AND post_status = 'publish'
            AND post_type IN ('post', 'page')",
            '%' . $wpdb->esc_like('data-brz-form-id') . '%'
        )
    );
    
    foreach ( $forms as $post ) {

        $form_title = $post->post_title;
        $form_content = $post->post_content;
        	
        preg_match_all( '/data-brz-form-id="([^"]+)"/', $form_content, $matches );
        $form_ids = array_unique($matches[1]);

        foreach ( $form_ids as $id ) {
            $all_forms[] = array(
                    'id' => $id,
                    'name' => $form_title . ' - ' . $id,     
                );
        }
        	
    }

    return $all_forms;

}

/**
 * Get the form title
 *
 * @since 1.0.0
 *
 * @param int $form_id
 *
 * @return string|null
 */
function gamipress_brizy_get_form_title( $form_id ) {

    global $wpdb;
    
    // Empty title if no ID provided
    if( empty( $form_id ) ) {
        return '';
    }

    $post_title = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT post_title 
            FROM {$wpdb->posts} 
            WHERE post_content LIKE %s 
            AND post_status = 'publish'
            AND post_type IN ('post', 'page')
            LIMIT 1",
            '%' . $wpdb->esc_like($form_id) . '%'
        )
    );

    return $post_title . ' - ' . $form_id;

}

/**
 * Get form fields values
 *
 * @since 1.0.0
 *
 * @param array $fields
 *
 * @return array
 */
function gamipress_brizy_get_form_fields_values( $fields ) {

    $form_fields = array();

    // Loop all fields
    foreach ( $fields as $field_name => $field_value ) {
        
        $field_name = $field_value->label;
        $value = ( isset( $field_value->value ) ? $field_value->value : '' );

        $form_fields[$field_name] = $value;

    }

    return $form_fields;

}