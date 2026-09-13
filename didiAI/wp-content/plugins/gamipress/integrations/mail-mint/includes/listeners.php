<?php
/**
 * Listeners
 *
 * @package GamiPress\Mail_Mint\Listeners
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param array    $tags
 * @param int|array $contact_id
 */
function gamipress_mail_mint_tag_added( $tags, $contact_id ) {

    if( ! class_exists ( 'Mint\MRM\DataBase\Models\ContactModel' ) )
        return;

    // Get contact email
    $contact_email = Mint\MRM\DataBase\Models\ContactModel::get_email_by_id( $contact_id );

    // Bail if not contact email
    if ( ! $contact_email )
        return;
        
    $user = get_user_by( 'email', $contact_email['email'] );
    $user_id = $user->ID;
        
    // Make sure contact has an user ID assigned
    if ( $user_id === 0 )
        return;
      
    foreach ( $tags as $tag_id ) {

        // Trigger any tag added
        do_action( 'gamipress_mail_mint_tag_added', $tag_id['id'], $user_id );

        // Trigger specific tag added
        do_action( 'gamipress_mail_mint_specific_tag_added', $tag_id['id'], $user_id );

    }

}
add_action( 'mailmint_tag_applied', 'gamipress_mail_mint_tag_added', 10, 2 );

/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param array $tags
 * @param array $contact_ids
 */
function gamipress_mail_mint_tag_removed( $tags, $contact_id ) {

    if( ! class_exists ( 'Mint\MRM\DataBase\Models\ContactModel' ) )
        return;

    if ( is_array( $contact_id ) ){
        // Get contact email
        $contact_email = Mint\MRM\DataBase\Models\ContactModel::get_email_by_id( $contact_id[0] );
    }

    // Bail if not contact email
    if ( ! $contact_email )
        return;
        
    $user = get_user_by( 'email', $contact_email['email'] );
    $user_id = $user->ID;
        
    // Make sure contact has an user ID assigned
    if ( $user_id === 0 )
        return;
   
    foreach ( $tags as $tag_id ) {

        // Trigger any tag added
        do_action( 'gamipress_mail_mint_tag_removed', $tag_id, $user_id );

        // Trigger specific tag added
        do_action( 'gamipress_mail_mint_specific_tag_removed', $tag_id, $user_id );

    }
    

}
add_action( 'mint_tag_removed', 'gamipress_mail_mint_tag_removed', 10, 2 );


/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param array    $lists
 * @param int|array $contact_id
 */
function gamipress_mail_mint_list_added( $lists, $contact_id ) {

    if( ! class_exists ( 'Mint\MRM\DataBase\Models\ContactModel' ) )
        return;

    // Get contact email
    $contact_email = Mint\MRM\DataBase\Models\ContactModel::get_email_by_id( $contact_id );

    // Bail if not contact email
    if ( ! $contact_email )
        return;
        
    $user = get_user_by( 'email', $contact_email['email'] );
    $user_id = $user->ID;
        
    // Make sure contact has an user ID assigned
    if ( $user_id === 0 )
        return;

    foreach( $lists as $list_id ) {
    
        // Trigger any list added
        do_action( 'gamipress_mail_mint_list_added', $list_id['id'], $user_id );
    
        // Trigger specific list added
        do_action( 'gamipress_mail_mint_specific_list_added', $list_id['id'], $user_id );
    
    }

}
add_action( 'mailmint_list_applied', 'gamipress_mail_mint_list_added', 10, 2 );


/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param array    $lists
 * @param int|array $contact_id
 */
function gamipress_mail_mint_list_removed( $lists, $contact_id ) {

    if( ! class_exists ( 'Mint\MRM\DataBase\Models\ContactModel' ) )
        return;

    if ( is_array( $contact_id ) ){
        // Get contact email
        $contact_email = Mint\MRM\DataBase\Models\ContactModel::get_email_by_id( $contact_id[0] );
    }
        
    // Bail if not contact email
    if ( ! $contact_email )
        return;
        
    $user = get_user_by( 'email', $contact_email['email'] );
    $user_id = $user->ID;
        
    // Make sure contact has an user ID assigned
    if ( $user_id === 0 )
        return;

    foreach( $lists as $list_id ) {
    
        // Trigger any list removed
        do_action( 'gamipress_mail_mint_list_removed', $list_id, $user_id );
    
        // Trigger specific list removed
        do_action( 'gamipress_mail_mint_specific_list_removed', $list_id, $user_id );
    
    }   

}
add_action( 'mint_list_removed', 'gamipress_mail_mint_list_removed', 10, 2 );

/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param int    $form_id    
 * @param int    $contact_id 
 * @param object $contact
 */
function gamipress_mail_mint_submit_form( $form_id, $contact_id, $contact ) {

    $user_id = get_current_user_id();

    // Login is required
    if ( $user_id === 0 )
        return;

    // Trigger any form submitted
    do_action( 'gamipress_mail_mint_submit_form', $form_id, $user_id );
    
    // Trigger specific form submitted
    do_action( 'gamipress_mail_mint_specific_submit_form', $form_id, $user_id );

}
add_action( 'mailmint_after_form_submit', 'gamipress_mail_mint_submit_form', 10, 3 );

/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param int   $contact_id Contact ID.
 * @param array $params     Saved data.
 */
function gamipress_mail_mint_register_contact( $contact_id, $params ) {

    if( ! class_exists ( 'Mint\MRM\DataBase\Models\ContactModel' ) )
        return;

    // Get contact email
    $contact_email = Mint\MRM\DataBase\Models\ContactModel::get_email_by_id( $contact_id );

    // Bail if not contact email
    if ( ! $contact_email )
        return;
        
    $user = get_user_by( 'email', $contact_email['email'] );
    $user_id = $user->ID;
        
    // Make sure contact has an user ID assigned
    if ( $user_id === 0 )
        return;

    // Trigger register contact
    do_action( 'gamipress_mail_mint_register_contact', $user_id );

}
add_action( 'mailmint_contacts_saved', 'gamipress_mail_mint_submit_form', 10, 2 );
