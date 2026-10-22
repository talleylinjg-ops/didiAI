<?php
/**
 * Abilities
 *
 * @package     GamiPress\AI_Assistant\Abilities
 * @since       1.0.0
 * @version     1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get the system instructions
 *
 * @since 1.0.0
 */
function gamipress_ai_assistant_get_system_instructions() {

    $points_types = gamipress_get_points_types_slugs();
    $achievement_types = gamipress_get_achievement_types_slugs();
    $rank_types = gamipress_get_rank_types_slugs();

    $system_instructions = array(
        "You are an AI assistant for GamiPress.",
        "Be helpful, precise, and professional.",
        "You have strict catalog restrictions due to ability parameters.",
        "Do not guess slugs for points, achievements or ranks.",
        "The types available in this site are:",
        "Points types: " . implode( ', ', $points_types )  . ".",
        "Achievement types: " . implode( ', ', $achievement_types )  . ".",
        "Rank types: " . implode( ', ', $rank_types )  . ".",
        "Do not guess slugs for triggers, activities or events when configuring requirements.",
        "Always use the listed abilities to perform tasks instead of guessing or pretending.",
        "Do not generate a text response after using abilities. They already return one.",
    );

    /**
     * Filter to override the system instructions
     *
     * @since 1.0.0
     *
     * @param string $system_instructions
     *
     * @return string
     */
    return apply_filters( 'gamipress_ai_assistant_get_system_instructions', implode( ' ', $system_instructions ) );
}

/**
 * Register abilities category
 *
 * @since 1.0.0
 */
function gamipress_ai_assistant_register_category() {
    if ( ! function_exists( 'wp_register_ability_category' ) ) return;

    wp_register_ability_category( 'gamipress', array(
        'label'       => __( 'GamiPress', 'gamipress' ),
        'description' => __( 'Ability to manage, create and design GamiPress elements such as points, achievements and ranks using AI.', 'gamipress' ),
    ) );

}
add_action( 'wp_abilities_api_categories_init', 'gamipress_ai_assistant_register_category' );

/**
 * Get abilities
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_ai_assistant_get_abilities() {

    $registered = wp_get_abilities();
    $categories = array();

    foreach( $registered as $ability => $object ) {
        if( strpos( $ability, 'gamipress' ) !== false ) {
            $categories[$ability] = $object;
        }
    }

    return $categories;
}

/**
 * Get abilities slugs
 *
 * @since 1.0.0
 *
 * @return array
 */
function gamipress_ai_assistant_get_abilities_slugs() {
    return array_keys( gamipress_ai_assistant_get_abilities() );
}
