<?php
/**
 * Functions
 *
 * @package     GamiPress\AI_Assistant\Functions
 * @since       1.0.0
 * @version     1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Check if is a GamiPress page to render/enqueue our assets
 *
 * @since   1.0.0
 */
function gamipress_ai_assistant_is_valid_page() {

    if ( ( isset( $_GET['page'] ) && (
            $_GET['page'] === 'gamipress'
            || $_GET['page'] === 'gamipress_user_earnings'
            || $_GET['page'] === 'edit_gamipress_user_earnings'
            || $_GET['page'] === 'gamipress_logs'
            || $_GET['page'] === 'edit_gamipress_logs'
            || $_GET['page'] === 'gamipress_add_ons'
            || $_GET['page'] === 'gamipress_assets'
            || $_GET['page'] === 'gamipress_tools'
            || $_GET['page'] === 'gamipress_settings'
            || $_GET['page'] === 'gamipress_licenses'
            || $_GET['page'] === 'gamipress_badge_builder'
        )
    ) ) {
        return true;
    }

    global $typenow;

    if (
        $typenow === 'points-type'
        || $typenow === 'achievement-type'
        || $typenow === 'rank-type'
        || in_array( $typenow, gamipress_get_achievement_types_slugs() )
        || in_array( $typenow, gamipress_get_rank_types_slugs() )
    ) {
        return true;
    }

    return false;
}

/**
 * Get all triggers slugs
 *
 * @since   1.0.0
 *
 * @return array
 */
function gamipress_ai_assistant_get_triggers_slugs( $original = false ) {

    $triggers = gamipress_get_activity_triggers();
    $slugs = array();

    foreach ( $triggers as $group => $group_triggers ) {
        foreach( $group_triggers as $trigger => $label ) {
            $slugs[] = $original ? $trigger : gamipress_ai_assistant_improve_slug( $trigger );
        }
    }

    return $slugs;

}

/**
 * Trigger slugs replacements
 *
 * @since	    1.0.0
 *
 * @return array
 */
function gamipress_ai_assistant_trigger_slugs_replacements() {
    $replacements = array(
        array( 'gamipress_affwp_', 'affiliatewp_' ),
        array( 'gamipress_ld_', 'learndash_' ),
        array( 'gamipress_wc_', 'woocommerce_' ),
    );

    if( defined( 'BP_PLATFORM_VERSION' ) ) {
        $replacements[] = array( 'gamipress_bp_', 'buddyboss_buddypress_' );
        $replacements[] = array( 'gamipress_bbp_', 'buddyboss_bbpress_' );
    } else {
        $replacements[] = array( 'gamipress_bp_', 'buddypress_' );
        $replacements[] = array( 'gamipress_bbp_', 'bbpress_' );
    }

    return $replacements;
}

/**
 * Improve slug for the AI engine
 *
 * @since   1.0.0
 *
 * @param string $slug
 *
 * @return string
 */
function gamipress_ai_assistant_improve_slug( $slug ) {

    switch( $slug ) {
        case 'gamipress_register':
        case 'gamipress_login':
        case 'gamipress_new_comment':
        case 'gamipress_specific_new_comment':
        case 'gamipress_new_comment_post_type':
        case 'gamipress_user_post_comment':
        case 'gamipress_user_specific_post_comment':
        case 'gamipress_user_post_comment_post_type':
        case 'gamipress_spam_comment':
        case 'gamipress_specific_spam_comment':
        case 'gamipress_spam_comment_post_type':
        case 'gamipress_publish_post':
        case 'gamipress_delete_post':
        case 'gamipress_publish_page':
        case 'gamipress_delete_page':
        case 'gamipress_publish_post_type':
        case 'gamipress_delete_post_type':
        case 'gamipress_add_role':
        case 'gamipress_add_specific_role':
        case 'gamipress_set_role':
        case 'gamipress_set_specific_role':
        case 'gamipress_remove_role':
        case 'gamipress_remove_specific_role':
        case 'gamipress_update_user_meta_any_value':
        case 'gamipress_update_user_meta_specific_value':
        case 'gamipress_update_post_meta_any_value':
        case 'gamipress_update_post_meta_specific_value':
        // Site Interactions
        case 'gamipress_site_visit':
        case 'gamipress_post_visit':
        case 'gamipress_specific_post_visit':
        case 'gamipress_post_type_visit':
        case 'gamipress_user_post_visit':
        case 'gamipress_user_specific_post_visit':
        case 'gamipress_user_post_type_visit':
            $slug = str_replace( 'gamipress_', 'wordpress_', $slug );
            break;
        // GamiPress
        case 'specific-achievement':
        case 'any-achievement':
        case 'all-achievements':
        case 'revoke-specific-achievement':
        case 'revoke-any-achievement':
        case 'earn-points':
        case 'points-balance':
        case 'gamipress_expend_points':
        case 'earn-rank':
        case 'revoke-rank':
            $slug = 'gamipress_' . $slug;
            break;
        default:
            foreach( gamipress_ai_assistant_trigger_slugs_replacements() as $replacement )
                $slug = str_replace( $replacement[0], $replacement[1], $slug );

            $slug = str_replace( 'gamipress_', '', $slug );
            break;
    }



    return $slug;

}

