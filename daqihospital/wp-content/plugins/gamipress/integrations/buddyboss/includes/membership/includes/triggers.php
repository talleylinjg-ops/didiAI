<?php
/**
 * Triggers
 *
 * @package GamiPress\BuddyPress\Triggers
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register BuddyPress specific triggers
 *
 * @param array $triggers
 * @return mixed
 */
function gamipress_bbmembership_activity_triggers( $triggers ) {

    // BuddyBoss Courses

    if ( class_exists( 'buddybossmembership\courses\models\Course' ) ) {
        $triggers[__( 'BuddyBoss Courses', 'gamipress' )] = array(
            'gamipress_bbmembership_complete_lesson'              => __( 'Complete a lesson', 'gamipress' ),
            'gamipress_bbmembership_complete_specific_lesson'     => __( 'Complete a specific lesson', 'gamipress' ),
            'gamipress_bbmembership_complete_section'             => __( 'Complete a section', 'gamipress' ),
            'gamipress_bbmembership_complete_specific_section'    => __( 'Complete a specific section', 'gamipress' ),
            'gamipress_bbmembership_complete_course'              => __( 'Complete a course', 'gamipress' ),
            'gamipress_bbmembership_complete_specific_course'     => __( 'Complete a specific course', 'gamipress' ),

        );
    }

    if ( class_exists( 'buddybossmembership\quizzes\models\Quiz' ) ) {
        $triggers[__( 'BuddyBoss Courses', 'gamipress' )]['gamipress_bbmembership_complete_quiz'] = __( 'Complete a quiz', 'gamipress' );
        $triggers[__( 'BuddyBoss Courses', 'gamipress' )]['gamipress_bbmembership_complete_specific_quiz'] = __( 'Complete a specific quiz', 'gamipress' );
    }

    // BuddyBoss Memberships
    if ( function_exists( 'buddybossmembership' ) ) {
        $triggers[__( 'BuddyBoss Memberships', 'gamipress' )] = array(
            'gamipress_bbmembership_add_membership'               => __( 'Get added to a membership', 'gamipress' ),
            'gamipress_bbmembership_add_specific_membership'      => __( 'Get added to a specific membership', 'gamipress' ),
            'gamipress_bbmembership_suspend_membership'           => __( 'Suspend a membership', 'gamipress' ),
            'gamipress_bbmembership_suspend_specific_membership'  => __( 'Suspend a specific membership', 'gamipress' ),
            'gamipress_bbmembership_cancel_membership'            => __( 'Cancel a membership', 'gamipress' ),
            'gamipress_bbmembership_cancel_specific_membership'   => __( 'Cancel a specific membership', 'gamipress' ),
        );
    }

    return $triggers;

}
add_filter( 'gamipress_buddyboss_activity_triggers', 'gamipress_bbmembership_activity_triggers' );

/**
 * Build custom activity trigger label
 *
 * @since  1.0.0
 *
 * @param string    $title
 * @param integer   $requirement_id
 * @param array     $requirement
 *
 * @return string
 */
function gamipress_bbmembership_activity_trigger_label( $title, $requirement_id, $requirement ) {

    $bp_member_type = ( isset( $requirement['bp_member_type'] ) ) ? $requirement['bp_member_type'] : '';
    $bp_field_value = ( isset( $requirement['bp_field_value'] ) ) ? $requirement['bp_field_value'] : '';

    switch( $requirement['trigger_type'] ) {

        case 'gamipress_bbmembership_set_member_type':

            if( function_exists( 'bp_get_member_types' ) ) {
                $member_types = bp_get_member_types( array(), 'objects' );
            } else {
                $member_types = array();
            }

            if( isset( $member_types[$bp_member_type] ) ) {
                return sprintf( __( 'Get assigned to the %s type', 'gamipress' ), $member_types[$bp_member_type]->labels['singular_name'] );
            }
            break;

        case 'gamipress_bbmembership_update_profile_specific_value':
            $achievement_post_id = absint( $requirement['achievement_post'] );
            $bp_field_name = gamipress_get_specific_activity_trigger_post_title( $achievement_post_id, $requirement['trigger_type'], get_current_blog_id() );
            return sprintf( __( 'Update %s field with %s value', 'gamipress' ), $bp_field_name, $bp_field_value );

            break;

    }

    return $title;
}
add_filter( 'gamipress_activity_trigger_label', 'gamipress_bbmembership_activity_trigger_label', 10, 3 );

