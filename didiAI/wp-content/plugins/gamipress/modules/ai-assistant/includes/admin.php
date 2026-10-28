<?php
/**
 * Admin
 *
 * @package     GamiPress\AI_Assistant\Admin
 * @since       1.0.0
 * @version     1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AI Assistant settings
 *
 * @since 1.0.0
 *
 * @param array $settings
 *
 * @return array
 */
function gamipress_ai_assistant_settings_fields( $settings ) {

    $settings['disable_ai_assistant'] = array(
        'name' => __( 'Disable AI Assistant', 'gamipress' ),
        'tooltip'   => __( 'Disable the GamiPress AI Assistant.', 'gamipress' ),
        'label_cb' => 'cmb_tooltip_label_cb',
        'type' => 'checkbox',
        'classes' => 'gamipress-switch',
    );

    return $settings;
}
add_filter( 'gamipress_general_settings_fields', 'gamipress_ai_assistant_settings_fields' );