/**
 * Restore slug
 *
 * @since   1.0.0
 *
 * @param string $slug
 *
 * @return string
 */
function gamipress_ai_assistant_restore_slug( $slug ) {

    switch( $slug ) {
        case 'wordpress_register':
        case 'wordpress_login':
        case 'wordpress_new_comment':
        case 'wordpress_specific_new_comment':
        case 'wordpress_new_comment_post_type':
        case 'wordpress_user_post_comment':
        case 'wordpress_user_specific_post_comment':
        case 'wordpress_user_post_comment_post_type':
        case 'wordpress_spam_comment':
        case 'wordpress_specific_spam_comment':
        case 'wordpress_spam_comment_post_type':
        case 'wordpress_publish_post':
        case 'wordpress_delete_post':
        case 'wordpress_publish_page':
        case 'wordpress_delete_page':
        case 'wordpress_publish_post_type':
        case 'wordpress_delete_post_type':
        case 'wordpress_add_role':
        case 'wordpress_add_specific_role':
        case 'wordpress_set_role':
        case 'wordpress_set_specific_role':
        case 'wordpress_remove_role':
        case 'wordpress_remove_specific_role':
        case 'wordpress_update_user_meta_any_value':
        case 'wordpress_update_user_meta_specific_value':
        case 'wordpress_update_post_meta_any_value':
        case 'wordpress_update_post_meta_specific_value':
        // Site Interactions
        case 'wordpress_site_visit':
        case 'wordpress_post_visit':
        case 'wordpress_specific_post_visit':
        case 'wordpress_post_type_visit':
        case 'wordpress_user_post_visit':
        case 'wordpress_user_specific_post_visit':
        case 'wordpress_user_post_type_visit':
            $slug = str_replace( 'wordpress_', 'gamipress_', $slug );
            break;
        // GamiPress
        case 'gamipress_specific-achievement':
        case 'gamipress_any-achievement':
        case 'gamipress_all-achievements':
        case 'gamipress_revoke-specific-achievement':
        case 'gamipress_revoke-any-achievement':
        case 'gamipress_earn-points':
        case 'gamipress_points-balance':
        case 'gamipress_gamipress_expend_points':
        case 'gamipress_earn-rank':
        case 'gamipress_revoke-rank':
            $slug = str_replace( 'gamipress_', '', $slug );
            break;
        default:
            foreach( gamipress_ai_assistant_trigger_slugs_replacements() as $replacement )
                $slug = str_replace( $replacement[1], $replacement[0], $slug );
            break;
    }

    return $slug;

}

/**
 * Get requirements ability properties
 *
 * @since   1.0.0
 *
 * @return array
 */
