<?php
/**
 * Add Points Awards Ability
 *
 * @package     GamiPress\AI_Assistant\Ability\Add_Points_Awards
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability_Add_Points_Awards extends GamiPress_AI_Assistant_Ability {

    public $ability = 'add-points-awards';

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register() {

        $this->args = array(
            'label'               => esc_html__( 'Add points awards to a points type in GamiPress', 'gamipress' ),
            'description'         => esc_html__( 'Design and insert a new points award into the database.', 'gamipress' ),
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'points_type' => array(
                        'description' => __( 'The GamiPress points type.', 'gamipress' ),
                        'type' => 'string',
                        //'enum' => gamipress_get_points_types_slugs(),
                    ),
                    'points_awards' => array(
                        'description' => esc_html__( 'List of points awards or rules for the points type. Define the criteria to meet to earn points of this type.', 'gamipress' ),
                        'type'        => 'array',
                        'items'       => array(
                            'type' => 'object',
                            'properties' => gamipress_ai_assistant_get_requirements_properties( 'points-award' ),
                        ),
                    ),
                ),
                'required' => array( 'points_type' ),
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
            'points_type' => '',
            'points_awards' => array(),
        ) );

        $points_type = sanitize_text_field( $args['points_type'] );
        $points_awards = $args['points_awards'];

        // Check the points type
        $type = gamipress_get_points_type( $points_type );

        if( ! $type )
            return $this->response_error( sprintf( __( 'I couldn\'t find the points type "%s".', 'gamipress' ), $points_type ) );

        // Requirements
        $updated_awards = gamipress_ai_assistant_process_requirements(
            $points_awards,
            $type['ID'],
            'points-award'
        );

        $awards_count = count( $updated_awards );
        $awards_label = _n( 'award', 'awards', $awards_count, 'gamipress' );
        $awards_answer = gamipress_ai_assistant_get_requirements_answer( $updated_awards );

        // Final response
        $post_link = get_edit_post_link( $type['ID'] );
        $post_display = $type['plural_name'];

        if( $post_link !== null ) {
            $post_display = '[' . $post_display . '](' . $post_link . ')';
        }

        // translators: %1$s: Award label. %2$s: Point type name
        $message = sprintf( __( 'I added the following points %1$s to %2$s:', 'gamipress' ), $awards_label, $post_display );
        $message .= $awards_answer;

        return $this->response_success( $message );

    }

}
new GamiPress_AI_Assistant_Ability_Add_Points_Awards();