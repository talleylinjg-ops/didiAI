<?php
/**
 * Create Rank Type Ability
 *
 * @package     GamiPress\AI_Assistant\Ability\Create_Rank_Type
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability_Create_Rank_Type extends GamiPress_AI_Assistant_Ability {

    public $ability = 'create-rank-type';

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register() {

        $this->args = array(
            'label'               => esc_html__( 'Create a rank type in GamiPress', 'gamipress' ),
            'description'         => esc_html__( 'Design and insert a new rank type into the database.', 'gamipress' ),
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'singular_name' => array(
                        'description' => esc_html__( 'The rank type singular name (e.g. "Gem", "Credit").', 'gamipress' ),
                        'type'        => 'string',
                    ),
                    'plural_name' => array(
                        'description' => esc_html__( 'The rank type plural name (e.g. "Gems", "Credits").', 'gamipress' ),
                        'type'        => 'string',
                    ),
                    'slug' => array(
                        'description' => esc_html__( 'Optional. Custom slug or identifier. Derived automatically from the plural if omitted.', 'gamipress' ),
                        'type'        => 'string',
                    ),
                ),
                'required' => array( 'singular_name', 'plural_name' ),
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
            'singular_name'     => '',
            'plural_name'       => '',
            'slug'              => '',
        ) );

        $singular_name = sanitize_text_field( $args['singular_name'] );
        $plural_name = sanitize_text_field( $args['plural_name'] );
        $slug = gamipress_sanitize_slug( $args['slug'] );
        
        // If only plural provided
        if ( empty( $singular_name ) && ! empty( $plural_name ) ) {
            $singular_name = $plural_name;
        }

        // If not plural provided
        if ( empty( $plural_name ) ) {
            $plural_name = $singular_name . 's';
        }

        // If not slug provided
        if ( empty( $slug ) ) {
            $slug = gamipress_sanitize_slug( $plural_name );
        }
        
        // Insert post
        $post_data = array(
            'post_type'   => 'rank-type',
            'post_status' => 'publish',
            'post_title'  => $singular_name,
            'post_name'   => $slug,
        );

        $post_id = wp_insert_post( $post_data, true );

        if( is_wp_error( $post_id ) ) {
            // translators: %1$s: Rank type singular %2$s: Error messages
            return $this->response_error( sprintf( __( 'I couldn\'t create the %1$s. Reason: %2$s.', 'gamipress' ), $singular_name, $post_id->get_error_message() ) );
        }

        if ( current_user_can( 'edit_post', $post_id ) ) {
            $post_display = '[' . $singular_name . '](' . get_edit_post_link( $post_id ) . ')';
        } else {
            $post_display = $singular_name;
        }

        // Update plural
        gamipress_update_post_meta( $post_id, '_gamipress_plural_name', $plural_name );

        $message = sprintf( __( 'I created the %s rank type.', 'gamipress' ), $post_display );

        return $this->response_success( $message );

    }

}
new GamiPress_AI_Assistant_Ability_Create_Rank_Type();