<?php
/**
 * Award Rank Ability
 *
 * @package     GamiPress\AI_Assistant\Ability\Award_Rank
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability_Award_Rank extends GamiPress_AI_Assistant_Ability {

    public $ability = 'award-rank';

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register() {

        $this->args = array(
            'label'               => __( 'Award a rank in GamiPress', 'gamipress' ),
            'description'         => __( 'Awards a specific rank to a user. Requires the user, the rank type, and the rank to award.', 'gamipress' ),
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'user' => array(
                        'type'        => 'string',
                        'description' => __( 'The ID, email or name of the user who will receive the points.', 'gamipress' ),
                    ),
                    'rank_type' => array(
                        'description' => __( 'The GamiPress rank type.', 'gamipress' ),
                        'type' => 'string',
                        //'enum' => gamipress_get_rank_types_slugs(),
                    ),
                    'rank' => array(
                        'description' => __( 'The rank to award. Get the rank ID.', 'gamipress' ),
                        'type'        => 'string',
                    ),
                ),
                'required' => array( 'user', 'rank_type', 'rank' ),
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
            'rank_type' => '',
            'rank' => '',
        ) );

        $user_search = sanitize_text_field( $args['user'] );
        $rank_type = sanitize_text_field( $args['rank_type'] );
        $rank_search = sanitize_text_field( $args['rank'] );

        // Check the user
        $user = $this->get_user( $user_search );
        if( $this->is_response_error( $user ) ) return $user;

        // Check the ranks type
        $type = gamipress_get_rank_type( $rank_type );
        if( ! $type )
            return $this->response_error( sprintf( __( 'I couldn\'t find the rank type "%s".', 'gamipress' ), $rank_type ) );

        // Check the post
        $rank = $this->get_post( $rank_search, array( 'post_type' => $type['slug'] ) );
        if( $this->is_response_error( $rank ) ) return $rank;

        // Award the rank to the user
        gamipress_update_user_rank( $user->ID, $rank->ID );

        $user_display = $user->display_name;

        if ( current_user_can( 'edit_users' ) ) {
            $user_display = '[' . $user_display . '](' . get_edit_user_link( $user->ID ) . ')';
        }

        $post_display = $rank->post_title;

        if ( current_user_can( 'edit_post', $rank->ID ) ) {
            $post_display = '[' . $rank->post_title . '](' . get_edit_post_link( $rank->ID ) . ')';
        }

        $url_credential = '[' . __( 'credential', 'gamipress' ) . '](' . gamipress_get_credential_url( $rank->ID, $user->ID ) . ')';

        $message = sprintf( __( 'I awarded %s to %s. View the %s.', 'gamipress' ), $post_display, $user_display, $url_credential );

        return $this->response_success( $message );

    }

}
new GamiPress_AI_Assistant_Ability_Award_Rank();