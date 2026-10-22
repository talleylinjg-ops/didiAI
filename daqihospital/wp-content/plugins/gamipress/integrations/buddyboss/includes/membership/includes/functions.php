<?php
/**
 * Functions
 *
 * @package GamiPress\BuddyPress\Functions
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Compatibility function to check if a BuddyPress module is active
 *
 * @since 1.2.0
 *
 * @param string $component The component name.
 *
 * @return bool
 */
function gamipress_bbmembership_is_active( $component = '' ) {

    if( function_exists( 'bp_is_active' ) ) {
        return bp_is_active( $component );
    }

    return true;

}

/**
 * Overrides GamiPress AJAX Helper for selecting posts
 *
 * @since 1.0.0
 */
function gamipress_bbmembership_ajax_get_posts() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check if user can manage GamiPress
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'gamipress' ) );
    }

    // Bail if class does not exist
    if ( ! class_exists( 'buddybossmembership\courses\models\Section' ) ) {
        return;
    }

    global $wpdb;

    if( isset( $_REQUEST['post_type'] ) && in_array( 'bbcs-section', $_REQUEST['post_type'] ) ) {

        $results = array();

        // Pull back the search string
        $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : false;

        // Get the sections
        $sections_obj = new buddybossmembership\courses\models\Section();
        $sections = $sections_obj->find_all();

        foreach ( $sections as $section_id => $section_name ) {

            if( ! empty( $search ) ) {
                if( strpos( strtolower( $section_name ), strtolower( $search ) ) === false ) {
                    continue;
                }
            }

            // Results should meet Select2 structure
            $results[] = array(
                'ID' => $section_id,
                'post_title' => $section_name,
            );

        }

        // Return our results
        wp_send_json_success( $results );
        die;
    } 

}
add_action( 'wp_ajax_gamipress_get_posts', 'gamipress_bbmembership_ajax_get_posts', 5 );

/**
 * Get the section title
 *
 * @since 1.0.0
 *
 * @param int $section_id
 *
 * @return string|null
 */
function gamipress_bbmembership_get_section_title( $section_id ) {

    // Empty title if no ID provided
    if( absint( $section_id ) === 0 ) {
        return '';
    }

    // Bail if class does not exist
    if ( ! class_exists( 'buddybossmembership\courses\models\Section' ) ) {
        return;
    }

    // Get the sections
    $sections_obj = new buddybossmembership\courses\models\Section();
    $section = $sections_obj->find_by_id( $section_id );

    return $section->title;

}