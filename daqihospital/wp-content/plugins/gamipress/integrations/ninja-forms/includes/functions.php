<?php
/**
 * Functions
 *
 * @package GamiPress\Ninja_Forms\Functions
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Overrides GamiPress AJAX Helper for selecting posts
 *
 * @since 1.0.0
 */
function gamipress_nf_ajax_get_posts() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check if user can manage GamiPress
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'gamipress' ) );
    }

    if( isset( $_REQUEST['post_type'] ) && in_array( 'nf_form', $_REQUEST['post_type'] ) ) {
        global $wpdb;

        // Pull back the search string
        $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

        $results = $wpdb->get_results( $wpdb->prepare(
            "
		SELECT p.id AS ID, p.title AS post_title
		FROM   {$wpdb->prefix}nf3_forms AS p
		WHERE  p.title LIKE %s
		",
            "%%{$search}%%"
        ) );

        // Return our results
        wp_send_json_success( $results );
        die;
    }

}
add_action( 'wp_ajax_gamipress_get_posts', 'gamipress_nf_ajax_get_posts', 5 );