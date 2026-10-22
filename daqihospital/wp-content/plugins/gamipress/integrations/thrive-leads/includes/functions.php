<?php
/**
 * Functions
 *
 * @package GamiPress\Thrive_Leads\Functions
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Overrides GamiPress AJAX Helper for selecting posts
 *
 * @since 1.0.0
 */
function gamipress_thrive_leads_ajax_get_posts() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check if user can manage GamiPress
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'gamipress' ) );
    }

    if( isset( $_REQUEST['post_type'] ) && in_array( 'thrive_form', $_REQUEST['post_type'] ) ) {

        $results = array();

        $type_forms = get_posts(
            array(
                'post_type'      => 'tve_form_type',
                'posts_per_page' => -1,
                'post_status'    => 'any',   
            )
        );
            
        foreach ( $type_forms as $parent ) {
            $forms = tve_leads_get_form_variations( $parent->ID );
    
            foreach ( $forms as $form ) {
                $results[ ] = array(
                    'ID' => $form['key'], 
                    'post_title' => $form['post_title']
                );
            }
        }

        // Return our results
        wp_send_json_success( $results );
        die;
    }

}
add_action( 'wp_ajax_gamipress_get_posts', 'gamipress_thrive_leads_ajax_get_posts', 5 );

/**
 * Get the form title
 *
 * @since 1.0.0
 *
 * @param int $form_id
 *
 * @return string|null
 */
function gamipress_thrive_leads_get_form_title( $form_id ) {

    // Empty title if no ID provided
    if( absint( $form_id ) === 0 ) {
        return '';
    }

    $form = tve_leads_get_form_variation( null, $form_id );

    return $form['post_title'];

}