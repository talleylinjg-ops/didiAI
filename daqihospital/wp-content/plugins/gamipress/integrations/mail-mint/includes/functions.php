<?php
/**
 * Functions
 *
 * @package GamiPress\Mail_Mint\Functions
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Overrides GamiPress AJAX Helper for selecting posts
 *
 * @since 1.0.0
 */
function gamipress_mail_mint_ajax_get_posts() {

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

    if( isset( $_REQUEST['post_type'] ) && in_array( 'mail_mint_tags', $_REQUEST['post_type'] ) ) {

        $tags = gamipress_mail_mint_get_tags();

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

    } else if( isset( $_REQUEST['post_type'] ) && in_array( 'mail_mint_lists', $_REQUEST['post_type'] ) ) {

        $lists = gamipress_mail_mint_get_lists();

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

    } else if( isset( $_REQUEST['post_type'] ) && in_array( 'mail_mint_forms', $_REQUEST['post_type'] ) ) {

        $lists = gamipress_mail_mint_get_forms();

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
add_action( 'wp_ajax_gamipress_get_posts', 'gamipress_mail_mint_ajax_get_posts', 5 );

/**
 * Get the tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_mail_mint_get_tags( ){

    $tags = array();

    // Bail if class does not exist
    if( ! class_exists ( 'Mint\MRM\DataBase\Models\ContactGroupModel' ) )
        return;

    //Type: lists or tags
    $all_tags = Mint\MRM\DataBase\Models\ContactGroupModel::get_all_lists_or_tags( 'tags' );

    foreach ( $all_tags as $tag ) {
        $tags[] = array(
            'id'    => $tag['id'],
            'name'  => $tag['title'],
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
function gamipress_mail_mint_get_tag_title( $tag_id ) {

    // Empty title if no ID provided
    if( absint( $tag_id ) === 0 )
        return '';

    // Bail if class does not exist
    if( ! class_exists ( 'Mint\MRM\DataBase\Models\ContactGroupModel' ) || ! class_exists ( 'MRM\Common\MrmCommon' ) )
        return;

    $all_tags = Mint\MRM\DataBase\Models\ContactGroupModel::get_all_lists_or_tags( 'tags' );

    $tag = MRM\Common\MrmCommon::search_for_id( strval( $tag_id ), $all_tags );

    return $tag['title'];

}

/**
 * Get the lists
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_mail_mint_get_lists( ){

    $lists = array();

    // Bail if class does not exist
    if( ! class_exists ( 'Mint\MRM\DataBase\Models\ContactGroupModel' ) )
        return;

    //Type: lists or tags
    $all_lists = Mint\MRM\DataBase\Models\ContactGroupModel::get_all_lists_or_tags( 'lists' );

    foreach ( $all_lists as $list ) {
        $lists[] = array(
            'id'    => $list['id'],
            'name'  => $list['title'],
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
function gamipress_mail_mint_get_list_title( $list_id ) {

    // Empty title if no ID provided
    if( absint( $list_id ) === 0 )
        return '';

    // Bail if class does not exist
    if( ! class_exists ( 'Mint\MRM\DataBase\Models\ContactGroupModel' ) || ! class_exists ( 'MRM\Common\MrmCommon' ) )
        return;
        
    $all_lists = Mint\MRM\DataBase\Models\ContactGroupModel::get_all_lists_or_tags( 'lists' );

    $list = MRM\Common\MrmCommon::search_for_id( strval( $list_id ), $all_lists );

    return $list['title'];

}

/**
 * Get forms
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_mail_mint_get_forms( ){

    $forms = array();

    // Bail if class does not exist
    if( ! class_exists ( 'Mint\MRM\DataBase\Models\FormModel' ) )
        return '';

    $all_forms = Mint\MRM\DataBase\Models\FormModel::get_all( 'id', 'asc', 'published' );

    foreach ( $all_forms['data'] as $form ) {
        $forms[] = array(
            'id'    => $form['id'],
            'name'  => $form['title'],
        );
    }

    return $forms;

}

/**
 * Get the form title
 *
 * @since 1.0.0
 *
 * @param int $list_id
 *
 * @return string|null
 */
function gamipress_mail_mint_get_form_title( $form_id ) {

    // Empty title if no ID provided
    if( absint( $form_id ) === 0 )
        return '';

    // Bail if class does not exist
    if( ! class_exists ( 'Mint\MRM\DataBase\Models\FormModel' ) )
        return '';

    $form = Mint\MRM\DataBase\Models\FormModel::get( $form_id );

    return $form['title'];

}