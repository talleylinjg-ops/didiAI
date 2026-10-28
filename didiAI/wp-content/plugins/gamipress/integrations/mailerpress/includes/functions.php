<?php
/**
 * Functions
 *
 * @package GamiPress\MailerPress\Functions
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Overrides GamiPress AJAX Helper for selecting posts
 *
 * @since 1.0.0
 */
function gamipress_mailerpress_ajax_get_posts() {

    // Security check, forces to die if not security passed
    check_ajax_referer( 'gamipress_admin', 'nonce' );

    // Check if user can manage GamiPress
    if( ! current_user_can( gamipress_get_manager_capability() ) ) {
        wp_send_json_error( __( 'You\'re not allowed to perform this action.', 'gamipress' ) );
    }

    global $wpdb;

    $results = array();

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    if( isset( $_REQUEST['post_type'] ) && in_array( 'mailerpress_tags', $_REQUEST['post_type'] ) ) {

        $tags = gamipress_mailerpress_get_tags();

        foreach ( $tags as $tag ) {

            if( ! empty( $search ) ) {
                if( strpos( strtolower( $tag['name'] ), strtolower( $search ) ) === false ) {
                    continue;
                }
            }

            // Results should meet same structure like posts
            $results[] = array(
                'ID' => $tag['id'],
                'post_title' => $tag['name'],
            );

        }

        // Return our results
        wp_send_json_success( $results );
        die;

    } else if( isset( $_REQUEST['post_type'] ) && in_array( 'mailerpress_lists', $_REQUEST['post_type'] ) ) {

        $lists = gamipress_mailerpress_get_lists();

        foreach ( $lists as $list ) {

            if( ! empty( $search ) ) {
                if( strpos( strtolower( $list['name'] ), strtolower( $search ) ) === false ) {
                    continue;
                }
            }

            // Results should meet same structure like posts
            $results[] = array(
                'ID' => $list['id'],
                'post_title' => $list['name'],
            );

        }

        // Return our results
        wp_send_json_success( $results );
        die;

    }

}
add_action( 'wp_ajax_gamipress_get_posts', 'gamipress_mailerpress_ajax_get_posts', 5 );

/**
 * Get the tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_mailerpress_get_tags( ){

    $tags = array();

    // Bail if class does not exist
    if( ! class_exists ( 'MailerPress\Models\Tags' ) )
        return;

    // Get tags data
    $tags_data = new MailerPress\Models\Tags;
    $all_tags = $tags_data->getAll();

    foreach ( $all_tags as $tag ) {

        $tags[] = array(
            'id'    => $tag->tag_id,
            'name'  => $tag->name,
        );
    }

    return $tags;

}

/**
 * Get the tag title
 *
 * @since 1.0.0
 *
 * @param int $tag_id
 *
 * @return string|null
 */
function gamipress_mailerpress_get_tag_title( $tag_id ) {

    // Empty title if no ID provided
    if( absint( $tag_id ) === 0 ) {
        return '';
    }

    global $wpdb;

    $tag_name = $wpdb->get_var( "SELECT name FROM {$wpdb->prefix}mailerpress_tags WHERE tag_id = {$tag_id}" );

    return $tag_name;

}

/**
 * Get the lists
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_mailerpress_get_lists( ){

    $lists = array();

    // Bail if class does not exist
    if( ! class_exists ( 'MailerPress\Models\Lists' ) )
        return;

    // Get lists data
    $lists_data = new MailerPress\Models\Lists;
    $all_lists = $lists_data->getLists();

    foreach ( $all_lists as $list ) {
        $lists[] = array(
            'id'    => $list['list_id'],
            'name'  => $list['name'],
        );
    }

    return $lists;

}

/**
 * Get the list title
 *
 * @since 1.0.0
 *
 * @param int $list_id
 *
 * @return string|null
 */
function gamipress_mailerpress_get_list_title( $list_id ) {

    // Empty title if no ID provided
    if( absint( $list_id ) === 0 ) {
        return '';
    }

    global $wpdb;

    $list_name = $wpdb->get_var( "SELECT name FROM {$wpdb->prefix}mailerpress_lists WHERE list_id = {$list_id}" );

    return $list_name;

}
