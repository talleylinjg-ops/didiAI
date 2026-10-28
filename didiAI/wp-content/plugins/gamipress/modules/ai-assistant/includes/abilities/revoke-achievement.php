<?php
/**
 * Revoke Achievement Ability
 *
 * @package     GamiPress\AI_Assistant\Ability\Revoke_Achievement
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability_Revoke_Achievement extends GamiPress_AI_Assistant_Ability {

    public $ability = 'revoke-achievement';

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register() {

        $this->args = array(
            'label'               => __( 'Revoke an achievement in GamiPress', 'gamipress' ),
            'description'         => __( 'Revokes a specific achievement to a user. Requires the user, the achievement type, and the achievement to award.', 'gamipress' ),
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'user' => array(
                        'type'        => 'string',
                        'description' => __( 'The ID, email or name of the user who will receive the points.', 'gamipress' ),
                    ),
                    'achievement_type' => array(
                        'description' => __( 'The GamiPress achievement type.', 'gamipress' ),
                        'type' => 'string',
                        //'enum' => gamipress_get_achievement_types_slugs(),
                    ),
                    'achievement' => array(
                        'description' => __( 'The achievement to award. Get the achievement ID.', 'gamipress' ),
                        'type'        => 'string',
                    ),
                ),
                'required' => array( 'user', 'achievement_type', 'achievement' ),
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
            'achievement_type' => '',
            'achievement' => '',
        ) );

        $user_search = sanitize_text_field( $args['user'] );
        $achievement_type = sanitize_text_field( $args['achievement_type'] );
        $achievement_search = sanitize_text_field( $args['achievement'] );

        // Check the user
        $user = $this->get_user( $user_search );
        if( $this->is_response_error( $user ) ) return $user;

        // Check the achievements type
        $type = gamipress_get_achievement_type( $achievement_type );
        if( ! $type )
            return $this->response_error( sprintf( __( 'I couldn\'t find the achievement type "%s".', 'gamipress' ), $achievement_type ) );

        // Check the post
        $achievement = $this->get_post( $achievement_search, array( 'post_type' => $type['slug'] ) );
        
        if( $this->is_response_error( $achievement ) ) return $achievement;

        // Revoke the achievement to the user
        gamipress_revoke_achievement_to_user( $achievement->ID, $user->ID );

        $user_display = $user->display_name;

        if ( current_user_can( 'edit_users' ) ) {
            $user_display = '[' . $user_display . '](' . get_edit_user_link( $user->ID ) . ')';
        }

        $post_display = $achievement->post_title;

        if ( current_user_can( 'edit_post', $achievement->ID ) ) {
            $post_display = '[' . $achievement->post_title . '](' . get_edit_post_link( $achievement->ID ) . ')';
        }

        $message = sprintf( __( 'I revoked %s to %s.', 'gamipress' ), $post_display, $user_display );

        return $this->response_success( $message );

    }

}
new GamiPress_AI_Assistant_Ability_Revoke_Achievement();