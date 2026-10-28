<?php
/**
 * Create Points Type Ability
 *
 * @package     GamiPress\AI_Assistant\Ability\Create_Points_Type
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability_Create_Points_Type extends GamiPress_AI_Assistant_Ability {

    public $ability = 'create-points-type';

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register() {

        $this->args = array(
            'label'               => esc_html__( 'Create a points type in GamiPress', 'gamipress' ),
            'description'         => esc_html__( 'Design and insert a new points type into the database.', 'gamipress' ),
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'singular_name' => array(
                        'description' => esc_html__( 'The points type singular name (e.g. "Gem", "Credit").', 'gamipress' ),
                        'type'        => 'string',
                    ),
                    'plural_name' => array(
                        'description' => esc_html__( 'The points type plural name (e.g. "Gems", "Credits").', 'gamipress' ),
                        'type'        => 'string',
                    ),
                    'slug' => array(
                        'description' => esc_html__( 'Optional. Custom slug or identifier. Derived automatically from the plural if omitted.', 'gamipress' ),
                        'type'        => 'string',
                    ),
                    'points_awards' => array(
                        'description' => esc_html__( 'List of points awards or rules for the points type. Define the criteria to meet to earn points of this type.', 'gamipress' ),
                        'type'        => 'array',
                        'items'       => array(
                            'type' => 'object',
                            'properties' => gamipress_ai_assistant_get_requirements_properties( 'points-award' ),
                        ),
                    ),
                    'points_deducts' => array(
                        'description' => esc_html__( 'List of points deducts or rules for the points type. Define the criteria to meet to lose points of this type.', 'gamipress' ),
                        'type'        => 'array',
                        'items'       => array(
                            'type' => 'object',
                            'properties' => gamipress_ai_assistant_get_requirements_properties( 'points-deduct' ),
                        ),
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
            'points_awards'     => array(),
            'points_deducts'    => array(),
        ) );

        $singular_name = sanitize_text_field( $args['singular_name'] );
        $plural_name = sanitize_text_field( $args['plural_name'] );
        $slug = gamipress_sanitize_slug( $args['slug'] );
        $points_awards = $args['points_awards'];
        $points_deducts = $args['points_deducts'];

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
            'post_type'   =>'points-type',
            'post_status' => 'publish',
            'post_title'  => $singular_name,
            'post_name'   => $slug,
        );

        $post_id = wp_insert_post( $post_data, true );

        if( is_wp_error( $post_id ) ) {
            // translators: %1$s: Points type singular %2$s: Error messages
            return $this->response_error( sprintf( __( 'I couldn\'t create the %1$s. Reason: %2$s.', 'gamipress' ), $singular_name, $post_id->get_error_message() ) );
        }

        // Update plural
        gamipress_update_post_meta( $post_id, '_gamipress_plural_name', $plural_name );

        // Register the type (needed if there are more ability calls)
        $post = get_post( $post_id ); // Post name can be different, so this is required

        gamipress_register_points_type( $post_id, $post->post_title, $plural_name, $post->post_name );

        // Requirements
        $updated_awards = gamipress_ai_assistant_process_requirements(
            $points_awards,
            $post_id,
            'points-award'
        );

        $updated_deducts = gamipress_ai_assistant_process_requirements(
            $points_deducts,
            $post_id,
            'points-deduct'
        );

        $awards_count = count( $updated_awards );
        $deducts_count = count( $updated_deducts );

        $awards_answer = gamipress_ai_assistant_get_requirements_answer( $updated_awards );
        $deducts_answer = gamipress_ai_assistant_get_requirements_answer( $updated_deducts );

        $awards_title = _n( 'Award', 'Awards', $awards_count, 'gamipress' );
        $deducts_title = _n( 'Deduct', 'Deducts', $deducts_count, 'gamipress' );

        if ( current_user_can( 'edit_post', $post_id ) ) {
            $post_display = '[' . $singular_name . '](' . get_edit_post_link( $post_id ) . ')';
        } else {
            $post_display = $singular_name;
        }

        if( $awards_count > 0 && $deducts_count > 0 ) {
            $message = sprintf( __( 'I created the %1$s points type with:', 'gamipress' ), $post_display );
            $message .= "\n";
            $message .= '####' . $awards_title . "\n";
            $message .= $awards_answer;
            $message .= '####' . $deducts_title . "\n";
            $message .= $deducts_answer;
        } else if( $awards_count > 0 && $deducts_count === 0 ) {
            $message = sprintf( __( 'I created the %1$s points type with:', 'gamipress' ), $post_display );
            $message .= "\n";
            $message .= '####' . $awards_title . "\n";
            $message .= $awards_answer;
        } else if( $awards_count === 0 && $deducts_count > 0 ) {
            $message = sprintf( __( 'I created the %1$s points type with:', 'gamipress' ), $post_display );
            $message .= "\n";
            $message .= '####' . $deducts_title . "\n";
            $message .= $deducts_answer;
        } else {
            $message = sprintf( __( 'I created the %s points type.', 'gamipress' ), $post_display );
        }

        return $this->response_success( $message );

    }

}
new GamiPress_AI_Assistant_Ability_Create_Points_Type();