function gamipress_ai_assistant_get_requirements_properties( $post_type = '' ) {

    $properties = array(
        'trigger' => array(
            'description' => esc_html__( 'The trigger, event, activity, step or requirement to meet this requirement.', 'gamipress' ),
            'type' => 'string',
            'enum' => gamipress_ai_assistant_get_triggers_slugs(),
        ),
        'times' => array(
            'description' => esc_html__( 'The number of times required to perform the trigger.', 'gamipress' ),
            'type' => 'integer',
            'default' => '1',
        ),

        // Points required
        'points_condition' => array(
            'description' => esc_html__( 'The points condition.', 'gamipress' )
                . esc_html__( 'Optional, only if requirement is related to points.', 'gamipress' ),
            'type' => 'string',
            'enum' => array_keys( gamipress_number_condition_options() ),
        ),
        'points_required' => array(
            'description' => esc_html__( 'The points amount.', 'gamipress' )
                . esc_html__( 'Optional, only if requirement is related to points.', 'gamipress' ),
            'type' => 'integer',
        ),
        'points_type_required' => array(
            'description' => esc_html__( 'The points type.', 'gamipress' )
                . esc_html__( 'Optional, only if requirement is related to points.', 'gamipress' ),
            'type' => 'string',
//            'enum' => gamipress_get_points_types_slugs(),
        ),

        // Post/type required
        'post_required' => array(
            'description' => esc_html__( 'ID or title of the required post.', 'gamipress' )
                . sprintf( esc_html__( 'Optional, only if requirement is related to a specific %s.', 'gamipress' ), implode( ', ', gamipress_get_post_types_slugs() ) ) ,
            'type' => 'string',
        ),
        'post_type_required' => array(
            'description' => esc_html__( 'The post type.', 'gamipress' )
                . sprintf( esc_html__( 'Optional, only if requirement is related to a specific %s.', 'gamipress' ), implode( ', ', gamipress_get_post_types_slugs() ) ) ,
            'type' => 'string',
            'enum' => gamipress_get_post_types_slugs(),
        ),
    );

    switch( $post_type ) {
        case 'points-award':
            $properties['points'] = array(
                'description' => esc_html__( 'The points amount to award.', 'gamipress' )
                    . esc_html__( 'Required.', 'gamipress' ),
                'type' => 'integer',
                'default' => '1',
            );
            // Comes from the parent!
            // $properties['points_type']
            $properties['maximum_earnings'] = array(
                'description' => esc_html__( 'The maximum number of times the points can be awarded.', 'gamipress' ),
                'type' => 'integer',
                'default' => '1',
            );
            break;
        case 'points-deduct':
            $properties['points'] = array(
                'description' => esc_html__( 'The points amount to deduct.', 'gamipress' )
                    . esc_html__( 'Required.', 'gamipress' ),
                'type' => 'integer',
                'default' => '1',
            );
            // Comes from the parent!
            // $properties['points_type']
            $properties['maximum_earnings'] = array(
                'description' => esc_html__( 'The maximum number of times the points can be deducted.', 'gamipress' ),
                'type' => 'integer',
                'default' => '1',
            );
            break;
    }

    return $properties;

}

/**
 * Process requirements
 *
 * @since   1.0.0
 */
