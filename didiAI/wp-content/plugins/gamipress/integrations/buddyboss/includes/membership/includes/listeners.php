<?php
/**
 * Listeners
 *
 * @package GamiPress\BBMembership\Listeners
 * @since 1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/* ----------------------------------------
 * Courses
 ------------------------------------------ */

/**
 * Complete course listener
 *
 * @since 1.0.0
 *
 * @param buddyboss\courses\models\UserProgress $user_progress
 */
function gamipress_bbmembership_complete_course( $user_progress ) {

    // Shorthand
    $course_id = $user_progress->course_id;
    $user_id = $user_progress->user_id;

    // Trigger event for completing a course
    do_action( 'gamipress_bbmembership_complete_course', $user_id, $course_id );

    // Trigger event for completing a specific course
    do_action( 'gamipress_bbmembership_complete_specific_course', $user_id, $course_id );

}
add_action( 'bbcs_completed_course', 'gamipress_bbmembership_complete_course' );

/**
 * Complete section listener
 *
 * @since 1.0.0
 *
 * @param buddyboss\sections\models\UserProgress $user_progress
 * @param int $section_id
 */
function gamipress_bbmembership_complete_section( $user_progress, $section_id ) {

    // Shorthand
    $user_id = $user_progress->user_id;

    // Trigger event for completing a section
    do_action( 'gamipress_bbmembership_complete_section', $user_id, $section_id );

    // Trigger event for completing a specific section
    do_action( 'gamipress_bbmembership_complete_specific_section', $user_id, $section_id );

}
add_action( 'bbcs_completed_section', 'gamipress_bbmembership_complete_section', 10, 2 );

/**
 * Complete lesson/quiz listener
 *
 * @since 1.0.0
 *
 * @param buddyboss\courses\models\UserProgress $user_progress
 */
function gamipress_bbmembership_complete_lesson( $user_progress ) {

    // Shorthand
    $lesson_id = $user_progress->lesson_id; // Lesson or Quiz
    $user_id = $user_progress->user_id;

    if ( get_post_type( $lesson_id ) !== 'bbcs-quiz' ) {
        // Trigger event for completing a lesson
        do_action( 'gamipress_bbmembership_complete_lesson', $user_id, $lesson_id );

        // Trigger event for completing a specific lesson
        do_action( 'gamipress_bbmembership_complete_specific_lesson', $user_id, $lesson_id );
    } else {
        // Trigger event for completing a quiz
        do_action( 'gamipress_bbmembership_complete_quiz', $user_id, $lesson_id );

        // Trigger event for completing a specific quiz
        do_action( 'gamipress_bbmembership_complete_specific_quiz', $user_id, $lesson_id );
    }
    
}
add_action( 'bbcs_completed_lesson', 'gamipress_bbmembership_complete_lesson' );

/**
 * Add membership listener
 *
 * @since 1.0.0
 *
 * @param BbmsSubscription $subscription
 */
function gamipress_bbmembership_add_membership( $subscription ) {

    // Shorthand
    $subscription_id = $subscription->id;
    $membership_id = $subscription->product_id;
    $user_id = $subscription->user_id;
    $status = $subscription->status;
    
    // Bail if status is not active
    if ( $status !== 'active' )
        return;

    // Trigger event for adding user to a membership
    do_action( 'gamipress_bbmembership_add_membership', $user_id, $membership_id );

    // Trigger event for adding user to a specific membership
    do_action( 'gamipress_bbmembership_add_specific_membership', $user_id, $membership_id );

}
add_action( 'bbms_subscription_saved', 'gamipress_bbmembership_add_membership' );

/**
 * Suspend membership listener
 *
 * @since 1.0.0
 *
 * @param BbmsSubscription $subscription
 */
function gamipress_bbmembership_suspend_membership( $subscription ) {

    // Shorthand
    $subscription_id = $subscription->id;
    $membership_id = $subscription->product_id;
    $user_id = $subscription->user_id;

    // Trigger event for suspending user to a membership
    do_action( 'gamipress_bbmembership_suspend_membership', $user_id, $membership_id );

    // Trigger event for suspending user to a specific membership
    do_action( 'gamipress_bbmembership_suspend_specific_membership', $user_id, $membership_id );

}
add_action( 'bbms_subscription_status_suspended', 'gamipress_bbmembership_suspend_membership' );

/**
 * Cancel membership listener
 *
 * @since 1.0.0
 *
 * @param BbmsSubscription $subscription
 */
function gamipress_bbmembership_cancel_membership( $subscription ) {

    // Shorthand
    $subscription_id = $subscription->id;
    $membership_id = $subscription->product_id;
    $user_id = $subscription->user_id;

    // Trigger event for canceling user to a membership
    do_action( 'gamipress_bbmembership_cancel_membership', $user_id, $membership_id );

    // Trigger event for canceling user to a specific membership
    do_action( 'gamipress_bbmembership_cancel_specific_membership', $user_id, $membership_id );

}
add_action( 'bbms_subscription_status_cancelled', 'gamipress_bbmembership_cancel_membership' );