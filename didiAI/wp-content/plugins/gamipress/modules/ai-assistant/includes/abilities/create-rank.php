<?php
/**
 * Create Rank Ability
 *
 * @package     GamiPress\AI_Assistant\Ability\Create_Rank
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability_Create_Rank extends GamiPress_AI_Assistant_Ability {

    public $ability = 'create-rank';

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register() {

        $this->args = array(
            'label'               => esc_html__( 'Create a rank in GamiPress', 'gamipress' ),
            'description'         => esc_html__( 'Design and insert a new rank of a type into the database.', 'gamipress' ),
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'title' => array(
                        'description' => esc_html__( 'A clean, professional, and descriptive title for the rank.', 'gamipress' ),
                        'type'        => 'string',
                    ),
                    'description' => array(
                        'description' => esc_html__( 'A clean, professional, and descriptive description for the rank.', 'gamipress' ),
                        'type'        => 'string',
                    ),
                    'rank_type' => array(
                        'description' => esc_html__( 'The GamiPress rank type.', 'gamipress' ),
                        'type' => 'string',
                        //'enum' => gamipress_get_rank_types_slugs(),
                    ),
                    'requirements' => array(
                        'description' => esc_html__( 'List of achievement steps, requirements or triggers. Define the criteria to meet to earn that rank.', 'gamipress' ),
                        'type'        => 'array',
                        'items'       => array(
                            'type' => 'object',
                            'properties' => gamipress_ai_assistant_get_requirements_properties( 'rank-requirement' ),
                        ),
                    ),
                ),
                'required' => array( 'title', 'rank_type' ),
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

        global $wpdb;

        $args = wp_parse_args( (array) $args, array(
            'title' => '',
            'description' => '',
            'rank_type' => '',
            'requirements' => array(),
        ) );

        $title = sanitize_text_field( $args['title'] );
        $description = sanitize_text_field( $args['description'] );
        $rank_type = sanitize_text_field( $args['rank_type'] );
        $requirements = $args['requirements'];

        // Check the rank type
        $type = gamipress_get_rank_type( $rank_type );
        if( ! $type )
            return $this->response_error( sprintf( __( 'I couldn\'t find the rank type "%s".', 'gamipress' ), $rank_type ) );

        // Rank position
        $posts = GamiPress()->db->posts;
        $position = absint( $wpdb->get_var( $wpdb->prepare(
            "SELECT p.menu_order
			FROM {$posts} AS p
			WHERE p.post_type = %s
			ORDER BY menu_order DESC
			LIMIT 1",
            $rank_type,
        ) ) );

        $position += 1;

        // Make description block compatible
        $description = '<!-- wp:paragraph -->' . $description . '<!-- /wp:paragraph -->';

        // Insert post
        $post_data = array(
            'post_title' => $title,
            'post_content' => $description,
            'post_type' => $rank_type,
            'menu_order' => $position,
            'post_status' => 'draft', // TODO: FUTURE: Add optional input schema to manage status, for the moment, draft to prevent published unwanted results
        );

        $post_id = wp_insert_post( $post_data, true );

        if( is_wp_error( $post_id ) ) {
            // translators: %1$s: Rank type singular %2$s: Error messages
            return $this->response_error( sprintf( __( 'I couldn\'t create the %1$s. Reason: %2$s.', 'gamipress' ), $type['singular_name'], $post_id->get_error_message() ) );
        }

        // Requirements
        $updated_requirements = gamipress_ai_assistant_process_requirements(
            $requirements,
            $post_id,
            'rank-requirement'
        );

        $requirements_count = count( $updated_requirements );

        $requirements_answer = gamipress_ai_assistant_get_requirements_answer( $updated_requirements );

        // Final response
        $post = get_post( $post_id );
        $post_link = get_edit_post_link( $post );
        $post_display = empty( $post->post_title ) ? esc_html__( '(no title)' ) : $post->post_title;

        if( $post_link !== null ) {
            $post_display = '[' . $post_display . '](' . $post_link . ')';
        }

        if ( $requirements_count > 0 ) {
            $message = sprintf( __( 'I created the %1$s %2$s that requires:', 'gamipress' ), $type['singular_name'], $post_display );
            $message .= $requirements_answer;
        } else{
            // translators: %1$s: Rank type singular %2$s: Rank name
            $message = sprintf( __( 'I created the %1$s %2$s.', 'gamipress' ), $type['singular_name'], $post_display );
        }

        return $this->response_success( $message );

    }

}
new GamiPress_AI_Assistant_Ability_Create_Rank();