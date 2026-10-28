<?php
/**
 * Add Points Deducts Ability
 *
 * @package     GamiPress\AI_Assistant\Ability\Add_Points_Deducts
 * @author      GamiPress <contact@gamipress.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class GamiPress_AI_Assistant_Ability_Add_Points_Deducts extends GamiPress_AI_Assistant_Ability {

    public $ability = 'add-points-deducts';

    /**
     * Register the ability
     *
     * @since 1.0.0
     */
    public function register() {

        $this->args = array(
            'label'               => esc_html__( 'Add points deducts to a points type in GamiPress', 'gamipress' ),
            'description'         => esc_html__( 'Design and insert a new points deduct into the database.', 'gamipress' ),
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'points_type' => array(
                        'description' => __( 'The GamiPress points type.', 'gamipress' ),
                        'type' => 'string',
                        //'enum' => gamipress_get_points_types_slugs(),
                    ),
                    'points_deducts' => array(
                        'description' => esc_html__( 'List of points deducts or rules for the points type. Define the criteria to meet to earn points of this type.', 'gamipress' ),
                        'type'        => 'array',
                        'items'       => array(
                            'type' => 'object',
                            'properties' => gamipress_ai_assistant_get_requirements_properties( 'points-deduct' ),
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
            'points_deducts' => array(),
        ) );

        $points_type = sanitize_text_field( $args['points_type'] );
        $points_deducts = $args['points_deducts'];

        // Check the points type
        $type = gamipress_get_points_type( $points_type );

        if( ! $type )
            return $this->response_error( sprintf( __( 'I couldn\'t find the points type "%s".', 'gamipress' ), $points_type ) );

        // Requirements
        $updated_deducts = gamipress_ai_assistant_process_requirements(
            $points_deducts,
            $type['ID'],
            'points-deduct'
        );

        $deducts_count = count( $updated_deducts );
        $deducts_label = _n( 'deduct', 'deducts', $deducts_count, 'gamipress' );
        $deducts_answer = gamipress_ai_assistant_get_requirements_answer( $updated_deducts );

        // Final response
        $post_link = get_edit_post_link( $type['ID'] );
        $post_display = $type['plural_name'];

        if( $post_link !== null ) {
            $post_display = '[' . $post_display . '](' . $post_link . ')';
        }

        // translators: %1$s: Deduct label. %2$s: Point type name
        $message = sprintf( __( 'I added the following points %1$s to %2$s:', 'gamipress' ), $deducts_label, $post_display );
        $message .= $deducts_answer;

        return $this->response_success( $message );

    }

}
new GamiPress_AI_Assistant_Ability_Add_Points_Deducts();