/**
 * Register BuddyPress specific activity triggers
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_triggers
 * @return array
 */
function gamipress_bbmembership_specific_activity_triggers( $specific_activity_triggers ) {

    $specific_activity_triggers['gamipress_bbmembership_complete_specific_lesson'] = array( 'bbcs-lesson' );
    $specific_activity_triggers['gamipress_bbmembership_complete_specific_section'] = array( 'bbcs-section' ); // To add function
    $specific_activity_triggers['gamipress_bbmembership_complete_specific_course'] = array( 'bbcs-course' );
    $specific_activity_triggers['gamipress_bbmembership_complete_specific_quiz'] = array( 'bbcs-quiz' );
    $specific_activity_triggers['gamipress_bbmembership_add_specific_membership'] = array( 'bm-product' );
    $specific_activity_triggers['gamipress_bbmembership_suspend_specific_membership'] = array( 'bm-product' );
    $specific_activity_triggers['gamipress_bbmembership_cancel_specific_membership'] = array( 'bm-product' );
    

    return $specific_activity_triggers;
}
add_filter( 'gamipress_specific_activity_triggers', 'gamipress_bbmembership_specific_activity_triggers' );

/**
 * Register BuddyPress specific activity triggers labels
 *
 * @since  1.0.0
 *
 * @param  array $specific_activity_trigger_labels
 * @return array
 */
