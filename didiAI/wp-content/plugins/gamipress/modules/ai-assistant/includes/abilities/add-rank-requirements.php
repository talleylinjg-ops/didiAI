<?php
/**
 * Add Rank Requirements Ability
 *
 * @package     GamiPress\AI_Assistant\Ability\Add_Rank_Requirements
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability_Add_Rank_Requirements extends GamiPress_AI_Assistant_Ability {

    public $ability = 'add-rank-requirements';

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register() {

        $this->args = array(
            'label'               => esc_html__( 'Add requirements to a rank in GamiPress', 'gamipress' ),
            'description'         => esc_html__( 'Design and insert new rank requirements.', 'gamipress' ),
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'rank' => array(
                        'description' => esc_html__( 'The rank ID, title or slug.', 'gamipress' ),
                        'type'        => 'string',
                    ),
                    'requirements' => array(
                        'description' => esc_html__( 'List of achievement steps, requirements or triggers. Define the criteria to meet to reach that rank.', 'gamipress' ),
                        'type'        => 'array',
                        'items'       => array(
                            'type' => 'object',
                            'properties' => gamipress_ai_assistant_get_requirements_properties( 'rank-requirement' ),
                        ),
                    ),
                ),
                'required' => array( 'rank', 'requirements' ),
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
            'rank' => '',
            'requirements' => array(),
        ) );

        $rank = sanitize_text_field( $args['rank'] );
        $requirements = $args['requirements'];

        // Check the rank type
        $post = $this->get_post( $rank );
        if( $this->is_response_error( $post ) )
            return $post;

        if( ! gamipress_get_rank_type( $post->post_type ) )
            return $this->response_error( sprintf( __( 'The post %s is not a rank.', 'gamipress' ), '#' . $post->ID . ' - ' . $post->post_title ) );

        // Requirements
        $updated_requirements = gamipress_ai_assistant_process_requirements(
            $requirements,
            $post->ID,
            'rank-requirement'
        );

        $requirements_count = count( $updated_requirements );
        $requirements_label = _n( 'requirement', 'requirements', $requirements_count, 'gamipress' );
        $requirements_answer = gamipress_ai_assistant_get_requirements_answer( $updated_requirements );

        // Final response
        $post_link = get_edit_post_link( $post );
        $post_display = empty( $post->post_title ) ? esc_html__( '(no title)' ) : $post->post_title;

        if( $post_link !== null ) {
            $post_display = '[' . $post_display . '](' . $post_link . ')';
        }

        // translators: %1$s: Requirement label. %2$s: Rank name
        $message = sprintf( __( 'I added the following %1$s to %2$s:', 'gamipress' ), $requirements_label, $post_display );
        $message .= $requirements_answer;

        return $this->response_success( $message );

    }

}
new GamiPress_AI_Assistant_Ability_Add_Rank_Requirements();