function gamipress_ai_assistant_process_requirements( $requirements = array(), $parent_id = 0, $post_type = '' ) {

    // Used to access ability helper functions
    $ability = new GamiPress_AI_Assistant_Ability();
    $parent_type = gamipress_get_post_type( $parent_id );

    // Our types
    $points_type_slugs = gamipress_get_points_types_slugs();
    $achievement_type_slugs = gamipress_get_achievement_types_slugs();
    $rank_type_slugs = gamipress_get_rank_types_slugs();
    $post_type_slugs = gamipress_get_post_types_slugs();

    $updated_requirements = array();

    // Requirements
    if( is_array( $requirements ) && count( $requirements ) ) {

        $triggers = gamipress_ai_assistant_get_triggers_slugs( true );

        foreach( $requirements as $i => $r ) {

            $r = wp_parse_args( $r, array(
                'trigger' => '',
                'times' => 1,
            ) );

            $r['trigger'] = gamipress_ai_assistant_restore_slug( sanitize_key( trim( $r['trigger'] ) ) );

            // Bail if not is a valid trigger
            if( ! in_array( $r['trigger'], $triggers ) ) {

                $r['trigger'] = 'gamipress_' . $r['trigger'];

                if( ! in_array( $r['trigger'], $triggers ) ) continue;
            }

            $requirement_data = array(
                'post_type'   => $post_type,
                'post_status' => 'publish',
                'post_parent' => $parent_id,
                'menu_order'  => $i,
            );

            $requirement_id = wp_insert_post( $requirement_data );

            // Couldn't insert the requirement
            if( is_wp_error( $requirement_id ) ) continue;

            $data = array();
            $data['ID'] = $requirement_id;
            $data['status'] = 'publish';
            $data['trigger_type'] = $r['trigger'];
            $data['count'] = absint( $r['times'] );

            $data['title'] = '';
            $data['achievement_type'] = '';
            $data['achievement_post'] = 0;
            $data['achievement_post_site_id'] = '';
            $data['maximum_earnings'] = 1;

            // Points award/deduct
            if( in_array( $post_type, array( 'points-award', 'points-deduct' ) ) ) {
                $data['points'] = isset( $r['points'] ) ? absint( $r['points'] ) : 1;
                $data['points_type'] = $parent_type;
                $data['maximum_earnings'] = isset( $r['maximum_earnings'] ) ? absint( $r['maximum_earnings'] ) : 1;
            }

            // Points required
            if( isset( $r['points_condition'] )
                || isset( $r['points_required'] )
                || isset( $r['points_type_required'] ) ) {

                // Ensure the array keys to prevent PHP warnings
                $r = wp_parse_args( $r, array(
                    'points_condition' => 'greater_or_equal',
                    'points_required' => 1,
                    'points_type_required' => '',
                ) );

                $options = array_keys( gamipress_number_condition_options() );
                $data['points_condition'] = gamipress_validate_from_array( $r['points_condition'], $options, 'greater_or_equal' );
                $data['points_required'] = absint( $r['points_required'] );
                $data['points_type_required'] = gamipress_validate_from_array( $r['points_type_required'], $points_type_slugs, '' );

            }

            // Post type
            if( isset( $r['post_type_required'] ) ) {

                switch ( $r['trigger'] ) {
                    // post_type_required > achievement_type
                    case 'any-achievement':
                    case 'all-achievements':
                    case 'revoke-any-achievement':
                        $data['achievement_type'] = gamipress_validate_from_array( $r['post_type_required'], $achievement_type_slugs, '' );
                        break;
                    // post_type_required > rank_type_required
                    case 'earn-rank':
                    case 'revoke-rank':
                        $data['rank_type_required'] = gamipress_validate_from_array( $r['post_type_required'], $rank_type_slugs, '' );
                        break;
                    // post_type_required
                    default:
                        $data['post_type_required'] = gamipress_validate_from_array( $r['post_type_required'], $post_type_slugs, '' );
                        break;
                }

            }

            // Post
            if( isset( $r['post_required'] ) ) {

                if( ! isset( $r['post_type_required'] ) ) $r['post_type_required'] = '';

                switch ( $r['trigger'] ) {
                    // post_required > achievement_post
                    case 'specific-achievement':
                    case 'revoke-specific-achievement':
                        $post = $ability->get_post( $r['post_required'], array(
                            'post_type' => gamipress_validate_from_array( $r['post_type_required'], $achievement_type_slugs, '' )
                        ) );

                        if( ! $ability->is_response_error( $post ) )
                            $data['achievement_post'] = $post->ID;

                        break;
                    // post_required > rank_required
                    case 'earn-rank':
                    case 'revoke-rank':
                        $post = $ability->get_post( $r['post_required'], array(
                            'post_type' => gamipress_validate_from_array( $r['post_type_required'], $rank_type_slugs, '' )
                        ) );

                        if( ! $ability->is_response_error( $post ) )
                            $data['rank_required'] = $post->ID;

                        break;
                    // post_required
                    default:
                        $post = $ability->get_post( $r['post_required'], array(
                            'post_type' => gamipress_validate_from_array( $r['post_type_required'], $post_type_slugs, '' )
                        ) );

                        if( ! $ability->is_response_error( $post ) )
                            $data['achievement_post'] = $post->ID;

                        break;
                }

            }

            $updated_requirements[] = gamipress_update_requirement( $data, $i );
        }
    }

    return $updated_requirements;

}

/**
 * Get requirements answer
 * 
 * @param array $requirements
 *
 * @since   1.0.0
 */
function gamipress_ai_assistant_get_requirements_answer( $requirements = array() ) {

    // Bail if not requirements
    if ( empty( $requirements ) ) {
        return '';
    }

    $requirements_answer = "\n";

    foreach( $requirements as $requirement ) {
        
        $title = $requirement['title'];
        $requirements_answer .= '- ' . esc_html( $title ) . "\n";
        
    }

    $requirements_answer .= "\n";

    return $requirements_answer;
}