function gamipress_bbmembership_specific_activity_trigger_label( $specific_activity_trigger_labels ) {

    $specific_activity_trigger_labels['gamipress_bbmembership_complete_specific_lesson'] = __( 'Complete %s lesson', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bbmembership_complete_specific_section'] = __( 'Complete %s section', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bbmembership_complete_specific_course'] = __( 'Complete %s course', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bbmembership_complete_specific_quiz'] = __( 'Complete %s quiz', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bbmembership_add_specific_membership'] = __( 'Get added to %s membership', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bbmembership_suspend_specific_membership'] = __( 'Suspend %s membership', 'gamipress' );
    $specific_activity_trigger_labels['gamipress_bbmembership_cancel_specific_membership'] = __( 'Cancel %s membership', 'gamipress' );

    return $specific_activity_trigger_labels;
}
add_filter( 'gamipress_specific_activity_trigger_label', 'gamipress_bbmembership_specific_activity_trigger_label' );

/**
 * Get specific activity trigger post title
 *
 * @since  1.2.1
 *
 * @param  string   $post_title
 * @param  integer  $specific_id
 * @param  string   $trigger_type
 * @param  int      $site_id
 * @return string
 */
function gamipress_bbmembership_specific_activity_trigger_post_title( $post_title, $specific_id, $trigger_type, $site_id ) {

    switch( $trigger_type ) {
        case 'gamipress_bbmembership_complete_specific_section':
            if( absint( $specific_id ) !== 0 ) {
                // Get the section title
                $section_title = gamipress_bbmembership_get_section_title( $specific_id );

                $post_title = $section_title;
            }
            break;

    }

    return $post_title;

}
add_filter( 'gamipress_specific_activity_trigger_post_title', 'gamipress_bbmembership_specific_activity_trigger_post_title', 10, 4 );

/**
 * Get plugin specific activity trigger permalink
 *
 * @since  1.0.0
 *
 * @param  string   $permalink
 * @param  integer  $specific_id
 * @param  string   $trigger_type
 * @param  integer  $site_id
 *
 * @return string
 */
function gamipress_bbmembership_specific_activity_trigger_permalink( $permalink, $specific_id, $trigger_type, $site_id ) {

    switch( $trigger_type ) {
        case 'gamipress_bbmembership_complete_specific_section':
            $permalink = '';
            break;
    }

    return $permalink;

}
add_filter( 'gamipress_specific_activity_trigger_permalink', 'gamipress_bbmembership_specific_activity_trigger_permalink', 10, 4 );

/**
 * Get user for a given trigger action.
 *
 * @since  1.0.0
 *
 * @param  integer $user_id user ID to override.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 * @return integer          User ID.
 */
function gamipress_bbmembership_trigger_get_user_id( $user_id, $trigger, $args ) {

    switch ( $trigger ) {
        case 'gamipress_bbmembership_complete_lesson':
        case 'gamipress_bbmembership_complete_specific_lesson':
        case 'gamipress_bbmembership_complete_section':
        case 'gamipress_bbmembership_complete_specific_section':
        case 'gamipress_bbmembership_complete_course':
        case 'gamipress_bbmembership_complete_specific_course':
        case 'gamipress_bbmembership_complete_quiz':
        case 'gamipress_bbmembership_complete_specific_quiz':
        case 'gamipress_bbmembership_add_membership':
        case 'gamipress_bbmembership_add_specific_membership':
        case 'gamipress_bbmembership_suspend_membership':
        case 'gamipress_bbmembership_suspend_specific_membership':
        case 'gamipress_bbmembership_cancel_membership':
        case 'gamipress_bbmembership_cancel_specific_membership':
            $user_id = $args[0];
            break;
    }

    return $user_id;

}

add_filter( 'gamipress_trigger_get_user_id', 'gamipress_bbmembership_trigger_get_user_id', 10, 3);

/**
 * Get the id for a given specific trigger action.
 *
 * @since  1.0.5
 *
 * @param  integer  $specific_id Specific ID.
 * @param  string  $trigger Trigger name.
 * @param  array   $args    Passed trigger args.
 *
 * @return integer          Specific ID.
 */
function gamipress_bbmembership_specific_trigger_get_id( $specific_id, $trigger = '', $args = array() ) {

    switch ( $trigger ) {
        case 'gamipress_bbmembership_complete_lesson':
        case 'gamipress_bbmembership_complete_specific_lesson':
        case 'gamipress_bbmembership_complete_section':
        case 'gamipress_bbmembership_complete_specific_section':
        case 'gamipress_bbmembership_complete_course':
        case 'gamipress_bbmembership_complete_specific_course':
        case 'gamipress_bbmembership_complete_quiz':
        case 'gamipress_bbmembership_complete_specific_quiz':
        case 'gamipress_bbmembership_add_membership':
        case 'gamipress_bbmembership_add_specific_membership':
        case 'gamipress_bbmembership_suspend_membership':
        case 'gamipress_bbmembership_suspend_specific_membership':
        case 'gamipress_bbmembership_cancel_membership':
        case 'gamipress_bbmembership_cancel_specific_membership':
            $specific_id = $args[1];
            break;
    }

    return $specific_id;
}
add_filter( 'gamipress_specific_trigger_get_id', 'gamipress_bbmembership_specific_trigger_get_id', 10, 3 );

/**
 * Extended meta data for event trigger logging
 *
 * @since 1.0.5
 *
 * @param array 	$log_meta
 * @param integer 	$user_id
 * @param string 	$trigger
 * @param integer 	$site_id
 * @param array 	$args
 *
 * @return array
 */
function gamipress_bbmembership_log_event_trigger_meta_data( $log_meta, $user_id, $trigger, $site_id, $args ) {

    switch ( $trigger ) {
        // BuddyBoss reactions
        case 'gamipress_bbmembership_complete_lesson':
        case 'gamipress_bbmembership_complete_specific_lesson':
            // Lesson ID
            $log_meta['lesson_id'] = $args[1];
            break;
        case 'gamipress_bbmembership_complete_section':
        case 'gamipress_bbmembership_complete_specific_section':
            // Section ID
            $log_meta['section_id'] = $args[1];
            break;
        case 'gamipress_bbmembership_complete_course':
        case 'gamipress_bbmembership_complete_specific_course':
            // Course ID
            $log_meta['course_id'] = $args[1];
            break;
        case 'gamipress_bbmembership_complete_quiz':
        case 'gamipress_bbmembership_complete_specific_quiz':
            // Quiz ID
            $log_meta['quiz_id'] = $args[1];
            break;
        case 'gamipress_bbmembership_add_membership':
        case 'gamipress_bbmembership_add_specific_membership':
        case 'gamipress_bbmembership_suspend_membership':
        case 'gamipress_bbmembership_suspend_specific_membership':
        case 'gamipress_bbmembership_cancel_membership':
        case 'gamipress_bbmembership_cancel_specific_membership':
            // Membership ID
            $log_meta['membership_id'] = $args[1];
            break;
    }

    return $log_meta;
}
add_filter( 'gamipress_log_event_trigger_meta_data', 'gamipress_bbmembership_log_event_trigger_meta_data', 10, 5 );
