<?php
/**
 * Scripts
 *
 * @package     GamiPress\AI_Assistant\Scripts
 * @since       1.0.0
 * @version     1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function gamipress_ai_assistant_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'gamipress-ai-assistant-css', GAMIPRESS_AI_ASSISTANT_URL . 'assets/css/gamipress-ai-assistant' . $suffix . '.css', array( ), GAMIPRESS_AI_ASSISTANT_VER, 'all' );

    // Scripts
    wp_register_script( 'gamipress-ai-assistant-showdown-js', GAMIPRESS_AI_ASSISTANT_URL . 'assets/libs/showdown.min.js', array(), GAMIPRESS_AI_ASSISTANT_VER, true );
    wp_register_script( 'gamipress-ai-assistant-js', GAMIPRESS_AI_ASSISTANT_URL . 'assets/js/gamipress-ai-assistant' . $suffix . '.js', array( 'jquery', 'gamipress-ai-assistant-showdown-js' ), GAMIPRESS_AI_ASSISTANT_VER, true );

}
add_action( 'admin_init', 'gamipress_ai_assistant_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 *
 * @param string $hook
 *
 * @return      void
 */
function gamipress_ai_assistant_admin_enqueue_scripts( $hook ) {

    if( ! gamipress_ai_assistant_is_valid_page() ) return;

    // Stylesheets
    wp_enqueue_style( 'gamipress-ai-assistant-css' );

    // Scripts (libs)
    wp_enqueue_script( 'gamipress-ai-assistant-showdown-js' );

    // Localize scripts
    wp_localize_script( 'gamipress-ai-assistant-js', 'gamipress_ai_assistant', array(
        'nonce' => gamipress_get_admin_nonce(),
        'i18n'                  => array(
            'first_message' => __( 'Hi! What can I help you with today?', 'gamipress' ),
            'loading'       => __( 'Thinking', 'gamipress' ),
            'error_message' => __( 'Oops! Something went wrong. Please, try again.', 'gamipress' ),
        ),
    ) );

    // Scripts
    wp_enqueue_script( 'gamipress-ai-assistant-js' );

}
add_action( 'admin_enqueue_scripts', 'gamipress_ai_assistant_admin_enqueue_scripts' );