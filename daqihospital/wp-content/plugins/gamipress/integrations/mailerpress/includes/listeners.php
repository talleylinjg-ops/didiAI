<?php
/**
 * Listeners
 *
 * @package GamiPress\MailerPress\Listeners
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Trigger listener 
 *
 * @since 1.0.0
 *
 * @param int $contact_id Contact ID.
 * @param int $tag_id Tag ID.
 */
function gamipress_mailerpress_tag_added( $contact_id, $tag_id ) {

    if( ! class_exists ( 'MailerPress\Models\Contacts' ) )
        return;

    // Get contact data
    $contact_data = new MailerPress\Models\Contacts;
    $contact = $contact_data->get( $contact_id );

    // Bail if not contact email
    if ( ! $contact )
        return;
    
    $user = get_user_by( 'email', $contact->email );
    
    // Make sure contact has an user ID assigned
    if ( empty( $user ) ) {
        return;
    }
      
    // Trigger any tag added
    do_action( 'gamipress_mailerpress_tag_added', $tag_id, $user->ID );

    // Trigger specific tag added
    do_action( 'gamipress_mailerpress_specific_tag_added', $tag_id, $user->ID );

}
add_action( 'mailerpress_contact_tag_added', 'gamipress_mailerpress_tag_added', 10, 2 );

/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param int $contact_id Contact ID.
 * @param int $tag_id Tag ID.
 */
function gamipress_mailerpress_tag_removed( $contact_id, $tag_id ) {

    if( ! class_exists ( 'MailerPress\Models\Contacts' ) )
        return;

    // Get contact data
    $contact_data = new MailerPress\Models\Contacts;
    $contact = $contact_data->get( $contact_id );

    // Bail if not contact email
    if ( ! $contact )
        return;
    
    $user = get_user_by( 'email', $contact->email );
    
    // Make sure contact has an user ID assigned
    if ( empty( $user ) ) {
        return;
    }
   
    // Trigger any tag added
    do_action( 'gamipress_mailerpress_tag_removed', $tag_id, $user->ID );

    // Trigger specific tag added
    do_action( 'gamipress_mailerpress_specific_tag_removed', $tag_id, $user->ID );

}
add_action( 'mailerpress_contact_tag_removed', 'gamipress_mailerpress_tag_removed', 10, 2 );


/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param int $contact_id Contact ID.
 * @param int $list_id List ID.
 */
function gamipress_mailerpress_list_added( $contact_id, $list_id ) {

    if( ! class_exists ( 'MailerPress\Models\Contacts' ) )
        return;

    // Get contact data
    $contact_data = new MailerPress\Models\Contacts;
    $contact = $contact_data->get( $contact_id );

    // Bail if not contact email
    if ( ! $contact )
        return;
    
    $user = get_user_by( 'email', $contact->email );
    
    // Make sure contact has an user ID assigned
    if ( empty( $user ) ) {
        return;
    }
    
    // Trigger any list added
    do_action( 'gamipress_mailerpress_list_added', $list_id, $user->ID );

    // Trigger specific list added
    do_action( 'gamipress_mailerpress_specific_list_added', $list_id, $user->ID );
    
    

}
add_action( 'mailerpress_contact_list_added', 'gamipress_mailerpress_list_added', 10, 2 );


/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param int $contact_id Contact ID.
 * @param int $list_id List ID.
 */
function gamipress_mailerpress_list_removed( $contact_id, $list_id ) {

    if( ! class_exists ( 'MailerPress\Models\Contacts' ) )
        return;

    // Get contact data
    $contact_data = new MailerPress\Models\Contacts;
    $contact = $contact_data->get( $contact_id );

    // Bail if not contact email
    if ( ! $contact )
        return;
    
    $user = get_user_by( 'email', $contact->email );
    
    // Make sure contact has an user ID assigned
    if ( empty( $user ) ) {
        return;
    }

    // Trigger any list removed
    do_action( 'gamipress_mailerpress_list_removed', $list_id, $user->ID );

    // Trigger specific list removed
    do_action( 'gamipress_mailerpress_specific_list_removed', $list_id, $user->ID );
    
}
add_action( 'mailerpress_contact_list_removed', 'gamipress_mailerpress_list_removed', 10, 2 );

/**
 * Trigger listener
 *
 * @since 1.0.0
 *
 * @param int   $contactId Contact ID.
 */
function gamipress_mailerpress_register_contact( $contactId ) {

    if( ! class_exists ( 'MailerPress\Models\Contacts' ) )
        return;

    // Get contact data
    $contact_data = new MailerPress\Models\Contacts;
    $contact = $contact_data->get( $contactId );

    // Bail if not contact email
    if ( ! $contact )
        return;
    
    $user = get_user_by( 'email', $contact->email );
    
    // Make sure contact has an user ID assigned
    if ( empty( $user ) ) {
        return;
    }

    // Trigger register contact
    do_action( 'gamipress_mailerpress_register_contact', $user->ID );

}
add_action( 'mailerpress_contact_created', 'gamipress_mailerpress_register_contact' );
