<?php
/**
 * Award Points Ability
 *
 * @package     GamiPress\AI_Assistant\Ability\Award_points
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability_Award_Points extends GamiPress_AI_Assistant_Ability {

    public $ability = 'award-points';

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register() {

        $this->args = array(
            'label'               => __( 'Award points in GamiPress', 'gamipress' ),
            'description'         => __( 'Awards a specified number of GamiPress points to a user. Requires the user, the points type, and the number of points to award.', 'gamipress' ),
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'user' => array(
                        'type'        => 'string',
                        'description' => __( 'The ID, email or name of the user who will receive the points.', 'gamipress' ),
                    ),
                    'points_type' => array(
                        'description' => __( 'The GamiPress points type.', 'gamipress' ),
                        'type' => 'string',
                        //'enum' => gamipress_get_points_types_slugs(),
                    ),
                    'points' => array(
                        'description' => __( 'The number of points to award. Must be a positive integer.', 'gamipress' ),
                        'type'        => 'integer',
                    ),
                    'user_earning' => array(
                        'description' => __( 'Decide whether to record the movement in the user earnings table. By default true unless the user specifies otherwise.', 'gamipress' ),
                        'type'        => 'boolean',
                        'default'     => 'true',
                    ),
                    'user_earning_title' => array(
                        'description' => __( 'Text for the user earning record.', 'gamipress' ),
                        'type'        => 'string',
                        'default'     => __( 'Manual balance adjustment', 'gamipress' ),
                    ),
                ),
                'required' => array( 'user', 'points_type', 'points' ),
                'additionalProperties' => false,
            ),
        );

    }

    /**
     * Ability execute callback
     *
     * @since 1.0.0
     *
     * @param array $args
     *
     * @return array array( 'success' => true|false, 'message' => '' )
     */
    public function execute( $args ) {

        $args = wp_parse_args( (array) $args, array(
            'user' => '',
            'points_type' => '',
            'points' => '',
            'user_earning' => true,
            'user_earning_title' => __( 'Manual balance adjustment', 'gamipress' ),
        ) );

        $user_search = sanitize_text_field( $args['user'] );
        $points_type = sanitize_text_field( $args['points_type'] );
        $points = (int) $args['points'];
        $user_earning = (bool) $args['user_earning'];
        $user_earning_title = sanitize_text_field( $args['user_earning_title'] );

        // Check the user
        $user = $this->get_user( $user_search );
        if( $this->is_response_error( $user ) ) return $user;

        // Check the points type
        $points_type_id = gamipress_get_points_type_id( $points_type );
        if( ! $points_type_id )
            return $this->response_error( sprintf( __( 'I couldn\'t find the points type "%s".', 'gamipress' ), $points_type ) );

        // Award the points to the user
        gamipress_award_points_to_user( $user->ID, $points, $points_type );

        if( $user_earning ) {
            // Insert the custom user earning for the manual balance adjustment
            $user_earning_id = gamipress_insert_user_earning( $user->ID, array(
                'title'	        => $user_earning_title,
                'user_id'	    => $user->ID,
                'post_id'	    => $points_type_id,
                'post_type' 	=> 'points-type',
                'points'	    => $points,
                'points_type'	=> $points_type,
                'date'	        => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
            ) );
        }

        $points_formatted = gamipress_format_points( $points, $points_type, false );

        $user_display = $user->display_name;

        if ( current_user_can( 'edit_users' ) ) {
            $user_display = '[' . $user_display . '](' . get_edit_user_link( $user->ID ) . ')';
        }

        if( $user_earning ) {
            // translators: %1$s: Points amount & label %2$s: User %3$s: Reason
            $message = sprintf( __( 'I awarded %1$s to %2$s and registered the earning with the reason "%3$s".', 'gamipress' ), $points_formatted, $user_display, $user_earning_title );
        } else {
            // translators: %1$s: Points amount & label %2$s: User
            $message = sprintf( __( 'I awarded %1$s to %2$s without registering in user earnings.', 'gamipress' ), $points_formatted, $user_display );
        }

        return $this->response_success( $message );

    }

}
new GamiPress_AI_Assistant_Ability_Award_Points();